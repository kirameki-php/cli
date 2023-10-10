<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Kirameki\Cli\Output\AnsiDecorator;
use Kirameki\Cli\Output\Decorator;
use Kirameki\Cli\Output\ProgressBar;
use Kirameki\Stream\StderrStream;
use Kirameki\Stream\StdoutStream;
use Kirameki\Stream\StreamWritable;
use function implode;

class Output
{
    /**
     * @param StreamWritable $stdout
     * @param StreamWritable $stderr
     * @param Decorator $decorator
     */
    public function __construct(
        public readonly StreamWritable $stdout = new StdoutStream(),
        public readonly StreamWritable $stderr = new StderrStream(),
        public readonly Decorator $decorator = new AnsiDecorator(),
    )
    {
    }

    /**
     * @param string ...$text
     * @return $this
     */
    protected function toStdout(string ...$text): static
    {
        $this->stdout->write(implode('', $text));
        return $this;
    }

    /**
     * @param string ...$text
     * @return $this
     */
    protected function toStderr(string ...$text): static
    {
        $this->stderr->write(implode('', $text));
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function text(string $text): static
    {
        return $this->toStdout(
            $this->decorator->text($text),
        );
    }

    /**
     * @param string|null $text
     * @return $this
     */
    public function line(?string $text = null): static
    {
        return $this->toStdout(
            $this->decorator->text($text ?? ''),
            $this->decorator->newLine(),
        );
    }

    /**
     * @param string $text
     * @return $this
     */
    public function debug(string $text): static
    {
        return $this->toStdout(
            $this->decorator->debug($text),
            $this->decorator->newLine(),
        );
    }

    /**
     * @param string $text
     * @return $this
     */
    public function info(string $text): static
    {
        return $this->toStdout(
            $this->decorator->info($text),
            $this->decorator->newLine(),
        );
    }

    /**
     * @param string $text
     * @return $this
     */
    public function warn(string $text): static
    {
        return $this->toStderr(
            $this->decorator->warn($text),
            $this->decorator->newLine(),
        );
    }

    /**
     * @param string $text
     * @return $this
     */
    public function error(string $text): static
    {
        return $this->toStderr(
            $this->decorator->error($text),
            $this->decorator->newLine(),
        );
    }

    /**
     * @param int $start
     * @param int $end
     * @param int $width
     * @return ProgressBar
     */
    public function progressBar(
        int $start = 0,
        int $end = 100,
        int $width = ProgressBar::DefaultWidth,
    ): ProgressBar
    {
        return new ProgressBar($this, $start, $end, $width);
    }
}
