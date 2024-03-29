<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\Input;

use Kirameki\Cli\Exceptions\InvalidInputException;
use Kirameki\Cli\Input\LineReader;
use Kirameki\Stream\TmpFileStream;
use SouthPointe\Ansi\Stream;
use Tests\Kirameki\Cli\TestCase;
use function dump;
use const PHP_EOL;
use const SEEK_CUR;

final class LineReaderTest extends TestCase
{
    public function test_readline_twice(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("hello" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello', $reader->readline());
        $this->assertSame(5, $reader->point);
        $this->assertSame(5, $reader->end);

        $inStream->write('abc' . PHP_EOL);
        $inStream->seek(-4, SEEK_CUR);

        $this->assertSame('abc', $reader->readline());
        $this->assertSame(3, $reader->point);
        $this->assertSame(3, $reader->end);
    }

    public function test_multibyte(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write('あ' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('あ', $reader->readline());
    }

    public function test_escape_backspace(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("1\x7F" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('', $reader->readline());
    }

    public function test_escape_delete(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // 1 + cursor back + delete
        $inStream->write("1\x02\x04" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('', $reader->readline());
    }

    public function test_escape_cut_to_bol(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // 1 + cursor back + cut to beginning of line
        $inStream->write("1\x15" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('', $reader->readline());
        $this->assertSame('1', $reader->clipboard);
    }

    public function test_escape_cut_to_eol(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // 1 + cursor back + cut to end of line
        $inStream->write("1\x02\x0b" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('', $reader->readline());
        $this->assertSame('1', $reader->clipboard);
    }

    public function test_escape_cut_word(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello world\x17" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello ', $reader->readline());
        $this->assertSame('world', $reader->clipboard);
    }

    public function test_escape_cut_word_at_beginning(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello world\x01\x17" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello world', $reader->readline());
        $this->assertSame('', $reader->clipboard);
    }

    public function test_escape_cut_word_with_trailing_spaces(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello world   \x17" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello ', $reader->readline());
        $this->assertSame('world   ', $reader->clipboard);
    }

    public function test_escape_paste_nothing(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello\x19" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello', $reader->readline());
        $this->assertSame('', $reader->clipboard);
    }

    public function test_escape_paste_word(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello\x17\x19\x19" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hellohello', $reader->readline());
        $this->assertSame('hello', $reader->clipboard);
    }

    public function test_escape_transpose_last(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // abc + transpose
        $inStream->write("abc\x14" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('acb', $reader->readline());
        $this->assertSame(3, $reader->point);
    }

    public function test_escape_transpose_first(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // abc + transpose
        $inStream->write("abc\x01\x14" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('abc', $reader->readline());
        $this->assertSame(0, $reader->point);
    }

    public function test_escape_transpose_back(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // abc + cursor back + transpose
        $inStream->write("abc\x02\x14" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('acb', $reader->readline());
        $this->assertSame(3, $reader->point);
    }

    public function test_escape_cursor_forward(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello\x01\x06" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello', $reader->readline());
        $this->assertSame(1, $reader->point);
        $this->assertSame(5, $reader->end);
    }

    public function test_escape_cursor_eol(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello\x02\x05" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello', $reader->readline());
        $this->assertSame(5, $reader->point);
        $this->assertSame(5, $reader->end);
    }

    public function test_clear_screen(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello world\f" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello world', $reader->readline());
        $this->assertSame(11, $reader->point);
        $this->assertSame(11, $reader->end);
    }

    public function test_next_word(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // hello world + cut word
        $inStream->write("hello   world\x01\ef\ef" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello   world', $reader->readline());
        $this->assertSame(13, $reader->point);
        $this->assertSame(13, $reader->end);
    }

    public function test_prev_word(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("hello   world  \eb" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('hello   world  ', $reader->readline());
        $this->assertSame(8, $reader->point);
        $this->assertSame(15, $reader->end);
    }

    public function test_escape_seq_csi_partial(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("\e[");
        $inStream->rewind();

        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('Invalid CSI sequence');
        $this->assertSame('', $reader->readline());
    }

    public function test_escape_seq_csi_cursor_back(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("a\e[D" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('a', $reader->readline());
        $this->assertSame(0, $reader->point);
        $this->assertSame(1, $reader->end);
    }

    public function test_escape_seq_csi_cursor_back_multi(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("ab\e[3D" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('ab', $reader->readline());
        $this->assertSame(0, $reader->point);
        $this->assertSame(2, $reader->end);
    }

    public function test_escape_seq_csi_cursor_forward(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // a + BOL + cursor forward
        $inStream->write("a\x01\e[C" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('a', $reader->readline());
        $this->assertSame(1, $reader->point);
        $this->assertSame(1, $reader->end);
    }

    public function test_escape_seq_csi_cursor_forward_multi(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // a + BOL + cursor forward x3
        $inStream->write("ab\x01\e[3C" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('ab', $reader->readline());
        $this->assertSame(2, $reader->point);
        $this->assertSame(2, $reader->end);
    }

    public function test_escape_seq_csi_with_spacing(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        // a + BOL + cursor forward x3
        $inStream->write("ab\e[1 D" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('ab', $reader->readline());
        $this->assertSame(1, $reader->point);
        $this->assertSame(2, $reader->end);
    }

    public function test_escape_seq_csi_invalid(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("\e[" . PHP_EOL);
        $inStream->rewind();

        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('Invalid CSI sequence');
        $this->assertSame('', $reader->readline());
    }

    public function test_escape_seq_osc_valid(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("\e]hello\e\\" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('', $reader->readline());
        $this->assertSame(0, $reader->point);
        $this->assertSame(0, $reader->end);
    }

    public function test_escape_seq_osc_invalid(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("\e]hello\e" . PHP_EOL);
        $inStream->rewind();

        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('Invalid OSC sequence');
        $this->assertSame('', $reader->readline());
    }

    public function test_escape_seq_ss2_ss3(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $reader = new LineReader($inStream, new Stream($outStream->getResource()));

        $inStream->write("a\eN\eO" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('a', $reader->readline());
        $this->assertSame(1, $reader->point);
        $this->assertSame(1, $reader->end);
    }
}
