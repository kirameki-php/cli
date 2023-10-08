<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\Input;
use Kirameki\Cli\Input\AutoCompleteReader;
use Kirameki\Stream\TmpFileStream;
use SouthPointe\Ansi\Stream;
use function substr;
use const PHP_EOL;

final class InputTest extends TestCase
{
    public function test_text(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write('123' . PHP_EOL);
        $inStream->write('456' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('123', $input->text('t:'));
        $this->assertSame('456', $input->text('t:'));
        $this->assertStringContainsString('t:123', $outStream->readFromStartToEnd());
    }

    public function test_autoComplete(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write(AutoCompleteReader::UP_ARROW);
        $inStream->write(AutoCompleteReader::UP_ARROW);
        $inStream->write(AutoCompleteReader::DOWN_ARROW);
        $inStream->write("\t" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('789', $input->autoComplete(['123', '456', '789'], 'ac:'));
        $this->assertStringContainsString('ac:789', $outStream->readFromStartToEnd());
    }

    public function test_autoComplete_no_completion(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write(AutoCompleteReader::UP_ARROW);
        $inStream->write("\t" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame("\t", $input->autoComplete([], 'ac:'));
        $this->assertStringContainsString('ac:', $outStream->readFromStartToEnd());
    }

    public function test_integer_valid(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write('123' . PHP_EOL);
        $inStream->write('456' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame(123, $input->integer('in:'));
        $this->assertSame(456, $input->integer('in:'));
    }

    public function test_integer_invalid(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write('123a' . PHP_EOL);
        $inStream->write('0' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame(0, $input->integer('in:'));
        $this->assertStringContainsString('Integer value is required.', $outStream->readFromStartToEnd());
    }

    public function test_hidden(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write('123' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('123', $input->hidden('hidden:'));
    }

    public function test_masked(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();
        $input = new Input($inStream, new Stream($outStream->getResource()));

        $inStream->write("aあ" . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('aあ', $input->masked());
        $this->assertSame('∗∗', substr($outStream->readFromStartToEnd(), 24, -6));
    }
}
