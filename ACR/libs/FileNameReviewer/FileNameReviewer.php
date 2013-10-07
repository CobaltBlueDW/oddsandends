<?php
/**
 * This file describes a File Name Reviewer
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
 * Reviews for file names
 *
 * @author dwipperfurth
 */
class FileNameReviewer extends CodeReviewer{
    
    protected $supportedTypes = null;   // support all types
    
    /**
     *  creates a FileNameReviewer
     * 
     * @param option $defaultOptions value object of default options
     */
    function __construct($defaultOptions) {
        $this->reviewer = 'FileNameReviewer';
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

        $issues = array();
        foreach($options->codeRules as $ruleName=>$ruleOptions){
            $ruleOptions->filePath = $filePath;
            $issues = array_merge( $issues, $this->codeRules->$ruleName->findAll($filePath, $ruleOptions) );
        }
        
        return $issues;
    }
    
}
