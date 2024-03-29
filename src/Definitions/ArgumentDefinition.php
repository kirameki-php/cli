<?php declare(strict_types=1);

namespace Kirameki\Cli\Definitions;

readonly class ArgumentDefinition extends ParameterDefinition
{
    /**
     * @param string $name
     * @param string $description
     * @param bool $allowMultiple
     * @param bool $optional
     * @param string|list<string>|null $default
     */
    public function __construct(
        string $name,
        string $description = '',
        bool $allowMultiple = false,
        public bool $optional = false,
        string|array|null $default = null,
    )
    {
        parent::__construct(
            $name,
            $description,
            $allowMultiple,
            $default,
        );
    }
}
