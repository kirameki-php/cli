<?php declare(strict_types=1);

namespace Kirameki\Cli\Exceptions;

use Kirameki\Cli\ExitCode;

class ParseException extends CliException
{
    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return ExitCode::InvalidArgument;
    }
}
