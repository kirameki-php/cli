<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use function preg_match;

class IntegerReader extends LineReader
{
    protected function formatInput(string $input): string
    {
        $formatted = parent::formatInput($input);

        // NG
        if (!preg_match("/^[0-9]$/", $formatted)) {
            $this->ansi->bell();
            return '';
        }

        return $formatted;

    }
}
