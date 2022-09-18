<?php

use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\Output\Ansi;

require './vendor/autoload.php';

$input = new Input(new Output($ansi = new Ansi()), new Input\Stream(STDIN));

//dump($input->masked('Password: '));

readline_callback_handler_install('123', fn($char) => dump($char));
while (true) {
    $read = [STDIN];
    $write = $except = [];
    $stream = stream_select($read, $write, $except, 0);

    readline_callback_read_char();
}
