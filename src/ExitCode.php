<?php declare(strict_types=1);

namespace Kirameki\Cli;

class ExitCode
{
    public const Success = 0;
    public const GeneralError = 1;
    public const NotExecutable = 126;
    public const CommandNotFound = 127;
    public const KilledBySigInt = 130;
    public const KilledBySigTerm = 143;
    public const StatusOutOfRange = 255;
}
