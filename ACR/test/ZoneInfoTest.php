<?php

class printer {
    function printThis($string){
        print $string;
        /* test */
        // test
        // {
        print "}";
    }
}

$p = new printer();
$p->printThis("{'/* test*/hat");

