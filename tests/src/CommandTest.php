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
use function posix_kill;
use const SIGUSR1;

class CommandTest extends TestCase
{
    /**
     * @param string $name
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
                $this->captureSignal($this->signal, $this->callback);

                return ExitCode::Success;
            }
        };
    }

    public function test_captureSignal(): void
    {
        $triggered = false;

        $command = $this->commandWithSigResponder('t', SIGUSR1, function(SignalAction $signal) use (&$triggered) {
            $triggered = true;
            $signal->shouldTerminate(false);
        });
        $command->execute(new SignalHandler(), new Input(), new Output(), []);
        posix_kill(posix_getpid(), SIGUSR1);

        self::assertTrue($triggered);
    }
}
