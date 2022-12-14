<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\DefinedArgument;
use Kirameki\Cli\Definitions\DefinedOption;
use function array_key_exists;
use function array_values;

class CommandDefinition
{
    /**
     * @var array<int, DefinedArgument>
     */
    protected array $argumentsByIndex;

    /**
     * @var array<string, DefinedArgument>
     */
    protected array $argumentsByName;

    /**
     * @param string $name
     * @param array<string, DefinedArgument> $arguments
     * @param array<string, DefinedOption> $longOptions
     * @param array<string, DefinedOption> $shortOptions
     */
    public function __construct(
        protected string $name,
        array $arguments,
        protected array $longOptions,
        protected array $shortOptions,
    )
    {
        $this->argumentsByIndex = array_values($arguments);
        $this->argumentsByName = $arguments;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, DefinedArgument>
     */
    public function getArguments(): array
    {
        return $this->argumentsByName;
    }

    /**
     * @param int $index
     * @return DefinedArgument
     */
    public function getArgumentByIndex(int $index): DefinedArgument
    {
        return $this->argumentsByIndex[$index];
    }

    /**
     * @param string $name
     * @return DefinedArgument
     */
    public function getArgumentByName(string $name): DefinedArgument
    {
        return $this->argumentsByName[$name];
    }

    /**
     * @param string $name
     * @return DefinedOption
     */
    public function getLongOption(string $name): DefinedOption
    {
        return $this->longOptions[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function shortOptionExists(string $name): bool
    {
        return array_key_exists($name, $this->shortOptions);
    }

    /**
     * @param string $name
     * @return DefinedOption
     */
    public function getShortOption(string $name): DefinedOption
    {
        return $this->shortOptions[$name];
    }
}
