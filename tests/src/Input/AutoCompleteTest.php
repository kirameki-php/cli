<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\Input;

use Kirameki\Cli\Input\AutoComplete;
use Tests\Kirameki\Cli\TestCase;
use function dump;

final class AutoCompleteTest extends TestCase
{
    public function test_match_first(): void
    {
        $ac = new AutoComplete();
        $this->assertSame('it', $ac->complement('g', ['a', 'git']));
    }

    public function test_match_exact(): void
    {
        $ac = new AutoComplete();
        $this->assertSame('it', $ac->complement('git', ['a', 'git']));
    }

    public function test_match_double(): void
    {
        $ac = new AutoComplete();
        $this->assertNull($ac->complement('git g', ['a', 'git']));
    }
}
