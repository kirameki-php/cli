<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Exceptions\InvalidInputException;
use Kirameki\Cli\Input\HiddenReader;
use Kirameki\Cli\Input\IntegerReader;
use Kirameki\Cli\Input\LineReader;
use Kirameki\Cli\Input\MaskedReader;
use Kirameki\Stream\StdinStream;
use Kirameki\Stream\Streamable;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\Ansi\Stream as AnsiStream;
use function array_key_exists;
use function array_keys;
use function array_map;
use function end;
use function filter_var;
use function max;
use function str_pad;
use function strlen;
use const FILTER_VALIDATE_INT;
use const PHP_INT_MAX;
use const STR_PAD_LEFT;

class Input
{
    /**
     * @param Streamable $stdin
     * @param AnsiStream $output
     */
    public function __construct(
        readonly protected Streamable $stdin = new StdinStream(),
        readonly protected AnsiStream $output = new AnsiStream(),
    )
    {
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function text(string $prompt = ''): string
    {
        return (new LineReader($this->stdin, $this->output, $prompt))->readline();
    }

    /**
     * @param string $prompt
     * @return int|null
     */
    public function integer(string $prompt = ''): ?int
    {
        while (true) {
            $value = (new IntegerReader($this->stdin, $this->output, $prompt))->readline();
            $converted = filter_var($value, FILTER_VALIDATE_INT);

            if ($converted !== false) {
                return $converted;
            }

            // PHP converts all values greater than PHP_INT_MAX to PHP_INT_MAX
            // so check that string value does not overflow.
            $message = 'Integer overflow! allowed:±' . PHP_INT_MAX . ' given: ' . $value;

            $this->output
                ->fgColor(Color::Red)
                ->text($message)
                ->lineFeed()
                ->resetStyle()
                ->flush();
        }
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function hidden(string $prompt = ''): string
    {
        return (new HiddenReader($this->stdin, $this->output, $prompt))->readline();
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function masked(string $prompt = ''): string
    {
        return (new MaskedReader($this->stdin, $this->output, $prompt))->readline();
    }
}
