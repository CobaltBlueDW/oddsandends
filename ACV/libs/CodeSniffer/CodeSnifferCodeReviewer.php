<?php
/**
 * Class used to Integrate PHP Code Sniffer into Automated Code Review
 *
 * @author dwipperfurth
 */

class CodeSnifferCodeReviewer extends CodeReviewer{
    
    protected $csvKeyMap;
    protected $errorTypeMap;
    
    function __construct($defaultOptions) {
        $this->reviewer = 'CodeSnifferCodeReviewer';
        parent::__construct($defaultOptions);
        $this->csvKeyMap = array(
            "File"=>0,
            "Line"=>1,
            "Column"=>2,
            "Type"=>3,
            "Message"=>4,
            "Source"=>5,
            "Severity"=>6
        );
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
        
        if (!isset($options->standard)) $options->standard = 'moodle';
        
        exec('php "'.$config->libDir.'/CodeSniffer/phpcs.php" --severity=1 --standard="'.$options->standard.'" --report=csv --report-file="'.$config->workDir.'/phpcs_report.csv" "'.$filePath.'"');
        
        if (!is_file($config->workDir.'/phpcs_report.csv')) throw new Exception("Failed to find generated report.  Report possibly faield to generate.");
        
        $csIssues = $this->getCSV($config->workDir.'/phpcs_report.csv');
        
        $issues = array();
        if(isset($csIssues)){
            foreach($csIssues as $error){
                //set issue level
                switch ($error[$this->csvKeyMap['Type']]){
                    case 'error':
                        $issueLevel = CodeIssue::LEVEL_ERROR;
                        break;
                    case 'warning':
                        $issueLevel = CodeIssue::LEVEL_WARNING;
                        break;
                    default:
                        $issueLevel = CodeIssue::LEVEL_NOTICE;
                }
                
                //construct isue object
                $issues []= new CodeIssue(
                        $this->reviewer,
                        $filePath,
                        $issueLevel,
                        $error[$this->csvKeyMap['Severity']],
                        false,
                        $error[$this->csvKeyMap['Message']],
                        $error[$this->csvKeyMap['Line']],
                        $error[$this->csvKeyMap['Column']]
                );
            }
        }
        
        return $issues;
    }
    
    protected function getCSV($filePath){
        $handle = fopen($filePath, 'r');
        $keys = fgetcsv($handle);   //ignore keys for now
        if (isset($keys)) $this->csvKeyMap = array_flip($keys);
        $matrix = array();
        while (($array = fgetcsv($handle)) != false) {
            $matrix []= $array;
        }
        
        return $matrix;
    }
    
}

?>
