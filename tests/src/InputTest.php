<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use function dump;

class InputTest extends TestCase
{
    public function test_memory_stream()
    {
        dump(tempnam('/dd/test2', 'name_'));
    }
}
