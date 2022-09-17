<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Input\Stream;
use Kirameki\Cli\Output\Ansi;

class Input
{
    /**
     * @param Ansi $ansi
     * @param Stream $stream
     */
    public function __construct(
        readonly protected Ansi $ansi,
        readonly protected Stream $stream,
    )
    {
    }

    /**
     * @param string|null $message
     * @return string|false
     */
    public function hidden(?string $message = null): string|false
    {
        if ($message !== null) {
            $this->ansi->text($message)->flush();
        }

        return $this->stream->readTo("\r", function (string $char) {
            if ($char === "\r") {
                $this->ansi->lineFeed()->flush();
            }
        });
    }

    /**
     * @param string|null $message
     * @param string $substitute
     * @return string|false
     */
    public function masked(?string $message = null, string $substitute = '*'): string|false
    {
        if ($message !== null) {
            $this->ansi->text($message)->flush();
        }

        return $this->stream->readTo("\r", function (string $char) use ($substitute) {
            if ($char !== "\r") {
                $this->ansi->text($substitute)->flush();
            } else {
                $this->ansi->lineFeed()->flush();
            }
        });
    }

    /**
     * @param string|null $message
     * @param bool $default
     * @return bool
     */
    public function confirm(?string $message = null, bool $default = false): bool
    {
        if ($message !== null) {
            $this->ansi->text($message . ' ');
        }

        $yes = 'yes';
        $no = 'no';

        $this->ansi->text("({$yes}/{$no}) ");

        if ($default !== null) {
            $this->ansi->text('[default: ' . ($default ? $yes : $no) . ']');
        }

        $this->ansi->text(': ');

        $this->ansi->flush();

        $input = $this->stream->readLine();

        if ($input === false) {
            return $default;
        }

        return $input === $yes;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->stream->close();
    }
}