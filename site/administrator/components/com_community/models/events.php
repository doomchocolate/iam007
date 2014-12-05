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
jimport( 'joomla.utilities.date' );

class CommunityModelEvents extends JModelLegacy
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
		$db			= JFactory::getDBO();
		$category	= JRequest::getInt( 'category' , 0 );
		$status 	= JRequest::getInt( 'status' , 2 );
		$condition	= '';
		$mainframe	= JFactory::getApplication();
		$ordering		= $mainframe->getUserStateFromRequest( "com_community.events.filter_order",		'filter_order',		'a.title',	'cmd' );
		$orderDirection	= $mainframe->getUserStateFromRequest( "com_community.events.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$orderBy		= ' ORDER BY '. $ordering .' '. $orderDirection;
		$search			= JRequest::getVar('search','');
		if( !empty( $search ) )
		{
			$condition	.= ' AND ( a.title LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. 'OR a.description LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. ')';
		}

		if( $category != 0 )
		{
			$condition	.= ' AND catid =' . $db->Quote( $category );
		}

		if( $status != 2 )
		{
			$condition .= ' AND a.published='. $db->Quote( $status );
		}

		$query		= 'SELECT a.* FROM ' . $db->quoteName( '#__community_events' ) . ' AS a '
					. 'WHERE 1'
					. $condition
					. ' AND (( a.' . $db->quoteName('repeat') .'=' . $db->quote('') .' || a.' . $db->quoteName('repeat') .' IS NULL)' . ' OR (a.' . $db->quoteName('parent') . '!= ' . $db->quote('0') .' && a.' . $db->quoteName('published') .' != ' . $db->quote(3) . ' && a.' . $db->quoteName('repeat') . ' IS NOT NULL)) '
					. $orderBy;

		return $query;
	}

	/**
	 * Returns the Groups
	 *
	 * @return Array	Array of groups object
	 **/
	public function getEvents()
	{
		if(empty($this->_data))
		{

			$query = $this->_buildQuery( );

			$this->_data	= $this->_getList( $this->_buildQuery() , $this->getState('limitstart'), $this->getState('limit') );
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
	public function getCategories()
	{
		$mainframe	= JFactory::getApplication();

		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_events_category');
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

	public function getEventsbyInterval($interval = 'week')
	{
		$db = $this->getDBO();

		switch ($interval)
		{
			case 'week':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_events')
				.' WHERE WEEK('.$db->quoteName('created').') = WEEK(curdate())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastweek':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_events')
				.' WHERE WEEK('.$db->quoteName('created').') = WEEK(curdate() - INTERVAL 7 DAY)'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'month':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_events')
				.' WHERE YEAR('.$db->quoteName('created').') = YEAR(CURDATE()) AND MONTH('.$db->quoteName('created').') = MONTH(CURDATE())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastmonth':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_events')
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

	public function getActiveEvent()
	{
		$db = $this->getDBO();

		$CTimeHelper = new CTimeHelper();
		$pastDate = $CTimeHelper->getLocaleDate();

		$query = 'SELECT * FROM '.$db->quoteName('#__community_events')
				.' WHERE '.$db->quoteName('published').'='.$db->Quote(1)
				.' AND '.$db->quoteName('enddate').' > '.$db->Quote($pastDate->format('Y-m-d H:i:s', true, false));

		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getPendingEvents()
	{
		$db = $this->getDBO();

		$query = 'SELECT COUNT(*) FROM '.$db->quoteName('#__community_events')
				.' WHERE '.$db->quoteName('published') .' = '.$db->quote(0);

		$db->setQuery($query);

		return $db->loadResult();
	}

}