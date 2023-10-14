<?php

$string = 'Some of "this string is\"" in quotes';
$arr = preg_split('/("[^"]*")|\h+/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

var_dump($arr);

//
//use Kirameki\Cli\Input;
//
//require './vendor/autoload.php';
//
//$input = new Input();
//$output = $input->autoComplete('text: ', ['one', 'two', 'three']);
//dump($output);
