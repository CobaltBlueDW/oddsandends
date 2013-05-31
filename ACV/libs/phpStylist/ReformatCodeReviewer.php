<?php
/**
 * integration point for phpStylist
 *
 * @author dwipperfurth
 */

class ReformatCodeReviewer extends CodeReviewer{
    
    static function archiveFile($filePath, $archiveName){
        global $config;
        
        $errorsDir = $config->workDir.'/reformat_errors';
        if (!is_dir($errorsDir)) mkdir($errorsDir);
        $tempFilePath = $errorsDir.'/'.$archiveName;
        $copyNum = 0;
        while (is_file($tempFilePath.'_'.$copyNum.'.php')) {
            $copyNum++;
        }
        $newFilePath = $tempFilePath.'_'.$copyNum.'.php';
        
        copy($filePath, $newFilePath);
        
        return $newFilePath;
    }
    
    static function clearArchive(){
        global $config;
        
        $errorsDir = $config->workDir.'/reformat_errors';
        if (!is_dir($errorsDir)) mkdir($errorsDir);
        
        $fileList = array_diff(scandir($errorsDir), array('..', '.'));

        foreach($fileList as $value){
            unlink($errorsDir.'/'.$value);
        }
    }
    
    function __construct($defaultOptions) {
        $this->reviewer = 'ReformatCodeReviewer';
        parent::__construct($defaultOptions);
        self::clearArchive();
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
        
        exec('php "'.$config->libDir.'/phpStylist/phpStylist.php" "'.$filePath.'" "'.$config->workDir.'/phpstylist_temp.php"');
        
        if (!is_file($config->workDir.'/phpstylist_temp.php')) throw new Exception("Failed to find generated file.  File possibly failed to generate.");
        
        //archive first
        $fileName = substr($filePath, strrpos($filePath,'/')+1);
        $fileName = substr($fileName, 0, strrpos($fileName, '.'));
        $newFilePath = self::archiveFile($config->workDir.'/phpstylist_temp.php', $fileName);
        
        @exec('php -l "'.$newFilePath, $result);
        
        $issues = array();
        
        if (count($result) == 1){   //no errors found
            //if old and new file are different, replace old file and make an issue noting the change
            if (md5_file($config->workDir.'/phpstylist_temp.php') != md5_file($filePath)) {
                $issues []= new CodeIssue(
                    'ReformatCodeReviewer',
                    $filePath,
                    0,
                    1,
                    true,
                    'The formating of this file has been beautified.',
                    0,
                    0,
                    null,
                    null
                );
                rename($newFilePath, $filePath);
            } 
        } else {    //errors found
            //create issues for errors
            foreach($result as $key=>$value){
                if($key==0) continue;
                $issues []= new CodeIssue(
                    'ReformatCodeReviewer',
                    $filePath,
                    2,
                    2,
                    false,
                    'Beautification failed: '.$value,
                    0,
                    0,
                    null,
                    null
                );
            }
        }
        
        return $issues;
    }
    
}

?>
