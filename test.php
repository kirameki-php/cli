<?php

use Kirameki\Cli\Input;

require './vendor/autoload.php';

$input = new Input();
$output = $input->integer('in: ');
dump($output);
