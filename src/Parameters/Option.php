<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

class Option extends Parameter
{
    public function __construct(
        string $name,
        public ?string $short = null,
    )
    {
        $this->name = $name;
    }
}
