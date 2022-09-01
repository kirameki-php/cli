<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\Option;
use function array_key_exists;
use function count;
use function explode;
use function is_array;
use function preg_match;
use function str_starts_with;
use function substr;

class InputParser
{
    protected array $definedOptions;

    public array $enteredOptions = [];

    protected int $currentIndex = 0;

    public function __construct(
        CommandDefinition $definition,
        protected array $parameters,
    )
    {
        $this->definedOptions = $definition->getOptions();
    }

    /**
     * @return array<string, array<>>
     */
    public function parse(): array
    {
        $parameterCount = count($this->parameters);

        while($this->currentIndex < $parameterCount) {
            if ($this->isLongOption($this->currentParameter())) {
                $this->processLongOption();
            }

            $this->currentIndex++;
        }

        return [
            'options' => $this->enteredOptions,
        ];
    }

    /**
     * @param string $raw
     * @return bool
     */
    protected function isOptionValue(string $raw): bool
    {
        return str_starts_with($raw, '-');
    }

    /**
     * @param string $raw
     * @return bool
     */
    protected function isLongOption(string $raw): bool
    {
        return (bool)preg_match('/--\w+/', $raw);
    }

    /**
     * @param string $raw
     * @return bool
     */
    protected function isShortOption(string $raw): bool
    {
        return (bool)preg_match('/-\w+/', $raw);
    }

    /**
     * @return string
     */
    protected function currentParameter(): string
    {
        return $this->parameters[$this->currentIndex];
    }

    /**
     * @return string|null
     */
    protected function nextParameter(): ?string
    {
        return $this->parameters[$this->currentIndex + 1] ?? null;
    }

    /**
     * @return void
     */
    protected function processLongOption(): void
    {
        $parts = explode('=', substr($this->currentParameter(), 2));
        $name = $parts[0];
        $value = $parts[1] ?? null;

        $option = $this->pullDefinedOption($name);

        // option was defined with =
        if ($value !== null) {
            $this->addToOption($name, $value);
            return;
        }

        // look at the next parameter to check if it's a value
        $next = $this->nextParameter();

        // $next is null, which means it reached the end of parameter list
        if ($next === null) {
            $this->addToOption($name, $option->getDefault());
            return;
        }

        // next value retrieved was non-option string which can be
        // interpreted as the value for option.
        if (!str_starts_with($next, '-')) {
            $this->addToOption($name, $next);
            return;
        }

        // no option value was defined.
        $this->addToOption($name, null);
    }

    protected function pullDefinedOption(string $name): Option
    {
        $option = $this->definedOptions[$name];

        if (!$option->isArray()) {
            unset($this->definedOptions[$name]);
        }

        return $option;
    }

    protected function addToOption(string $name, mixed $value): void
    {
        if (!array_key_exists($name, $this->enteredOptions)) {
            $this->enteredOptions[$name] = $value;
            return;
        }

        if (is_array($this->enteredOptions[$name])) {
            $this->enteredOptions[$name][] = $value;
            return;
        }

        $this->enteredOptions[$name] = [$this->enteredOptions[$name], $value];
    }
}
