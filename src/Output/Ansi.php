<?php declare(strict_types=1);

namespace Kirameki\Cli\Output;

use BackedEnum;
use Kirameki\Cli\Output\Ansi\C0;
use Kirameki\Cli\Output\Ansi\Color;
use Kirameki\Cli\Output\Ansi\Csi;
use Kirameki\Cli\Output\Ansi\Fe;
use Kirameki\Cli\Output\Ansi\Sgr;
use Stringable;
use function implode;

class Ansi
{
    /**
     * @var list<string>
     */
    protected array $sequences = [];

    /**
     * @param int|string|Stringable|BackedEnum ...$sequences
     * @return $this
     */
    public function sequence(int|string|Stringable|BackedEnum ...$sequences): static
    {
        foreach ($sequences as $sequence) {
            $this->sequences[] = match(true) {
                $sequence instanceof BackedEnum => (string) $sequence->value,
                $sequence instanceof Stringable => $sequence->__toString(),
                default => (string) $sequence,
            };
        }
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function text(string $text): static
    {
        return $this->sequence($text);
    }

    /**
     * @param string $text
     * @return $this
     */
    public function line(string $text): static
    {
        return $this->text($text)->noStyle()->carriageReturn()->lineFeed();
    }

    /**
     * @return $this
     */
    public function bell(): static
    {
        return $this->sequence(C0::Bell);
    }

    /**
     * @return $this
     */
    public function tab(): static
    {
        return $this->sequence(C0::Tab);
    }

    /**
     * @return $this
     */
    public function lineFeed(): static
    {
        return $this->sequence(C0::LineFeed);
    }

    /**
     * @return $this
     */
    public function carriageReturn(): static
    {
        return $this->sequence(C0::CarriageReturn);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function cursorUp(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::CursorUp);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function cursorDown(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::CursorDown);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function cursorForward(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::CursorForward);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function cursorBack(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::CursorBack);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function cursorNextLine(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::CursorNextLine);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function cursorPreviousLine(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::CursorPrevLine);
    }

    /**
     * @param int $rows
     * @param int $columns
     * @return $this
     */
    public function cursorPosition(int $rows = 1, int $columns = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, "$rows;$columns", Csi::CursorPosition);
    }

    /**
     * @param int $cells
     * @return $this
     */
    public function eraseInDisplay(int $cells = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $cells, Csi::EraseInDisplay);
    }

    /**
     * @return $this
     */
    public function eraseInLine(): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, 2, Csi::EraseInLine);
    }

    /**
     * @return $this
     */
    public function eraseToEndOfLine(): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, 0, Csi::EraseInLine);
    }

    /**
     * @return $this
     */
    public function eraseToStartOfLine(): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, 1, Csi::EraseInLine);
    }

    /**
     * New lines are added at the bottom.
     *
     * @param int $lines
     * @return $this
     */
    public function scrollUp(int $lines = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $lines, Csi::ScrollUp);
    }

    /**
     * New lines are added at the bottom.
     *
     * @param int $lines
     * @return $this
     */
    public function scrollDown(int $lines = 1): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $lines, Csi::ScrollDown);
    }

    /**
     * @see https://en.wikipedia.org/wiki/ANSI_escape_code#8-bit
     * @return $this
     */
    public function foreground(Color $color): static
    {
        return $this->color($color);
    }

    /**
     * @see https://en.wikipedia.org/wiki/ANSI_escape_code#8-bit
     * @param Color $color
     * @return $this
     */
    public function background(Color $color): static
    {
        return $this->color($color, Sgr::SetBackgroundColor);
    }

    /**
     * @param Color $color
     * @param Sgr $section
     * @return $this
     */
    public function color(Color $color, Sgr $section = Sgr::SetForegroundColor): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, $section, $color, Csi::Sgr);
    }

    /**
     * @return $this
     */
    public function noStyle(): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, Sgr::Reset, Csi::Sgr);
    }

    /**
     * @param bool $toggle
     * @return $this
     */
    public function bold(bool $toggle = true): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, ($toggle ? Sgr::Bold : Sgr::NormalIntensity));
    }

    /**
     * @param bool $toggle
     * @return $this
     */
    public function italic(bool $toggle = true): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, ($toggle ? Sgr::Italic : Sgr::NormalIntensity));
    }

    /**
     * @param bool $toggle
     * @return $this
     */
    public function underline(bool $toggle = true): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, ($toggle ? Sgr::Underline : Sgr::NotUnderlined));
    }

    /**
     * @return $this
     */
    public function blink(bool $toggle = true): static
    {
        return $this->sequence(C0::Escape, Fe::CSI, ($toggle ? Sgr::Blink : Sgr::NotBlinking));
    }

    /**
     * @return $this
     */
    public function flush(): static
    {
        $output = implode('', $this->sequences);
        echo $output;
        return $this;
    }
}
