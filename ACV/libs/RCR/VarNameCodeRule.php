<?php
/**
 * Description of VarNameCodeRule
 *
 * @author dwipperfurth
 */

class VarNameCodeRule extends CodeRule{
    
    function findAll(&$fileString, $options=null){
        if (empty($fileString) || ! is_string($fileString)) throw new Exception("Not given a proper file string.");
        if (!isset($options)) $options = $this->defaultOptions;
        
        $nIndecies = RegExCodeReviewer::getNewlineIndecies($fileString);
        
        $issues = array();

        $matchCount = preg_match_all('/\$[a-z0-9_]*[A-Z]+[a-z0-9_]*|\-\>[a-z0-9_]*[A-Z]+[a-z0-9_]*[^a-zA-Z0-9_\(]/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $name = $match[0];
                if (isset($options->exceptions->$name) && $options->exceptions->$name) continue;
                
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'VarNameCodeRule',
                        $options->filePath,
                        0,
                        1,
                        false,
                        "Variable ({$match[0]}) has a capital letter.",
                        $line+1,
                        $char,
                        $match[0],
                        null
                );
            }
        }
        
        $matchCount = preg_match_all('/\$[a-z0-9A-Z_]*[_]+[a-z0-9A-Z_]*|\-\>[a-z0-9A-Z_]*[_]+[a-z0-9A-Z_]*[^a-zA-Z0-9_\(]/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $name = $match[0];
                if (isset($options->exceptions->$name) && $options->exceptions->$name) continue;
                
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'VarNameCodeRule',
                        $options->filePath,
                        0,
                        2,
                        false,
                        "Variable ({$match[0]}) has an underscore.",
                        $line+1,
                        $char,
                        $match[0],
                        null
                );
            }
        }
        
        return $issues;
    }
    
}

?>
