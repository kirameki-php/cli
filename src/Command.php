<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Cli\Exceptions\CodeOutOfRangeException;
use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;
use Kirameki\Cli\Parameters\ParameterParser;
use Kirameki\Collections\Map;

abstract class Command
{
    /**
     * @var CommandDefinition
     */
    public readonly CommandDefinition $definition;

    /**
     * @var Map<string, Argument>
     */
    protected Map $arguments;

    /**
     * @var Map<string, Option>
     */
    protected Map $options;

    /**
     * @var Input
     */
    protected Input $input;

    /**
     * @var Output
     */
    protected Output $output;

    /**
     * @var SignalHandler
     */
    private SignalHandler $signal;

    public function __construct()
    {
        $builder = new CommandBuilder();
        $this->define($builder);
        $this->definition = $builder->build();
    }

    /**
     * Define the command and its arguments and options.
     *
     * @param CommandBuilder $builder
     * @return void
     */
    abstract protected function define(CommandBuilder $builder): void;

    /**
     * Parse the raw parameters and run the command.
     *
     * @param Input $input
     * @param Output $output
     * @param SignalHandler $signalHandler
     * @param list<string> $parameters
     * @return int
     */
    public function execute(
        SignalHandler $signalHandler,
        Input $input,
        Output $output,
        array $parameters,
    ): int
    {
        $parsed = $this->parseDefinition($parameters);

        $this->arguments = new Map($parsed['arguments']);
        $this->options = new Map($parsed['options']);
        $this->input = $input;
        $this->output = $output;
        $this->signal = $signalHandler;

        $code = $this->run() ?? ExitCode::Success;

        if ($code < 0 || $code > 255) {
            throw new CodeOutOfRangeException("Exit code must be between 0 and 255, {$code} given.", [
                'code' => $code,
                'definition' => $this->definition,
                'parameters' => $parameters,
            ]);
        }

        return $code;
    }

    /**
     * The method which runs the user defined logic.
     *
     * @return int|null
     * Exit code for the given command.
     * Must be between 0 and 255.
     */
    abstract protected function run(): ?int;

    /**
     * @param list<string> $parameters
     * @return array{
     *     arguments: array<string, Argument>,
     *     options: array<string, Option>,
     * }
     */
    protected function parseDefinition(array $parameters): array
    {
        return ParameterParser::parse($this->definition, $parameters);
    }

    protected function captureSignal(int $signal, Closure $callback): void
    {
        $this->signal->capture($signal, $callback);
    }
}
