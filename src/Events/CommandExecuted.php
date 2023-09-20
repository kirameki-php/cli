<?php declare(strict_types=1);

namespace Kirameki\Cli\Events;

use Kirameki\Cli\Command;
use Kirameki\Cli\Parameters\Argument;
use Kirameki\Cli\Parameters\Option;
use Kirameki\Collections\Map;
use Kirameki\Core\Event;

class CommandExecuted extends Event
{
    /**
     * @param Command $command
     * @param Map<string, Argument> $arguments
     * @param Map<string, Option> $options
     * @param int $exitCode
     */
    public function __construct(
        public readonly Command $command,
        public readonly Map $arguments,
        public readonly Map $options,
        public readonly int $exitCode,
    )
    {
    }
}
