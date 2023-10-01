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
    protected AutoComplete $completion;

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
        $this->completion = new AutoComplete($rules);
    }

    /**
     * @param string $input
     * @return void
     */
    protected function processInput(string $input): void
    {
        if ($input === "\t") {
            $complement = $this->completion->complement($this->buffer);
            if ($complement !== null) {
                $input = $complement;
            }
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
        $completion = $this->completion->complement($this->buffer);

        if ($completion === null) {
            return '';
        }

        return Ansi::buffer()
            ->fgColor(Color::Gray)
            ->text($completion)
            ->resetStyle()
            ->toString();
    }
}
