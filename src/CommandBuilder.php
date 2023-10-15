<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\ArgumentBuilder;
use Kirameki\Cli\Definitions\OptionBuilder;
use Kirameki\Cli\Exceptions\DefinitionException;
use function array_key_exists;
use function array_map;

class CommandBuilder
{
    /**
     * @param string|null $name
     * @param string $description
     * @param array<string, ArgumentBuilder> $argumentBuilders
     * @param array<string, OptionBuilder> $optionBuilders
     * @param array<string, string> $shortNameAliases
     * @param string|null $memoryLimit
     * @param int|null $timeLimit
     */
    public function __construct(
        protected ?string $name = null,
        protected string $description = '',
        protected array $argumentBuilders = [],
        protected array $optionBuilders = [],
        protected array $shortNameAliases = [],
        protected ?string $memoryLimit = null,
        protected ?int $timeLimit = null,
    )
    {
        $this->addHelpOption();
        $this->addVerboseOption();
        $this->addMemoryLimitOption();
        $this->addTimeLimitOption();
    }

    /**
     * @param string $name
     */
    public function name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function description(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string $name
     * @return ArgumentBuilder
     */
    public function argument(string $name): ArgumentBuilder
    {
        if (array_key_exists($name, $this->argumentBuilders)) {
            throw new DefinitionException("Argument [{$name}] already exists.", [
                'name' => $name,
                'argument' => $this->argumentBuilders[$name],
            ]);
        }
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

        if (array_key_exists($name, $this->optionBuilders)) {
            throw new DefinitionException("Option: --{$name} already exists.", [
                'name' => $name,
                'option' => $this->optionBuilders[$name],
            ]);
        }
        $this->optionBuilders[$name] = $builder;

        if ($short !== null) {
            if (array_key_exists($short, $this->shortNameAliases)) {
                throw new DefinitionException("Option: -{$short} already exists.", [
                    'name' => $name,
                    'option' => $this->optionBuilders[$name],
                ]);
            }
            $this->shortNameAliases[$short] = $name;
        }

        return $builder;
    }

    /**
     * @param string|null $size
     * @return void
     */
    public function setMemoryLimit(?string $size): void
    {
        if ($size !== null && !preg_match('/^[0-9]+[KMG]$/i', $size)) {
            throw new DefinitionException("Invalid memory limit format: {$size}. Format must be /[0-9]+[KMG]/i.", [
                'name' => $this->name,
                'size' => $size,
            ]);
        }
        $this->memoryLimit = $size;
    }

    /**
     * @param int|null $seconds
     * @return void
     */
    public function setTimeLimit(?int $seconds): void
    {
        $this->timeLimit = $seconds;
    }

    /**
     * @return CommandDefinition
     */
    public function build(): CommandDefinition
    {
        if ($this->name === null) {
            throw new DefinitionException('Name of command must be defined!');
        }

        return new CommandDefinition(
            $this->name,
            $this->description,
            array_map(fn($argBuilder) => $argBuilder->build(), $this->argumentBuilders),
            array_map(fn($optBuilder) => $optBuilder->build(), $this->optionBuilders),
            $this->shortNameAliases,
            $this->memoryLimit,
            $this->timeLimit,
        );
    }

    /**
     * @return void
     */
    protected function addVerboseOption(): void
    {
        $this->option('verbose', 'v')
            ->description('Set output to verbose mode. Verbosity can be adjusted by calling it multiple times (ex: -vv).')
            ->allowMultiple()
            ->noValue();
    }

    /**
     * @return void
     */
    protected function addHelpOption(): void
    {
        $this->option('help', 'h')
            ->description('Displays usage and the arguments and options you can use for the command.')
            ->noValue();
    }

    /**
     * @return void
     */
    protected function addMemoryLimitOption(): void
    {
        $this->option('memory-limit')
            ->description('Sets the memory limit for the command.')
            ->requiresValue();
    }

    /**
     * @return void
     */
    protected function addTimeLimitOption(): void
    {
        $this->option('time-limit')
            ->description('Sets the time limit for the command.')
            ->requiresValue();
    }
}
