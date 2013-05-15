<?php


require_once('Reporter.php');
require_once('XMLSerializer.php');

class XMLReporter extends Reporter {
    
    protected $isFirst;
    
    function open($filePath=null){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
            fwrite($this->fileHandle, '<?xml version="1.0" encoding="UTF-8" ?>'."\n<issues>\n\t");
        } else {
            $this->fileHandle = null;
            echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n<issues>\n\t";
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
            fwrite($this->fileHandle, "\n</issues>\n");
            fclose($this->fileHandle);  
        } else {
            echo "\n\t</issues>\n";
        }
        $this->fileHandle = null;
    }
    
    private function pushToFile($issues){
        foreach($issues as $issue){
            if ($this->isFirst) {
                fwrite($this->fileHandle, "<issue>".XMLSerializer::generateXML($issue)."</issue>");
                $this->isFirst = false;
                continue;
            }
            fwrite($this->fileHandle, "\n\t<issue>".XMLSerializer::generateXML($issue)."</issue>");
        }
    }
    
    private function pushToStdIO($issues){
        foreach($issues as $issue){
            if ($this->isFirst) {
                echo "<issue>".XMLSerializer::generateXML($issue)."</issue>";
                $this->isFirst = false;
                continue;
            }
            echo "\n\t<issue>".XMLSerializer::generateXML($issue)."</issue>";
        }
    }
    
}