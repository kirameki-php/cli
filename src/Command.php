<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;

abstract class Command
{
    /**
     * @var CommandDefinition
     */
    protected readonly CommandDefinition $definition;

    public function __construct()
    {
        $this->definition = new CommandDefinition();
        $this->setup(new CommandBuilder($this->definition));
    }

    /**
     * @param CommandBuilder $builder
     * @return void
     */
    abstract protected function setup(CommandBuilder $builder): void;

    /**
     * @return int
     */
    abstract public function run(): int;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->definition->name;
    }

    /**
     * @return array<string, Argument>
     */
    public function getArguments(): array
    {
        return $this->definition->arguments;
    }

    /**
     * @param string $name
     * @return Argument
     */
    public function getArgument(string $name): Argument
    {
        return $this->definition->arguments[$name];
    }

    /**
     * @return array<string, Option>
     */
    public function getOptions(): array
    {
        return $this->definition->options;
    }

    /**
     * @param string $name
     * @return Option
     */
    public function getOption(string $name): Option
    {
        return $this->definition->options[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->definition->options);
    }
}
