<?php declare(strict_types=1);

namespace Kirameki\Cli;

abstract class Command
{
    /**
     * @var CommandDefinition
     */
    protected readonly CommandDefinition $definition;

    /**
     * @var Inputs
     */
    protected Inputs $inputs;

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
     * @param list<string> $parameters
     * @return int
     */
    public function execute(array $parameters): int
    {
        $parser = new InputParser($this->definition, $parameters);
        $parsed = $parser->parse();

        $this->inputs = new Inputs(
            $parsed['arguments'],
            $parsed['options'],
        );

        return $this->run();
    }

    /**
     * @return int
     */
    abstract public function run(): int;
}
