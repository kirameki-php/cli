<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;

class Parameters
{
    /**
     * @param array<string, Argument> $arguments
     * @param array<string, Option> $options
     */
    public function __construct(
        public readonly array $arguments,
        public readonly array $options,
    )
    {
    }
}
