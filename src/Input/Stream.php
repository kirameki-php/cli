<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Closure;
use RuntimeException;
use Webmozart\Assert\Assert;
use function fclose;
use function fgets;
use function fread;
use function readline_callback_handler_install;
use function readline_callback_handler_remove;
use function stream_get_contents;
use function stream_select;
use const STDIN;

class Stream
{
    /**
     * @param resource $resource
     */
    public function __construct(
        protected $resource,
    )
    {
        if (!is_resource($resource)) {
            throw new RuntimeException('Stream only accepts resource.');
        }
    }

    /**
     * @param int<0, max> $length
     * @return string|false
     */
    public function read(int $length = 1): string|false
    {
        return fread($this->resource, $length);
    }

    /**
     * @param Closure(string, string):bool $callback
     * Invoked for each character read. First argument contains the character read and
     * second argument contains a string of all the chars upto the current char.
     *
     * @return string|false
     */
    public function readEach(Closure $callback): string|false
    {
        $input = '';

        $read = [STDIN];
        $write = $except = null;

        readline_callback_handler_install('', function() { });

        try {
            while (stream_select($read, $write, $except, null)) {
                $char = stream_get_contents(STDIN, 1);
                if ($char !== false) {
                    $continue = $callback($char, $input);

                    Assert::boolean($continue);

                    if ($continue) {
                        $input .= $char;
                    } else {
                        break;
                    }
                }
            }
        }
        finally {
            readline_callback_handler_remove();
        }

        return $input;
    }

    /**
     * @param int<0, max>|null $length
     * @return string|false
     */
    public function readLine(?int $length = null): string|false
    {
        return fgets($this->resource, $length);
    }

    public function close(): void
    {
        fclose($this->resource);
    }
}