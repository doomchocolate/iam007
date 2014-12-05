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

class CTableMemberList extends JTable
{
	var $id				= null;
	var $title			= null;
	var $description	= null;
	var $condition		= null;
	var $avataronly		= null;
	var $created		= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_memberlist' , 'id' , $db );
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getCriterias()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT ' . $db->quoteName( 'id' ) . ' FROM '
				. $db->quoteName( '#__community_memberlist_criteria' ) . ' WHERE '
				. $db->quoteName( 'listid' ) . '=' . $db->Quote( $this->id );
		$db->setQuery( $query );
		$rows	= $db->loadObjectList();

		$childs	= array();

		foreach( $rows as $row )
		{
			$criteria	= JTable::getInstance( 'MemberListCriteria' , 'CTable' );
			$criteria->load( $row->id );
			$childs[]	= $criteria;
		}

		return $childs;
	}

	public function delete($pk = null)
	{
		//Delete criterias first.
		$criterias	= $this->getCriterias();

		foreach( $criterias as $criteria )
		{
			$criteria->delete();
		}

		return parent::delete();
	}
}