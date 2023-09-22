<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Cli\Exceptions\CodeOutOfRangeException;
use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;
use Kirameki\Collections\Map;
use Kirameki\Core\Signal;
use Kirameki\Core\SignalEvent;
use Kirameki\Process\ExitCode;

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
    abstract public function define(CommandBuilder $builder): void;

    /**
     * Parse the raw parameters and run the command.
     *
     * @param Map<string, Argument> $arguments
     * @param Map<string, Option> $options
     * @param Input $input
     * @param Output $output
     * @return int
     */
    public function execute(
        Map $arguments,
        Map $options,
        Input $input,
        Output $output,
    ): int
    {
        $this->arguments = $arguments;
        $this->options = $options;
        $this->input = $input;
        $this->output = $output;

        $code = $this->run() ?? ExitCode::SUCCESS;

        if ($code < 0 || $code > 255) {
            throw new CodeOutOfRangeException("Exit code must be between 0 and 255, {$code} given.", [
                'code' => $code,
                'definition' => $this->definition,
                'arguments' => $arguments,
                'options' => $options,
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
     * @param int $signal
     * @param Closure(SignalEvent): mixed $callback
     * @return void
     */
    protected function onSignal(int $signal, Closure $callback): void
    {
        Signal::handle($signal, $callback);
    }
}
