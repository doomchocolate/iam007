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

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_community/tables');

class CTableFile extends JTable
{
	var $id					= null;
	var $name               = null;
	var $groupid            = null;
	var $discussionid       = null;
	var $bulletinid         = null;
	var $eventid            = null;
	var $profileid			= null;
	var $filepath           = null;
	var $filesize           = null;
	var $type				= 'miscellaneous';
	var $storage            = 'file';
	var $hits				= 0;
	var $creator            = null;
	var $created			= null;


	public function __construct( &$db )
	{
		parent::__construct( '#__community_files', 'id', $db );
	}


	public function store($updateNulls = false)
	{
		$this->filepath	= CString::str_ireplace( '\\' , '/' , $this->filepath );
		return parent::store();
	}

	public function delete($pk = null)
	{

		$storage = CStorage::getStorage($this->storage);

		$storage->delete($this->filepath);
		return parent::delete();
	}
}
?>