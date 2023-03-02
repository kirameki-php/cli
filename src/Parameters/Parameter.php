<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

use Kirameki\Cli\Definitions\ParameterDefinition;
use Kirameki\Core\Exceptions\RuntimeException;
use function array_key_exists;

/**
 * @template TDefinition as ParameterDefinition
 */
abstract class Parameter
{
    /**
     * @var bool
     */
    public readonly bool $wasEntered;

    /**
     * @param TDefinition $definition
     * @param list<string> $values
     * @param list<string|null> $enteredValues
     */
    public function __construct(
        public readonly ParameterDefinition $definition,
        public readonly array $values,
        protected readonly array $enteredValues,
    )
    {
        $this->wasEntered = $this->enteredValues !== [];
    }

    /**
     * @param int $at
     * @return string
     */
    public function value(int $at = 0): string
    {
        $values = $this->values;

        if (!array_key_exists($at, $values)) {
            throw new RuntimeException("No values exists at [{$at}]", [
                'at' => $at,
                'values' => $values,
            ]);
        }

        return $values[$at];
    }
}
