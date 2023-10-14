<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Cli\Exceptions\CodeOutOfRangeException;
use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;
use Kirameki\Collections\Map;
use Kirameki\Container\Container;
use Kirameki\Core\Signal;
use Kirameki\Core\SignalEvent;
use Kirameki\Process\ExitCode;
use function ini_set;
use function set_time_limit;

abstract class Command
{
    /**
     * @param Container $container
     * @param Input $input
     * @param Output $output
     * @param CommandDefinition $definition
     * @param Map<string, Argument> $arguments
     * @param Map<string, Option> $options
     */
    public function __construct(
        protected readonly Container $container,
        public readonly CommandDefinition $definition,
        protected readonly Input $input,
        protected readonly Output $output,
        public readonly Map $arguments,
        public readonly Map $options,
    )
    {
    }

    /**
     * Define the command and its arguments and options.
     *
     * @param CommandBuilder $builder
     * @return void
     */
    abstract public static function define(CommandBuilder $builder): void;

    /**
     * Parse the raw parameters and run the command.
     *
     * @return int
     */
    public function execute(): int
    {
        $this->applyRuntimeLimits();

        $code = $this->container->call($this->run(...)) ?? ExitCode::SUCCESS;

        if ($code < 0 || $code > 255) {
            throw new CodeOutOfRangeException("Exit code must be between 0 and 255, {$code} given.", [
                'code' => $code,
                'command' => $this,
                'arguments' => $this->arguments,
                'options' => $this->options,
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

    /**
     * @return bool
     */
    protected function isVerbose(): bool
    {
        return $this->options->get('verbose')->provided;
    }

    /**
     * @return void
     */
    private function applyRuntimeLimits(): void
    {
        $this->applyTimeLimit();
        $this->applyMemoryLimit();
    }

    /**
     * @return void
     */
    private function applyTimeLimit(): void
    {
        $option = $this->options->getOrNull('time-limit');

        $timeLimit = $option?->provided
            ? $option->valueAsInt()
            : $this->definition->getTimeLimit();

        if ($timeLimit !== null) {
            set_time_limit($timeLimit);
        }
    }

    /**
     * @return void
     */
    private function applyMemoryLimit(): void
    {
        // validate format
        $option = $this->options->getOrNull('memory-limit');

        $memoryLimit = $option?->provided
            ? $option->value()
            : $this->definition->getMemoryLimit();

        if ($memoryLimit !== null) {
            ini_set('memory_limit', $memoryLimit);
        }
    }
}
