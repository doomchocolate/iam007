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

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

class CTableEventMembers extends JTable
{
	var $id				= null;
	var $eventid		= null;
	var $memberid		= null;
	var $status			= null;
	var $permission		= null;
	var $invited_by		= null;
	var $created		= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_events_members', 'id', $db );
	}

	/**
	 * Overrides Joomla's JTable load as this table has composite keys and need
	 * to be loaded differently
	 *
	 * @access	public
	 *
	 * @return	boolean	True if successful
	 */
	public function load( $keys=null, $reset= true )
	{
		$eventId = $keys['eventId'];
		$memberId = $keys['memberId'];
		$query	= 'SELECT * FROM ' . $this->_db->quoteName( '#__community_events_members' ) . ' '
				. 'WHERE ' . $this->_db->quoteName( 'eventid' ) . '=' . $this->_db->Quote( $eventId ) . ' '
				. 'AND ' . $this->_db->quoteName( 'memberid' ) . '=' . $this->_db->Quote( $memberId );
		$this->_db->setQuery( $query );

		$result	= $this->_db->loadAssoc();

		if(empty($result))
		{
		    $result['id'] 			= '0';
		    $result['eventid'] 		= $eventId;
		    $result['memberid'] 	= $memberId;
		    $result['status'] 		= '0';
		    $result['permission'] 	= '0';
		    $result['invitedby'] 	= '0';
		    $result['created'] 		= '0';

		}

		$this->bind( $result );
	}

	/**
	 * Method to test if a specific user is already registered under a event
	 *
	 * @return boolean True if user is registered and false otherwise
	 **/
	public function exists()
	{
		$query	= 'SELECT COUNT(id) FROM ' . $this->_db->quoteName( '#__community_events_members' )
				. ' WHERE ' . $this->_db->quoteName( 'eventid' ) . '=' . $this->_db->Quote( $this->eventid )
				. ' AND ' . $this->_db->quoteName( 'memberid' ) . '=' . $this->_db->Quote( $this->memberid );

		$this->_db->setQuery( $query );

		$return	= ( $this->_db->loadResult() >= 1 ) ? true : false;

		if($this->_db->getErrorNum())
		{
			JError::raiseError( 500, $this->_db->stderr());
		}
		return $return;
	}

	/**
	 * Overrides Joomla's JTable store as this table has composite keys
	 **/
	public function store($updateNulls = false)
	{
		if( ! $this->exists() )
		{
 			$data			= new stdClass();

 			foreach( get_object_vars($this) as $property => $value )
 			{
 				// We dont want to set private properties
				if( JString::strpos( JString::strtolower($property) , '_') === false || $property == 'invited_by')
				{
					$data->$property	= $value;
				}
			}
			return $this->_db->insertObject( '#__community_events_members' , $data );
		}
		else
		{
			$query	= 'UPDATE ' . $this->_db->quoteName( '#__community_events_members' ) . ' '
					. 'SET ' . $this->_db->quoteName( 'status' ) . '=' . $this->_db->Quote( $this->status ) . ', '
					. $this->_db->quoteName( 'permission' ) . '=' . $this->_db->Quote( $this->permission ) . ', '
					. $this->_db->quoteName( 'invited_by' ) . '=' . $this->_db->Quote( $this->invited_by ) . ' '
					. 'WHERE ' . $this->_db->quoteName( 'eventid' ) . '=' . $this->_db->Quote( $this->eventid ) . ' '
					. 'AND ' . $this->_db->quoteName( 'memberid' ) . '=' . $this->_db->Quote( $this->memberid );

			$this->_db->setQuery( $query );
			$this->_db->query();

			if($this->_db->getErrorNum())
			{
				JError::raiseError( 500, $this->_db->stderr());
				return false;
			}
			return true;
		}
	}

	public function invite()
	{
		$this->status = COMMUNITY_EVENT_STATUS_INVITED;
	}

        public function attend()
        {
            $this->status = COMMUNITY_EVENT_STATUS_ATTEND;
        }

}