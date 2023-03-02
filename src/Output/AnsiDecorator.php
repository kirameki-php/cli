<?php declare(strict_types=1);

namespace Kirameki\Cli\Output;

use SouthPointe\Ansi\Buffer;
use SouthPointe\Ansi\Codes\Color;

class AnsiDecorator implements Decorator
{
    public function __construct(
        private readonly Buffer $buffer = new Buffer(),
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function newLine(): string
    {
        return $this->buffer->lineFeed()->extract();
    }

    /**
     * @inheritDoc
     */
    public function text(string $text): string
    {
        return $this->buffer
            ->text($text)
            ->resetStyle()
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function debug(string $text): string
    {
        return $this->buffer
            ->fgColor(Color::Gray)
            ->text($text)
            ->resetStyle()
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function info(string $text): string
    {
        return $this->buffer
            ->text($text)
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function notice(string $text): string
    {
        return $this->buffer
            ->fgColor(Color::Green)
            ->text($text)
            ->resetStyle()
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function warning(string $text): string
    {
        return $this->buffer
            ->fgColor(Color::Yellow)
            ->text($text)
            ->resetStyle()
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function error(string $text): string
    {
        return $this->buffer
            ->fgColor(Color::Red)
            ->text($text)
            ->resetStyle()
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function critical(string $text): string
    {
        return $this->buffer
            ->bgColor(Color::Red)
            ->fgColor(Color::White)
            ->text($text)
            ->resetStyle()
            ->extract();
    }

    /**
     * @inheritDoc
     */
    public function alert(string $text): string
    {
        return $this->buffer
            ->bgColor(Color::Red)
            ->fgColor(Color::White)
            ->blink()
            ->text($text)
            ->resetStyle()
            ->extract();
    }
}
