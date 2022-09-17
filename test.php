<?php

use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\Output\Ansi;

require './vendor/autoload.php';

$input = new Input(new Output(new Ansi()), new Input\Stream(STDIN));

$input->masked('Password: ');

$input->confirm('Continue?');
