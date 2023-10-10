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
    public function warn(string $text): string
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
}
