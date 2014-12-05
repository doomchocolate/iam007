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

class CommunityAdminModelGroups extends JModelLegacy
{
	/**
	 * Configuration data
	 *
	 * @var object
	 **/
	var $_params;

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;

	/**
	 * Configuration data
	 *
	 * @var int	Total number of rows
	 **/
	var $_total;

	/**
	 * Configuration data
	 *
	 * @var int	Total number of rows
	 **/
	var $_data;

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
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to return the total number of rows
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$this->_total	= $this->_getListCount( $this->_buildQuery() );
		}

		return $this->_total;
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

	/**
	 * Build the SQL query string
	 *
	 * @access	private
	 * @return	string	SQL Query string
	 */
	public function _buildQuery()
	{
		$db		= JFactory::getDBO();
		$category	= JRequest::getInt( 'category' , 0 );
		$status 	= JRequest::getInt( 'status' , 2 );
		$condition	= '';
		$mainframe	= JFactory::getApplication();
		$ordering		= $mainframe->getUserStateFromRequest( "com_community.groups.filter_order",		'filter_order',		'a.name',	'cmd' );
		$orderDirection	= $mainframe->getUserStateFromRequest( "com_community.groups.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$orderBy		= ' ORDER BY '. $ordering .' '. $orderDirection;
		$search			= JRequest::getVar('search','');

		if( !empty( $search ) )
		{
			$condition	.= ' AND ( a.name LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. 'OR username LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. 'OR a.description LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. ')';
		}

		if( $category != 0 )
		{
			$condition	.= ' AND a.categoryid=' . $db->Quote( $category );
		}

		if( $status != 2 )
		{
			$condition .= ' AND a.published='. $db->Quote( $status );
		}

		$query		= 'SELECT a.*, c.name AS username, COUNT(DISTINCT(b.memberid)) AS membercount FROM ' . $db->quoteName( '#__community_groups' ) . ' AS a '
					. 'LEFT JOIN ' . $db->quoteName( '#__community_groups_members') . ' AS b '
					. 'ON b.groupid=a.id '
					. 'LEFT JOIN ' . $db->quoteName( '#__users') . ' AS c '
					. 'ON a.ownerid=c.id '
					. 'WHERE 1'
					. $condition
					. ' GROUP BY a.id'
					. $orderBy;

		return $query;
	}

	/**
	 * Returns the Groups
	 *
	 * @return Array	Array of groups object
	 **/
	public function getGroups()
	{
		if(empty($this->_data))
		{

			$query = $this->_buildQuery( );

			$this->_data	= $this->_getList( $this->_buildQuery() , $this->getState('limitstart'), $this->getState('limit') );
		}

        foreach($this->_data  as $key=>$data)
		{
			$this->_data[$key]->membercount=$this->_getMemberCount($data->id);
		}

		return $this->_data;
	}

	public function getAllGroups()
	{
		$db		= JFactory::getDBO();

		$query	= "SELECT * FROM " . $db->quoteName( '#__community_groups');

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns the Groups Categories list
	 *
	 * @return Array An array of group category objects
	 **/
	public function &getCategories()
	{
		$mainframe	= JFactory::getApplication();

		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_groups_category');
		$db->setQuery( $query );
		$categories	= $db->loadObjectList();

		return $categories;
	}

	public function isLatestTable()
	{
		$fields	= $this->_getFields();

		if(!array_key_exists( 'membercount' , $fields ) )
		{
			return false;
		}

		if(!array_key_exists( 'wallcount' , $fields ) )
		{
			return false;
		}

		if(!array_key_exists( 'discusscount' , $fields ) )
		{
			return false;
		}

		return true;
	}

	public function _getFields( $table = '#__community_groups' )
	{
		$result	= array();
		$db		= JFactory::getDBO();

		$query	= 'SHOW FIELDS FROM ' . $db->quoteName( $table );

		$db->setQuery( $query );

		$fields	= $db->loadObjectList();

		foreach( $fields as $field )
		{
			$result[ $field->Field ]	= preg_replace( '/[(0-9)]/' , '' , $field->Type );
		}

		return $result;
	}

        public function _getMemberCount($id)
	{
		$db	= JFactory::getDBO();

		$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName( '#__community_groups_members' ) . ' AS a '
					. 'JOIN '. $db->quoteName( '#__users' ). ' AS b ON a.'.$db->quoteName('memberid').'=b.'.$db->quoteName('id')
					. 'AND b.'.$db->quoteName('block').'=0 '
					. 'WHERE ' . $db->quoteName('groupid') .'=' . $db->Quote( $id ) . ' '
					. 'AND ' . $db->quoteName('approved'). '=' . $db->Quote( '1' ) . ' '
					. 'AND permissions!=' . $db->Quote(COMMUNITY_GROUP_BANNED);

		$db->setQuery( $query );
		return $db->loadResult();
	}

	public function removeMember( $data )
	{
		$db	= $this->getDBO();

		$strSQL	= 'DELETE FROM ' . $db->quoteName('#__community_groups_members') . ' '
				. 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote( $data->groupid ) . ' '
				. 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote( $data->memberid );

		$db->setQuery( $strSQL );
		$db->query();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
	}

	/**
	 * Return an array of ids the user belong to
	 * @param type $userid
	 * @return type
	 *
	 */
	public function getGroupIds($userId)
	{
		$db		= $this->getDBO();
		$query		= 'SELECT DISTINCT a.'.$db->quoteName('id').' FROM ' . $db->quoteName('#__community_groups') . ' AS a '
				. ' LEFT JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b '
				. ' ON a.'.$db->quoteName('id').'=b.'.$db->quoteName('groupid')
				. ' WHERE b.'.$db->quoteName('approved').'=' . $db->Quote( '1' )
				. ' AND b.memberid=' . $db->Quote($userId);

		$db->setQuery( $query );
		$groupsid		= $db->loadColumn();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $groupsid;

	}

	public function getGroupsbyInterval($interval = 'week')
	{
		$db = $this->getDBO();

		switch ($interval)
		{
			case 'week':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_groups')
				.' WHERE WEEK('.$db->quoteName('created').') = WEEK(curdate())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastweek':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_groups')
				.' WHERE WEEK('.$db->quoteName('created').') = WEEK(curdate() - INTERVAL 7 DAY)'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'month':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_groups')
				.' WHERE YEAR('.$db->quoteName('created').') = YEAR(CURDATE()) AND MONTH('.$db->quoteName('created').') = MONTH(CURDATE())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastmonth':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_groups')
				.' WHERE YEAR('.$db->quoteName('created').') = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH('.$db->quoteName('created').') = MONTH(CURDATE() - INTERVAL 1 MONTH)'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			default:
				$query = '';
		}

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	public function getPendingGroups()
	{
		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_groups')
				.' WHERE '.$db->quoteName('published') .' = '.$db->quote(0);

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Check if the given group name exist.
	 * if id is specified, only search for those NOT within $id
	 */
	public function groupExist($name, $id=0) {
		$db		= $this->getDBO();

		$strSQL	= 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_groups')
			. ' WHERE '.$db->quoteName('name').'=' . $db->Quote( $name )
			. ' AND '.$db->quoteName('id').'!='. $db->Quote( $id ) ;


		$db->setQuery( $strSQL );
		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Retrieves the most active group throughout the site.
	 * @param   none
	 *
	 * @return  CTableGroup The most active group table object.
	 **/
	public function getMostActiveGroup()
	{
		$db		= $this->getDBO();

		$query	= 'SELECT '.$db->quoteName('cid').' FROM '.$db->quoteName('#__community_activities')
				. ' WHERE '.$db->quoteName('app').' LIKE ' . $db->Quote( 'groups%' ).''
				. ' GROUP BY '.$db->quoteName('cid')
				. ' ORDER BY COUNT(1) DESC '
				. ' LIMIT 1';
		$db->setQuery( $query );

		$id		= $db->loadResult();

		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );

		// return a null object if there is no group yet
		if( $id )
			return $group;
		else
			return null;
	}
}