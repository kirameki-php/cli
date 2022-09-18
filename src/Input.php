<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Cli\Input\Stream;
use RuntimeException;
use function array_key_exists;
use function grapheme_strlen;
use function is_string;
use function str_pad;
use function str_repeat;
use function trim;

class Input
{
    /**
     * @param Output $output
     * @param Stream $stream
     */
    public function __construct(
        readonly protected Output $output,
        readonly protected Stream $stream,
    )
    {
    }

    /**
     * @param int<0, max> $length
     * @return string|false
     */
    public function read(int $length = 1): string|false
    {
        return $this->stream->read($length);
    }

    /**
     * @return string|false
     */
    public function readLine(): string|false
    {
        return $this->stream->readLine();
    }

    /**
     * @param Closure(string):bool $callback
     * @return bool
     */
    public function readEach(Closure $callback): bool
    {
        return $this->stream->readEach($callback);
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
        $this->output->line($text);

        $choice = (string) $this->readLine();

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

        $this->output->text($text . ': ');

        $input = $this->readLine();

        if (is_string($input)) {
            $input = trim($input);
        }

        return match ($input) {
            $yes => true,
            $no => false,
            default => $default ?? throw new RuntimeException("Invalid input: '$input'"),
        };
    }

    /**
     * @param string $prompt
     * @return string|false
     */
    public function hidden(string $prompt = ''): string|false
    {
        return $this->stream->readEach($prompt, function (array $info) {
            $this->output->ansi->backspace($info['point']);
        });
    }

    /**
     * @param string $prompt
     * @param string $replacement
     * @return string|false
     */
    public function masked(string $prompt = '', string $replacement = '*'): string|false
    {
        $line = $this->stream->readEach($prompt, function (array $info) use ($replacement) {
            $this->output->ansi
                // Clear all output up to the end of prompt text.
                ->cursorBack($info['point'])->eraseToEndOfLine()
                // Write replacement text (will set the cursor to the end).
                ->text(str_repeat($replacement, $info['end']))
                // Set the cursor back to the offset position.
                ->cursorBack($info['end'] - $info['point']);
        });

        // Pressing enter with no input, shows duplicated prompt for some reason,
        // so we have to clear the line.
        $this->output->ansi->eraseLine();

        return $line;
    }

    /**
     * @param string|null $message
     * @return void
     */
    protected function writeMessage(?string $message = null): void
    {
        if ($message !== null) {
            $this->output->text($message);
        }
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->stream->close();
    }
}