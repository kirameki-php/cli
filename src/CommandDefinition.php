<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\Argument;
use Kirameki\Cli\Definitions\Option;

class CommandDefinition
{
    /**
     * @param string $name
     * @param array<string, Argument> $arguments
     * @param array<string, Option> $options
     */
    public function __construct(
        protected string $name,
        protected array $arguments,
        protected array $options,
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
     * @return Option
     */
    public function getOption(string $name): Option
    {
        return $this->options[$name];
    }

    /**
     * @return array<string, Option>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
