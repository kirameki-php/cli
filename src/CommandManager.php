<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Events\CommandExecuted;
use Kirameki\Cli\Events\CommandExecuting;
use Kirameki\Cli\Exceptions\CommandNotFoundException;
use Kirameki\Event\EventHandler;
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

    /**
     * @var array<class-string<Command>, Command>
     */
    protected array $resolved = [];

    public function __construct(
        protected EventHandler $eventHandler,
        protected SignalHandler $signalHandler = new SignalHandler(),
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
     * @param list<string> $parameters
     * @return int
     */
    public function execute(string $name, array $parameters = []): int
    {
        $command = $this->resolve($name);
        $eventHandler = $this->eventHandler;
        $signalHandler = $this->signalHandler;

        $eventHandler->dispatchClass(CommandExecuting::class, $command);

        $exitCode = $command->execute(
            $signalHandler,
            $this->input,
            $this->output,
            $parameters,
        );

        $signalHandler->clearCallbacks();

        $eventHandler->dispatchClass(CommandExecuted::class, $exitCode, $command);

        return $exitCode;
    }

    /**
     * @param string|class-string<Command> $name
     * @return Command
     */
    protected function resolve(string $name): Command
    {
        // Instantiate the commands once to get the alias names of all the commands.
        $this->instantiateUnresolved();

        // Get the alias if $name is given as name.
        if (array_key_exists($name, $this->aliasMap)) {
            $name = $this->aliasMap[$name];
        }

        if (!array_key_exists($name, $this->resolved)) {
            throw new CommandNotFoundException("Command: {$name} does not exist.", [
                'name' => $name,
                'registered' => $this->resolved,
            ]);
        }

        return $this->resolved[$name];
    }

    /**
     * @return void
     */
    protected function instantiateUnresolved(): void
    {
        foreach ($this->unresolved as $class) {
            $command = new $class();
            $this->resolved[$class] = $command;
            $this->aliasMap[$command->definition->getName()] = $class;
        }

        $this->unresolved = [];
    }
}
