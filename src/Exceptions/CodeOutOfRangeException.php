<?php declare(strict_types=1);

namespace Kirameki\Cli\Exceptions;

use Kirameki\Process\ExitCode;

class CodeOutOfRangeException extends CliException
{
    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return ExitCode::STATUS_OUT_OF_RANGE;
    }
}
