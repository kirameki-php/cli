<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\Output;

class OutputTest extends TestCase
{
    public function test_line(): void
    {
        $output = new Output();
        $output->debug("asdf");
        $output->error("DEfas");
    }
}
