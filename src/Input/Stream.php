<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Closure;
use function fclose;
use function fgets;
use function fread;
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
     * @param int<0, max> $length
     * @return string|false
     */
    public function readSilently(int $length): string|false
    {
        $prev = shell_exec('stty -g');

        try {
            shell_exec('stty -echo');
            return $this->read($length);
        }
        finally {
            shell_exec('stty ' . $prev);
        }
    }

    /**
     * @param string $break
     * Character which will terminate the reading process and return the combined string.
     *
     * @param Closure(string, string):void|null $callback
     * Invoked for each character read. First argument contains the character read and
     * second argument contains a string of all the chars upto the current char.
     * 
     * @return string|false
     */
    public function readTo(string $break, ?Closure $callback = null): string|false
    {
        $read = [STDIN];
        $write = $except = null;

        $input = '';
        readline_callback_handler_install('', function() { });
        while (stream_select($read, $write, $except, null)) {
            $char = stream_get_contents(STDIN, 1);

            if ($char !== false) {
                if ($callback !== null) {
                    $callback($char, $input);
                }
                $input .= $char;
            }

            if ($char === false || str_contains($break, $char)) {
                break;
            }
        }
        readline_callback_handler_remove();
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