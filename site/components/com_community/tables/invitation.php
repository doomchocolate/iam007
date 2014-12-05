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

class CTableInvitation extends JTable
{
	var $id			= null;

	/**
	 * Callback method
	 **/
	var $callback	= null;

	/**
	 * Unique identifier for the current invitation
	 **/
	var $cid		= null;

	/**
	 * Comma separated values for user id's
	 **/
	var $users		= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_invitations' , 'id' , $db );
	}

	/**
	 * Override parent's method as the loading method will be based on the
	 * unique callback and cid
	 **/
	public function load( $callback = null , $cid = null )
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( $this->_tbl ) . ' WHERE '
				. $db->quoteName( 'callback' ) . '=' . $db->Quote( $callback ) . ' '
				. 'AND ' . $db->quoteName( 'cid' ) . '=' . $db->Quote( $cid );

		$db->setQuery( $query );
		$result	= $db->loadAssoc();

		if(!is_null($result))
		{
			$this->bind( $result );
		}
	}

	/**
	 * Retrieves invited members from this table
	 *
	 * @return	Array	$users	An array containing user id's
	 **/
	public function getInvitedUsers()
	{
		$users	= explode( ',' , $this->users );

		return $users;
	}

	public function deleteInvitation($cid,$userid,$callback)
	{
		$this->load($callback,$cid);
		$users = explode(',',$this->users);

		foreach($users as $key => $user)
		{
			if($user == $userid)
			{
				unset($users[$key]);
			}
		}

		if(count($users) > 0)
		{
			$this->users = implode(',',$users);

			$this->store();
		}
		else
		{
			$this->delete();
		}
	}

}