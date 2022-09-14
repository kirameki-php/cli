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
     * @return $this
     */
    public function text(string $text): static
    {
        $this->ansi
            ->text($text)
            ->noStyle()
            ->flush();
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function line(string $text): static
    {
        $this->ansi
            ->line($text)
            ->flush();
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function debug(string $text): static
    {
        $this->ansi
            ->foreground(Color::Gray)
            ->line($text)
            ->flush();
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function info(string $text): static
    {
        return $this->line($text);
    }

    /**
     * @param string $text
     * @return $this
     */
    public function notice(string $text): static
    {
        $this->ansi
            ->foreground(Color::Green)
            ->line($text)
            ->flush();
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function warning(string $text): static
    {
        $this->ansi
            ->foreground(Color::Yellow)
            ->line($text)
            ->flush();
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function error(string $text): static
    {
        $this->ansi
            ->background(Color::Red)
            ->foreground(Color::White)
            ->line($text)
            ->noStyle()
            ->flush();
        return $this;
    }
}