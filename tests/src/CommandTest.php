<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Closure;
use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\ExitCode;
use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\SignalAction;
use Kirameki\Cli\SignalHandler;
use Kirameki\Collections\Map;
use Kirameki\Core\Exceptions\LogicException;
use function posix_getpid;
use function posix_kill;
use const SIGHUP;
use const SIGINT;
use const SIGKILL;
use const SIGQUIT;
use const SIGTERM;
use const SIGUSR1;

class CommandTest extends TestCase
{
    /**
     * @param string $name
     * @param int $signal
     * @param Closure $callback
     * @return Command
     */
    protected function commandWithSigResponder(string $name, int $signal, Closure $callback): Command
    {
        return new class($name, $signal, $callback) extends Command
        {
            public function __construct(
                protected string $name,
                protected int $signal,
                protected Closure $callback,
            )
            {
                parent::__construct();
            }

            protected function define(CommandBuilder $builder): void
            {
                $builder->name($this->name);
            }

            public function run(): ?int
            {
                $this->onSignal($this->signal, $this->callback);

                return ExitCode::Success;
            }
        };
    }

    public function test_onSignal(): void
    {
        $triggered = 0;
        $command = $this->commandWithSigResponder('t', SIGUSR1, function() use (&$triggered) {
            $triggered += 1;
        });
        $command->execute(new Map(), new Map(), new SignalHandler(), new Input(), new Output());
        posix_kill(posix_getpid(), SIGUSR1);

        self::assertSame(1, $triggered);
    }

    public function test_onSignal_terminating_signals(): void
    {
        $triggered = 0;
        foreach ([SIGHUP, SIGINT, SIGQUIT, SIGTERM] as $i => $signal) {
            $willTerminate = false;
            $command = $this->commandWithSigResponder('t', $signal, function(SignalAction $action) use (&$triggered, &$willTerminate) {
                $triggered += 1;
                $willTerminate = $action->markedForTermination();
                $action->shouldTerminate(false);
            });

            $command->execute(new Map(), new Map(), new SignalHandler(), new Input(), new Output());
            posix_kill(posix_getpid(), $signal);

            self::assertSame($i + 1, $triggered);
            self::assertTrue($willTerminate);
        }
    }

    public function test_onSignal_sigkill(): void
    {
        $this->expectExceptionMessage('SIGKILL cannot be captured.');
        $this->expectException(LogicException::class);

        $command = $this->commandWithSigResponder('t', SIGKILL, fn() => null);
        $command->execute(new Map(), new Map(), new SignalHandler(), new Input(), new Output());
    }
}
