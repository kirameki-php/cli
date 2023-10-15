<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Definitions\ArgumentDefinition;
use Kirameki\Cli\Definitions\OptionDefinition;
use function array_key_exists;
use function array_keys;

readonly class CommandDefinition
{
    /**
     * @var list<string>
     */
    protected array $argumentIndexAliases;

    /**
     * @param string $name
     * @param string $description
     * @param array<string, ArgumentDefinition> $arguments
     * @param array<string, OptionDefinition> $options
     * @param array<string, string> $shortNameAliases
     * @param string|null $memoryLimit
     * @param int|null $timeLimit
     */
    public function __construct(
        protected string $name,
        protected string $description,
        protected array $arguments,
        protected array $options,
        protected array $shortNameAliases,
        protected ?string $memoryLimit = null,
        protected ?int $timeLimit = null,
    )
    {
        $this->argumentIndexAliases = array_keys($arguments);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array<string, ArgumentDefinition>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param int $index
     * @return ArgumentDefinition|null
     */
    public function getArgumentByIndexOrNull(int $index): ?ArgumentDefinition
    {
        return array_key_exists($index, $this->argumentIndexAliases)
            ? $this->getArgument($this->argumentIndexAliases[$index])
            : null;
    }

    /**
     * @param string $name
     * @return ArgumentDefinition
     */
    public function getArgument(string $name): ArgumentDefinition
    {
        return $this->arguments[$name];
    }

    /**
     * @return array<string, OptionDefinition>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @return OptionDefinition|null
     */
    public function getOptionOrNull(string $name): ?OptionDefinition
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param string $char
     * @return bool
     */
    public function shortOptionExists(string $char): bool
    {
        return array_key_exists($char, $this->shortNameAliases);
    }

    /**
     * @param string $char
     * @return OptionDefinition|null
     */
    public function getOptionByShortOrNull(string $char): ?OptionDefinition
    {
        return $this->shortOptionExists($char)
            ? $this->getOptionOrNull($this->shortNameAliases[$char])
            : null;
    }

    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }
}
