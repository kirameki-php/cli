<?php declare(strict_types=1);

namespace Kirameki\Cli\Exceptions;

use Kirameki\Core\Exceptions\LogicException;

abstract class CliException extends LogicException
{
    /**
     * @return int
     */
    abstract public function getExitCode(): int;
}
