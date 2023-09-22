<?php declare(strict_types=1);

namespace Kirameki\Cli\Exceptions;

use Kirameki\Process\ExitCode;

class ParseException extends CliException
{
    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return ExitCode::INVALID_ARGUMENT;
    }
}
