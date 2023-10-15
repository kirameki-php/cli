<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli\_Commands;

use Closure;
use Kirameki\Cli\Command;
use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\CommandDefinition;
use Kirameki\Cli\Parameters\ParameterParser;
use Kirameki\Collections\Map;
use Kirameki\Process\ExitCode;
use Kirameki\Process\SignalEvent;

class TestableCommand extends Command
{
    /**
     * @var int
     */
    public int $exitCode = ExitCode::SUCCESS;

    /**
     * @var int|null
     */
    public ?int $signal = null;

    /**
     * @var Closure(SignalEvent): mixed|null
     */
    public ?Closure $onSignal = null;

    public static function define(CommandBuilder $builder): void
    {
        $builder->name('test');
        $builder->description('testable command');
        $builder->option('echo', 'e')
            ->allowMultiple()
            ->description('echo value')
            ->requiresValue();
    }

    protected function getDefinition(): CommandDefinition
    {
        $builder = new CommandBuilder();
        self::define($builder);
        return $builder->build();
    }

    /**
     * @param list<string> $parameters
     */
    public function testExecute(?CommandDefinition $definition = null, array $parameters = []): int
    {
        $definition ??= $this->getDefinition();
        $parsed = ParameterParser::parse($definition, $parameters);
        return $this->execute(
            $definition,
            new Map($parsed['arguments']),
            new Map($parsed['options']),
        );
    }

    protected function run(): ?int
    {
        if ($this->signal !== null && $this->onSignal !== null) {
            $this->onSignal($this->signal, $this->onSignal);
        }

        $echoOption = $this->options->get('echo');
        if ($echoOption->provided) {
            foreach ($echoOption->values as $value) {
                $this->output->line($value);
            }
        }

        return $this->exitCode;
    }

    public function checkIsVerbose(): bool
    {
        return $this->isVerbose();
    }
}
