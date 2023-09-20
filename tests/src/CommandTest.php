<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Closure;
use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\Exceptions\CodeOutOfRangeException;
use Kirameki\Cli\ExitCode;
use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Collections\Map;
use Kirameki\Core\Exceptions\LogicException;
use Kirameki\Core\SignalEvent;
use ValueError;
use function posix_getpid;
use function posix_kill;
use const SIGHUP;
use const SIGINT;
use const SIGKILL;
use const SIGQUIT;
use const SIGTERM;
use const SIGUSR1;

final class CommandTest extends TestCase
{
    /**
     * @param int $signal
     * @param Closure(SignalEvent): mixed $callback
     * @return Command
     */
    protected function commandWithSigResponder(int $signal, Closure $callback): Command
    {
        return new class($signal, $callback) extends Command
        {
            public function __construct(
                protected int $signal,
                protected Closure $callback,
            )
            {
                parent::__construct();
            }

            public function define(CommandBuilder $builder): void
            {
                $builder->name('t');
            }

            public function run(): ?int
            {
                $this->onSignal($this->signal, $this->callback);

                return ExitCode::Success;
            }
        };
    }

    public function test_invalid_return(): void
    {
        $this->expectExceptionMessage('pcntl_signal(): Argument #1 ($signal) must be greater than or equal to 1');
        $this->expectException(ValueError::class);

        $command = $this->commandWithSigResponder(-1, fn() => null);
        $command->execute(new Map(), new Map(), new Input(), new Output());
    }

    public function test_onSignal(): void
    {
        $triggered = 0;
        $command = $this->commandWithSigResponder(SIGUSR1, function() use (&$triggered) {
            $triggered += 1;
        });
        $command->execute(new Map(), new Map(), new Input(), new Output());
        posix_kill(posix_getpid(), SIGUSR1);

        self::assertSame(1, $triggered);
    }

    public function test_onSignal_terminating_signals(): void
    {
        $triggered = 0;
        foreach ([SIGHUP, SIGINT, SIGQUIT, SIGTERM] as $i => $signal) {
            $willTerminate = false;
            $command = $this->commandWithSigResponder($signal, function(SignalEvent $action) use (&$triggered, &$willTerminate) {
                $triggered += 1;
                $willTerminate = $action->markedForTermination();
                $action->shouldTerminate(false);
            });

            $command->execute(new Map(), new Map(), new Input(), new Output());
            posix_kill(posix_getpid(), $signal);

            self::assertSame($i + 1, $triggered);
            self::assertTrue($willTerminate);
        }
    }

    public function test_onSignal_sigkill(): void
    {
        $this->expectExceptionMessage('SIGKILL and SIGSEGV cannot be captured.');
        $this->expectException(LogicException::class);

        $command = $this->commandWithSigResponder(SIGKILL, fn() => null);
        $command->execute(new Map(), new Map(), new Input(), new Output());
    }
}
