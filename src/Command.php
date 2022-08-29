<?php declare(strict_types=1);

namespace Kirameki\Cli;

abstract class Command
{
    public function __construct(
        public readonly string $name,
        public readonly array $arguments,
        public readonly array $options,
    )
    {
    }

    public function setup()
    {

    }
}
