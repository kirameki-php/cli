<?php declare(strict_types=1);

namespace Kirameki\Cli\Output;

use const PHP_EOL;

class NoDecorator implements Decorator
{
    /**
     * @inheritDoc
     */
    public function newLine(): string
    {
        return PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function text(string $text): string
    {
        return $text;
    }

    /**
     * @inheritDoc
     */
    public function debug(string $text): string
    {
        return $this->text($text);
    }

    /**
     * @inheritDoc
     */
    public function info(string $text): string
    {
        return $this->text($text);
    }

    /**
     * @inheritDoc
     */
    public function warn(string $text): string
    {
        return $this->text($text);
    }

    /**
     * @inheritDoc
     */
    public function error(string $text): string
    {
        return $this->text($text);
    }
}
