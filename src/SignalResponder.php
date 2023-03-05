<?php declare(strict_types=1);

namespace Kirameki\Cli;

class SignalResponder
{
    /**
     * @var bool
     */
    protected bool $propagate = false;

    /**
     * @var bool
     */
    protected bool $terminate = true;

    public function __construct(
        public readonly int $signal,
        public readonly mixed $info,
    )
    {
    }

    /**
     * @param bool $toggle
     * @return void
     */
    public function stopPropagation(bool $toggle = true): void
    {
        $this->propagate = $toggle;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagate;
    }

    /**
     * @param bool $toggle
     * @return void
     */
    public function shouldTerminate(bool $toggle = false): void
    {
        $this->terminate = $toggle;
    }

    public function markedForTermination(): bool
    {
        return $this->terminate;
    }
}
