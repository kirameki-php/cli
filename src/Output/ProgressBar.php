<?php declare(strict_types=1);

namespace Kirameki\Cli\Output;

use Kirameki\Cli\Output;
use function number_format;
use function str_pad;
use const STR_PAD_LEFT;

class ProgressBar
{
    public const DefaultWidth = 120;

    public function __construct(
        protected Output $output,
        protected int $start = 0,
        protected int $end = 100,
        protected int $current = 0,
        protected int $width = self::DefaultWidth,
        protected string $completedMark = '▉',
        protected string $remainingMark = ' ',
        protected string $prefix = '',
        protected string $suffix = '',
    )
    {
    }

    /**
     * @return void
     */
    public function render(): void
    {
        $stdout = $this->output->stdout;
        $ratio = $this->getRatio();
        $max = $this->width;
        $completed = (int) ($max * $ratio);
        $remaining = $max - $completed;

        $stdout->write($this->prefix);
        for($i = 0; $i < $completed; $i++) {
            $stdout->write($this->completedMark);
        }
        for($j = $remaining; $j < $max; $j++) {
            $stdout->write($this->remainingMark);
        }
        $stdout->write($this->suffix);
        $stdout->write(' ');
        $percentage =  number_format($ratio * 100, 1);
        $percentageFormatted = str_pad($percentage, 4, ' ', STR_PAD_LEFT);
        $stdout->write("{$percentageFormatted}%");
    }

    /**
     * @return int
     */
    public function getStartingAmount(): int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEndingAmount(): int
    {
        return $this->end;
    }

    /**
     * @return int
     */
    public function getCurrentAmount(): int
    {
        return $this->current;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function advance(int $amount): static
    {
        $this->current += $amount;
        return $this;
    }

    /**
     * @return $this
     */
    public function reset(): static
    {
        $this->current = $this->start;
        return $this;
    }

    public function finish(): static
    {
        $this->current = $this->end;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->current >= $this->end;
    }

    /**
     * @return float
     */
    public function getRatio(): float
    {
        return $this->getEndingAmount() / $this->getCurrentAmount();
    }

    /**
     * @param int $decimals
     * @return float
     */
    public function calculatePercentage(int $decimals = 0): float
    {
        $percentage = $this->getRatio() * 100.0;
        return (float) number_format($percentage, $decimals);
    }
}
