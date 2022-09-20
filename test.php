<?php

use Kirameki\Cli\Input;
use Kirameki\Cli\Output;
use Kirameki\Cli\Output\Ansi;

require './vendor/autoload.php';

$output = new Output($ansi = new Ansi());
$input = new Input($output);

dump($input->hidden('Password: '));
