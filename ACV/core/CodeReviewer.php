<?php
/**
 * Interface(abstract class) or code reviewers
 *
 * @author dwipperfurth
 */

require_once('CodeIssue.php');

abstract class CodeReviewer {
    
    protected $reviewer = 'CodeReviewer';   //the name of the CodeReviewer for ue in CodeIssues
    protected $defaultOptions;      //properties object to use for default settings
    protected $codeRules;   //list of CodeRule classes
    
    function __construct($defaultOptions) {
        $this->defaultOptions = $defaultOptions;
        
        if (isset($this->defaultOptions->codeRules)){
            $this->codeRules = new stdClass();
            foreach($this->defaultOptions->codeRules as $ruleName=>$rule){
                if (!isset($rule->className)) throw new Exception("Required property of {$ruleName} item, className is missing.");
                $this->codeRules->$ruleName = new $rule->className($rule);
            }
        }
    }
    
    /**
     * A hook for the CodeReviewer, which is executed once before the review process
     *  
     * @param type $options 
     */
    function preReview($options=null){}
    
    /**
     * A hook for the CodeReviewer, which is executed once after the review process
     * 
     * @param type $options 
     */
    function postReview($options=null){}
    
    /**
     * Reviews a file potentially modifying it, and returning issues
     * 
     * @param type $fileResource
     * @param type $options 
     * @return array[CodeIssue] an array of issues found
     */
    abstract function reviewFile($filePath, $options=null);
    
}

?>
