<?php
/**
 * Description of VarNameCodeRule
 *
 * @author dwipperfurth
 */

class PHPTagsCodeRule extends CodeRule{
    
    function findAll(&$fileString, $options=null){
        if (empty($fileString) || ! is_string($fileString)) throw new Exception("Not given a proper file string.");
        if (!isset($options)) $options = $this->defaultOptions;
        
        $nIndecies = RegExCodeReviewer::getNewlineIndecies($fileString);
        
        $issues = array();

        $matchCount = preg_match_all('/\<\?\s/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'PHPTagsCodeRule',
                        $options->filePath,
                        0,
                        1,
                        false,
                        "Opening PHP tag does not use the '<?php' form.",
                        $line+1,
                        $char,
                        $match[0],
                        null
                );
            }
        }
        
        $matchCount = preg_match_all('/\<\?[pP][hH][pP][^\s]/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'PHPTagsCodeRule',
                        $options->filePath,
                        2,
                        2,
                        false,
                        "Opening PHP tag({$match[0]}...) does not have a whitespace postfix.",
                        $line+1,
                        $char,
                        $match[0],
                        null
                );
            }
        }
        
        $matchCount = preg_match_all('/\<\?(?:[pP][hH][pP])?\s/', $fileString, $ruleMatches);
        if ($matchCount > 1) return $issues;
        
        $matchCount = preg_match_all('/\?\>\s*$/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'PHPTagsCodeRule',
                        $options->filePath,
                        2,
                        3,
                        false,
                        "Closing php tag was found at the end of script file.",
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
