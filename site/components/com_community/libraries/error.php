<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('_JEXEC') or die('Restricted access');


class CError {

	static public function assert( $var1, $var2, $cond , $file = __FILE__ , $line = __LINE__) {
		switch( $cond ) {
			case 'eq':	// both var must be equal
				if( $var1 != $var2 ) JError::raiseError( 500, $file . ':' . $line);
				break;

			case 'lt':	// var1 must be less than var2
				if( $var1 >= $var2 ) JError::raiseError( 500, $file . ':' . $line);
				break;

			case 'gt':	// var1 must be greater than var2
				if( $var1 <= $var2 ) JError::raiseError( 500, $file . ':' . $line);
				break;

			case 'contains':	// var1 must be in var2 array
				break;

			case '!contains':	// var1 must be in var2 array
				break;

			case 'empty':	// both var must be equal
				if( !empty($var1) ) JError::raiseError( 500, $file . ':' . $line);
				break;

			case '!empty':	// both var must be equal
				if( empty($var1) ) JError::raiseError( 500, $file . ':' . $line);
				break;

			case 'istype':	// $var1 must be of type $var2
				$func = 'is_'.$var2;
				if( !$func($var1) ) JError::raiseError( 500, $file . ':' . $line);
				break;
		}
	}
}