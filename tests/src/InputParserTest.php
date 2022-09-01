<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use Kirameki\Cli\CommandBuilder;
use Kirameki\Cli\InputParser;
use function dump;

class InputParserTest extends TestCase
{
    public function test_array_of_options()
    {
        $builder = (new CommandBuilder())->name(__FUNCTION__);
        $builder->option('t1')->allowMultiple();
        $definition = $builder->build();

        $parser = new InputParser($definition, [
            '--t1', '--t1',
        ]);

        dump($parser->parse());
    }
}
