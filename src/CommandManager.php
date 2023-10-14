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
use Kirameki\Event\EventDispatcher;
use function array_key_exists;
use function array_shift;
use function assert;
use function class_exists;
use function is_subclass_of;

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
        protected Container $container,
        protected EventDispatcher $events,
        protected Input $input = new Input(),
        protected Output $output = new Output(),
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
        $args = preg_split('/("[^"]*")|\h+/', $input, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
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
        $command = $this->resolve($name, $parameters);
        $events = $this->events;

        $events->dispatch(new CommandExecuting($command));

        $exitCode = $command->execute();

        $events->dispatch(new CommandExecuted($command, $exitCode));

        return $exitCode;
    }

    /**
     * @param string|class-string<Command> $name
     * @param iterable<int, string> $parameters
     * @return Command
     */
    protected function resolve(string $name, iterable $parameters): Command
    {
        // Instantiate the commands once to get the alias names of all the commands.
        $this->registerAliasMap();

        $commandClass = $this->getCommandClass($name);

        if (class_exists($commandClass) && is_subclass_of($commandClass, Command::class)) {
            return $this->makeCommand($commandClass, $parameters);
        }

        throw new CommandNotFoundException("Command: {$name} is not registered.", [
            'name' => $name,
            'registered' => $this->unresolved,
        ]);
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

    protected function getCommandClass(string $name): string
    {
        return array_key_exists($name, $this->aliasMap)
            ? $this->aliasMap[$name]
            : $name;
    }

    /**
     * @param class-string<Command> $class
     * @param iterable<int, string> $parameters
     * @return Command
     */
    protected function makeCommand(string $class, iterable $parameters): Command
    {
        $definition = $this->getDefinition($class);
        $parsed = ParameterParser::parse($definition, Arr::from($parameters));

        return $this->container->make($class, [
            'container' => $this->container,
            'definition' => $definition,
            'arguments' => new Map($parsed['arguments']),
            'options' => new Map($parsed['options']),
            'input' => $this->input,
            'output' => $this->output,
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
}
