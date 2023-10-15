<?php declare(strict_types=1);

namespace Kirameki\Cli\Events;

use Kirameki\Cli\Command;
use Kirameki\Event\Event;

class CommandExecuting extends Event
{
    /**
     * @param Command $command
     */
    public function __construct(
        public readonly Command $command,
    )
    {
    }
}
