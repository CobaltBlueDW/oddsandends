<?php
/**
 * This file decribes a report so that they may be used uniformly
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
 *  Abstract class for use with generating reports
 *  
 * @author David Wipperfurth
 */
abstract class Reporter {
    
    protected $fileHandle;  //a refrence to a file resource for writting reports to
    
    /**
     *  creates a reporter 
     */
    function __construct(){
    }
    
    /**
     *  the first step in creating a report: opening a file/std out
     * 
     * @param string $filePath a file path for opening a file or null for std out
     */
    abstract function open($filePath);
    
    /**
     *  pushes an issue to output
     * 
     * @param array(CodeIssue) $issues an issue to output
     */
    abstract function push(array $issues);
    
    /**
     *  The last step in generating a report: closing the output stream. 
     */
    abstract function close();
    
}