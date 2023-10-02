<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use function array_is_list;
use function array_key_exists;
use function array_keys;
use function count;
use function explode;
use function is_array;
use function strlen;
use function strpos;
use function substr;
use function trim;

class AutoComplete
{
    /**
     * @param array<array-key, mixed> $rules
     */
    public function __construct(
        protected array $rules = [],
    )
    {
    }

    /**
     * @param string $input
     * @param int $index
     * @return string|null
     */
    public function complement(string $input, int $index): ?string
    {
        $rules = $this->rules;
        $words = explode(' ', $input);
        $wordCount = count($words);

        for ($i = 0; $i < $wordCount - 1; $i++) {
            $word = trim($words[$i]);
            if (array_key_exists($word, $rules)) {
                $rules = $rules[$word];
            } else {
                return null;
            }
        }

        if (!is_array($rules)) {
            return null;
        }

        $candidates = array_is_list($rules)
            ? $rules
            : array_keys($rules);

        $candidatesCount = count($candidates);
        if ($candidatesCount === 0) {
            return null;
        }

        $word = $words[$wordCount - 1];

        if ($word === '') {
            $candidatesIndex = $index % $candidatesCount;
            return $index >= 0
                ? $candidates[$candidatesIndex]
                : $candidates[$candidatesCount + $candidatesIndex - 1];
        }

        foreach ($candidates as $candidate) {
            $pos = strpos($candidate, $word);
            if ($pos !== false) {
                return substr($candidate, $pos + strlen($word));
            }
        }

        return null;
    }
}
