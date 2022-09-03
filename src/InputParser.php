<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\DefinedArgument;
use Kirameki\Cli\Definitions\DefinedOption;
use Kirameki\Cli\Input\Argument;
use Kirameki\Cli\Input\Option;
use RuntimeException;
use function array_key_exists;
use function count;
use function explode;
use function preg_match;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;

class InputParser
{
    /**
     * @var array<string, Argument>
     */
    protected array $enteredArguments = [];

    /**
     * @var array<string, Option>
     */
    protected array $enteredOptions = [];

    /**
     * @var int
     */
    protected int $cursor = 0;

    /**
     * @param CommandDefinition $definition
     * @param list<string> $parameters
     */
    public function __construct(
        protected CommandDefinition $definition,
        protected array $parameters,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function parse(): array
    {
        $parameterCount = count($this->parameters);

        while ($this->cursor < $parameterCount) {
            $parameter = $this->parameters[$this->cursor];

            match (true) {
                $this->isLongOption($parameter) => $this->processLongOption($parameter),
                $this->isShortOption($parameter) => $this->processShortOptions($parameter),
                default => $this->processArgument($parameter),
            };

            $this->cursor++;
        }

        return [
            'options' => $this->enteredOptions,
        ];
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function isLongOption(string $parameter): bool
    {
        return (bool)preg_match('/--\w+/', $parameter);
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function isShortOption(string $parameter): bool
    {
        return (bool)preg_match('/-\w+/', $parameter);
    }

    /**
     * @param string $parameter
     * @return bool
     */
    protected function isNotAnOption(string $parameter): bool
    {
        return !str_starts_with($parameter, '-');
    }

    /**
     * @return string|null
     */
    protected function nextParameter(): ?string
    {
        return $this->parameters[$this->cursor + 1] ?? null;
    }

    /**
     * @param string $parameter
     * @return void
     */
    protected function processLongOption(string $parameter): void
    {
        $parts = explode('=', substr($parameter, 2));
        $name = $parts[0];
        $value = $parts[1] ?? null;

        $defined = $this->getDefinedLongOption($name);

        if ($defined === null) {
            throw new RuntimeException(sprintf('Undefined option: %s', $name));
        }

        if ($value === null) {
            // look at the next parameter to check if it's a value
            $nextParameter = $this->nextParameter();

            if ($nextParameter !== null && $this->isNotAnOption($nextParameter)) {
                $value = $nextParameter;
                $this->cursor++;
            }
        }

        $value ??= $defined->getDefault();

        if ($defined->requireValue()) {
            throw new RuntimeException(sprintf('Value is required for option: %s', $name));
        }

        $this->addToOption($defined, $name, $value);
    }

    /**
     * @param string $parameter
     * @return void
     */
    protected function processShortOptions(string $parameter): void
    {
        $chars = substr($parameter, 1);

        for ($i = 0, $size = strlen($chars); $i < $size; $i++) {
            $char = $chars[$i];
            $defined = $this->getDefinedShortOption($char);

            if ($i === 0 && $defined === null) {
                throw new RuntimeException(sprintf('Undefined option: %s', $char));
            }

            $nextChar = $chars[$i + 1] ?? false;

            // on the last char, no need to go further.
            if ($nextChar === false) {
                $nextParameter = $this->nextParameter();
                ($nextParameter !== null && $this->isNotAnOption($nextParameter))
                    ? $this->addToOption($defined, $char, $nextParameter)
                    : $this->addToOption($defined, $char, null);
                break;
            }

            // if next char is not an option, assume it's an argument.
            if (!$this->definition->hasShortOption($nextChar)) {
                $value = substr($chars, $i);
                $this->addToOption($defined, $char, $value);
                break;
            }

            // if next char is another option, add the current option and move on.
            $this->addToOption($defined, $char, null);
        }
    }

    protected function processArgument(string $parameter): void
    {

    }

    /**
     * @param string $name
     * @return DefinedOption|null
     */
    protected function getDefinedLongOption(string $name): ?DefinedOption
    {
        return $this->checkOptionCount($this->definition->getLongOption($name));
    }

    /**
     * @param string $name
     * @return DefinedOption|null
     */
    protected function getDefinedShortOption(string $name): ?DefinedOption
    {
        return $this->checkOptionCount($this->definition->getShortOption($name));
    }

    /**
     * @param DefinedOption $option
     * @return DefinedOption
     */
    protected function checkOptionCount(DefinedOption $option): DefinedOption
    {
        $longName = $option->getName();

        if (array_key_exists($longName, $this->enteredOptions) && !$option->isArray()) {
            throw new RuntimeException(sprintf('Option: %s cannot be entered more than once', $longName));
        }

        return $option;
    }

    /**
     * @param DefinedOption $defined
     * @param string $entered
     * @param mixed $value
     * @return Option
     */
    protected function addToOption(DefinedOption $defined, string $entered, mixed $value): Option
    {
        $longName = $defined->getName();

        $this->enteredOptions[$longName] ??= new Option($defined, $entered);
        $this->enteredOptions[$longName]->addValue($value);

        return $this->enteredOptions[$longName];
    }

    /**
     * @param DefinedArgument $argument
     * @param mixed $value
     * @return Argument
     */
    protected function addToArgument(DefinedArgument $argument, mixed $value): Argument
    {
        $name = $argument->getName();

        $this->enteredArguments[$name] ??= new Argument($argument);
        $this->enteredArguments[$name]->addValue($value);

        return $this->enteredArguments[$name];
    }
}
