<?php
/**
 * This file escribes a Code Issue, which are generated by code reviewers
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
 * A Class to represent an issue in the code
 *
 * @author dwipperfurth
 */
class CodeIssue {
    
    const VERSION = '1.0';  //The version number of the CodeIssue class
    
    const LEVEL_NOTICE = 0; //The enumeration of the "notice" level
    const LEVEL_WARNING = 1;    //The enumaration of the "warning" level
    const LEVEL_ERROR = 2;  //The enumeration of the "error" level
    const LEVEL_CRITICAL = 3;   //The enumeration of the "critical" level
    
    /**
     *  Converts a level number into a string using the level's enumeration
     * 
     * @param int $levelNumber
     * @return string   the issue level as a string
     * @throws Exception    if enumeration i not found
     */
    static function levelToString($levelNumber){
        if (!isset($levelNumber)) $levelNumber = 0;
        if (!is_int($levelNumber) || $levelNumber < 0 || $levelNumber > 3) throw new Exception("Level Number is out of range.");
        $levels = array('Notice','Warning','Error','Critical');
        return $levels[$levelNumber];
    }
    
    public $reviewer;       //string    The name of the CodeReviewer
    public $fileName;       //string    The name of the file the issue was found in
    public $issueCode;      //int   The numberic reference / enumeration of the issue
    public $issueLevel;     //int   The level of sevarity of the issue
    public $modified;       //bool  True, if the reviewer modified the code as a result of this issue
    public $description;    //string    The description of the issue
    
    public $lineNumber;     //int   The line number / row number of the code issue
    public $charNumber;      //int   The character number / column number of code issue
    
    public $code;           //string    A snippet of the code in quetions
    public $modCode;        //string    If the code was modified, a snippet of the revised code
   
    /**
     * Setter Constructor
     * 
     * @param string $reviewer  The name of the CodeReviewer
     * @param string $fileName  The name of the file the issue was found in
     * @param int $issueCode    The numberic reference / enumeration of the issue
     * @param bool $modified    True, if the reviewer modified the code as a result of this issue
     * @param string $description   The description of the issue
     * @param int $lineNumber   The line number / row number of the code issue
     * @param int $charNumber   The character number / column number of code issue
     * @param string $code      A snippet of the code in quetions
     * @param string $modCode   If the code was modified, a snippet of the revised code
     */
    function __construct($reviewer, $fileName, $issueLevel, $issueCode, $modified=false, $description='',
                         $lineNumber=0, $charNumber=0, $code='', $modCode='') {
        $this->reviewer = $reviewer;
        $this->fileName = $fileName;
        $this->issueLevel = $issueLevel;
        $this->issueCode = $issueCode;
        $this->modified = $modified;
        $this->description = $description;
        $this->lineNumber = $lineNumber;
        $this->charNumber = $charNumber;
        $this->code = $code;
        $this->modCode = $modCode;
    }
    
    /**
     *  Returns a pretty version of regular fields
     * 
     * @return  string  pretty print of CodeIssue object 
     */
    function __toString() {
        return CodeIssue::levelToString($this->issueLevel)."(".$this->reviewer.
                ":".$this->issueCode.
                ") in '".$this->fileName.
                "' at L".$this->lineNumber.
                ": ".$this->description;
    }
    
}