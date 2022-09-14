<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

use Kirameki\Cli\Definitions\DefinedArgument;

/**
 * @template-extends Parameter<DefinedArgument>
 */
class Argument extends Parameter
{
    /**
     * @param DefinedArgument $defined
     */
    public function __construct(
        DefinedArgument $defined,
    )
    {
        parent::__construct($defined);
    }
}
