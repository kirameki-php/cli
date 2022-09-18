<?php

use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\Output\Ansi;

require './vendor/autoload.php';

$input = new Input(new Output($ansi = new Ansi()), new Input\Stream(STDIN));

dump($input->masked('Password: '));

//readline_callback_handler_install('question?: ', fn($line) => dump($line));
//$chars = '';
//while (true) {
//    $read = [STDIN];
//    $write = $except = [];
//    $stream = stream_select($read, $write, $except, 0);
//
//    $char = stream_get_contents(STDIN, 1);
//
//    if ($char === "\r") {
//        fwrite(STDIN, $chars . $char);
//        break;
//    }
//
//    $chars .= $char;
//}
