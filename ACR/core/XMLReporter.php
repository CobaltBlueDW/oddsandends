<?php
/**
 * This file is used to generate reports in an Xtensible Meta language file format
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

require_once('Reporter.php');
require_once('XMLSerializer.php');

/**
 *  a class to generate xml reports
 * 
 * @author David Wipperfurth 
 */
class XMLReporter extends Reporter {
    
    protected $isFirst; //used to demark the first entry for properly capping the file/output
    
    /**
     *  the first step in creating a report: opening a file/std out
     * 
     * @param string $filePath a file path for opening a file or null for std out
     */
    function open($filePath=null){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
            fwrite($this->fileHandle, '<?xml version="1.0" encoding="UTF-8" ?>'."\n<issues>\n\t");
        } else {
            $this->fileHandle = null;
            echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n<issues>\n\t";
        }
        $this->isFirst = true;
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
        if (isset($this->fileHandle)) {
            fwrite($this->fileHandle, "\n</issues>\n");
            fclose($this->fileHandle);  
        } else {
            echo "\n\t</issues>\n";
        }
        $this->fileHandle = null;
    }
    
    /**
     * outputs an issue to a given file. 
     * 
     * @param CodeIssue $issues an issue to write to file
     */
    private function pushToFile(array $issues){
        foreach($issues as $issue){
            if ($this->isFirst) {
                fwrite($this->fileHandle, "<issue>".XMLSerializer::generateXML($issue)."</issue>");
                $this->isFirst = false;
                continue;
            }
            fwrite($this->fileHandle, "\n\t<issue>".XMLSerializer::generateXML($issue)."</issue>");
        }
    }
    
    /**
     *  writes an issue to STDOUT (console/command prompt/log/etc.)
     * 
     * @param CodeIssue $issues an issue to write to StdOUT
     */
    private function pushToStdIO(CodeIssue $issues){
        foreach($issues as $issue){
            if ($this->isFirst) {
                echo "<issue>".XMLSerializer::generateXML($issue)."</issue>";
                $this->isFirst = false;
                continue;
            }
            echo "\n\t<issue>".XMLSerializer::generateXML($issue)."</issue>";
        }
    }
    
}