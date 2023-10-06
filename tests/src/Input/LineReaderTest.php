<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\Input;

use Kirameki\Cli\Input\LineReader;
use Kirameki\Stream\TmpFileStream;
use SouthPointe\Ansi\Stream;
use Tests\Kirameki\Cli\TestCase;
use const PHP_EOL;

final class LineReaderTest extends TestCase
{
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
}
