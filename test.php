<?php

use Kirameki\Cli\Input;

require './vendor/autoload.php';

$input = new Input();
$output = $input->text('text:');
$output = $input->masked('masked:');
dump($output);
