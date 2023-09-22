<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\_Commands;

use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Process\ExitCode;

class SuccessCommand extends Command
{
    public function __construct(
        protected int $code = ExitCode::SUCCESS,
    )
    {
        parent::__construct();
    }

    public function define(CommandBuilder $builder): void
    {
        $builder->name('success');
        $builder->description('Success command');
    }

    protected function run(): ?int
    {
        return $this->code;
    }
}
