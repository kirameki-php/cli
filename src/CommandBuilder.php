<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\ArgumentBuilder;
use Kirameki\Cli\Definitions\OptionBuilder;
use RuntimeException;
use function array_map;

class CommandBuilder
{
    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var array<string, ArgumentBuilder>
     */
    protected array $argumentBuilders = [];

    /**
     * @var array<string, OptionBuilder>
     */
    protected array $optionBuilders = [];

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
        return $this->argumentBuilders[$name] = new ArgumentBuilder($name);
    }

    /**
     * @param string $name
     * @param string|null $short
     * @return OptionBuilder
     */
    public function option(string $name, ?string $short = null): OptionBuilder
    {
        $builder = new OptionBuilder($name, $short);
        $this->optionBuilders[$name] = $builder;
        $this->optionBuilders[$short] = $builder;
        return $builder;
    }

    public function build(): CommandDefinition
    {
        if ($this->name === null) {
            throw new RuntimeException('Name of command must be defined!');
        }

        $arguments = array_map(
            fn(ArgumentBuilder $builder) => $builder->build(),
            $this->argumentBuilders
        );

        $options = array_map(
            fn(OptionBuilder $builder) => $builder->build(),
            $this->optionBuilders
        );

        return new CommandDefinition($this->name, $arguments, $options);
    }
}
