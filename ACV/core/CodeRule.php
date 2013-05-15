<?php
/**
 * Interface(abstract class) representing a discrete rule/test for a CodeReviewer
 * e.g. A CodeReviewer might instantiate 4 different CodeRules to check code for 4 different types of issues
 *
 * @author dwipperfurth
 */
abstract class CodeRule {
    
    protected $defaultOptions;      //properties object to use for default settings
    
    function __construct($defaultOptions) {
        $this->defaultOptions = $defaultOptions;
    }
    
    function setOptions($defaultOptions){
        $this->defaultOptions = $defaultOptions;
    }
    
    abstract function findAll(&$fileString, $options);
    
}

?>
