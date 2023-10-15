<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Events\CommandExecuted;
use Kirameki\Cli\Events\CommandExecuting;
use Kirameki\Cli\Exceptions\CommandNotFoundException;
use Kirameki\Cli\Exceptions\InvalidInputException;
use Kirameki\Cli\Parameters\ParameterParser;
use Kirameki\Collections\Map;
use Kirameki\Collections\Utils\Arr;
use Kirameki\Container\Container;
use Kirameki\Event\EventManager;
use function array_key_exists;
use function array_shift;
use function assert;
use function class_exists;
use function is_subclass_of;
use function preg_split;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

class CommandManager
{
    /**
     * @var array<string, class-string<Command>>
     */
    protected array $aliasMap = [];

    /**
     * @var list<class-string<Command>>
     */
    protected array $unresolved = [];

    public function __construct(
        protected readonly Container $container,
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
        $this->unresolved[] = $command;
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
        $args = preg_split('/"([^"]*)"|\h+/', $input, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
        assert($args !== false);

        $name = array_shift($args);
        if($name === null) {
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

        $command = $this->makeCommand($name);
        $definition = $this->getDefinition($command::class);
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

    /**
     * @param string|class-string<Command> $name
     * @return Command
     */
    protected function makeCommand(string $name): Command
    {
        return $this->container->make($this->getCommandClass($name));
    }

    /**
     * @param string|class-string<Command> $name
     * @return class-string<Command>
     */
    protected function getCommandClass(string $name): string
    {
        if (class_exists($name) && is_subclass_of($name, Command::class)) {
            return $name;
        }

        // Instantiate the commands once to get the alias names of all the commands.
        $this->registerAliasMap();

        if (array_key_exists($name, $this->aliasMap)) {
            return $this->aliasMap[$name];
        }

        throw new CommandNotFoundException("Command: {$name} is not registered.", [
            'name' => $name,
            'registered' => $this->unresolved,
        ]);
    }

    /**
     * @param class-string<Command> $command
     * @return CommandDefinition
     */
    protected function getDefinition(string $command): CommandDefinition
    {
        $builder = new CommandBuilder();
        $command::define($builder);
        return $builder->build();
    }

    /**
     * @return void
     */
    protected function registerAliasMap(): void
    {
        // TODO use file caching for aliasMap
        foreach ($this->unresolved as $class) {
            $name = $this->getDefinition($class)->getName();
            $this->aliasMap[$name] = $class;
        }
        $this->unresolved = [];
    }
}
