<?php

use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\Output\Ansi;

require './vendor/autoload.php';

$input = new Input(new Output($ansi = new Ansi()));

dump($input->hidden('Password: '));
