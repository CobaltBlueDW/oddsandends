<?php

/**
 * Description of ZoneInfo
 *
 * @author cobaltbluedw
 */
class ZoneInfo {
    
    public $type;       //The type of zone e.g. a string => " , or a scope => {
    public $subType;    //The optional subtype of a zone. For example, a type of "{", might have a sub type of "function".
    public $depth;      //the depth describes how many zones this zone is in e.g. { { "the depth of this string is 2" } }
    public $startIndex; //The char index into the main string of the beginning of the zone
    public $endIndex;   //The char index of the end of the zone
    
    function __construct($type=null, $depth=null, $startIndex=null, $endIndex=null) {
        $this->type = $type;
        $this->depth = $depth;
        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;
    }
    
}
