<?php
/**
 * This file is used to aid in generating XML for the XMLReporter
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

// functions adopted from http://www.sean-barton.co.uk/2009/03/turning-an-array-or-object-into-xml-using-php/

/**
 *  helper function for serializing XML 
 */
class XMLSerializer {

    public static function generateXML($obj, $node_block='nodes', $node_name='node'){
        if (is_array($obj)) return self::generateXmlFromArray($obj, $node_block, $node_name);
        if (is_object($obj)) return self::generateXmlFromObj($obj, $node_block, $node_name);
        
        //if it's not an object or an array why are you putting it into an xml file
        //oh well, in it goes...
        $temp = array($obj);
        return self::generateXmlFromArray($temp, $node_block, $node_name);
    }
    
    public static function generateValidXML($obj, $node_block='nodes', $node_name='node'){
        if (is_array($obj)) return self::generateValidXmlFromArray($obj, $node_block, $node_name);
        if (is_object($obj)) return self::generateValidXmlFromObj($obj, $node_block, $node_name);
        
        //if it's not an object or an array why are you putting it into an xml file
        //oh well, in it goes...
        $temp = array($obj);
        return self::generateValidXmlFromArray($temp, $node_block, $node_name);
    }
    
    public static function generateValidXmlFromObj($obj, $node_block='nodes', $node_name='node') {
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

    public static function generateXmlFromObj($obj, $node_block='nodes', $node_name='node') {
        $arr = get_object_vars($obj);
        return self::generateXmlFromArray($arr, $node_name);
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