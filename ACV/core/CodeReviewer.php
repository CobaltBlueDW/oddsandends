<?php
/**
 * This file describe a CodeReviewer such that they can be ued uniformly
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

require_once('CodeIssue.php');

/**
 *  an Interface(abstract class) used to define a communication layer for code reviewers
 * 
 * @author David Wipperfurth 
 */
abstract class CodeReviewer {
    
    protected $reviewer = 'CodeReviewer';   //the name of the CodeReviewer for ue in CodeIssues
    protected $defaultOptions;      //properties object to use for default settings
    protected $codeRules;   //list of CodeRule classes
    
    /**
     *  creates a code reviewer
     * 
     * @param object $defaultOptions    a value object of default options/parameters for the reviewer
     * @throws Exception    if a CodeRule is missing a className in the $defaultOptions object
     */
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
