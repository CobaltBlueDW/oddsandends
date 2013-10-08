<?php
/**
 * This file is used to generate report in a "text"(human readable) format
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
 *  a class to create text reports 
 * 
 * @author David Wipperfurth
 */
class TextReporter extends Reporter {
    
    /**
     *  the first step in creating a report: opening a file/std out
     * 
     * @param string $filePath a file path for opening a file or null for std out
     */
    function open($filePath=null){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
        } else {
            $this->fileHandle = null;
        }
        
    }
    
    /**
     *  pushes an issue to output
     * 
     * @param CodeIssue $issues an issue to output
     */
    function push(array $issues){
        if (isset($this->fileHandle)) {
            $this->pushToFile($issues);
        } else {
            $this->pushToStdIO($issues);
        }
    }
    
    /**
     *  The last step in generating a report: closing the output stream. 
     */
    function close(){
        if (isset($this->fileHandle)) fclose($this->fileHandle);  
        $this->fileHandle = null;
    }
    
    /**
     * outputs an issue to a given file. 
     * 
     * @param CodeIssue $issues an issue to write to file
     */
    private function pushToFile(array $issues){
        foreach($issues as $issue){
            fwrite($this->fileHandle, $issue."\n");
        }
    }
    
    /**
     *  writes an issue to STDOUT (console/command prompt/log/etc.)
     * 
     * @param CodeIssue $issues an issue to write to StdOUT
     */
    private function pushToStdIO(array $issues){
        foreach($issues as $issue){
            echo $issue."\n";
        }
    }
    
}