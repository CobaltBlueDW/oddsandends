<?php


require_once('Reporter.php');

class JSONReporter extends Reporter {
    
    protected $isFirst;
    
    function open($filePath=null){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
            fwrite($this->fileHandle, "[\n\t");
        } else {
            $this->fileHandle = null;
            echo "[\n\t";
        }
        $this->isFirst = true;
    }
    
    function push($issues){
        if (isset($this->fileHandle)) {
            $this->pushToFile($issues);
        } else {
            $this->pushToStdIO($issues);
        }
    }
    
    function close(){
        if (isset($this->fileHandle)) {
            fwrite($this->fileHandle, "\n]");
            fclose($this->fileHandle);  
        }else{
            echo "\n]";
        }
        $this->fileHandle = null;
    }
    
    private function pushToFile($issues){
        foreach($issues as $issue){
            if ($this->isFirst) {
                fwrite($this->fileHandle, json_encode($issue));
                $this->isFirst = false;
                continue;
            }
            fwrite($this->fileHandle, ",\n\t".json_encode($issue));
        }
    }
    
    private function pushToStdIO($issues){
        foreach($issues as $issue){
            if ($this->isFirst) {
                echo json_encode($issue);
                $this->isFirst = false;
                continue;
            }
            echo ",\n\t".json_encode($issue);
        }
    }
    
}