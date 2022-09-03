<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\DefinedArgument;
use Kirameki\Cli\Definitions\DefinedOption;
use function array_key_exists;

class CommandDefinition
{
    /**
     * @param string $name
     * @param array<string, DefinedArgument> $arguments
     * @param array<string, DefinedOption> $longOptions
     * @param array<string, DefinedOption> $shortOptions
     */
    public function __construct(
        protected string $name,
        protected array  $arguments,
        protected array  $longOptions,
        protected array  $shortOptions,
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getArgumentByName(string $name)
    {
        return $this->arguments[$name];
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
    public function hasShortOption(string $name): bool
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
