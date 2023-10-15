<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

use Kirameki\Cli\Definitions\ParameterDefinition;
use Kirameki\Collections\Vec;
use Kirameki\Core\Exceptions\RuntimeException;
use function array_key_exists;

/**
 * @template TDefinition as ParameterDefinition
 */
abstract readonly class Parameter
{
    /**
     * @param TDefinition $definition
     * @param Vec<string> $values
     * @param bool $provided
     */
    public function __construct(
        public ParameterDefinition $definition,
        public Vec $values,
        public bool $provided,
    )
    {
    }

    /**
     * @param int $at
     * @return string
     */
    public function value(int $at = 0): string
    {
        $values = $this->values->all();

        if (!array_key_exists($at, $values)) {
            throw new RuntimeException("No values exists at [{$at}]", [
                'at' => $at,
                'values' => $values,
            ]);
        }

        return $values[$at];
    }

    /**
     * TODO proper casting of int
     * @param int $at
     * @return int
     */
    public function valueAsInt(int $at = 0): int
    {
        return (int) $this->value($at);
    }
}
