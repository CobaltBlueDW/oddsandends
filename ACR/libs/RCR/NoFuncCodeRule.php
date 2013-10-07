<?php
/**
 * Description of NoExecCodeRule
 *
 * @author dwipperfurth
 */

class NoFuncCodeRule extends CodeRule{
    
    function findAll(&$fileString, $options=null){
        if (empty($fileString) || ! is_string($fileString)) throw new Exception("Not given a proper file string.");
        if (!isset($options)) $options = $this->defaultOptions;
        if (!isset($options->noFuncList)) throw new Exception("Required property of NoFuncCodeRule item, noFuncList is missing.");
        
        $nIndecies = RegExCodeReviewer::getNewlineIndecies($fileString);
        
        $issues = array();
        foreach($options->noFuncList as $noFuncKey=>$noFuncObj){
            if (empty($noFuncObj->funcName)) throw new Exception("Required property of noFuncList item, funcName is missing.");
            
            $matchCount = preg_match_all("/[^a-zA-Z0-9]{$noFuncObj->funcName}\(/", $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
            if ($matchCount) {
                foreach($ruleMatches[0] as $match){
                    $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                    $char = $match[1] - $nIndecies[$line];
                    
                    $issues []= new CodeIssue(
                            'NoFuncCodeRule',
                            $options->filePath,
                            1,
                            1,
                            false,
                            $noFuncObj->reason." function({$noFuncObj->funcName}) was used.",
                            $line+1,
                            $char,
                            $match[0],
                            null
                    );
                }
            }
        }
        
        return $issues;
    }
    
}

?>
