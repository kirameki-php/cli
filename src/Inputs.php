<?php declare(strict_types=1);

namespace Kirameki\Cli;

class Inputs
{
    public array $options = [];

    public function __construct(
        protected CommandDefinition $definition,
        protected iterable $rawInputs,
    )
    {
    }

    public function parseRawInput(iterable $rawInputs): void
    {
        foreach ($rawInputs as $raw) {
            if (str_starts_with($raw, '-')) {
                if (str_starts_with($raw, '--')) {
                    $name = substr($raw, 2);
                    $this->options[] = $this->getOption($name);
                }
            }
        }
    }

    public function hasOption(string $name): bool
    {

    }

    public function getOption(string $name): mixed
    {

    }
}
