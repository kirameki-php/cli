<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Exceptions\NotFoundException;
use function array_key_exists;

class CliManager
{
    /**
     * @var array<string, Command>
     */
    protected array $commands = [];

    public function register(Command $command): static
    {
        $name = $command->definition->getName();

        $this->commands[$name] = $command;

        return $this;
    }

    /**
     * @param string $name
     * @param list<string> $parameters
     * @return int
     */
    public function execute(string $name, array $parameters = []): int
    {
        $input = new Input();
        $output = new Output();

        if (!array_key_exists($name, $this->commands)) {
            throw new NotFoundException("Command: {$name} does not exist.", [
                'name' => $name,
                'parameters' => $parameters,
            ]);
        }

        $command = $this->commands[$name];

        return $command->execute($input, $output, $parameters);
    }
}
