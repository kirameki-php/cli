<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

class HiddenReader extends LineReader
{
    /**
     * @return string
     */
    protected function getRenderingText(): string
    {
        return $this->prompt;
    }
}
