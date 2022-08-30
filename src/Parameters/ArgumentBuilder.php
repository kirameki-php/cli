<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

class ArgumentBuilder extends ParameterBuilder
{
    /**
     * @return Argument
     */
    public function build(): Argument
    {
        return new Argument(
            $this->name,
            $this->description,
            $this->multiple,
            $this->optional,
            $this->default,
        );
    }
}
