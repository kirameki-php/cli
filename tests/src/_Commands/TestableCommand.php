<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\_Commands;

use Closure;
use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Collections\Map;
use Kirameki\Container\Container;
use Kirameki\Core\SignalEvent;
use Kirameki\Process\ExitCode;

class TestableCommand extends Command
{
    /**
     * @var int
     */
    public int $exitCode = ExitCode::SUCCESS;

    /**
     * @var int|null
     */
    public ?int $signal = null;

    /**
     * @var Closure(SignalEvent): mixed|null
     */
    public ?Closure $onSignal = null;

    public static function make(?CommandBuilder $builder = null): self
    {
        $builder ??= new CommandBuilder();
        self::define($builder);

        return new self(
            new Container(),
            $builder->build(),
            new Input(),
            new Output(),
            new Map(),
            new Map(),
        );
    }

    public static function define(CommandBuilder $builder): void
    {
        $builder->name('test');
        $builder->description('testable command');
    }

    protected function run(): ?int
    {
        if ($this->signal !== null && $this->onSignal !== null) {
            $this->onSignal($this->signal, $this->onSignal);
        }

        return $this->exitCode;
    }
}
