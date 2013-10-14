<?php

class printer {
    function printThis($string){
        print $string;
        /* test */
        // test
        // {
        print "}";
        // testing !!
        /* still testing !! */
        print "!!";
        if (!!true) print "this";
    }
}

$p = new printer();
$p->printThis("{'/* test*/hat}");

