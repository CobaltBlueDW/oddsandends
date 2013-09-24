<?php
/**
 * Class used to Integrate PHPMD into Automated Code Review
 *
 * @author dwipperfurth
 */

class PHPMDCodeReviewer extends CodeReviewer{
    
    protected $supportedTypes = array('php'=>true, 'html'=>true);
    
    function __construct($defaultOptions) {
        $this->reviewer = 'PHPMDCodeReviewer';
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
        
        
        
        exec('php "'.$config->libDir.'/phpmd/phpmd.php" "'.$filePath.'" text '.$this->getRuleList($options).' --reportfile "'.$config->workDir.'/phpmd_report.txt"');
        
        if (!is_file($config->workDir.'/phpmd_report.txt')) throw new Exception("Failed to find generated report.  Report possibly faield to generate.");
        
        $report = explode("\n", file_get_contents($config->workDir.'/phpmd_report.txt'));
        
        $issues = array();
        foreach($report as $line){
            if (strlen($line) < 3) continue;
            $break = strpos($line, "\t");
            $message = substr($line, $break+1);
            $front = substr($line, 0, $break);
            $lineNumber = substr($front, strrpos($front,':')+1);
            
            $issues []= new CodeIssue(
                $this->reviewer,
                $filePath,
                1,
                1,
                false,
                $message,
                $lineNumber,
                0,
                null,
                null
            );
        }
        
        return $issues;
    }
    
    protected function getRuleList($options=null){
        global $config;
        
        if (!isset($options)) $options = $this->defaultOptions;
        
        $listString = "";
        
        if($options->rulesets->codesize) $listString .= ',"'.$config->libDir.'/phpmd/resources/rulesets/codesize.xml"';
        if($options->rulesets->controversial) $listString .= ',"'.$config->libDir.'/phpmd/resources/rulesets/controversial.xml"';
        if($options->rulesets->design) $listString .= ',"'.$config->libDir.'/phpmd/resources/rulesets/design.xml"';
        if($options->rulesets->naming) $listString .= ',"'.$config->libDir.'/phpmd/resources/rulesets/naming.xml"';
        if($options->rulesets->unusedcode) $listString .= ',"'.$config->libDir.'/phpmd/resources/rulesets/unusedcode.xml"';
        
        if(!empty($listString)) $listString = substr($listString, 1);
        //echo $listString;
        
        return $listString;
    }
    
}

?>
