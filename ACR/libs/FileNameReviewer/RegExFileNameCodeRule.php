<?php
/**
 * This file describes a CodeRule for creating Generic Regular Expression rules for files
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
 * a class that runs generic regex rules from a file
 *
 * @author dwipperfurth
 */
class RegExFileNameCodeRule extends CodeRule{
    
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
    function findAll(&$fileString, $options=null){
        if (empty($fileString) || ! is_string($fileString)) throw new Exception("Not given a proper file string.");
        if (!isset($options)) $options = $this->defaultOptions;
        if (!isset($options->rules)) throw new Excpetion("Required property of GenRegExCodeRule item, rules is missing.");
        
        $issues = array();

        $defaultRuleID = 0;
        foreach($options->rules as $ruleName=>$rule){
            if (!isset($rule->regEx)) throw new Exception("Required property of {$ruleName} item, regEx is missing.");
            if (!isset($rule->level)) $rule->level = 0;
            if (!isset($rule->ruleID)) $rule->ruleID = $defaultRuleID++;
            if (!isset($rule->description)) $rule->description = "Failed Rule ({$rule->regEx})";
            
            $matchCount = preg_match_all($rule->regEx, $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
            if ($matchCount) {
                foreach($ruleMatches[0] as $match){
                    $foundException = false;
                    if (!empty($rule->except)) {
                        foreach($rule->except as $exception){
                            if (preg_match($exception, $match[0])) {
                                $foundException = true;
                                break;
                            }
                        }
                    }
                    if (!$foundException){
                        $issues []= new CodeIssue(
                                'RegExFileNameCodeRule',
                                $options->filePath,
                                $rule->level,
                                $rule->ruleID,
                                false,
                                $rule->description,
                                0,
                                0,
                                $match[0],
                                null
                        );
                    }
                }
            }
        }
        
        return $issues;
    }
    
}