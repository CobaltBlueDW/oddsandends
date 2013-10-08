<?php
/**
 * This file is used to manage the execution of different code reviewers
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
 *  recursively retrieves json files and parses them into one big object
 *
 *  Note: resource URLs must begin with "file://", use forward slashes for directory
 *  pathing, and must used the appropriate extension; e.g. '.json' for JSON files. 
 *
 *  @param  mixed   $ref    a json object or a path to a json file
 *  @param  string  $curPath    internal argument to support relative URLs 
 *  @return object  a json object
 */
function unpackJSON($ref, $curPath='./'){
    if (!isset($ref)) return null;
    if(is_string($ref)) $ref = json_decode(file_get_contents($ref));
    foreach($ref as $key=>$value){
        if(is_string($value) && strtolower(substr($value, 0, 7)) == "file://"){
            switch( strtolower(substr($value, strrpos($value, '.'))) ){
                case ".json":
                    $obj = json_decode(file_get_contents( $curPath.substr($value, 7) ));
                    $ref->$key = unpackJSON($obj, $curPath.substr($value, 7, strrpos($value, '/')-6));
                break;
                case ".php":
                    require_once( $curPath.substr($value, 7) );
                break;
            }
        }else if(is_object($value)){
            $ref->$key = unpackJSON($value, $curPath);
        }else if(is_array($value)){
            $newKey = 0;
            $newObj = new stdClass();
            foreach($value as $aVal){
                $newObj->$newKey = $aVal;
                $newKey++;
            }
            $ref->$key = unpackJSON($newObj, $curPath);
        }
    }
    return $ref;
}

/**
 * Returns an object that is the join of $obj2 onto $obj1
 * $obj1 keys will be overwritten by $obj2 if there is a collision.
 * 
 * @param object $obj1    join object 1
 * @param object $obj2    join object 2
 * @return object   resulting combined object 
 */
function objJoin($obj1, $obj2){
    foreach($obj2 as $key=>$value){
        $obj1->$key = $value;
    }
    
    return $obj1;
}

//include base libs
require_once('core/CodeIssue.php');
require_once('core/CodeRule.php');
require_once('core/CodeReviewer.php');
require_once('core/Target.php');
require_once('core/FileTarget.php');
require_once('core/TargetGenerator.php');
require_once('core/Reporter.php');

//handle console command args
$assocArgs = getopt("c:t:e:r:");

//consume base properties files
$configPath = 'config.json';
if (isset($assocArgs['c'])){
    if (is_array($assocArgs['c'])){
        $config = new stdClass();
        foreach($assocArgs['c'] as $path){
            $config = objJoin($config, unpackJSON($path));
        }
    } else {
        $config = unpackJSON($assocArgs['c']);
    }
} else {
    $config = unpackJSON($configPath);
}

//apply console properties to config
if (isset($assocArgs['t'])) $config->reviewTargets = $assocArgs['t'];
if (isset($assocArgs['e'])) $config->echoConfig = $assocArgs['e'];
if (isset($assocArgs['r'])){
    $tempObj = new stdClass();
    $tempObj->$assocArgs['r'] = true;
    $config->allowedReviews = $tempObj;
}

//prep required properties
if (empty($config) || !is_object($config)) throw new Exception("No configurations found!");
if (isset($config->echoConfig) && $config->echoConfig == true) echo json_encode($config);

/*
if (!isset($config->reviewTargets)) throw new Exception("Required property reviewTarget is missing.");
if (is_string($config->reviewTargets) && 
        strtolower(substr($config->reviewTargets, strrpos($config->reviewTargets, '.')))=='.json'){
    $config->reviewTarget = unpackJSON($config->reviewTargets);
}
if (is_string($config->reviewTargets)){
    $tempObj = new stdClass();
    $tempObj->soloTarget = $config->reviewTargets;
    $config->reviewTargets = $tempObj;
}
 */

if (!isset($config->codeReviewers)) throw new Exception("Required property codeReviewers is missing.");
if (isset($config->allowedReviews)){
    foreach($config->allowedReviews as $allowKey=>$allowValue){
        if ($allowValue !== true) unset($config->codeReviewers->$allowKey);
    }
}

if (!isset($config->targetGenerators)) throw new Exception("Required property targetGenerators is missing.");
if (isset($config->allowedGenerators)){
    foreach($config->allowedGenerators as $allowKey=>$allowValue){
        if ($allowValue !== true) unset($config->targetGenerators->$allowKey);
    }
}

//prep resources
if (!is_dir($config->workDir)) {
    mkdir($config->workDir, 0777);
}
$codeReviewers = new stdClass();
foreach($config->codeReviewers as $revName=>$reviewer){
    if (!isset($reviewer->className)) throw new Exception("Required property of {$revName} item, className is missing.");
    $codeReviewers->$revName = new $reviewer->className($reviewer);
}

$targetGenerators = new stdClass();
foreach($config->targetGenerators as $genName=>$generator){
    if (!isset($generator->className)) throw new Exception("Required property of {$genName} item, className is missing.");
    $targetGenerators->$genName = new $generator->className($generator);
}

//run preReview
foreach($codeReviewers as $reviewer){
    $reviewer->preReview();
}

//setup output/reporter
if (!isset($config->output->reportFormat)) $config->output->reportFormat = 'json';
if (!isset($config->Reporters->{$config->output->reportFormat})) throw new Exception("Specified Reporter '{$config->output->reportFormat}' was not found in Reporter list");
$reporter = new $config->Reporters->{$config->output->reportFormat}->className($config->Reporters->{$config->output->reportFormat});
if (isset($config->output->reportFile)) {
    $reporter->open($config->output->reportFile);
} else {
    $reporter->open();
}

//setup target generators
$targetFile = null;
if (isset($config->output->targetListFile)) {
    $targetFile = fopen($config->output->targetListFile, 'w');
}

//run automation
foreach($targetGenerators as $generator){
    if (!empty($targetFile)) fwrite ($targetFile, get_class($generator).":\n");
    while($target = $generator->next()){
        if (!empty($targetFile)) fwrite ($targetFile, $target."\n");
        foreach($codeReviewers as $reviewer){
            $result = $reviewer->reviewTarget($target);
            if (is_array($result)) $reporter->push($result);
        }
    }
}

if (!empty($targetFile)) fclose($targetFile);
$reporter->close();

//run postReview
foreach($codeReviewers as $reviewer){
    $reviewer->postReview();
}



