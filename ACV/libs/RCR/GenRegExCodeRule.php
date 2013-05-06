<?php
/**
 * Description of VarNameCodeRule
 *
 * @author dwipperfurth
 */

class GenRegExCodeRule extends CodeRule{
    
    function findAll(&$fileString, $options=null){
        if (empty($fileString) || ! is_string($fileString)) throw new Exception("Not given a proper file string.");
        if (!isset($options)) $options = $this->defaultOptions;
        if (!isset($options->rules)) throw new Excpetion("Required property of GenRegExCodeRule item, rules is missing.");
        
        $nIndecies = RegExCodeReviewer::getNewlineIndecies($fileString);
        
        $issues = array();

        $defaultRuleID = 0;
        foreach($options->rules as $ruleName=>$rule){
            if (!isset($rule->regEx)) throw new Exception("Required property of {$ruleName} item, regEx is missing.");
            if (!isset($rule->level)) $rule->level = 0;
            if (!isset($rule->ruleID)) $rule->ruleID = $defaultRuleID++;
            if (!isset($rule->description)) $rule->description = "Failed Rule ({$rule->regEx})";
            
            $matchCount = preg_match_all($rule->regEx, $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
            if ($matchCount) {
                foreach($ruleMatches[0] as $match){
                    $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                    $char = $match[1] - $nIndecies[$line];

                    $issues []= new CodeIssue(
                            'GenRegExCodeRule',
                            $options->filePath,
                            $rule->level,
                            $rule->ruleID,
                            false,
                            $rule->description,
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
