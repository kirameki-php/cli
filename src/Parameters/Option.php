<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

class Option extends Parameter
{
    public function __construct(
        string $name,
        protected ?string $short = null,
        string $description = '',
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
}
