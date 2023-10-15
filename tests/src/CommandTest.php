<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Closure;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\Exceptions\CodeOutOfRangeException;
use Kirameki\Cli\Exceptions\DefinitionException;
use Kirameki\Core\Exceptions\LogicException;
use Kirameki\Process\ExitCode;
use Kirameki\Process\SignalEvent;
use Tests\Kirameki\Cli\_Commands\TestableCommand;
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
     * @return TestableCommand
     */
    protected function makeCommandWithSigResponder(int $signal, Closure $callback): TestableCommand
    {
        $command = new TestableCommand();
        $command->signal = $signal;
        $command->onSignal = $callback;
        return $command;
    }

    public function test_invalid_return(): void
    {
        $this->expectExceptionMessage('Exit code must be between 0 and 255, -1 given.');
        $this->expectException(CodeOutOfRangeException::class);

        try {
            $command = new TestableCommand();
            $command->exitCode = -1;
            $command->testExecute();
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
        $command->testExecute();
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

            $command->testExecute();
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
        $command->testExecute();
    }

    public function test_setMemoryLimit_valid_size(): void
    {
        $command = new TestableCommand();
        $builder = new CommandBuilder('test');
        $command::define($builder);
        $size = '512M';
        $builder->setMemoryLimit($size);
        $definition = $builder->build();
        $this->assertSame($size, $definition->getMemoryLimit());
        $command->testExecute($definition);
        self::assertSame($size, ini_get('memory_limit'));
    }

    public function test_setMemoryLimit_invalid_string(): void
    {
        $this->expectExceptionMessage('Invalid memory limit format: 1T. Format must be /[0-9]+[KMG]/i.');
        $this->expectException(DefinitionException::class);

        $command = new TestableCommand();
        $builder = new CommandBuilder('test');
        $command::define($builder);
        $builder->setMemoryLimit('1T');
        $command->testExecute($builder->build());
    }

    public function test_setMemoryLimit_invalid_int(): void
    {
        $this->expectExceptionMessage('Invalid memory limit format: 123. Format must be /[0-9]+[KMG]/i.');
        $this->expectException(DefinitionException::class);

        $command = new TestableCommand();
        $builder = new CommandBuilder('test');
        $command::define($builder);
        $builder->setMemoryLimit('123');
        $command->testExecute($builder->build());
    }

    public function test_setMemoryLimit_from_option(): void
    {
        $command = new TestableCommand();
        $builder = new CommandBuilder('test');
        $command::define($builder);
        $memoryLimit = '1G';
        $definition = $builder->build();
        $this->assertNull($definition->getMemoryLimit());
        $command->testExecute($definition, ['--memory-limit', $memoryLimit]);
    }

    public function test_setTimeLimit_from_definition(): void
    {
        $command = new TestableCommand();
        $builder = new CommandBuilder('test');
        $command::define($builder);
        $timeLimit = 9999;
        $builder->setTimeLimit($timeLimit);
        $definition = $builder->build();
        $this->assertSame($timeLimit, $definition->getTimeLimit());
        $command->testExecute($definition);
    }

    public function test_setTimeLimit_from_option(): void
    {
        $command = new TestableCommand();
        $builder = new CommandBuilder('test');
        $command::define($builder);
        $timeLimit = 9999;
        $definition = $builder->build();
        $this->assertNull($definition->getTimeLimit());
        $command->testExecute($definition, ['--time-limit', (string) $timeLimit]);
    }

    public function test_is_verbose(): void
    {
        $command = new TestableCommand();
        $command->testExecute();
        self::assertFalse($command->checkIsVerbose());
    }

    public function test_is_not_verbose(): void
    {
        $command = new TestableCommand();
        $command->testExecute();
        self::assertFalse($command->checkIsVerbose());
    }
}
