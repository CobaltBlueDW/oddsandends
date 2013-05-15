<?php


require_once('Reporter.php');

class CSVReporter extends Reporter {
    
    protected $writeHeader;
    
    function __construct($createHeader = true){
        $this->writeHeader = false;
    }
    
    function open($filePath=null, $createHeader = true){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
        } else {
            $this->fileHandle = null;
        }
        $this->writeHeader = $createHeader;
    }
    
    function push($issues){
        if (isset($this->fileHandle)) {
            $this->pushToFile($issues);
        } else {
            $this->pushToStdIO($issues);
        }
    }
    
    function close(){
        if (isset($this->fileHandle)) fclose($this->fileHandle);  
        $this->fileHandle = null;
        $this->writeHeader = false;
    }
    
    protected function pushToFile($issues){
        if ($this->writeHeader) {
            fwrite($this->fileHandle, $this->genHeader($issues));
            $this->writeHeader = false;
        }
        foreach($issues as $line){
            $str = '';
            foreach($line as $key=>$value){
                if (is_string($value)) {
                    $str = $str.'"'.addslashes($value).'",';
                } else {
                    $str = $str.$value.',';
                }
            }
            $str = substr($str, 0, strlen($str)-1)."\n";
            fwrite($this->fileHandle, $str);
        }
    }
    
    protected function pushToStdIO($issues){
        if ($this->writeHeader) {
            echo $this->genHeader($issues);
            $this->writeHeader = false;
        }
        foreach($issues as $line){
            $str = '';
            foreach($line as $key=>$value){
                if (is_string($value)) {
                    $str = $str.'"'.addslashes($value).'",';
                } else {
                    $str = $str.$value.',';
                }
            }
            $str = substr($str, 0, strlen($str)-1)."\n";
            echo $str;
        }
    }
    
    protected function genHeader($issues){
        $str = '';
        foreach($issues[0] as $key=>$value){
            $str = $str.'"'.$key.'",';
        }
        $str = substr($str, 0, strlen($str)-1)."\n";
        
        return $str;
    }
    
}