<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

use Kirameki\Cli\Definitions\DefinedOption;

/**
 * @template-extends Parameter<DefinedOption>
 */
class Option extends Parameter
{
    /**
     * @param DefinedOption $defined
     * @param string $entered
     */
    public function __construct(
        DefinedOption $defined,
        protected readonly string $entered,
    )
    {
        parent::__construct($defined);
    }
}
