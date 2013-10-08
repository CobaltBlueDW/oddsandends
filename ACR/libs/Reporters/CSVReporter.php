<?php
/**
 * This file generates reports in the Camma Seperated Variables file format
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
 *  Can generate a report in CSV(comma seperated variables) format
 * 
 * @author David Wipperfurth 
 */
class CSVReporter extends Reporter {
    
    protected $writeHeader; //boolean to determine if a CSV header should be written
    
    /**
     *  creates a CSVReporter object
     * 
     */
    function __construct(){
        $this->writeHeader = false;
        $this->fileHandle = null;
    }
    
    /**
     *  this is the first step in creating a report.  Eithers opens a file or STDOUT
     * for writing to.
     * 
     * @param string $filePath  path to a file, or null for STDOUT
     * @param boolean $createHeader writes a CSV header if true
     */
    function open($filePath=null, $createHeader = true){
        if (isset($filePath)) {
            $this->fileHandle = fopen($filePath, 'w');
        } else {
            $this->fileHandle = null;
        }
        $this->writeHeader = $createHeader;
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
        $this->writeHeader = false;
    }
    
    /**
     * outputs an issue to a given file. 
     * 
     * @param CodeIssue $issues an issue to write to file
     */
    protected function pushToFile(array $issues){
        if ($this->writeHeader) {
            fwrite($this->fileHandle, $this->genHeader($issues));
            $this->writeHeader = false;
        }
        foreach($issues as $line){
            $str = '';
            foreach($line as $key=>$value){
                if (is_string($value)) {
                    $str = $str.'"'.addslashes($value).'",';
                } else {
                    $str = $str.$value.',';
                }
            }
            $str = substr($str, 0, strlen($str)-1)."\n";
            fwrite($this->fileHandle, $str);
        }
    }
    
    /**
     *  writes an issue to STDOUT (console/command prompt/log/etc.)
     * 
     * @param CodeIssue $issues an issue to write to StdOUT
     */
    protected function pushToStdIO(CodeIssue $issues){
        if ($this->writeHeader) {
            echo $this->genHeader($issues);
            $this->writeHeader = false;
        }
        foreach($issues as $line){
            $str = '';
            foreach($line as $key=>$value){
                if (is_string($value)) {
                    $str = $str.'"'.addslashes($value).'",';
                } else {
                    $str = $str.$value.',';
                }
            }
            $str = substr($str, 0, strlen($str)-1)."\n";
            echo $str;
        }
    }
    
    /**
     *  generates a header for the CSV
     * 
     * @param CodeIssue $issues an isue to use as a template for the header generation
     * @return string the generated heaer text
     */
    protected function genHeader(array $issues){
        $str = '';
        foreach($issues[0] as $key=>$value){
            $str = $str.'"'.$key.'",';
        }
        $str = substr($str, 0, strlen($str)-1)."\n";
        
        return $str;
    }
    
}