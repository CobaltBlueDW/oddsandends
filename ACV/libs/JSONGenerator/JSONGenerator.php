<?php
/**
 * This file describe a TargetGenerator such that they can be used uniformly
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
 *  a target genrator that generates targets from JSON objects
 * 
 * @author David Wipperfurth 
 */
class JSONGenerator extends TargetGenerator{
    
    /**
    *  Flattens a directory structure into a list of file paths
    * 
    * @param type $dirRef  a path to a directory
    * @param string $curPath   current path used to support recursion
    * @return array    list of file paths in directory 
    */
    public static function compileFileList($dirRef, $curPath='./'){
        $curPath = $curPath.$dirRef."/";
        $fileList = array_diff(scandir($curPath), array('..', '.'));

        $newArray = array();
        $opt = new stdClass();
        foreach($fileList as $value){
            if (is_dir($curPath.$value)){
                $newArray = array_merge($newArray, self::compileFileList($value, $curPath));
            }else{
                $opt->path = $curPath.$value;
                $newArray []= new FileTarget($opt);
            }
        }

        return $newArray;
    }
    
    protected $fileList;
    protected $fileListIndex;
    
    /**
     * "Generates" and returns the next target this generator can produce based
     * on the setup parameters.
     * 
     * @param   stdClass    $options    any additional relavant parameters that might affect this next call
     * @return String|false   the next target (as a file path), or false if no more targets to return
     */
    public function next(stdClass $options=null){
        $this->fileListIndex++;
        
        if (isset($this->fileList[$this->fileListIndex])) {
            return $this->fileList[$this->fileListIndex];
        } else {
            return false;
        }
    }
    
    public function reset(){
        $this->fileListIndex = -1;
    }
    public function close(){
        $this->fileListIndex = 0;
        unset($this->fileList);
    }
    
    protected function setup($defaultOptions){
        $this->defaultOptions = $defaultOptions;
        
        $this->fileList = array();
        $opt = new stdClass();
        foreach ($this->defaultOptions->targets as $target) {
            if(is_dir($target)){
                $this->fileList = array_merge($this->fileList, self::compileFileList($target));
            } else {
                $opt->path = $target;
                $this->fileList []= new FileTarget($opt);
            }
        }
        
        if(isset($this->defaultOptions->targetListOutputFile)) {
            file_put_contents($config->workDir."/".$this->defaultOptions->targetListOutputFile, json_encode($this->fileList));
        }
        
        $this->reset();
    }
    
    
    
}
