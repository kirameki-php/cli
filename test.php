<?php

use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\Output\Ansi;

require './vendor/autoload.php';

$input = new Input(new Output(new Ansi()), new Input\Stream(STDIN));

dump($input->masked('Password: '));

//$input->confirm('Continue?');
exit(1);
$input = '';

readline_callback_handler_install('', fn() => true);
try {
    while (true) {
        $read = [STDIN];
        $write = $except = [];
        $stream = stream_select($read, $write, $except, 0);
        dump($stream);

        $char = stream_get_contents(STDIN, 1);

        dump($char);

        if ($char === false) {
            break;
        }

        $input .= $char;

        if ($char === "\r") {
            break;
        }
    }
}
finally {
    readline_callback_handler_remove();
}
dump($stream);

return $input;