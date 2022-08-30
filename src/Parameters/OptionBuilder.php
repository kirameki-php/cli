<?php declare(strict_types=1);

namespace Kirameki\Cli\Parameters;

class OptionBuilder extends ParameterBuilder
{
    /**
     * @var string|null
     */
    public ?string $short = null;

    public function __construct(string $name, ?string $short)
    {
        parent::__construct($name);
        $this->short = $short;
    }

    /**
     * @return Option
     */
    public function build(): Option
    {
        return new Option(
            $this->name,
            $this->short,
            $this->description,
            $this->multiple,
            $this->optional,
            $this->default,
        );
    }
}
