<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\ParameterParser;
use Webmozart\Assert\Assert;

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

    /**
     * @var Input
     */
    protected Input $input;

    /**
     * @var Output
     */
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
     * @param Input $input
     * @param Output $output
     * @param list<string> $rawParameters
     * @return int
     */
    public function execute(Input $input, Output $output, array $rawParameters): int
    {
        $this->input = $input;

        $this->output = $output;

        $parser = new ParameterParser($this->definition, $rawParameters);
        $parsed = $parser->parse();

        $this->parameters = new Parameters(
            $parsed['arguments'],
            $parsed['options'],
        );

        $code = $this->handle() ?? 0;

        Assert::range($code, 0, 255);

        return $code;
    }

    /**
     * @return int|null
     */
    abstract public function handle(): mixed;
}
