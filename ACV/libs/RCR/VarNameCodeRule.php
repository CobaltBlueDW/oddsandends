<?php
/**
 * This file describes a CodeRule for valiating variable names
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
 * a coderule class describing valid Variable Names
 *
 * @author dwipperfurth
 */
class VarNameCodeRule extends CodeRule{
    
    
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
        
        $nIndecies = RegExCodeReviewer::getNewlineIndecies($fileString);
        
        $issues = array();

        $matchCount = preg_match_all('/\$[a-z0-9_]*[A-Z]+[a-z0-9_]*|\-\>[a-z0-9_]*[A-Z]+[a-z0-9_]*[^a-zA-Z0-9_\(]/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $name = $match[0];
                if (isset($options->exceptions->$name) && $options->exceptions->$name) continue;
                
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'VarNameCodeRule',
                        $options->filePath,
                        0,
                        1,
                        false,
                        "Variable ({$match[0]}) has a capital letter.",
                        $line+1,
                        $char,
                        $match[0],
                        null
                );
            }
        }
        
        $matchCount = preg_match_all('/\$[a-z0-9A-Z_]*[_]+[a-z0-9A-Z_]*|\-\>[a-z0-9A-Z_]*[_]+[a-z0-9A-Z_]*[^a-zA-Z0-9_\(]/', $fileString, $ruleMatches, PREG_OFFSET_CAPTURE);
        if ($matchCount) {
            foreach($ruleMatches[0] as $match){
                $name = $match[0];
                if (isset($options->exceptions->$name) && $options->exceptions->$name) continue;
                
                $line = RegExCodeReviewer::findInsertIndex($nIndecies, $match[1]);
                $char = $match[1] - $nIndecies[$line];

                $issues []= new CodeIssue(
                        'VarNameCodeRule',
                        $options->filePath,
                        0,
                        2,
                        false,
                        "Variable ({$match[0]}) has an underscore.",
                        $line+1,
                        $char,
                        $match[0],
                        null
                );
            }
        }
        
        return $issues;
    }
    
}
