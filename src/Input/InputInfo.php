<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use function assert;
use function grapheme_strlen;
use function grapheme_substr;
use function in_array;
use function is_array;
use function max;
use function min;
use function preg_match;
use function str_starts_with;

final class InputInfo
{
    public const BOL = "\x01"; // ctrl+a
    public const EOL = "\x05"; // ctrl+e
    public const BACKSPACE = ["\x08", "\x7F"]; // ctrl+h, delete key
    public const DELETE = "\x04"; // ctrl+d
    public const CUT_TO_BOL = "\x15"; // ctrl+u
    public const CUT_TO_EOL = "\x0b"; // ctrl+k
    public const CUT_WORD = "\x17"; // ctrl+w
    public const PASTE = "\x19"; // ctrl+y
    public const CURSOR_FORWARD = ["\x06", "\e[C"]; // ctrl+f, right arrow
    public const CURSOR_BACK = ["\x02", "\e[D"]; // ctrl+b, left arrow
    public const END = ["\x00", "\x0a", "\x0d", "\r"]; // EOF, ctrl+j,  ctrl+m, carriage return
    public const CLEAR_SCREEN = "\f"; // ctrl+l
    public const NEXT_WORD = "\ef"; // option+f
    public const PREV_WORD = "\eb"; // option+b

    public string $buffer = '';
    public string $latest = '';
    public string $clipboard = '';
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
        $end = $this->end;
        $size = grapheme_strlen($key);

        $this->latest = $key;

        if ($this->matchesKey($key, self::BACKSPACE)) {
            if ($point > 0) {
                --$this->point;
                --$this->end;
                $this->buffer = $this->substr($buffer, 0, $point - 1) . $this->substr($buffer, $point);
            }
        } elseif ($this->matchesKey($key, self::DELETE)) {
            if ($end > 0) {
                --$this->end;
                $this->buffer = $this->substr($buffer, 0, $point) . $this->substr($buffer, $point + 1);
            }
        } elseif ($this->matchesKey($key, self::CUT_TO_BOL)) {
            $this->buffer = $this->substr($buffer, $point);
            $this->clipboard = $this->substr($buffer, 0, $point);
            $this->point = 0;
            $this->end = $end - $point;
        } elseif ($this->matchesKey($key, self::CUT_TO_EOL)) {
            $this->buffer = $this->substr($buffer, 0, $point);
            $this->clipboard = $this->substr($buffer, $point);
        } elseif ($this->matchesKey($key, self::CUT_WORD)) {
            $lookahead = $point - 1;
            $cursor = $point;
            while ($lookahead >= 0 && !$this->isWord($buffer[$lookahead])) {
                --$cursor;
                --$lookahead;
            }
            while ($lookahead >= 0 && $this->isWord($buffer[$lookahead])) {
                --$cursor;
                --$lookahead;
            }
            $this->buffer = $this->substr($buffer, 0, $cursor) . $this->substr($buffer, $point);
            $this->clipboard = $this->substr($buffer, $cursor, $point - $cursor);
            $this->point = $cursor;
            $this->end -= $point - $cursor;
        } elseif ($this->matchesKey($key, self::PASTE)) {
            $pasting = $this->clipboard;
            $this->buffer = $this->substr($buffer, 0, $point) . $pasting . $this->substr($buffer, $point);
            $move = grapheme_strlen($pasting);
            $this->point += $move;
            $this->end += $move;
        } elseif ($this->matchesKey($key, self::CURSOR_FORWARD)) {
            $this->point = min([$point + 1, $end]);
        } elseif ($this->matchesKey($key, self::CURSOR_BACK)) {
            $this->point = max([0, $point - 1]);
        } elseif ($this->matchesKey($key, self::BOL)) {
            $this->point = 0;
        } elseif ($this->matchesKey($key, self::EOL)) {
            $this->point = $end;
        } elseif ($this->matchesKey($key, self::END)) {
            $this->done = true;
        } elseif ($this->matchesKey($key, self::CLEAR_SCREEN)) {
            // TODO: clear the screen
        } elseif ($this->matchesKey($key, self::NEXT_WORD)) {
            $cursor = $this->point;
            while ($cursor < $end && !$this->isWord($buffer[$cursor])) {
                ++$cursor;
            }
            while ($cursor < $end && $this->isWord($buffer[$cursor])) {
                ++$cursor;
            }
            $this->point = $cursor;
        } elseif ($this->matchesKey($key, self::PREV_WORD)) {
            $lookahead = $this->point - 1;
            while ($lookahead >= 0 && !$this->isWord($buffer[$lookahead])) {
                --$this->point;
                --$lookahead;
            }
            while ($lookahead >= 0 && $this->isWord($buffer[$lookahead])) {
                --$this->point;
                --$lookahead;
            }
        } elseif (str_starts_with($key, "\e")) {
            // do nothing
        } else {
            $this->buffer = $this->substr($buffer, 0, $point) . $key . $this->substr($buffer, $point);
            $this->point += $size;
            $this->end += $size;
        }

        return $this;
    }

    /**
     * @param string $char
     * @return bool
     */
    protected function isWord(string $char): bool
    {
        // match separators (\p{Z}) or symbols (\p{S})
        return ! preg_match("/[\p{Z}\p{S}]/", $char);
    }

    protected function substr(string $string, int $offset, ?int $length = null): string
    {
        $newStr = grapheme_substr($string, $offset, $length);
        assert($newStr !== false);
        return $newStr;
    }

    /**
     * @param string $key
     * @param string|list<string> $candidate
     * @return bool
     */
    protected function matchesKey(string $key, string|array $candidate): bool
    {
        if (is_array($candidate)) {
            return in_array($key, $candidate, true);
        }
        return $key === $candidate;
    }
}
