<?php

// functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/

class XMLSerializer {

    public static function generateXML($obj, $node_block='nodes', $node_name='node'){
        if (is_array($obj)) return self::generateValidXmlFromArray($obj, $node_block, $node_name);
        if (is_object($obj)) return self::generateValidXmlFromObj($obj, $node_block, $node_name);
        
        //if it's not an object or an array why are you putting it into an xml file
        //oh well, in it goes...
        $temp = array($obj);
        return self::generateValidXmlFromArray($temp, $node_block, $node_name);
    }
    
    public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::generateValidXmlFromArray($arr, $node_block, $node_name);
    }

    public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }

                $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

}