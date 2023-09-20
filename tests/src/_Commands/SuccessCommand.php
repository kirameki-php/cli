<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\_Commands;

use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\ExitCode;

class SuccessCommand extends Command
{
    public function define(CommandBuilder $builder): void
    {
        $builder->name('success');
        $builder->description('Success command');
    }

    protected function run(): ?int
    {
        return ExitCode::Success;
    }
}
