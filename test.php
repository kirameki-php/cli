<?php

use Kirameki\Cli\Input;

require './vendor/autoload.php';

$input = new Input();
$output = $input->autoComplete('text: ', ['one', 'two', 'three']);
dump($output);
