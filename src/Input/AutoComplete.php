<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use function array_is_list;
use function array_key_exists;
use function array_keys;
use function count;
use function dump;
use function explode;
use function strpos;
use function substr;
use function trim;

class AutoComplete
{
    /**
     * @param string $input
     * @param array<array-key, mixed> $rules
     * @return string|null
     */
    public function complement(string $input, array $rules): ?string
    {
        $words = explode(' ', $input);
        $maxWordCount = count($words);

        for ($i = 0; $i < $maxWordCount - 1; $i++) {
            $word = trim($words[$i]);
            if (array_key_exists($word, $rules)) {
                $rules = $rules[$word];
            } else {
                return null;
            }
        }

        $candidates = array_is_list($rules)
            ? $rules
            : array_keys($rules);

        foreach ($candidates as $candidate) {
            $pos = strpos($candidate, $words[$maxWordCount - 1]);
            if ($pos !== false) {
                return substr($candidate, $pos + 1);
            }
        }

        return null;
    }
}
