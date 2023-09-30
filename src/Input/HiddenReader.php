<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Closure;
use Kirameki\Stream\Streamable;
use SouthPointe\Ansi\Stream;
use function assert;
use function grapheme_extract;
use function grapheme_strlen;
use function grapheme_substr;
use function in_array;
use function is_array;
use function mb_strlen;
use function mb_strwidth;
use function preg_match;
use function readline_callback_handler_install;
use function readline_callback_handler_remove;
use function str_starts_with;
use function stream_get_contents;
use function stream_select;
use function strlen;
use function substr;

class HiddenReader extends LineReader
{
    /**
     * @return string
     */
    protected function getRenderingText(): string
    {
        return $this->prompt;
    }
}
