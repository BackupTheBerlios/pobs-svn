#!/usr/bin/php
<?php

function hash($string, $type) {
    
    switch($type) {

    case 'func':
        $c = 'F';
        break;
    case 'class':
        $c = 'C';
        break;
    case 'cnstrtr':
        $c = 'C';
        break;
    default:
        $c = 'P';
        break;

    }

    return $c . substr(md5($string), 1, 8);

}

fputs(STDOUT, hash($_SERVER['argv'][1], $_SERVER['argv'][2]) . "\n");

?>
