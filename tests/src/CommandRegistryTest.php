<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\CommandRegistry;
use Kirameki\Cli\Exceptions\DuplicateEntryException;
use Kirameki\Container\Container;
use PHPUnit\Framework\Attributes\After;
use Tests\Kirameki\Cli\_Commands\AlternateCommand;
use Tests\Kirameki\Cli\_Commands\AlternateDupCommand;
use Tests\Kirameki\Cli\_Commands\TestableCommand;

final class CommandRegistryTest extends TestCase
{
    #[After]
    protected function removeCache(): void
    {
        @unlink('/tmp/kirameki/command-aliases.php');
        @rmdir('/tmp/kirameki');
    }

    protected function makeCommandRegistry(): CommandRegistry
    {
        return new CommandRegistry(new Container());
    }

    public function test_register_same_class(): void
    {
        $registry = $this->makeCommandRegistry();
        $registry->register(TestableCommand::class);

        $this->expectExceptionMessage('Command: ' . TestableCommand::class . ' is already registered.');
        $this->expectException(DuplicateEntryException::class);
        $registry->register(TestableCommand::class);
    }

    public function test_register_same_name(): void
    {
        $registry = $this->makeCommandRegistry();
        $registry->register(AlternateCommand::class);

        $registry->register(AlternateDupCommand::class);

        $this->expectExceptionMessage('Command: alt is already registered.');
        $this->expectException(DuplicateEntryException::class);
        $registry->syncAliasMap();
    }

    public function test_importAliasMap_use_cache(): void
    {
        $registry = $this->makeCommandRegistry();
        $registry->register(TestableCommand::class);
        $registry->makeCommand('test');
        $registry->makeCommand('test');

        $cacheFilePath = '/tmp/kirameki/command-aliases.php';
        $this->assertFileExists($cacheFilePath);
        $this->assertSame(['test' => TestableCommand::class], require $cacheFilePath);
    }

    public function test_importAliasMap_update_cache_added(): void
    {
        $registry = new CommandRegistry(new Container(), devMode: true);
        $registry->register(TestableCommand::class);
        $registry->makeCommand('test');
        $registry->register(AlternateCommand::class);
        $registry->makeCommand('test');

        $this->assertFileExists('/tmp/kirameki/command-aliases.php');
        $this->assertSame([
            'test' => TestableCommand::class,
            'alt' => AlternateCommand::class,
        ], require '/tmp/kirameki/command-aliases.php');
    }

    public function test_importAliasMap_update_cache_removed(): void
    {
        $registry = new CommandRegistry(new Container(), devMode: true);

        $registry->register(TestableCommand::class);
        $registry->register(AlternateCommand::class);
        $registry->makeCommand('test');

        $registry = new CommandRegistry(new Container(), devMode: true);
        $registry->register(TestableCommand::class);
        $registry->makeCommand('test');

        $this->assertFileExists('/tmp/kirameki/command-aliases.php');
        $this->assertSame(['test' => TestableCommand::class], require '/tmp/kirameki/command-aliases.php');
    }
}
