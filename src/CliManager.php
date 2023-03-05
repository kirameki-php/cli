<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Exceptions\CommandNotFoundException;
use function array_key_exists;

class CliManager
{
    /**
     * @var array<string, class-string<Command>>
     */
    protected array $commandAliasMap = [];

    /**
     * @var list<class-string<Command>>
     */
    protected array $unresolved = [];

    /**
     * @var array<class-string<Command>, Command>
     */
    protected array $resolved = [];

    public function __construct(
        protected SignalHandler $signalHandler,
        protected Input $input,
        protected Output $output,
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
        $this->instantiateCommandsOnce();

        if (array_key_exists($name, $this->commandAliasMap)) {
            $name = $this->commandAliasMap[$name];
        }

        if (!array_key_exists($name, $this->resolved)) {
            throw new CommandNotFoundException("Command: {$name} does not exist.", [
                'name' => $name,
                'parameters' => $parameters,
            ]);
        }

        $command = $this->resolved[$name];

        return $command->execute(
            $this->signalHandler,
            $this->input,
            $this->output,
            $parameters,
        );
    }

    protected function instantiateCommandsOnce(): void
    {
        foreach ($this->unresolved as $class) {
            $command = new $class();
            $this->resolved[$class] = $command;
            $this->commandAliasMap[$command->definition->getName()] = $class;
        }

        $this->unresolved = [];
    }
}
