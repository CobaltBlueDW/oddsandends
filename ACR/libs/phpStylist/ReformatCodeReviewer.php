<?php
/**
 * This file decribes a CodeReviewer that can reformat(pretty print) code syntax
 *
 * @copyright Copyright 2012 Web Courseworks, Ltd.
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License 2.0
 *
 * This file is intended to be included with the ACR
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * integration point for phpStylist
 *
 * @author dwipperfurth
 */
class ReformatCodeReviewer extends CodeReviewer{
    
    protected $supportedTypes = array('php'=>true, 'html'=>true, 'css'=>true);
    
    /**
     *  copies a file into the archive directory
     * 
     * @global object $config
     * @param string $filePath path to file to archive
     * @param type $archiveName name the file should have in the archive
     * @return string the path to the file in the archive
     */
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
    
    /**
     *  cleans-out the directory used for archiving reformatted files
     * 
     * @global object $config 
     */
    static function clearArchive(){
        global $config;
        
        $errorsDir = $config->workDir.'/reformat_errors';
        if (!is_dir($errorsDir)) mkdir($errorsDir);
        
        $fileList = array_diff(scandir($errorsDir), array('..', '.'));

        foreach($fileList as $value){
            unlink($errorsDir.'/'.$value);
        }
    }
    
    /**
     *  create a ReformatCodeReviewer object
     * 
     * @param type $defaultOptions 
     */
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
        
        if (isset($options->config)) file_put_contents($config->libDir.'/phpStylist/working_temp.json', json_encode($options->config));
        
        exec('php "'.$config->libDir.'/phpStylist/phpStylist.php" "'.$filePath.'" "'.$config->workDir.'/phpstylist_temp.php"');
        
        if (!is_file($config->workDir.'/phpstylist_temp.php')) throw new Exception("Failed to find generated file.  File possibly failed to generate.");
        
        //archive first
        $fileName = substr($filePath, strrpos($filePath,'/')+1);
        $fileName = substr($fileName, 0, strrpos($fileName, '.'));
        $newFilePath = self::archiveFile($config->workDir.'/phpstylist_temp.php', $fileName);
        
        @exec('php -l "'.$newFilePath.'"', $result);
        
        $issues = array();
        
        if (count($result) == 1){   //no errors found
            //if old and new file are different, replace old file and make an issue noting the change
            if (isset($options->modFiles) && $options->modFiles && md5_file($config->workDir.'/phpstylist_temp.php') != md5_file($filePath)) {
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
