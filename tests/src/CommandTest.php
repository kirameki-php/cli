<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Closure;
use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\Exceptions\CodeOutOfRangeException;
use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Collections\Map;
use Kirameki\Core\Exceptions\LogicException;
use Kirameki\Core\SignalEvent;
use Kirameki\Process\ExitCode;
use Tests\Kirameki\Cli\_Commands\SuccessCommand;
use function ini_get;
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
    protected function makeCommandWithSigResponder(int $signal, Closure $callback): Command
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

                return ExitCode::SUCCESS;
            }
        };
    }

    protected function makeRuntimeCommand(?string $memoryLimit = null, ?int $timeLimit = null): Command
    {
        return new class($memoryLimit, $timeLimit) extends Command
        {
            public function __construct(
                protected ?string $memoryLimit,
                protected ?int $timeLimit,
            )
            {
                parent::__construct();
            }

            public function define(CommandBuilder $builder): void
            {
                $builder->name('t');
                $builder->setMemoryLimit($this->memoryLimit);
                $builder->setTimeLimit($this->timeLimit);
            }

            public function run(): ?int
            {
                return ExitCode::SUCCESS;
            }
        };
    }

    public function test_invalid_return(): void
    {
        $this->expectExceptionMessage('Exit code must be between 0 and 255, -1 given.');
        $this->expectException(CodeOutOfRangeException::class);

        try {
            $command = new SuccessCommand(-1);
            $command->execute(new Map(), new Map(), new Input(), new Output());
        } catch (CodeOutOfRangeException $e) {
            self::assertSame(ExitCode::STATUS_OUT_OF_RANGE, $e->getExitCode());
            throw $e;
        }
    }

    public function test_onSignal(): void
    {
        $triggered = 0;
        $command = $this->makeCommandWithSigResponder(SIGUSR1, function() use (&$triggered) {
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
            $command = $this->makeCommandWithSigResponder($signal, function(SignalEvent $action) use (&$triggered, &$willTerminate) {
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

        $command = $this->makeCommandWithSigResponder(SIGKILL, fn() => null);
        $command->execute(new Map(), new Map(), new Input(), new Output());
    }

    public function test_setMemoryLimit_valid_size(): void
    {
        $command = $this->makeRuntimeCommand('512M');
        $command->execute(new Map(), new Map(), new Input(), new Output());
        self::assertSame('512M', ini_get('memory_limit'));
    }

    public function test_setMemoryLimit_invalid_string(): void
    {
        $command = $this->makeRuntimeCommand('1G');
        $command->execute(new Map(), new Map(), new Input(), new Output());
    }
}
