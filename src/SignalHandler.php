<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Core\Exceptions\LogicException;
use function array_key_exists;
use function count;
use function in_array;
use function pcntl_async_signals;
use function pcntl_signal;
use const SIGHUP;
use const SIGINT;
use const SIGKILL;
use const SIGTERM;

class SignalHandler
{
    /**
     * @see https://www.gnu.org/software/libc/manual/html_node/Termination-Signals.html
     */
    public final const TermSignals = [
        SIGHUP,  // 1
        SIGINT,  // 2
        SIGQUIT, // 3
        SIGTERM, // 15
    ];

    /**
     * @var array<int, list<Closure(SignalEvent): mixed>>
     */
    protected array $mappedCallbacks = [];

    /**
     * @param int $signal
     * @param Closure(SignalEvent): mixed $callback
     * @return void
     */
    public function capture(int $signal, Closure $callback): void
    {
        if ($signal === SIGKILL) {
            throw new LogicException('SIGKILL cannot be captured.', [
                'signal' => $signal,
                'callback' => $callback,
            ]);
        }

        // Set async on, once.
        if (count($this->mappedCallbacks) === 0) {
            pcntl_async_signals(true);
        }

        // Set signal handler trigger once.
        if (!array_key_exists($signal, $this->mappedCallbacks)) {
            pcntl_signal($signal, $this->invoke(...));
        }

        $this->mappedCallbacks[$signal][] = $callback;
    }

    /**
     * @param int $signal
     * @param mixed $siginfo
     * @return void
     */
    protected function invoke(int $signal, mixed $siginfo): void
    {
        $event = $this->createSignalEvent($signal, $siginfo);

        if (in_array($signal, self::TermSignals)) {
            $event->shouldTerminate(true);
        }

        foreach ($this->mappedCallbacks[$signal] as $callback) {
            $callback($event);
            if ($event->isPropagationStopped()) {
                break;
            }
        }

        if ($event->markedForTermination()) {
            /** @see https://tldp.org/LDP/abs/html/exitcodes.html **/
            exit(128 + $signal);
        }
    }

    /**
     * @param int $signal
     * @param mixed $siginfo
     * @return SignalEvent
     */
    protected function createSignalEvent(int $signal, mixed $siginfo): SignalEvent
    {
        return new SignalEvent($signal, $siginfo);
    }
}
