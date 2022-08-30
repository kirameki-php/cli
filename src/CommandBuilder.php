<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\ArgumentBuilder;
use Kirameki\Cli\Parameters\OptionBuilder;

class CommandBuilder
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var array<string, ArgumentBuilder>
     */
    protected array $arguments = [];

    /**
     * @var array<string, OptionBuilder>
     */
    protected array $options = [];

    /**
     * @param CommandDefinition $definition
     */
    public function __construct(
        protected CommandDefinition $definition,
    )
    {
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $name
     * @return ArgumentBuilder
     */
    public function argument(string $name): ArgumentBuilder
    {
        return $this->arguments[$name] = new ArgumentBuilder($name);
    }

    /**
     * @param string $name
     * @param string|null $short
     * @return OptionBuilder
     */
    public function option(string $name, ?string $short = null): OptionBuilder
    {
        return $this->options[$name] = new OptionBuilder($name, $short);
    }

    /**
     * @return CommandDefinition
     */
    public function getDefinition(): CommandDefinition
    {
        return $this->definition;
    }
}
