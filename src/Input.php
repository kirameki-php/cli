<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Input\AutoCompleteReader;
use Kirameki\Cli\Input\HiddenReader;
use Kirameki\Cli\Input\LineReader;
use Kirameki\Cli\Input\MaskedReader;
use Kirameki\Stream\StdinStream;
use Kirameki\Stream\Streamable;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\Ansi\Stream as AnsiStream;
use function filter_var;
use const FILTER_VALIDATE_INT;

class Input
{
    /**
     * @param Streamable $input
     * @param AnsiStream $ansi
     */
    public function __construct(
        readonly protected Streamable $input = new StdinStream(),
    )
    {
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function text(string $prompt = ''): string
    {
        return (new LineReader($this->input, $this->ansi, $prompt))->readline();
    }

    /**
     * @param array<array-key, mixed> $rules
     * @param string $prompt
     * @return string
     */
    public function autoComplete(array $rules, string $prompt = ''): string
    {
        return (new AutoCompleteReader($this->input, $this->ansi, $prompt, $rules))->readline();
    }

    /**
     * @param string $prompt
     * @return int|null
     */
    public function integer(string $prompt = ''): ?int
    {
        while (true) {
            $value = (new LineReader($this->input, $this->ansi, $prompt))->readline();
            $converted = filter_var($value, FILTER_VALIDATE_INT);

            if ($converted !== false) {
                return $converted;
            }

            // PHP converts all values greater than PHP_INT_MAX to PHP_INT_MAX
            // so check that string value does not overflow.
            $this->ansi
                ->fgColor(Color::Red)
                ->text('Integer value is required.')
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
        return (new HiddenReader($this->input, $this->ansi, $prompt))->readline();
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function masked(string $prompt = ''): string
    {
        return (new MaskedReader($this->input, $this->ansi, $prompt))->readline();
    }
}
