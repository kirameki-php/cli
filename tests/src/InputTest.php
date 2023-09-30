<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\Input;
use Kirameki\Stream\TmpFileStream;
use SouthPointe\Ansi\Stream;
use function dump;
use const PHP_EOL;

final class InputTest extends TestCase
{
    public function test_integer(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();

        $input = new Input(
            $inStream,
            new Stream($outStream->getResource()),
        );

        $inStream->write('123' . PHP_EOL);
        $inStream->write('456' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame(123, $input->integer('in:'));
        $this->assertSame(456, $input->integer('in:'));
    }

    public function test_hidden(): void
    {
        $outStream = new TmpFileStream();
        $inStream = new TmpFileStream();

        $input = new Input(
            $inStream,
            new Stream($outStream->getResource()),
        );

        $inStream->write('123' . PHP_EOL);
        $inStream->write('123' . PHP_EOL);
        $inStream->write('123' . PHP_EOL);
        $inStream->rewind();

        $this->assertSame('123', $input->hidden('hidden:'));
    }
}
