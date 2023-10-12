<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Events\CommandExecuted;
use Kirameki\Cli\Events\CommandExecuting;
use Kirameki\Cli\Exceptions\CommandNotFoundException;
use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;
use Kirameki\Cli\Parameters\ParameterParser;
use Kirameki\Collections\Map;
use Kirameki\Collections\Utils\Arr;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use function array_key_exists;

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
     * @param string|class-string<Command> $name
     * @param iterable<int, string> $parameters
     * @return int
     */
    public function execute(string $name, iterable $parameters = []): int
    {
        $command = $this->resolve($name);
        $definition = $this->getDefinition($command::class);

        $parsed = $this->parseDefinition($definition, Arr::from($parameters));
        $arguments = new Map($parsed['arguments']);
        $options = new Map($parsed['options']);

        $events = $this->events;

        $events->dispatch(new CommandExecuting($command, $arguments, $options));

        $exitCode = $command->execute(
            $arguments,
            $options,
            $this->input,
            $this->output,
        );

        $events->dispatch(new CommandExecuted($command, $arguments, $options, $exitCode));

        return $exitCode;
    }

    /**
     * @param string|class-string<Command> $name
     * @return Command
     */
    protected function resolve(string $name): Command
    {
        if (class_exists($name) && is_subclass_of($name, Command::class)) {
            return $this->container->make($name);
        }

        // Instantiate the commands once to get the alias names of all the commands.
        $this->registerAliasMap();

        // Get the alias if `$name` is given as name.
        if (array_key_exists($name, $this->aliasMap)) {
            return $this->container->make($this->aliasMap[$name]);
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

    /**
     * @param class-string<Command> $command
     * @return CommandDefinition
     */
    protected function getDefinition(string $command): CommandDefinition
    {
        return $this->container->make($command)->definition;
    }

    /**
     * @param CommandDefinition $definition
     * @param list<string> $parameters
     * @return array{
     *     arguments: array<string, Argument>,
     *     options: array<string, Option>,
     * }
     */
    protected function parseDefinition(
        CommandDefinition $definition,
        array $parameters,
    ): array
    {
        return ParameterParser::parse($definition, $parameters);
    }
}
