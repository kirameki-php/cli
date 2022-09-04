<?php declare(strict_types=1);

namespace Kirameki\Cli;

class Inputs
{
    public function __construct(
        public readonly array $arguments,
        public readonly array $options,
    )
    {
    }
}
