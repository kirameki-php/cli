<?php declare(strict_types=1);

namespace Kirameki\Cli\Events;

use Kirameki\Cli\Command;
use Kirameki\Event\Event;

class CommandExecuted extends Event
{
    /**
     * @param Command $command
     */
    public function __construct(
        public readonly int $exitCode,
        public readonly Command $command,
    )
    {
    }
}
