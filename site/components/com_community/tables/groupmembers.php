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

class CTableGroupMembers extends JTable
{
	var $groupid		= null;
	var $memberid		= null;
	var $approved		= null;
	var $permissions	= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_groups_members', 'id', $db );
	}

	/**
	 * Method to test if a specific user is already registered under a group
	 *
	 * @return boolean True if user is registered and false otherwise
	 **/
	public function exists()
	{
		$db		=  $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups_members' )
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ' '
				. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $this->memberid );

		$db->setQuery( $query );

		$return	= ( $db->loadResult() >= 1 ) ? true : false;

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $return;
	}

	/**
	 * Overrides Joomla's JTable load as this table has composite keys and need
	 * to be loaded differently
	 *
	 * @access	public
	 *
	 * @return	boolean	True if successful
	 */
	public function load( $keys=null, $reset=true )
	{
		$memberId = $keys['memberId'];
		$groupId = $keys['groupId'];
		$db		= $this->getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_groups_members' ) . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $groupId ) . ' '
				. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $memberId );
		$db->setQuery( $query );

		$result	= $db->loadAssoc();
		$this->bind( $result );
	}

	/**
	 * Overrides Joomla's JTable store as this table has composite keys
	 *
	 * @param	string	User's id
	 * @param	string	Group's id
	 * @return boolean True if user is registered and false otherwise
	 **/
	public function store($updateNulls = false)
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
			return $db->insertObject( '#__community_groups_members' , $data );
		}
		else
		{
			$query	= 'UPDATE ' . $db->quoteName( '#__community_groups_members' ) . ' '
					. 'SET ' . $db->quoteName( 'approved' ) . '=' . $db->Quote( $this->approved ) . ', '
					. $db->quoteName( 'permissions' ) . '=' . $db->Quote( $this->permissions ) . ' '
					. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ' '
					. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $this->memberid );
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

	/**
	 * Approve the member
	 *
	 **/
	public function approve()
	{
		$db		= $this->getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_groups_members' ) . ' SET '
				. $db->quoteName( 'approved' ) . '=' . $db->Quote( '1' ) . ' '
				. 'WHERE ' . $db->quoteName( 'groupid' ) . '=' . $db->Quote( $this->groupid ) . ' '
				. 'AND ' . $db->quoteName( 'memberid' ) . '=' . $db->Quote( $this->memberid );

		$db->setQuery( $query );
		$db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		// Update user group list
		$user = CFactory::getUser( $this->memberid );
		$user->updateGroupList();

	}
}