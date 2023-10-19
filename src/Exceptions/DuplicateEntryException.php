<?php declare(strict_types=1);

namespace Kirameki\Cli\Exceptions;

use Kirameki\Core\Exceptions\LogicException;
use Throwable;

class DuplicateEntryException extends LogicException
{
    public function __construct(string $name, ?iterable $context = null, int $code = 0, ?Throwable $previous = null)
    {
        $message = "Command: {$name} is already registered.";
        parent::__construct($message, $context, $code, $previous);
    }
}
