<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\ParameterParser;

abstract class Command
{
    /**
     * @var CommandDefinition
     */
    protected readonly CommandDefinition $definition;

    /**
     * @var Parameters
     */
    protected Parameters $parameters;

    protected Output $output;

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
     * @param Output $output
     * @param list<string> $rawParameters
     * @return int
     */
    public function execute(Output $output, array $rawParameters): int
    {
        $parser = new ParameterParser($this->definition, $rawParameters);
        $parsed = $parser->parse();

        $this->parameters = new Parameters(
            $parsed['arguments'],
            $parsed['options'],
        );

        return $this->handle();
    }

    /**
     * @return int
     */
    abstract public function handle(): int;
}
