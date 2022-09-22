<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use function grapheme_strlen;
use function substr;

final class InputInfo
{
    public const CURSOR_BACK = "\e[C";
    public const CURSOR_FORWARD = "\e[D";
    public const BACKSPACE = "\x7F";
    public const RETURN = "\r";
    public const EOF = "\x00";
    public const EOT = "\x04";

    public string $buffer = '';
    public string $last;
    public int $point = 0;
    public int $end = 0;
    public bool $done = false;

    /**
     * @param string $key
     * @return $this
     */
    public function update(string $key): self
    {
        $buffer = $this->buffer;
        $point = $this->point;
        $size = grapheme_strlen($key);

        $this->last = $key;

        if ($key === self::BACKSPACE) {
            if ($point > 0) {
                --$this->point;
                --$this->end;
            }
        }
        elseif ($key === self::RETURN) {
            $this->done = true;
        }
        else {
            $this->buffer = substr($buffer, 0, $point) . $key . substr($buffer, $point);
            $this->point += $size;
            $this->end += $size;
        }

        return $this;
    }
}
