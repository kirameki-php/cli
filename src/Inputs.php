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
}
