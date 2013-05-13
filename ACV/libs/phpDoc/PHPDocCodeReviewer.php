<?php
/**
 * Class used to Integrate PHP Doc into Automated Code Review
 *
 * @author dwipperfurth
 */

class PHPDocCodeReviewer extends CodeReviewer{
    
    function __construct($defaultOptions) {
        $this->reviewer = 'PHPDocCodeReviewer';
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
        if (!is_dir($config->workDir)) throw new Exception("Working directory ({$config->workDir}) was not found.");
        
        exec('php "'.$config->libDir.'/phpDoc/bin/phpdoc.php" -f "'.$filePath.'" -t "'.$config->workDir.'" --template checkstyle');
        
        if (!is_file($config->workDir.'/checkstyle.xml')) throw new Exception("Failed to find generated report.  Report possibly failed to generate.");
        
        $report = simplexml_load_file($config->workDir.'/checkstyle.xml');
        
        $issues = array();
        
        if (!isset($report->file)) return $issues;
        if (!isset($report->file->error)) return $issues;

        foreach($report->file->error as $error){
            $curError = new stdClass();
            foreach($error->attributes() as $key => $value) {
                $curError->$key = $value;
            }
            
            
            switch ($curError->severity){
                case 'error':
                    $issueLevel = CodeIssue::LEVEL_ERROR;
                    break;
                case 'warning':
                    $issueLevel = CodeIssue::LEVEL_WARNING;
                    break;
                default:
                    $issueLevel = CodeIssue::LEVEL_NOTICE;
            }
            
            $issues []= new CodeIssue(
                $this->reviewer,
                $filePath,
                $issueLevel,
                1,
                false,
                $curError->message,
                $curError->line
            );
        }
 
        return $issues;
    }
    
}

?>
