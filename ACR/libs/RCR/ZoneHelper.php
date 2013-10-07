<?php

require_once('ZoneInfo.php');

/**
 * Description of ZoneHelper
 *
 * @author cobaltbluedw
 */
class ZoneHelper {
    //list of zone encapsulating tokens
    static $zoneTokenMap = array(
        '{' => true,
        '}' => true,
        '/*' => true,
        '*/' => true,
        '"' => true,
        "'" => true,
        '//' => true,
        "\n" => true,
    );
    
    //Regex to find all zone tokens in a string
    static $zoneTokenRegEx = "/{|}|\"|'|\n|\\/\\/|\\/\\*|\\*\\/|>>>|<<</";
    
    protected $zoneList = null;
    
    public function __construct($string=null) {
        if (!empty($string)) $this->load($string);
    }
    
    public function load($string, $validateSyntax = false){
        $string = str_replace("\r\n", " \r", $string);
        $string = str_replace("\r", "\n", $string);
        
        preg_match_all(self::$zoneTokenRegEx, $string, $matches, PREG_OFFSET_CAPTURE);
        
        //die(json_encode($matches));
        
        $this->zoneList = array();
        $stack = array();
        $matches = $matches[0];  //sluff the extra arrays preg makes
        foreach($matches as $match){    //$match is ['match string', index]
            // Handle empty stacks
            if (empty($stack)) {
                if ($match[0] != "\n") array_unshift($stack, new ZoneInfo($match[0], 0, $match[1]));
                //print json_encode($stack);
                continue;
            }
            
            // Handle rule changing tokens
            if ($stack[0]->type == '"') {
                if ($match[0] == '"' && substr($string, $match[1]-1, 1) != "\\") {
                    $stack[0]->endIndex = $match[1];
                    $this->zoneList []= array_shift($stack);
                }
                continue;
            }
            
            if ($stack[0]->type == '//') {
                if ($match[0] == "\n") {
                    $stack[0]->endIndex = $match[1];
                    $this->zoneList []= array_shift($stack);
                }
                continue;
            }
            
            if ($stack[0]->type == '/*') {
                if ($match[0] == '*/') {
                    $stack[0]->endIndex = $match[1];
                    $this->zoneList []= array_shift($stack);
                }
                continue;
            }
            
            // handle depth increase tokens
            if ($match[0] == '{' || $match[0] == '"' || $match[0] == "'" ||
                $match[0] == '//' || $match[0] == '/*') {
                array_unshift($stack, new ZoneInfo($match[0], count($stack), $match[1]));
                continue;
            }
            
            // handle properly formed closing tokens
            if ($match[0] == '}' && $stack[0]->type == '{') {
                $stack[0]->endIndex = $match[1];
                $this->zoneList []= array_shift($stack);
            }
            
            // assume anything not handled yet is incorrect syntax, like a 
            // closing bracket without a matching open bracket.  Ignore it or throw exception.
            if ($validateSyntax) throw new Exception("Unexpected '".$match[0]."' token found at index ".$match[1].".");
        }
        
        // once done parsing the stack should be empty (all open tokens should have been closed)
        if ($validateSyntax && !empty($stack)) throw new Exception("Expected closing token for '".$stack[0]->type."' at index ".$stack[0]->startIndex.".");
        
        //die(json_encode($tokenList));
        
        // the list is in closed-firt order, and I want it returd in opened-first order
        // so apply cludgy solution for now
        // usort($tokenList, function(ZoneInfo $a, ZoneInfo $b){ return $a->startIndex > $b->startIndex; });
        // nevermind
    }
    
    public function clear(){
        $this->zoneList = null;
    }
    
    /**
     *  Returns a list of zones that the given index is in.
     * 
     * todo: change from O(n) efficiency to O(ln(n)) efficiency
     * 
     * @param array $array  array of sorted numbers
     * @param int $insert   number to find an insert location for
     * @return int the index into the first array where the insert number should be inserted
     */
    public function findZonesAt($index){
        if (empty($this->zoneList)) return false;
        
        $point = 0;
        $zones = array();
        while(isset($this->zoneList[$point]) && $this->zoneList[$point]->startIndex < $index){
            if($this->zoneList[$point]->endIndex > $index){
                $zones []= $this->zoneList[$point];
            }
        }
        
        return $zones;
    }

}