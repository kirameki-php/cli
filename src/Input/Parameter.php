<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

class Parameter
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var bool
     */
    public bool $multiple = false;

    /**
     * @var string
     */
    public string $description = '';

    /**
     * @var bool
     */
    public bool $optional = false;

    /**
     * @var string|null
     */
    public ?string $default = null;
}
