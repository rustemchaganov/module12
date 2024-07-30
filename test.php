<?php

$string = "aaa, bbb, ccc, ddd, eee, fff";

$arr = explode(',', $string);
$string = implode(',', array_slice($arr, 0, 3));

var_dump($arr);


