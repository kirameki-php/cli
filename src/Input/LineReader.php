<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Kirameki\Stream\Streamable;
use SouthPointe\Ansi\Stream;
use function assert;
use function grapheme_extract;
use function grapheme_strlen;
use function grapheme_substr;
use function in_array;
use function is_array;
use function mb_strlen;
use function mb_strwidth;
use function preg_match;
use function shell_exec;
use function str_starts_with;
use function stream_get_contents;
use function stream_select;
use function strlen;
use function substr;
use function trim;
use const GRAPHEME_EXTR_COUNT;

class LineReader
{
    public const BOL = "\x01"; // ctrl+a
    public const EOL = "\x05"; // ctrl+e
    public const BACKSPACE = ["\x08", "\x7F", "\b"]; // ctrl+h, delete key
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
     * @param Streamable $stdin
     * @param Stream $ansi
     * @param string $prompt
     */
    public function __construct(
        protected readonly Streamable $stdin,
        protected readonly Stream $ansi,
        public string $prompt = '',
    )
    {
    }

    /**
     * @return string
     */
    public function readline(): string
    {
        $settings = trim((string) shell_exec('stty -g'));

        // -icanon for non-canonical mode (callback after each character read)
        // -echo to hide output so we can handle it ourself
        shell_exec('stty -icanon -echo');

        try {
            $this->processInput('');
            while (!$this->done) {
                $this->processInput($this->waitForInput());
            }
        }
        finally {
            // restore stty settings
            shell_exec("stty {$settings}");
        }

        return $this->buffer;
    }

    /**
     * @return string
     */
    protected function waitForInput(): string
    {
        $stream = $this->stdin->getResource();
        $read = [$stream];
        $write = $except = null;
        stream_select($read, $write, $except, null);

        $char = stream_get_contents($stream, 1);

        if ($char === false) {
            return self::DELETE;
        }

        if ($char === "\e") {
            return $this->readEscapeSequences($stream, $char);
        }

        if (!preg_match("//u", $char)) {
            return $this->readMultibytePortions($stream, $char);
        }

        return $char;
    }

    /**
     * @param resource $stream
     * @param string $input
     * @return string
     */
    protected function readMultibytePortions($stream, string $input): string
    {
        do {
            $char = stream_get_contents($stream, 1);
            if ($char === false) {
                break;
            }
            $input .= $char;
        }
        while(!preg_match("//u", $input));

        return $input;
    }

    /**
     * @param resource $stream
     * @param string $input
     * @return string
     */
    protected function readEscapeSequences(mixed $stream, string $input): string
    {
        $readByte = static fn() => stream_get_contents($stream, 1);

        $char = $readByte();
        $input .= $char;

        // CSI (Control Sequence Introducer)
        if ($char === '[') {
            if (($char = $readByte()) === false) {
                return $input;
            }
            while($char >= "\x30" && $char <= "\x3F") {
                $input .= $char;
                $char = $readByte();
            }
            while($char >= "\x20" && $char <= "\x2F") {
                $input .= $char;
                $char = $readByte();
            }
            if ($char >= "\x40" && $char <= "\x7E") {
                $input .= $char;
            }
        }
        // OSC (Operating System Command)
        elseif ($char === ']') {
            while(substr($input, -2) !== '\e\\') {
                $input .= $readByte();
            }
        }
        // SS2 or SS3 (Single Shifts)
        elseif ($char === 'N' || $char === 'O') {
            $input .= $readByte();
        }

        return $input;
    }

    /**
     * @param string $input
     * @return void
     */
    protected function processInput(string $input): void
    {
        $buffer = $this->buffer;
        $point = $this->point;
        $end = $this->end;

        $this->latest = $input;

        if (self::matchesKey($input, self::BACKSPACE)) {
            if ($point > 0) {
                $this->point--;
                $this->end--;
                $this->buffer = self::substr($buffer, 0, $point - 1) . self::substr($buffer, $point);
            }
        }
        elseif (self::matchesKey($input, self::DELETE)) {
            if ($end > 0) {
                $this->end--;
                $this->buffer = self::substr($buffer, 0, $point) . self::substr($buffer, $point + 1);
            }
        }
        elseif (self::matchesKey($input, self::CUT_TO_BOL)) {
            $this->buffer = self::substr($buffer, $point);
            $this->clipboard = self::substr($buffer, 0, $point);
            $this->point = 0;
            $this->end = $end - $point;
        }
        elseif (self::matchesKey($input, self::CUT_TO_EOL)) {
            $this->buffer = self::substr($buffer, 0, $point);
            $this->clipboard = self::substr($buffer, $point);
        }
        elseif (self::matchesKey($input, self::CUT_WORD)) {
            $lookahead = $point - 1;
            $cursor = $point;
            while ($lookahead >= 0 && !self::isWord($buffer[$lookahead])) {
                --$cursor;
                --$lookahead;
            }
            while ($lookahead >= 0 && self::isWord($buffer[$lookahead])) {
                --$cursor;
                --$lookahead;
            }
            $this->buffer = self::substr($buffer, 0, $cursor) . self::substr($buffer, $point);
            $this->clipboard = self::substr($buffer, $cursor, $point - $cursor);
            $this->point = $cursor;
            $this->end -= $point - $cursor;
        }
        elseif (self::matchesKey($input, self::PASTE)) {
            $pasting = $this->clipboard;
            $this->buffer = self::substr($buffer, 0, $point) . $pasting . self::substr($buffer, $point);
            $move = grapheme_strlen($pasting);
            $this->point += $move;
            $this->end += $move;
        }
        elseif (self::matchesKey($input, self::CURSOR_FORWARD)) {
            if ($point < $end) {
                $this->point = $point + 1;
            }
        }
        elseif (self::matchesKey($input, self::CURSOR_BACK)) {
            if ($point > 0) {
                $this->point = $point - 1;
            }
        }
        elseif (self::matchesKey($input, self::BOL)) {
            $this->point = 0;
        }
        elseif (self::matchesKey($input, self::EOL)) {
            $this->point = $end;
        }
        elseif (self::matchesKey($input, self::END)) {
            $this->done = true;
        }
        elseif (self::matchesKey($input, self::CLEAR_SCREEN)) {
            $this->ansi
                ->eraseScreen()
                ->cursorPosition(1, 1)
                ->flush();
        }
        elseif (self::matchesKey($input, self::NEXT_WORD)) {
            $cursor = $point;
            while ($cursor < $end && !self::isWord($buffer[$cursor])) {
                ++$cursor;
            }
            while ($cursor < $end && self::isWord($buffer[$cursor])) {
                ++$cursor;
            }
            $this->point = $cursor;
        }
        elseif (self::matchesKey($input, self::PREV_WORD)) {
            $lookahead = $point - 1;
            while ($lookahead >= 0 && !self::isWord($buffer[$lookahead])) {
                --$this->point;
                --$lookahead;
            }
            while ($lookahead >= 0 && self::isWord($buffer[$lookahead])) {
                --$this->point;
                --$lookahead;
            }
        }
        elseif (str_starts_with($input, "\e")) {
            // do nothing
        }
        else {
            $input = $this->formatInput($input);
            $length = grapheme_strlen($input);
            $this->buffer = self::substr($buffer, 0, $point) . $input . self::substr($buffer, $point);
            $this->point += $length;
            $this->end += $length;
        }

        $this->done
            ? $this->done()
            : $this->render();
    }

    /**
     * @param string $input
     * @return string
     */
    protected function formatInput(string $input): string
    {
        return $input;
    }

    /**
     * @return void
     */
    protected function render(): void
    {
        $this->ansi
            ->eraseLine()
            ->carriageReturn()
            ->text($this->getRenderingText())
            ->carriageReturn()
            ->cursorForward($this->calcCursorPosition())
            ->flush();
    }

    /**
     * @return void
     */
    protected function done(): void
    {
        $this->ansi
            ->lineFeed()
            ->flush();
    }

    /**
     * @return string
     */
    protected function getRenderingText(): string
    {
        return $this->prompt . $this->buffer;
    }

    /**
     * @param string $char
     * @return bool
     */
    protected static function isWord(string $char): bool
    {
        // match separators (\p{Z}) or symbols (\p{S})
        return !preg_match("/[\p{Z}\p{S}]/", $char);
    }

    protected static function substr(string $string, int $offset, ?int $length = null): string
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
    protected static function matchesKey(string $key, string|array $candidate): bool
    {
        return is_array($candidate)
            ? in_array($key, $candidate, true)
            : $key === $candidate;
    }

    /**
     * @return int
     */
    protected function calcCursorPosition(): int
    {
        $buffer = $this->buffer;
        $position = 0;
        $offset = 0;
        $bytes = strlen(self::substr($buffer, 0, $this->point));

        while ($offset < $bytes) {
            $char = grapheme_extract($buffer, 1, GRAPHEME_EXTR_COUNT, $offset, $offset);
            if ($char !== false) {
                $position += self::getCharWidth($char);
            }
        }

        return strlen($this->prompt) + $position;
    }

    /**
     * @param string $char
     * @return int
     */
    protected static function getCharWidth(string $char): int
    {
        // detect full-width characters
        // mb_strlen check is required since some emojis will return values greater than 1 with mb_strwidth.
        // Ex: mb_strwidth('👋🏻') will return 2 but should return 1.
        return (mb_strwidth($char) === 2 && mb_strlen($char) === 1)
            ? 2
            : 1;
    }
}