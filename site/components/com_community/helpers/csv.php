<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

defined('JPATH_PLATFORM') or die;

/**
 * JUtility is a utility functions class
 *
 * @package     Joomla.Platform
 * @subpackage  Utilities
 * @since       11.1
 */
class CCSV
{
	/**
	 * Inser data into csv string
	 */
	static public function insert($csv, $item)
	{
		$items	=   explode( ',', trim( $csv, ',' ) );
		array_push( $items, $item );
		$items	=   array_unique( $items );
		return ltrim( implode( ',', $items ), ',' );
	}

	/**
	 * Remove data into csv string
	 */
	static public function remove($csv, $item)
	{
		$items	=   explode( ',', trim( $csv, ',' ) );
		$items = array_diff( $items, array($item ));
		return ltrim( implode( ',', $items ), ',' );
	}

	/**
	 * Return true if the item is part of the csv data
	 */
	static public function exist($csv, $item)
	{
		$items	=   explode( ',', trim( $csv, ',' ) );
		return in_array( strval($item), $items );
	}

	/**
	 *
	 */
	static public function count($csv)
	{
		$csv = trim( $csv, ',' );

		if(empty($csv)){
			return 0;
		}

		$items	=   explode( ',', $csv);
		return count($items);
	}

	/**
	 * Merge 2 csv string
	 */
	static public function merge($csv1,$csv2)
	{
		$items1	=   explode( ',', trim( $csv1, ',' ) );
		$items2	=   explode( ',', trim( $csv2, ',' ) );
		$items = array_unique(array_merge($items1, $items2));
		return ltrim( implode( ',', $items ), ',' );
	}

	static public function diff( $csv1, $csv2)
	{
		$items1	=   explode( ',', trim( $csv1, ',' ) );
		$items2	=   explode( ',', trim( $csv2, ',' ) );
		$items = array_unique(array_diff($items1, $items2));
		return ltrim( implode( ',', $items ), ',' );
	}
}