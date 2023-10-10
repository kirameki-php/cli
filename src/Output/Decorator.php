<?php declare(strict_types=1);

namespace Kirameki\Cli\Output;

interface Decorator
{
    /**
     * @return string
     */
    public function newLine(): string;

    /**
     * @param string $text
     * @return string
     */
    public function text(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public function debug(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public function info(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public function warn(string $text): string;

    /**
     * @param string $text
     * @return string
     */
    public function error(string $text): string;
}
