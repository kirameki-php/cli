<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

use Kirameki\Cli\CommandDefinition;
use Kirameki\Cli\Definitions\ArgumentDefinition;
use Kirameki\Cli\Definitions\OptionDefinition;
use Kirameki\Cli\Definitions\ParameterDefinition;
use Kirameki\Cli\Exceptions\ParseException;
use function array_is_list;
use function array_key_exists;
use function array_keys;
use function count;
use function explode;
use function gettype;
use function is_array;
use function is_string;
use function ltrim;
use function preg_match;
use function str_starts_with;
use function strlen;
use function substr;

class ParameterParser
{
    /**
     * @param CommandDefinition $definition
     * @param list<string> $parameters
     * @return array{
     *     arguments: array<string, Argument>,
     *     options: array<string, Option>,
     * }
     */
    public static function parse(CommandDefinition $definition, array $parameters): array
    {
        $self = new self($definition, $parameters);
        return $self->execute();
    }

    /**
     * @param CommandDefinition $definition
     * @param list<string> $parameters
     * @param int $argumentCursor
     * @param int $parameterCursor
     * @param array<string, list<string|null>> $argumentValues
     * @param array<string, list<string|null>> $optionValues
     */
    protected function __construct(
        protected CommandDefinition $definition,
        protected array $parameters,
        protected int $argumentCursor = 0,
        protected int $parameterCursor = 0,
        protected array $argumentValues = [],
        protected array $optionValues = [],
    )
    {
    }

    /**
     * @return array{
     *     arguments: array<string, Argument>,
     *     options: array<string, Option>,
     * }
     */
    public function execute(): array
    {
        $this->processParameters();

        return [
            'arguments' => $this->makeArguments(),
            'options' => $this->makeOptions(),
        ];
    }

    /**
     * @return string|null
     */
    protected function nextParameterOrNull(): ?string
    {
        return $this->parameters[$this->parameterCursor + 1] ?? null;
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function isLongOption(string $parameter): bool
    {
        return (bool) preg_match('/^--\w+/', $parameter);
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function isShortOption(string $parameter): bool
    {
        return (bool) preg_match('/^-\w+/', $parameter);
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function isNotOption(string $parameter): bool
    {
        return !str_starts_with($parameter, '-');
    }

    /**
     * @return void
     */
    protected function processParameters(): void
    {
        $parameterCount = count($this->parameters);
        $parameters = $this->parameters;
        while ($this->parameterCursor < $parameterCount) {
            $parameter = $parameters[$this->parameterCursor];
            match (true) {
                $this->isLongOption($parameter) => $this->processAsLongOption($parameter),
                $this->isShortOption($parameter) => $this->processAsShortOptions($parameter),
                default => $this->processAsArgument($parameter),
            };
        }
    }

    /**
     * @param string $parameter
     * @return void
     */
    protected function processAsLongOption(string $parameter): void
    {
        $parts = explode('=', $parameter, 2);
        $name = ltrim($parts[0], '-');
        $value = $parts[1] ?? null;
        $defined = $this->getDefinedOption($name);

        if ($defined->valueRequired) {
            // value given with =
            if ($value !== null) {
                $this->addOptionValue($defined, $value);
                $this->parameterCursor++;
                return;
            }

            // value might have been given as space,
            // look at the next parameter to check if that's the case.
            $nextParameter = $this->nextParameterOrNull();
            if ($nextParameter !== null && $this->isNotOption($nextParameter)) {
                $this->addOptionValue($defined, $nextParameter);
                $this->parameterCursor+= 2;
                return;
            }

            if ($defined->default === null) {
                throw new ParseException("Option: --{$name} requires a value.", [
                    'defined' => $defined,
                    'parameters' => $this->parameters,
                    'cursor' => $this->parameterCursor,
                ]);
            }
        }

        // no value should be given but is given.
        if ($value !== null) {
            throw new ParseException("Option: --{$name} no value expected but \"{$value}\" given.", [
                'defined' => $defined,
                'parameters' => $this->parameters,
                'cursor' => $this->parameterCursor,
            ]);
        }

        $this->addOptionValue($defined, null);
        $this->parameterCursor++;
    }

    /**
     * @param string $parameter
     * @return void
     */
    protected function processAsShortOptions(string $parameter): void
    {
        $chars = ltrim($parameter, '-');

        for ($i = 0, $size = strlen($chars); $i < $size; $i++) {
            $char = $chars[$i];
            $defined = $this->getDefinedOptionByShortName($char);

            if ($defined->valueRequired) {
                // Next char is not an option
                $nextChar = $chars[$i + 1] ?? '';

                // Has more chars
                if ($nextChar !== '') {
                    if ($this->definition->shortOptionExists($nextChar)) {
                        // Preceding char is an option so move on.
                    }
                    else {
                        // Preceding chars will be considered a value.
                        $remainingChars = substr($chars, $i + 1);
                        $this->addOptionValue($defined, $remainingChars);
                        $this->parameterCursor++;
                        return;
                    }
                }
                // No more chars left
                else {
                    // Look at the next parameter to check if value is given as string.
                    $nextParameter = $this->nextParameterOrNull();
                    if ($nextParameter !== null && $this->isNotOption($nextParameter)) {
                        $this->addOptionValue($defined, $nextParameter);
                        $this->parameterCursor+= 2;
                        return;
                    }
                }

                if ($defined->default === null) {
                    throw new ParseException("Option: -{$char} (--{$defined->name}) requires a value.", [
                        'defined' => $defined,
                        'parameters' => $this->parameters,
                        'cursor' => $this->parameterCursor,
                    ]);
                }
            }

            $this->addOptionValue($defined, null);
            $this->parameterCursor++;
        }
    }

    /**
     * @param string $parameter
     * @return void
     */
    protected function processAsArgument(string $parameter): void
    {
        $defined = $this->definition->getArgumentByIndexOrNull($this->argumentCursor);

        if ($defined === null) {
            throw new ParseException("Argument [{$this->argumentCursor}: \"{$parameter}\"] is not defined.", [
                'parameters' => $this->parameters,
                'cursor' => $this->argumentCursor,
            ]);
        }

        $this->argumentValues[$defined->name][] = $parameter;

        if (!$defined->allowMultiple) {
            $this->argumentCursor++;
        }

        $this->parameterCursor++;
    }

    /**
     * @return array<string, Argument>
     */
    protected function makeArguments(): array
    {
        $arguments = [];
        foreach ($this->definition->getArguments() as $name => $defined) {
            $enteredValues = $this->argumentValues[$name] ?? [];

            if (empty($enteredValues) && !$defined->optional) {
                throw new ParseException("Missing required argument: {$name}.", [
                    'parameters' => $this->parameters,
                    'defined' => $defined,
                ]);
            }

            $mergedValues = empty($enteredValues)
                ? $this->mergeDefaults($defined, [null])
                : $this->mergeDefaults($defined, $enteredValues);

            $arguments[$name] = new Argument($defined, $mergedValues, $enteredValues);
        }

        return $arguments;
    }

    /**
     * @return array<string, Option>
     */
    protected function makeOptions(): array
    {
        $options = [];
        foreach ($this->definition->getOptions() as $name => $defined) {
            $enteredValues = $this->optionValues[$name] ?? [];
            $mergedValues = $this->mergeDefaults($defined, $enteredValues);
            $options[$name] = new Option($defined, $mergedValues, $enteredValues);
        }
        return $options;
    }

    /**
     * @param string $name
     * @return OptionDefinition
     */
    protected function getDefinedOption(string $name): OptionDefinition
    {
        $defined = $this->definition->getOptionOrNull($name);

        if ($defined === null) {
            throw new ParseException("Option: --{$name} is not defined.", [
                'parameters' => $this->parameters,
                'name' => $name,
            ]);
        }

        return $defined;
    }

    /**
     * @param string $char
     * @return OptionDefinition
     */
    protected function getDefinedOptionByShortName(string $char): OptionDefinition
    {
        $defined = $this->definition->getOptionByShortOrNull($char);

        if ($defined === null) {
            throw new ParseException("Option: -{$char} is not defined.", [
                'parameters' => $this->parameters,
                'cursor' => $this->parameterCursor,
                'char' => $char,
            ]);
        }

        return $defined;
    }

    /**
     * @param OptionDefinition $defined
     * @param mixed $value
     * @return void
     */
    protected function addOptionValue(OptionDefinition $defined, mixed $value): void
    {
        $name = $defined->name;

        if (array_key_exists($name, $this->optionValues) && !$defined->allowMultiple) {
            throw new ParseException("Option: --{$name} cannot be entered more than once.", [
                'defined' => $defined,
                'parameters' => $this->parameters,
            ]);
        }

        $this->optionValues[$name][] = $value;
    }

    /**
     * @param ParameterDefinition $defined
     * @param list<string|null> $values
     * @return list<string>
     */
    protected function mergeDefaults(ParameterDefinition $defined, array $values): array
    {
        $default = $defined->default ?? '';

        if ($defined->allowMultiple) {
            if (is_string($default)) {
                foreach (array_keys($values) as $index) {
                    $values[$index] ??= $default;
                }
            }

            if (is_array($default)) {
                if (!array_is_list($default)) {
                    $this->throwParseException($defined, 'Default values must be list<string>, map given.');
                }

                foreach ($default as $index => $value) {
                    if (!is_string($value)) {
                        $type = gettype($value);
                        $this->throwParseException($defined, "Default values must consist of strings, {$type} given.");
                    }
                    $values[$index] ??= $value;
                }
            }
        } else {
            if (!is_string($default)) {
                $type = gettype($default);
                $this->throwParseException($defined, "Default values must consist of strings, {$type} given.");
            }
            foreach (array_keys($values) as $index) {
                $values[$index] ??= $default;
            }
        }

        // Convert null (no value given) to empty string.
        $result = [];
        foreach ($values as $index => $value) {
            $result[$index] = $value ?? '';
        }
        return $result;
    }

    /**
     * @param ParameterDefinition $defined
     * @param string $message
     * @param array<string, mixed> $context
     * @return never
     */
    protected function throwParseException(ParameterDefinition $defined, string $message, array $context = []): never
    {
        $type = ($defined instanceof ArgumentDefinition)
            ? "Option: --{$defined->name}"
            : "Argument: [{$defined->name}]";

        throw new ParseException("{$type} {$message}", $context + [
            'defined' => $defined,
        ]);
    }
}
