<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.filesystem.file' );

require_once( JPATH_ROOT .'/components/com_community/libraries/storage/s3.php' );
require_once( JPATH_ROOT .'/components/com_community/libraries/storage/file.php' );

class CStorage
{
	public static function getStorage($type = 'file')
	{
		// If store file is empty, it should default to 'file'
		if( empty($type) )
		{
			$type = 'file';
		}

		$classname = ucfirst($type) . '_CStorage';
		$obj = new $classname();
		$obj->_init();
		return $obj;
	}
}


class CStorageMethod
{
	/**
	 * Put a resource into a remote storage.
	 * Return true if successful
	 */
	public function put()
	{
	}

	public function exists()
	{
	}

	/**
	 * Retrive the entire resource locally
	 */
	public function get($uri)
	{
	}

	public function getURI(){}

	public function read(){}
	public function write(){}
	public function delete(){}
	public function getExt(){}
}


