<?php declare(strict_types=1);

namespace Kirameki\Cli\Input\Builders;

use Kirameki\Cli\Input\Argument;

class ArgumentBuilder extends ParameterBuilder
{
    /**
     * @param string $name
     */
    public function __construct(
        string $name,
    )
    {
        parent::__construct($argument = new Argument());
        $argument->name = $name;
    }
}
