<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\Output;
use Kirameki\Cli\Output\NoDecorator;
use Kirameki\Stream\TmpFileStream;
use const PHP_EOL;

final class OutputTest extends TestCase
{
    public function test_text(): void
    {
        $outStream = new TmpFileStream();
        $errStream = new TmpFileStream();
        $output = new Output($outStream, $errStream, new NoDecorator());
        $output->text('あ');

        $this->assertSame('あ', $outStream->readFromStartToEnd());
        $this->assertSame('', $errStream->readFromStartToEnd());
    }

    public function test_line(): void
    {
        $outStream = new TmpFileStream();
        $errStream = new TmpFileStream();
        $output = new Output($outStream, $errStream, new NoDecorator());
        $output->line('あ');

        $this->assertSame('あ' . PHP_EOL, $outStream->readFromStartToEnd());
        $this->assertSame('', $errStream->readFromStartToEnd());
    }

    public function test_debug(): void
    {
        $outStream = new TmpFileStream();
        $errStream = new TmpFileStream();
        $output = new Output($outStream, $errStream, new NoDecorator());
        $output->debug('あ');

        $this->assertSame('あ' . PHP_EOL, $outStream->readFromStartToEnd());
        $this->assertSame('', $errStream->readFromStartToEnd());
    }

    public function test_info(): void
    {
        $outStream = new TmpFileStream();
        $errStream = new TmpFileStream();
        $output = new Output($outStream, $errStream, new NoDecorator());
        $output->info('あ');

        $this->assertSame('あ' . PHP_EOL, $outStream->readFromStartToEnd());
        $this->assertSame('', $errStream->readFromStartToEnd());
    }

    public function test_warn(): void
    {
        $outStream = new TmpFileStream();
        $errStream = new TmpFileStream();
        $output = new Output($outStream, $errStream, new NoDecorator());
        $output->warn('あ');

        $this->assertSame('', $outStream->readFromStartToEnd());
        $this->assertSame('あ' . PHP_EOL, $errStream->readFromStartToEnd());
    }

    public function test_error(): void
    {
        $outStream = new TmpFileStream();
        $errStream = new TmpFileStream();
        $output = new Output($outStream, $errStream, new NoDecorator());
        $output->error('あ');

        $this->assertSame('', $outStream->readFromStartToEnd());
        $this->assertSame('あ' . PHP_EOL, $errStream->readFromStartToEnd());
    }
}
