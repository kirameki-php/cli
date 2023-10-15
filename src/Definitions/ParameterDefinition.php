<?php declare(strict_types=1);

namespace Kirameki\Cli\Definitions;

abstract readonly class ParameterDefinition
{
    /**
     * @param string $name
     * @param string $description
     * @param bool $allowMultiple
     * @param string|list<string>|null $default
     */
    public function __construct(
        public string $name,
        public string $description = '',
        public bool $allowMultiple = false,
        public string|array|null $default = null,
    )
    {
    }
}
