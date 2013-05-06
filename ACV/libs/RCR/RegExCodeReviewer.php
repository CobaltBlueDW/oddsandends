<?php
/**
 * Reguar Expression based code check/fixes
 *
 * @author dwipperfurth
 */

class RegExCodeReviewer extends CodeReviewer{
    
    static function findInsertIndex($array, $insert){
        $arrayLength = count($array);
        for($i=0; $i<$arrayLength; $i++){
            if ($insert < $array[$i]){
                return $i - 1;
            }
        }
        
        return $arrayLength-1;
    }
    
    static function getNewlineIndecies($string){
        $string = str_replace("\r\n", " \r", $string);
        $string = str_replace("\r", "\n", $string);
        //todo: fix newline inaccuracy
        
        $indexList = array();
        $pos = 0;
        while ( $pos !== false ) {
            $indexList []= $pos;
            $pos = strpos($string, "\n", $pos+1);
        }
        
        return $indexList;
    }
    
    function __construct($defaultOptions) {
        $this->reviewer = 'RegExCodeReviewer';
        parent::__construct($defaultOptions);
    }
    
    /**
     * Reviews a file potentially modifying it, and returning issues
     * 
     * @param type $fileResource
     * @param type $options 
     * @return array[CodeIssue] an array of issues found
     */
    function reviewFile($filePath, $options=null){
        if (!isset($filePath)) throw new Exception("No File Specified");
        if (!isset($options)) $options = $this->defaultOptions;
        if (!isset($options->codeRules)) throw new Exception("No Rules to Apply");
        
        foreach($options->codeRules as $ruleName=>$rule){
            if (!isset($this->codeRules->$ruleName)) throw new Exception("Requested CodeRule ( {$ruleName} ) Not Loaded");
        }
        
        $fileString = file_get_contents($filePath);
        $issues = array();
        foreach($options->codeRules as $ruleName=>$ruleOptions){
            $ruleOptions->filePath = $filePath;
            $issues = array_merge( $issues, $this->codeRules->$ruleName->findAll($fileString, $ruleOptions) );
        }
        
        return $issues;
    }
    
}

?>
