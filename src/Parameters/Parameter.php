<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

abstract class Parameter
{
    /**
     * @param string $name
     * @param string $description
     * @param bool $multiple
     * @param bool $optional
     * @param string|null $default
     */
    public function __construct(
        protected readonly string $name,
        protected readonly string $description = '',
        protected readonly bool $multiple = false,
        protected readonly bool $optional = false,
        protected readonly ?string $default = null,
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @return bool
     */
    public function getOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return string|null
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }
}
