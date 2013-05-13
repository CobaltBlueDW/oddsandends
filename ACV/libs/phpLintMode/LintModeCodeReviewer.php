<?php
/**
 * runs "php -l <filename>" on each file to check for compile-time errors
 *
 * @author dwipperfurth
 */

class LintModeCodeReviewer extends CodeReviewer{
    
    function __construct($defaultOptions) {
        $this->reviewer = 'LintModeCodeReviewer';
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
        global $config;
        
        if (!isset($filePath)) throw new Exception("No File Specified");
        if (!isset($options)) $options = $this->defaultOptions;
        
        @exec('php -l "'.$filePath.'"', $result);
        
        $issues = array();
        
        foreach($result as $key=>$value){
            if($key==0 || $key==count($result)-1) continue;
            $issues []= new CodeIssue(
                'LintModeCodeReviewer',
                $filePath,
                3,
                1,
                false,
                substr($value, 0, strrpos($value, " in ")),
                substr($value, strrpos($value, "line")+5),
                0,
                null,
                null
            );
        }
        
        return $issues;
    }
    
}

?>
