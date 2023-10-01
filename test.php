<?php

use Kirameki\Cli\Input;

require './vendor/autoload.php';

$input = new Input();
$output = $input->autoComplete('text: ', ['skip' => null, 'git' => ['commit', 'push']]);
dump($output);
