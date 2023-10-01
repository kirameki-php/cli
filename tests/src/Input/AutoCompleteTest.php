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
        $this->assertSame('it', $ac->complement('g', ['skip', 'git']));
    }

    public function test_match_exact(): void
    {
        $ac = new AutoComplete();
        $this->assertSame('it', $ac->complement('git', ['skip', 'git']));
    }

    public function test_match_repeat(): void
    {
        $ac = new AutoComplete();
        $this->assertNull($ac->complement('git g', ['skip', 'git']));
    }

    public function test_match_twice(): void
    {
        $ac = new AutoComplete();
        $this->assertSame('ommit', $ac->complement('git c', ['skip' => null, 'git' => ['commit', 'push']]));
    }
}
