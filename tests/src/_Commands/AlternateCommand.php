<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\_Commands;

use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\CommandDefinition;

class AlternateCommand extends Command
{
    public static function define(CommandBuilder $builder): void
    {
        $builder->name('alt');
        $builder->description('testable command alternate');
    }

    protected function getDefinition(): CommandDefinition
    {
        $builder = new CommandBuilder();
        self::define($builder);
        return $builder->build();
    }

    protected function run(): ?int
    {
        return null;
    }
}
