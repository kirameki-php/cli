<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Cli\Input\Stream;
use Kirameki\Cli\Output\Ansi;
use RuntimeException;
use function array_key_exists;
use function is_string;
use function trim;

class Input
{
    /**
     * @param Ansi $ansi
     * @param Stream $stream
     */
    public function __construct(
        readonly protected Ansi   $ansi,
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
     * @param Closure(string, string):bool $callback
     * @return string|false
     */
    public function readEach(Closure $callback): string|false
    {
        return $this->stream->readEach($callback);
    }

    /**
     * @param array<array-key, string> $choices
     * @return string
     */
    public function choice(array $choices): string
    {
        $maxStrLen = max(array_map(strlen(...), array_keys($choices))) ?: 0;

        foreach ($choices as $key => $value) {
            $this->ansi
                ->text(str_pad($key, $maxStrLen) . '.')
                ->line($value)
                ->flush();
        }

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
        $this->writeMessage($message);

        $yes = 'y';
        $no = 'n';

        $this->ansi->text("({$yes}/{$no}) ");

        if ($default !== null) {
            $this->ansi->text('[default: ' . ($default ? $yes : $no) . ']');
        }

        $this->ansi->text(': ');

        $this->ansi->flush();

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
     * @param string|null $message
     * @return string|false
     */
    public function hidden(?string $message = null): string|false
    {
        $this->writeMessage($message);

        return $this->stream->readEach(function (string $char) {
            if ($char === "\r") {
                $this->ansi->lineFeed()->flush();
                return false;
            }
            return true;
        });
    }

    /**
     * @param string|null $message
     * @param string $replacement
     * @return string|false
     */
    public function masked(?string $message = null, string $replacement = '*'): string|false
    {
        $this->writeMessage($message);

        return $this->stream->readEach(function (string $char) use ($replacement) {
            if ($char === "\r") {
                $this->ansi->lineFeed()->flush();
                return false;
            }
            $this->ansi->text($replacement)->flush();
            return true;
        });
    }

    /**
     * @param string|null $message
     * @return void
     */
    protected function writeMessage(?string $message = null): void
    {
        if ($message !== null) {
            $this->ansi->text($message)->flush();
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