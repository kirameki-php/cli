<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;

class CommandDefinition
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var array<string, Argument>
     */
    public array $arguments = [];

    /**
     * @var array<string, Option>
     */
    public array $options = [];
}
