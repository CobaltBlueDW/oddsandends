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
 *  an Interface(abstract class) used to define a communication layer for target generation
 * 
 * @author David Wipperfurth 
 */
abstract class TargetGenerator {
    
    protected $defaultOptions;
    
    /**
     *  creates a target generator
     * 
     * @param object $defaultOptions    a value object of default options/parameters for the generator
     */
    public function __construct($defaultOptions) {
        $this->setup($defaultOptions);
    }
    
    /**
     * "Generates" and returns the next target this generator can produce based
     * on the setup parameters.
     * 
     * @param   stdClass    $options    any additional relavant parameters that might affect this next call
     * @return String|false   the next target (as a file path), or false if no more targets to return
     */
    abstract public function next(stdClass $options=null);
    public function reset(){}
    public function close(){}
    
    protected function setup($defaultOptions){
        $this->defaultOptions = $defaultOptions;
        $this->reset();
    }
    
}
