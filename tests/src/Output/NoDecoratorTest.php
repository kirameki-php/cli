<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\Output;

use Kirameki\Cli\Output\Decorator;
use Kirameki\Cli\Output\NoDecorator;
use Tests\Kirameki\Cli\TestCase;
use const PHP_EOL;

final class NoDecoratorTest extends TestCase
{
    protected function getDecorator(): Decorator
    {
        return new NoDecorator();
    }

    public function test_text(): void
    {
        $output = $this->getDecorator()->text('あ');
        $this->assertSame('あ', $output);
    }

    public function test_newLine(): void
    {
        $output = $this->getDecorator()->newLine();
        $this->assertSame(PHP_EOL, $output);
    }

    public function test_debug(): void
    {
        $output = $this->getDecorator()->debug('あ');
        $this->assertSame('あ', $output);
    }

    public function test_info(): void
    {
        $output = $this->getDecorator()->info('あ');
        $this->assertSame('あ', $output);
    }

    public function test_warn(): void
    {
        $output = $this->getDecorator()->warn('あ');
        $this->assertSame('あ', $output);
    }

    public function test_error(): void
    {
        $output = $this->getDecorator()->error('あ');
        $this->assertSame('あ', $output);
    }
}
