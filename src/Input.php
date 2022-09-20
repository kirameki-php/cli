<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use RuntimeException;
use function array_key_exists;
use function array_keys;
use function array_map;
use function grapheme_strlen;
use function is_string;
use function max;
use function readline;
use function readline_callback_handler_install;
use function readline_callback_handler_remove;
use function readline_callback_read_char;
use function readline_info;
use function shell_exec;
use function str_pad;
use function str_repeat;
use function stream_select;
use function system;
use function trim;
use const STDIN;

class Input
{
    /**
     * @param Output $output
     */
    public function __construct(
        readonly protected Output $output,
    )
    {
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function readLine(string $prompt = ''): string
    {
        $line = readline($prompt);
        return is_string($line) ? $line : '';
    }

    /**
     * @param array<array-key, string> $choices
     * @return string
     */
    public function choice(array $choices): string
    {
        $maxStrLen = max(array_map(grapheme_strlen(...), array_keys($choices))) ?: 0;

        $text = '';
        foreach ($choices as $key => $value) {
            $text .= str_pad($key, $maxStrLen) . '. ' . $value;
        }

        $choice = $this->readLine($text);

        if (array_key_exists($choice, $choices)) {
            return $choice;
        }

        throw new RuntimeException("Invalid input: '$choice'");
    }

    /**
     * @param string|null $message
     * @param bool|null $default
     * @return bool
     */
    public function confirm(?string $message = null, ?bool $default = null): bool
    {
        $yes = 'y';
        $no = 'n';

        $text = ($message ?? '') . "({$yes}/{$no}) ";
        if ($default !== null) {
            $text .= '[default: ' . ($default ? $yes : $no) . ']';
        }

        $input = $this->readLine($text . ': ');

        return match ($input) {
            $yes => true,
            $no => false,
            default => $default ?? throw new RuntimeException("Invalid input: '$input'"),
        };
    }

    /**
     * @param string $prompt
     * @return string
     */
    public function hidden(string $prompt = ''): string
    {
        $stty = trim((string) shell_exec('stty -g'));
        system("stty -echo");
        $input = $this->readline($prompt);
        system("stty $stty");

        // HACK: Pressing enter with no input shows duplicated prompt
        // for some reason, so we have to add a line feed.
        $this->output->line();

        return $input;
    }

    /**
     * @param string $prompt
     * @param string $replacement
     * @return string
     */
    public function masked(string $prompt = '', string $replacement = '*'): string
    {
        return $this->readEach($prompt, function (array $info) use ($replacement) {
            $this->output->ansi
                // Clear all output up to the end of prompt text.
                ->cursorBack($info['point'])->eraseToEndOfLine()
                // Write replacement text (will set the cursor to the end).
                ->text(str_repeat($replacement, $info['end']))
                // Set the cursor back to the offset position.
                ->cursorBack($info['end'] - $info['point']);
        });
    }

    /**
     * @param string $prompt
     * @param Closure(array<string, mixed>, ?bool): (mixed|false)|null $callback
     * Invoked for each character read. First argument contains the character read and
     * second argument contains a string of all the chars upto the current char.
     *
     * @return string
     */
    public function readEach(string $prompt, ?Closure $callback = null): string
    {
        $line = '';
        $done = false;

        readline_callback_handler_install($prompt, static function(string $buffer) use (&$done, &$line) {
            $done = true;
            $line = $buffer;
        });

        try {
            $read = [STDIN];
            $write = null;
            $except = null;
            while (true) {
                stream_select($read, $write, $except, null);
                readline_callback_read_char();

                if ($callback !== null) {
                    $info = (array) readline_info();
                    if ($callback($info, $done) === false) {
                        break;
                    }
                }

                if ($done) {
                    break;
                }
            }
        }
        finally {
            readline_callback_handler_remove();
        }

        // HACK: Pressing enter with no input shows duplicated prompt
        // for some strange reason, so we have to clear the line.
        $this->output->ansi->eraseLine();

        return $line;
    }
}