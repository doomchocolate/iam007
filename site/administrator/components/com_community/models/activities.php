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

jimport( 'joomla.application.component.model' );


class CommunityModelActivities extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$mainframe	= JFactory::getApplication();

		// Call the parents constructor
		parent::__construct();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( 'com_community.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Retrieves the JPagination object
	 *
	 * @return object	JPagination object
	 **/
	public function &getPagination()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	public function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$this->_total	= $this->_getListCount( $this->_buildQuery() );
		}

		return $this->_total;
	}

	public function _buildQuery()
	{
		$db			= JFactory::getDBO();
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$actor		= $jinput->get('actor', '', 'NONE'); //JRequest::getVar( 'actor' , '' );
		$archived	= JRequest::getInt( 'archived' , 0 );
		$app		= $jinput->get('app' , 'none', 'NONE'); //JRequest::getVar( 'app' , 'none' );
		$where		= array();
		$userId 	= 0;

		if(!empty($actor)){
			$userId		= CUserHelper::getUserId( $actor );
		}
		if( $userId != 0 )
		{
			$where[]	= 'actor=' . $db->Quote( $userId ) . ' ';
		}

		if( $archived != 0 )
		{
			$archived	= $archived - 1;
			$where[]	= 'archived=' . $db->Quote( $archived ) . ' ';
		}

		if( $app != 'none' )
		{

			$where[]	= 'app=' . $db->Quote( $app );
		}

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_activities' );

		if( !empty($where) )
		{
			for( $i = 0; $i < count( $where ); $i++ )
			{
				if( $i == 0 )
				{
					$query	.= ' WHERE ';
				}
				else
				{
					$query	.= ' AND ';
				}
				$query	.= $where[ $i ];
			}
		}

		$query	.= ' ORDER BY created DESC';
		return $query;
	}

	public function getFilterApps()
	{
		$db		= $this->getDBO();

		$query	= 'SELECT DISTINCT app FROM ' . $db->quoteName( '#__community_activities' );
		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if( $db->getErrorNum() )
			return false;

		return $result;
	}

	public function getActivities()
	{
		if(empty($this->_data))
		{
			$query			= $this->_buildQuery();
			$this->_data	= $this->_getList( $query , $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_data;
	}

	public function delete( $activityId )
	{
		$db		= JFactory::getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_activities' ) . ' WHERE '
				. $db->quoteName( 'id' ) . '=' . $db->Quote( $activityId );
		$db->setQuery( $query );
		$db->Query();

		if( $db->getErrorNum() )
			return false;

		return true;
	}

	public function purge()
	{
		$db		= JFactory::getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_activities' );
		$db->setQuery( $query );

		$db->Query();

		if( $db->getErrorNum() )
			return false;

		return true;
	}

	public function getUserStatus()
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_activities' ) . ' WHERE '
				. $db->quoteName( 'app' ) . '=' . $db->Quote( 'profile' )
				. 'AND '.$db->quoteName('comment_type') .' = '. $db->Quote('profile.status')
				. 'AND '.$db->quoteName('actor').' = '. $db->quoteName('target')
				. 'ORDER BY '.$db->quoteName('id').'DESC LIMIT 0, 5';

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		return $result;
	}

	public function archiveAll()
	{
		$db = $this->getDBO();

	 	$sql = 'UPDATE ' . $db->quoteName('#__community_activities') . ' act'
                . ' SET act.' . $db->quoteName('archived') . ' = ' . $db->Quote(1)
                . ' WHERE '
                /* Only archive those not archived yet */
                . $db->quoteName('archived') . '=' .
                $db->Quote(0);
        $db->setQuery($sql);

        return $db->query();
	}

	public function archiveSelected($id)
	{
		$db = $this->getDBO();

	 	$sql = 'UPDATE ' . $db->quoteName('#__community_activities') . ' act'
                . ' SET act.' . $db->quoteName('archived') . ' = ' . $db->Quote(1)
                . ' WHERE '
                /* Only archive those not archived yet */
                . $db->quoteName('archived') . '=' . $db->Quote(0)
                . ' AND act.'.$db->quoteName('id') . '='.$db->Quote($id);
        $db->setQuery($sql);

        return $db->query();
	}

	public function getActCount(){
		$db = $this->getDBO();

		$sql = 'SELECT COUNT(*) FROM '. $db->quoteName('#__community_activities');

		$db->setQuery($sql);
		return $db->loadResult();
	}
}