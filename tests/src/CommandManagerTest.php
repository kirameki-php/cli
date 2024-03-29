<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\CommandManager;
use Kirameki\Cli\CommandRegistry;
use Kirameki\Cli\Events\CommandExecuted;
use Kirameki\Cli\Events\CommandExecuting;
use Kirameki\Cli\Exceptions\CommandNotFoundException;
use Kirameki\Cli\Exceptions\InvalidInputException;
use Kirameki\Cli\Output;
use Kirameki\Container\Container;
use Kirameki\Event\EventManager;
use Kirameki\Process\ExitCode;
use Kirameki\Stream\MemoryStream;
use PHPUnit\Framework\Attributes\After;
use Tests\Kirameki\Cli\_Commands\AlternateCommand;
use Tests\Kirameki\Cli\_Commands\TestableCommand;

final class CommandManagerTest extends TestCase
{
    #[After]
    protected function removeCache(): void
    {
        @unlink('/tmp/kirameki/command-aliases.php');
        @rmdir('/tmp/kirameki');
    }

    protected function makeCommandManager(CommandRegistry $registry = null, EventManager $events = null): CommandManager
    {
        return new CommandManager(
            $registry ?? new CommandRegistry(new Container()),
            $events ?? new EventManager(),
        );
    }

    public function test_run_using_name(): void
    {
        $events = new EventManager();

        $executing = 0;
        $executed = 0;
        $events->append(CommandExecuting::class, function () use (&$executing) { $executing++; });
        $events->append(CommandExecuted::class, function () use (&$executed) { $executed++; });

        $manager = $this->makeCommandManager(events: $events);
        $manager->register(TestableCommand::class);
        $this->assertSame(0, $manager->run('test'));
        $this->assertSame(1, $executing);
        $this->assertSame(1, $executed);
    }

    /**
     * @group test
     */
    public function test_run_using_class_name(): void
    {
        $events = new EventManager();

        $executing = 0;
        $executed = 0;
        $events->append(CommandExecuting::class, function () use (&$executing) { $executing++; });
        $events->append(CommandExecuted::class, function () use (&$executed) { $executed++; });

        $manager = $this->makeCommandManager(events: $events);
        $manager->register(TestableCommand::class);
        $this->assertSame(0, $manager->run(TestableCommand::class));
        $this->assertSame(1, $executing);
        $this->assertSame(1, $executed);
    }

    /**
     * @group test
     */
    public function test_run_unregistered_name(): void
    {
        $this->expectExceptionMessage('Command: success is not registered.');
        $this->expectException(CommandNotFoundException::class);

        try {
            $manager = $this->makeCommandManager();
            $manager->run('success');
        } catch (CommandNotFoundException $e) {
            $this->assertSame(ExitCode::COMMAND_NOT_FOUND, $e->getExitCode());
            throw $e;
        }
    }

    public function test_run_unregistered_class(): void
    {
        $manager = $this->makeCommandManager();
        $this->assertSame(0, $manager->run(TestableCommand::class));
    }

    public function test_parseAndRun_command_only(): void
    {
        $events = new EventManager();
        $executing = 0;
        $events->append(CommandExecuting::class, function () use (&$executing) { $executing++; });

        $manager = $this->makeCommandManager(events: $events);
        $manager->register(TestableCommand::class);
        $this->assertSame(0, $manager->parseAndRun('test'));
        $this->assertSame(1, $executing);
    }

    public function test_parseAndRun_with_option(): void
    {
        $events = new EventManager();
        $executing = 0;
        $events->append(CommandExecuting::class, function () use (&$executing) { $executing++; });

        $stdout = new MemoryStream();
        $output = new Output($stdout);
        $manager = new CommandManager(new CommandRegistry(new Container()), $events, $output);
        $manager->register(TestableCommand::class);
        $this->assertSame(0, $manager->parseAndRun('test --echo "quotes" --echo no-quotes'));
        $this->assertSame(1, $executing);
        $this->assertSame("quotes\e[0m\nno-quotes\e[0m\n", $stdout->readFromStartToEnd());
    }

    public function test_parseAndRun_blank(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('No command name given.');

        $manager = $this->makeCommandManager();
        $manager->register(TestableCommand::class);
        $manager->parseAndRun('');
    }

    public function test_importAliasMap_use_cache(): void
    {
        $manager = $this->makeCommandManager();
        $manager->register(TestableCommand::class);
        $manager->run('test');
        $manager->run('test');

        $cacheFilePath = '/tmp/kirameki/command-aliases.php';
        $this->assertFileExists($cacheFilePath);
        $this->assertSame(['test' => TestableCommand::class], require $cacheFilePath);
    }

    public function test_importAliasMap_update_cache_added(): void
    {
        $registry = new CommandRegistry(new Container(), devMode: true);
        $events = new EventManager();
        $manager = new CommandManager($registry, $events);
        $manager->register(TestableCommand::class);
        $manager->run('test');
        $manager->register(AlternateCommand::class);
        $manager->run('test');

        $this->assertFileExists('/tmp/kirameki/command-aliases.php');
        $this->assertSame([
            'test' => TestableCommand::class,
            'alt' => AlternateCommand::class,
        ], require '/tmp/kirameki/command-aliases.php');
    }

    public function test_importAliasMap_update_cache_removed(): void
    {
        $registry = new CommandRegistry(new Container(), devMode: true);
        $events = new EventManager();

        $manager = new CommandManager($registry, $events);
        $manager->register(TestableCommand::class);
        $manager->register(AlternateCommand::class);
        $manager->run('test');

        $registry = new CommandRegistry(new Container(), devMode: true);
        $manager = new CommandManager($registry, $events);
        $manager->register(TestableCommand::class);
        $manager->run('test');

        $this->assertFileExists('/tmp/kirameki/command-aliases.php');
        $this->assertSame(['test' => TestableCommand::class], require '/tmp/kirameki/command-aliases.php');
    }
}
