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
class CommunityTableGroupMembers extends JTable
{
	var $groupid		= null;
	var $memberid		= null;
	var $approved		= null;
	var $permissions	= null;

	public function __construct(&$db)
	{
		parent::__construct('#__community_groups_members','id', $db);
	}

	public function load(  $keys=null, $reset=true  )
	{
		$memberId = $keys['memberId'];
		$groupId = $keys['groupId'];

		$db		= $this->getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_groups_members' ) . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupId ) . ' '
				. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $memberId );
		$db->setQuery( $query );

		$member	= $db->loadObject();

		if( !$member )
			return false;

		$this->groupid		= $member->groupid;
		$this->memberid		= $member->memberid;
		$this->approved		= $member->approved;
		$this->permissions	= $member->permissions;

		return true;
	}

	public function store()
	{
		$db		= $this->getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_groups_members' ) . ' '
				. 'SET ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( $this->approved ) . ','
				. $db->quoteName( 'permissions' ) . '=' . $db->Quote( $this->permissions ) . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ' '
				. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $this->memberid );
		$db->setQuery( $query );
		return $db->query();
	}
}