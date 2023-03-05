<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\ExitCode;
use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use function dump;
use function posix_kill;
use const SIGTERM;

class CommandTest extends TestCase
{
    /**
     * @param string $name
     * @return Command
     */
    protected function makeCommand(string $name = __CLASS__): Command
    {
        return new class($name) extends Command
        {
            public function __construct(protected string $name)
            {
                parent::__construct();
            }

            protected function define(CommandBuilder $builder): void
            {
                $builder->name($this->name);
            }

            public function run(): ?int
            {
                $this->captureSignal(SIGTERM, function(int $signal, mixed $info) {
                    dump($info);
                });

                return ExitCode::Success;
            }
        };
    }

    public function test_captureSignal(): void
    {
        $command = $this->makeCommand('test');
        $command->execute(new Input(), new Output(), []);

        posix_kill(posix_getpid(), SIGTERM);
    }
}
