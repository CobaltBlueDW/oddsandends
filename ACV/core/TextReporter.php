<?php


require_once('Reporter.php');

class TextReporter extends Reporter {
    
    function open($filePath=null){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
        } else {
            $this->fileHandle = null;
        }
        
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
    }
    
    private function pushToFile($issues){
        foreach($issues as $issue){
            fwrite($this->fileHandle, $issue."\n");
        }
    }
    
    private function pushToStdIO($issues){
        foreach($issues as $issue){
            echo $issue."\n";
        }
    }
    
}