<?php declare(strict_types=1);

namespace Kirameki\Cli;

use Closure;
use Kirameki\Core\Exceptions\LogicException;
use function array_key_exists;
use function array_keys;
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
     * @var array<int, list<Closure(SignalAction): mixed>>
     */
    protected array $mappedCallbacks = [];

    public function __destruct()
    {
        $this->restoreDefaultCallbacks();
    }

    /**
     * @param int $signal
     * @param Closure(SignalAction): mixed $callback
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

        // Set async on once.
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
            $event->shouldTerminate();
        }

        foreach ($this->mappedCallbacks[$signal] as $callback) {
            $callback($event);
        }

        if ($event->markedForTermination()) {
            /** @see https://tldp.org/LDP/abs/html/exitcodes.html **/
            exit(128 + $signal);
        }
    }

    /**
     * @private
     * @interal
     * @return void
     */
    public function restoreDefaultCallbacks(): void
    {
        if (count($this->mappedCallbacks) > 0) {
            $signals = array_keys($this->mappedCallbacks);
            foreach ($signals as $signal) {
                pcntl_signal($signal, SIG_DFL);
            }
            $this->mappedCallbacks = [];
        }
    }

    /**
     * @param int $signal
     * @param mixed $siginfo
     * @return SignalAction
     */
    protected function createSignalEvent(int $signal, mixed $siginfo): SignalAction
    {
        return new SignalAction($signal, $siginfo);
    }
}
