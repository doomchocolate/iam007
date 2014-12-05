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

class CTableGroupInvite extends JTable
{
	var $groupid	= null;
	var $userid		= null;
	var $creator	= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_groups_invite' , 'groupid' , $db );
	}

	public function isOwner()
	{
		$my		= CFactory::getUser();

		return $my->id == $this->userid;
	}

	/*public function load( $keys=null, $reset=true )
	{
		$this->userid = $keys['userid'];
		$this->groupid = $keys['groupid'];
	}*/

	public function exists()
	{
		$db		=  $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups_invite' )
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ' '
				. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid );

		$db->setQuery( $query );

		$return	= ( $db->loadResult() >= 1 ) ? true : false;

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $return;
	}

	// public function load( $keys=null, $reset=true )
	// {
	// 	$groupId = $keys['groupId'];
	// 	$userId = $keys['userId'];
	// 	$db		=  $this->getDBO();

	// 	$query	= 'SELECT * FROM ' . $db->quoteName( $this->_tbl ) . ' '
	// 			. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupId ) . ' '
	// 			. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userId );
	// 	$db->setQuery( $query );

	// 	$result	= $db->loadAssoc();

	// 	if( !empty($result )){

	// 		$this->bind( $result );
	// 		return true;
	// 	}


	// 	return false;
	// }

	public function store( $updateNulls = false )
	{
		$db		=  $this->getDBO();

		if( !$this->exists() )
		{
 			$data			= new stdClass();

 			foreach( get_object_vars($this) as $property => $value )
 			{
 				// We dont want to set private properties
				if( JString::strpos( JString::strtolower($property) , '_') === false )
				{
					$data->$property	= $value;
				}
			}
			return $db->insertObject( '#__community_groups_invite' , $data );
		}
		else
		{
			$query	= 'UPDATE ' . $db->quoteName( '#__community_groups_invite' ) . ' '
					. 'SET ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ', '
					. $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid ) . ' ,'
					. $db->quoteName( 'creator' ) . '=' . $db->Quote( $this->creator ) . ' '
					. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ' '
					. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $this->userid );
			$db->setQuery( $query );
			$db->query();

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
				return false;
			}
			return true;
		}
	}

	public function delete($pk = null)
	{
		$db		=  $this->getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( $this->_tbl ) . ' WHERE '
				. $db->quoteName('groupid'). '=' . $db->Quote( $this->groupid ) . ' AND '
				. $db->quoteName('userid') .'=' . $db->Quote( $this->userid );
		$db->setQuery( $query );
		return $db->Query();
	}
}