<?php declare(strict_types=1);

namespace Kirameki\Cli\Definitions;

class Option extends Parameter
{
    public function __construct(
        string $name,
        protected ?string $short = null,
        string $description = '',
        protected bool $requireValue = true,
        bool $multiple = false,
        bool $optional = false,
        ?string $default = null,
    )
    {
        parent::__construct(
            $name,
            $description,
            $multiple,
            $optional,
            $default,
        );
    }

    /**
     * @return string|null
     */
    public function getShort(): ?string
    {
        return $this->short;
    }

    /**
     * @return bool
     */
    public function requireValue(): bool
    {
        return $this->requireValue;
    }
}
