<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\CommandManager;
use Kirameki\Cli\Events\CommandExecuting;
use Kirameki\Cli\Exceptions\CommandNotFoundException;
use Kirameki\Cli\ExitCode;
use Kirameki\Container\Container;
use Kirameki\Event\EventDispatcher;
use Tests\Kirameki\Cli\_Commands\SuccessCommand;

final class CommandManagerTest extends TestCase
{
    public function test_execute_name(): void
    {
        $events = new EventDispatcher();

        $executing = 0;
        $executed = 0;
        $events->listen(CommandExecuting::class, function () use (&$executing) { $executing++; });
        $events->listen(CommandExecuting::class, function () use (&$executed) { $executed++; });

        $manager = new CommandManager(new Container(), $events);
        $manager->register(SuccessCommand::class);
        $this->assertSame(0, $manager->execute('success'));
        $this->assertSame(1, $executing);
        $this->assertSame(1, $executed);
    }

    public function test_execute_class(): void
    {
        $events = new EventDispatcher();

        $executing = 0;
        $executed = 0;
        $events->listen(CommandExecuting::class, function () use (&$executing) { $executing++; });
        $events->listen(CommandExecuting::class, function () use (&$executed) { $executed++; });

        $manager = new CommandManager(new Container(), $events);
        $manager->register(SuccessCommand::class);
        $this->assertSame(0, $manager->execute(SuccessCommand::class));
        $this->assertSame(1, $executing);
        $this->assertSame(1, $executed);
    }

    public function test_execute_unregistered_name(): void
    {
        $this->expectExceptionMessage('Command: success is not registered.');
        $this->expectException(CommandNotFoundException::class);

        try {
            $events = new EventDispatcher();
            $manager = new CommandManager(new Container(), $events);
            $manager->execute('success');
        } catch (CommandNotFoundException $e) {
            $this->assertSame(ExitCode::CommandNotFound, $e->getExitCode());
            throw $e;
        }
    }

    public function test_execute_unregistered_class(): void
    {
        $events = new EventDispatcher();
        $manager = new CommandManager(new Container(), $events);
        $this->assertSame(0, $manager->execute(SuccessCommand::class));
    }
}
