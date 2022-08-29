<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

class Argument extends Parameter
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
