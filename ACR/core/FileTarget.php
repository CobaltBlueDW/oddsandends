<?php
/**
 * This file describes a File Target, which are generated by TargetGenerators
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
 * A Class to represent a file target to be reviewed
 *
 * @author dwipperfurth
 */
class FileTarget extends Target {
   
    protected $path = null;         //@var string The file path
    protected $name = null;         //@var string The file name
    protected $type = null;         //@var string The file type
    protected $cache = false;       //@var boolean if true contents will be cached
    private $contents = null;       //@var string The contents of the file as a string used for caching
    
    /**
     * Setter Constructor
     * 
     * @param string $path  The name of the CodeReviewer
     */
    public function __construct(stdClass $defaultConfig=null){
        if (isset($defaultConfig->cache)) $this->cache = $defaultConfig->cache;
        if (isset($defaultConfig->path)) $this->setPath($defaultConfig->path);
    }
    
    public function getContents($forceReload = false){
        if (!$forceReload && isset($this->contents)) return $this->contents;
        
        $temp = file_get_contents($this->path);
        
        if ($this->cache) $this->contents = $temp;
        
        return $temp;
    }
    
    public function getPath(){return $this->path;}
    public function getName(){return $this->name;}
    public function getType(){return $this->type;}
    
    public function setPath($path){
        //validate file path
        if (!is_string($path) || !is_readable($path)) throw new Exception('FileTarget::path('.$path.') is an invalid path.');
        
        //set type
        $path_parts = pathinfo($path);
        if (isset($path_parts['extension'])) $this->setType($path_parts['extension']);
        
        //set file name
        if (isset($path_parts['basename'])) $this->setName($path_parts['basename']);
        
        //set path
        $this->path = $path;
    }
    
    public function setType($type){$this->type = $type;}
    public function setName($name){$this->name = $name;}
}