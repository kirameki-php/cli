<?php declare(strict_types=1);

namespace Kirameki\Cli\Events;

use Kirameki\Cli\Command;
use Kirameki\Core\Event;

class CommandExecuted extends Event
{
    /**
     * @param Command $command
     * @param int $exitCode
     */
    public function __construct(
        public readonly Command $command,
        public readonly int $exitCode,
    )
    {
    }
}
