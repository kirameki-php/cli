<?php declare(strict_types=1);

namespace Kirameki\Cli\Definitions;

class OptionBuilder extends ParameterBuilder
{
    /**
     * @var string|null
     */
    protected ?string $short = null;

    /**
     * @var bool
     */
    private bool $requireValue = false;

    /**
     * @param string $name
     * @param string|null $short
     */
    public function __construct(string $name, ?string $short = null)
    {
        parent::__construct($name);
        $this->short = $short;
    }

    /**
     * @return $this
     */
    public function noValue(): static
    {
        $this->requireValue(false);
        return $this;
    }

    /**
     * @param bool $toggle
     * @return $this
     */
    public function requireValue(bool $toggle = true): static
    {
        $this->requireValue = $toggle;
        return $this;
    }

    /**
     * @return DefinedOption
     */
    public function build(): DefinedOption
    {
        return new DefinedOption(
            $this->name,
            $this->short,
            $this->description,
            $this->requireValue,
            $this->multiple,
            $this->optional,
            $this->default,
        );
    }
}
