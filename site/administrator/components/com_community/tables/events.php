<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * JomSocial Table Model
 */
class CommunityTableEvents extends JTable
{
	var $id				= null;
	var $catid			= null;
	var $contentid		= null;
	var $type			= null;
	var $title			= null;
	var $location		= null;
	var $description	= null;
	var $creator		= null;
	var $startdate		= null;
	var $enddate		= null;
	var $permission		= null;
	var $avatar			= null;
	var $invitedcount	= null;
	var $confirmedcount	= null;
	var $declinedcount 	= null;
	var $maybecount		= null;
	var $created		= null;
	var $hits			= null;
	var $published		= null;

	public function __construct(&$db)
	{
		parent::__construct('#__community_events','id', $db);
	}

	public function getWallCount()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_wall') . ' '
				. 'WHERE ' . $db->quoteName( 'contentid' ) . '=' . $db->Quote( $this->id ) . ' '
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'events' ) . ' '
				. 'AND ' . $db->quoteName( 'published' ) . '=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}

	public function getDiscussCount()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups_discuss') . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->id );

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}

	public function isMember( $memberId , $groupId )
	{
		$db 		= JFactory::getDBO();
		$query 	= 'SELECT * FROM ' . $db->quoteName( '#__community_groups_members' ) . ' '
					. 'WHERE ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $memberId ) . ' '
					. 'AND ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupId );

		$db->setQuery( $query );

		$count 	= ( $db->loadResult() > 0 ) ? true : false;
		return $count;
	}

	public function addMember( $data )
	{
		$db	=& $this->getDBO();

		// Test if user if already exists
		if( !$this->isMember($data->memberid, $data->groupid) )
		{
			$db->insertObject('#__community_groups_members' , $data);
		}

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $data;
	}

	public function addMembersCount( $groupId )
	{
		$db		=& $this->getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_groups' )
				. 'SET ' . $db->quoteName( 'membercount' ) . '= (membercount +1) '
				. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $groupId );
		$db->setQuery( $query );
		$db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
	}

	public function getMembersCount()
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups_members') . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->id )
				. 'AND ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}
}