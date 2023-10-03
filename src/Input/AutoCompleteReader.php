<?php declare(strict_types=1);

namespace Kirameki\Cli\Input;

use Kirameki\Stream\Streamable;
use SouthPointe\Ansi\Ansi;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\Ansi\Stream;
use function array_is_list;
use function array_keys;
use function explode;
use function strpos;
use function trim;

class AutoCompleteReader extends LineReader
{
    public const UP_ARROW = "\e[A"; // up arrow
    public const DOWN_ARROW = "\e[B"; // down arrow
    public const TAB = "\t"; // tab

    /**
     * @var WordCompletion
     */
    protected WordCompletion $completion;

    /**
     * @var int
     */
    protected int $suggestIndex = 0;

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
        array $rules = [],
    )
    {
        parent::__construct($stdin, $ansi, $prompt);
        $this->completion = new WordCompletion($rules);
    }

    /**
     * @param string $input
     * @return void
     */
    protected function processInput(string $input): void
    {
        // apply the suggestion
        if ($input === self::TAB) {
            $predicted = $this->completion->predict($this->buffer, $this->suggestIndex);
            if ($predicted !== null) {
                $input = $predicted;
            }
        }
        // see prev suggestion
        elseif ($input === self::UP_ARROW) {
            $this->suggestIndex--;
        }
        // see next suggestion
        elseif ($input === self::DOWN_ARROW) {
            $this->suggestIndex++;
        }
        parent::processInput($input);
    }

    /**
     * @return string
     */
    protected function getRenderingText(): string
    {
        return parent::getRenderingText() . $this->getCompletion();
    }

    /**
     * @return string
     */
    protected function getCompletion(): string
    {
        $predicted = $this->completion->predict($this->buffer, $this->suggestIndex);

        if ($predicted === null) {
            $this->suggestIndex = 0;
            return '';
        }

        return Ansi::buffer()
            ->fgColor(Color::Gray)
            ->text($predicted)
            ->resetStyle()
            ->toString();
    }
}
