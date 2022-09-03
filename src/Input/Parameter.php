<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Kirameki\Cli\Definitions\DefinedParameter;
use RuntimeException;
use function sprintf;

abstract class Parameter
{
    /**
     * @var list<string|null>
     */
    protected array $values = [];

    /**
     * @param DefinedParameter $defined
     */
    public function __construct(
        protected readonly DefinedParameter $defined,
    )
    {
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function addValue(?string $value): void
    {
        if (!$this->defined->isArray()) {
            throw new RuntimeException(
                sprintf('Option: %s does not accept array of inputs', $this->defined->getName())
            );
        }

        $this->values[] = $value;
    }
}
