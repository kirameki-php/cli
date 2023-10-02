<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\Input;

use Kirameki\Cli\Input\AutoComplete;
use Tests\Kirameki\Cli\TestCase;

final class AutoCompleteTest extends TestCase
{
    public function test_match_none(): void
    {
        $ac = new AutoComplete([]);
        $this->assertNull($ac->complement('g'));
    }

    public function test_match_first(): void
    {
        $ac = new AutoComplete(['skip', 'git']);
        $this->assertSame('it', $ac->complement('g'));
    }

    public function test_match_partial(): void
    {
        $ac = new AutoComplete(['skip' => null]);
        $this->assertSame('ip', $ac->complement('sk'));
    }

    public function test_match_exact(): void
    {
        $ac = new AutoComplete(['skip', 'git']);
        $this->assertSame('', $ac->complement('git'));
    }

    public function test_match_repeat(): void
    {
        $ac = new AutoComplete(['skip', 'git']);
        $this->assertNull($ac->complement('git g'));
    }

    public function test_match_next_null(): void
    {
        $ac = new AutoComplete(['a' => null]);
        $this->assertNull($ac->complement('a '));
    }

    public function test_match_twice(): void
    {
        $ac = new AutoComplete(['skip' => null, 'git' => ['commit', 'push']]);
        $this->assertSame('ommit', $ac->complement('git c'));
    }
}
