<?php declare(strict_types=1);

namespace Kirameki\Cli;

abstract class Command
{
    /**
     * @var CommandDefinition
     */
    protected readonly CommandDefinition $definition;

    public function __construct()
    {
        $builder = new CommandBuilder();
        $this->setup($builder);
        $this->definition = $builder->build();
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
}
