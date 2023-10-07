<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\Input;

use Kirameki\Cli\Input\WordCompletion;
use Tests\Kirameki\Cli\TestCase;

final class WorldCompletionTest extends TestCase
{
    public function test_no_rules(): void
    {
        $ac = new WordCompletion([]);
        $this->assertNull($ac->predict('g', 0));
    }

    public function test_no_match(): void
    {
        $ac = new WordCompletion(['one']);
        $this->assertNull($ac->predict('two', 0));
    }
    public function test_initial(): void
    {
        $ac = new WordCompletion(['skip', 'git']);
        $this->assertSame('skip', $ac->predict('', 0));
    }

    public function test_match_first_letter(): void
    {
        $ac = new WordCompletion(['skip', 'git']);
        $this->assertSame('it', $ac->predict('g', 0));
    }

    public function test_match_partial_letter(): void
    {
        $ac = new WordCompletion(['skip' => null]);
        $this->assertSame('ip', $ac->predict('sk', 0));
    }

    public function test_match_exact(): void
    {
        $ac = new WordCompletion(['skip', 'git']);
        $this->assertSame('', $ac->predict('git', 0));
    }

    public function test_match_repeat(): void
    {
        $ac = new WordCompletion(['skip', 'git']);
        $this->assertNull($ac->predict('git g', 0));
    }

    public function test_match_next_null(): void
    {
        $ac = new WordCompletion(['a' => null]);
        $this->assertNull($ac->predict('a ', 0));
    }

    public function test_match_twice(): void
    {
        $ac = new WordCompletion(['skip' => null, 'git' => ['commit', 'push']]);
        $this->assertSame('ommit', $ac->predict('git c', 0));
    }
}
