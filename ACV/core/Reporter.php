<?php
/**
 *  Abstract class for use with generating reports
 *  
 */

abstract class Reporter {
    
    protected $fileHandle;
    
    function __construct(){
    }
    
    abstract function open($filePath);
    
    abstract function push($issues);
    
    abstract function close();
    
}