<?php declare(strict_types=1);

namespace Kirameki\Cli\Definitions;

class ArgumentBuilder extends ParameterBuilder
{
    /**
     * @return DefinedArgument
     */
    public function build(): DefinedArgument
    {
        return new DefinedArgument(
            $this->name,
            $this->description,
            $this->multiple,
            $this->optional,
            $this->default,
        );
    }
}
