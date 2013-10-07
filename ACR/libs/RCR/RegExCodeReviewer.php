<?php
/**
 * This file describes a Regular Expression CodeReviewer
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

require_once('ZoneHelper.php');

/**
 * Reguar Expression based code check/fixes
 *
 * @author dwipperfurth
 */
class RegExCodeReviewer extends CodeReviewer{
    
    protected $supportedTypes = array('php'=>true, 'html'=>true);
    
    /**
     *  find where in the array of numbers a new number should be inserted. Used
     *  for calculating line numbers for issues.
     * 
     * todo: change from O(n) efficiency to O(ln(n)) efficiency
     * 
     * @param array $array  array of sorted numbers
     * @param int $insert   number to find an insert location for
     * @return int the index into the first array where the insert number should be inserted
     */
    static function findInsertIndex($array, $insert){
        $arrayLength = count($array);
        for($i=0; $i<$arrayLength; $i++){
            if ($insert < $array[$i]){
                return $i - 1;
            }
        }
        
        return $arrayLength-1;
    }
    
    /**
     *  generates a list of newline indexes in occurance order.  This is useful for
     * determining line numbers from an index.
     * 
     * @param string $string    a string to generate newline indexes for
     * @return array    an array of newline indexes in occurance order
     */
    static function getNewlineIndecies($string){
        $string = str_replace("\r\n", " \r", $string);
        $string = str_replace("\r", "\n", $string);
        //todo: fix newline inaccuracy
        
        $indexList = array();
        $pos = 0;
        while ( $pos !== false ) {
            $indexList []= $pos;
            $pos = strpos($string, "\n", $pos+1);
        }
        
        return $indexList;
    }
    
    /**
     *  creates a RegExCodeReviewer
     * 
     * @param option $defaultOptions value object of default options
     */
    function __construct($defaultOptions) {
        $this->reviewer = 'RegExCodeReviewer';
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
        if (!isset($filePath)) throw new Exception("No File Specified");
        if (!isset($options)) $options = $this->defaultOptions;
        if (!isset($options->codeRules)) throw new Exception("No Rules to Apply");
        
        foreach($options->codeRules as $ruleName=>$rule){
            if (!isset($this->codeRules->$ruleName)) throw new Exception("Requested CodeRule ( {$ruleName} ) Not Loaded");
        }
        
        $fileString = file_get_contents($filePath);

        $issues = array();
        foreach($options->codeRules as $ruleName=>$ruleOptions){
            $ruleOptions->filePath = $filePath;
            $issues = array_merge( $issues, $this->codeRules->$ruleName->findAll($fileString, $ruleOptions) );
        }
        
        return $issues;
    }
    
}
