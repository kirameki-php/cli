<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\ParameterBuilder;
use Kirameki\Cli\Parameters\Option;

class CommandBuilder
{
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
        $this->definition->name = $name;
        return $this;
    }

    /**
     * @param string $name
     * @return ParameterBuilder
     */
    public function argument(string $name): ParameterBuilder
    {
        return new ParameterBuilder(
            $this->definition->arguments[$name] = new Argument($name),
        );
    }

    /**
     * @param string $name
     * @param string|null $short
     * @return ParameterBuilder
     */
    public function option(string $name, ?string $short = null): ParameterBuilder
    {
        return new ParameterBuilder(
            $this->definition->options[$name] = new Option($name, $short),
        );
    }

    /**
     * @return CommandDefinition
     */
    public function getDefinition(): CommandDefinition
    {
        return $this->definition;
    }
}
