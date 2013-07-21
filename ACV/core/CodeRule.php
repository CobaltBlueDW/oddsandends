<?php
/**
 * This file describes a CodeRule
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
 * Interface(abstract class) representing a discrete rule/test for a CodeReviewer
 * e.g. A CodeReviewer might instantiate 4 different CodeRules to check code for 4 different types of issues
 *
 * @author David Wipperfurth
 */
abstract class CodeRule {
    
    protected $defaultOptions;      //properties object to use for default settings
    
    /**
     *  creates a CodeRule
     * 
     * @param object $defaultOptions    default options for a CodeRule
     */
    function __construct($defaultOptions) {
        $this->defaultOptions = $defaultOptions;
    }
    
    /**
     *  sets the default options
     * 
     * @param object $defaultOptions    default options for a CodeRule
     */
    function setOptions($defaultOptions){
        $this->defaultOptions = $defaultOptions;
    }
    
    /**
     *  Given the text of a file, and any file specific options, finds all applications
     * of this code rule.
     * 
     * @param string    $fileString a string representation of a code file to find 
     *  and apply the rule to.  Given as reference so that it may make changes to 
     *  the string if needed, but usually shouldn't.
     * @param object    $options    a value object used to override any default options set for this rule.
     * @return array    a list of CodeIssues this rule generated from application to the fileString
     */
    abstract function findAll(&$fileString, $options);
    
}

?>
