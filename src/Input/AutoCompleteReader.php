<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Kirameki\Stream\Streamable;
use SouthPointe\Ansi\Stream;
use function array_is_list;
use function array_keys;
use function explode;
use function strpos;
use function trim;

class AutoCompleteReader extends LineReader
{
    /**
     * @param Streamable $stdin
     * @param Stream $ansi
     * @param string $prompt
     * @param array<array-key, mixed> $rules
     */
    public function __construct(
        Streamable $stdin,
        Stream $ansi,
        string $prompt = '',
        protected array $rules = [],
    )
    {
        parent::__construct($stdin, $ansi, $prompt);
    }

    /**
     * @return string
     */
    protected function getRenderingText(): string
    {
        $completion = $this->getCompletion();

        return $this->prompt . $this->buffer;
    }

    /**
     * @return string
     */
    protected function getCompletion(): ?string
    {
        $current = $this->rules;
        $lastWord = null;
        $words = explode(' ', $this->buffer);
        $maxWordCount = count($words);
        for ($i = 0; $i < $maxWordCount; $i++) {
            $lastWord = trim($words[$i]);
            $current = $current[$lastWord] ?? null;
            if ($current === null) {
                return null;
            }
        }

        $candidates = array_is_list($current)
            ? $current
            : array_keys($current);

        if ($i === $maxWordCount) {
            return $candidates[0] ?? null;
        }

        foreach ($candidates as $candidate) {
            $pos = strpos($candidate, $lastWord);
            if ($pos === false) {
                return $candidate;
            } else {
                return substr($candidate, $pos);
            }
        }
        return null;
    }
}
