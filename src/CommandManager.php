<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Events\CommandExecuted;
use Kirameki\Cli\Events\CommandExecuting;
use Kirameki\Cli\Exceptions\InvalidInputException;
use Kirameki\Cli\Parameters\ParameterParser;
use Kirameki\Collections\Map;
use Kirameki\Collections\Utils\Arr;
use Kirameki\Event\EventManager;
use function array_shift;
use function assert;
use function preg_split;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

class CommandManager
{
    /**
     * @param CommandRegistry $registry
     * @param EventManager $events
     * @param Output $output
     * @param Input $input
     */
    public function __construct(
        protected readonly CommandRegistry $registry,
        protected readonly EventManager $events,
        protected readonly Output $output = new Output(),
        protected readonly Input $input = new Input(),
    )
    {
    }

    /**
     * @param class-string<Command> $command
     * @return $this
     */
    public function register(string $command): static
    {
        $this->registry->register($command);
        return $this;
    }

    /**
     * @param string $input
     * @return int
     */
    public function parseAndRun(string $input): int
    {
        // Splits $input into command name + parameters.
        // Double-quoted strings are properly handled through the regex below.
        $args = preg_split('/"([^"]*)"|\h+/', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        assert($args !== false);

        $name = array_shift($args);
        if ($name === null) {
            throw new InvalidInputException('No command name given.', [
                'input' => $input,
            ]);
        }

        return $this->run($name, $args);
    }

    /**
     * @param string|class-string<Command> $name
     * @param iterable<int, string> $parameters
     * @return int
     */
    public function run(string $name, iterable $parameters = []): int
    {
        $events = $this->events;

        $command = $this->registry->makeCommand($name);
        $definition = $command::getDefinition();
        $parsed = ParameterParser::parse($definition, Arr::from($parameters));

        $events->emit(new CommandExecuting($command));

        $exitCode = $command->execute(
            $definition,
            new Map($parsed['arguments']),
            new Map($parsed['options']),
            $this->output,
            $this->input,
        );

        $events->emit(new CommandExecuted($command, $exitCode));

        return $exitCode;
    }
}
