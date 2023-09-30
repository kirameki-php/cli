<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

class MaskedReader extends LineReader
{
    /**
     * @return string
     */
    protected function getRenderingText(): string
    {
        return $this->prompt . str_repeat('∗', $this->end);
    }
}
