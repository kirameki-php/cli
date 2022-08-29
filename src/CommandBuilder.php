<?php declare(strict_types=1);

namespace Kirameki\Cli;

abstract class CommandBuilder
{
    protected string $name;

    protected array $arguments;

    protected array $options;

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function argument(string $name, string $default = null): static
    {

        return $this;
    }

    public function option(string $long, string $short = null, string $default = null): static
    {
        $this->arguments[$long] = $default;
        return $this;
    }
}
