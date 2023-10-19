<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\_Commands;

use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;

class AlternateDupCommand extends Command
{
    public static function define(CommandBuilder $builder): void
    {
        $builder->name('alt');
        $builder->description('duplicate of command alternate for error checking');
    }

    protected function run(): ?int
    {
        return null;
    }
}
