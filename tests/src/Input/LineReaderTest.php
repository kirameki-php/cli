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
}
