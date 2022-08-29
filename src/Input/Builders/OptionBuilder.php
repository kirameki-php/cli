<?php declare(strict_types=1);

namespace Kirameki\Cli\Input\Builders;

use Kirameki\Cli\Input\Option;

/**
 * @template-extends ParameterBuilder<Option>
 */
class OptionBuilder extends ParameterBuilder
{
    /**
     * @param string $name
     * @param string $short
     */
    public function __construct(
        string $name,
        protected string $short,
    )
    {
        parent::__construct($option = new Option());
        $option->name = $name;
        $option->short = $short;
    }
}
