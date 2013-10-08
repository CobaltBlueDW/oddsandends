<?php
/**
 * This file describe a TargetGenerator
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
 *  a target genrator that generates targets from Git Diffs
 * 
 * @author David Wipperfurth 
 */
class LocalGitGenerator extends TargetGenerator{
    
    protected $fileList;
    protected $fileListIndex;
    
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
    
    public function validateGit(){
        exec("git --version" ,$result);
        if (empty($result) || substr($result[0], 4, 7) != 'version') {
            throw new Exception('"git --version" command attempted with an unexpected response.  git may not be properly installed/configured.');
        }
    }
    
    public function getDiffFiles(){
        global $config;
        
        if (!is_dir($config->workDir)) throw new Exception("Working directory ({$config->workDir}) was not found.");
        
        $repoPath = rtrim($this->defaultOptions->repoDir, '/');
        exec('git --git-dir="'.$repoPath.'/.git" diff --name-only "'.$this->defaultOptions->fromCommit.'"..."'.$this->defaultOptions->toCommit.'"', $results);
        
        $opt = new stdClass();
        foreach($results as $key => $value){
            $opt->path = $repoPath.'/'.$value;
            $results[$key] = new FileTarget($opt);
        }
        
        $this->fileList = $results;
        
        if(isset($this->defaultOptions->targetListOutputFile)) {
            file_put_contents($config->workDir."/".$this->defaultOptions->targetListOutputFile, json_encode($this->fileList));
        }
    }
    
    protected function setup($defaultOptions){
        $this->defaultOptions = $defaultOptions;
        $this->validateGit();
        $this->getDiffFiles();
        $this->reset();
    }
}
