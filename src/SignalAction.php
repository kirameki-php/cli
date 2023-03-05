<?php declare(strict_types=1);

namespace Kirameki\Cli;

class SignalAction
{
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
    public function shouldTerminate(bool $toggle = true): void
    {
        $this->terminate = $toggle;
    }

    public function markedForTermination(): bool
    {
        return $this->terminate;
    }
}