<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use function grapheme_strlen;

final class InputInfo
{
    public string $prompt;
    public string $buffer = '';
    public string $latest = '';
    public string $clipboard = '';
    public int $point = 0;
    public int $end = 0;
    public bool $done = false;

    /**
     * @param string|null $prompt
     */
    public function __construct(?string $prompt)
    {
        $this->prompt = $prompt ?? '';
    }

    /**
     * @return int
     */
    public function cursorPosition(): int
    {
        return grapheme_strlen($this->prompt) + $this->point;
    }
}
