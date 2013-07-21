<?php
/**
 * This file describes a CodeReviewer that runs PHP Lint Mode to validate PHP syntax 
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
 * runs "php -l <filename>" on each file to check for compile-time errors
 *
 * @author dwipperfurth
 */

class LintModeCodeReviewer extends CodeReviewer{
    
    /**
     *  creates a LintModeCodeReview object
     * 
     * @param object $defaultOptions a value object with default options
     */
    function __construct($defaultOptions) {
        $this->reviewer = 'LintModeCodeReviewer';
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
        
        @exec('php -l "'.$filePath.'"', $result);
        
        $issues = array();
        
        foreach($result as $key=>$value){
            if($key==0 || $key==count($result)-1) continue;
            $issues []= new CodeIssue(
                'LintModeCodeReviewer',
                $filePath,
                3,
                1,
                false,
                substr($value, 0, strrpos($value, " in ")),
                substr($value, strrpos($value, "line")+5),
                0,
                null,
                null
            );
        }
        
        return $issues;
    }
    
}