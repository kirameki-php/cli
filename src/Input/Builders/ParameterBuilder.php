<?php declare(strict_types=1);

namespace Kirameki\Cli\Input\Builders;

use Kirameki\Cli\Input\Parameter;

/**
 * @template TParameter of Parameter
 */
class ParameterBuilder
{
    /**
     * @param TParameter $params
     */
    public function __construct(
        protected Parameter $params,
    )
    {
    }

    /**
     * @param bool $toggle
     * @return $this
     */
    public function multiple(bool $toggle = true): static
    {
        $this->params->multiple = $toggle;
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function description(string $text): static
    {
        $this->params->description = $text;
        return $this;
    }

    /**
     * @param string|null $default
     * @return $this
     */
    public function optional(string $default = null): static
    {
        $this->params->optional = true;
        $this->params->default = $default;
        return $this;
    }
}
