<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Output\Ansi;
use Kirameki\Cli\Output\Ansi\Color;

class Output
{
    /**
     * @var Ansi
     */
    readonly public Ansi $ansi;

    /**
     * @param Ansi|null $ansi
     */
    public function __construct(?Ansi $ansi = null)
    {
        $this->ansi = $ansi ?? new Ansi();
    }

    /**
     * @param string $text
     * @return Ansi
     */
    public function text(string $text): Ansi
    {
        return $this->ansi
            ->text($text)
            ->noStyle()
            ->flush();
    }

    /**
     * @param string $text
     * @return Ansi
     */
    public function line(string $text): Ansi
    {
        return $this->ansi
            ->line($text)
            ->flush();
    }

    /**
     * @param string $text
     * @return Ansi
     */
    public function debug(string $text): Ansi
    {
        return $this->ansi
            ->foreground(Color::Gray)
            ->line($text)
            ->flush();
    }

    /**
     * @param string $text
     * @return Ansi
     */
    public function info(string $text): Ansi
    {
        return $this->line($text);
    }

    /**
     * @param string $text
     * @return Ansi
     */
    public function warning(string $text): Ansi
    {
        return $this->ansi
            ->foreground(Color::Yellow)
            ->line($text)
            ->flush();
    }

    /**
     * @param string $text
     * @return Ansi
     */
    public function error(string $text): Ansi
    {
        return $this->ansi
            ->background(Color::Red)
            ->foreground(Color::White)
            ->line($text)
            ->noStyle()
            ->flush();
    }
}