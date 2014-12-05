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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class CommunityAdminModelVideos extends JModelLegacy
{

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination;

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


	/**
	 * Build the SQL query string
	 *
	 * @access	private
	 * @return	string	SQL Query string
	 */
	public function _buildQuery()
	{
		$category	= JRequest::getInt( 'category' , 0 );
		$status 	= JRequest::getInt( 'status' , 2 );
		$mainframe	= JFactory::getApplication();
		//$search			= $mainframe->getUserStateFromRequest( "com_community.videos.search", 'search', '', 'string' );
		$search	= JRequest::getVar('search','');
		$db			= JFactory::getDBO();
		$condition = '';

		if( !empty( $search ) )
		{
			$condition	.= ' AND ( a.title LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. 'OR a.description LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. ')';
		}

		if( $category != 0 )
		{
			$condition	.= ' AND a.category_id =' . $db->Quote( $category );
		}

		if( $status != 2 )
		{
			$condition .= ' AND a.published='. $db->Quote( $status );
		}

		$query		= 'SELECT * FROM '
					. $db->quoteName( '#__community_videos' ) . ' As a '
					. 'WHERE 1'
					. $condition
					. ' ORDER BY a.created DESC';

		return $query;
	}

	public function getVideos()
	{
		if(empty($this->_data))
		{

			$query			= $this->_buildQuery();
			$this->_data	= $this->_getList( $query , $this->getState( 'limitstart' ) , $this->getState( 'limit') );
		}
		return $this->_data;
	}

	public function getCategories()
	{
		$mainframe	= JFactory::getApplication();

		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_videos_category');
		$db->setQuery( $query );
		$categories	= $db->loadObjectList();

		return $categories;
	}

	public function delete( $activityId )
	{
		$db		= JFactory::getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_videos' ) . ' WHERE '
				. $db->quoteName( 'id' ) . '=' . $db->Quote( $activityId );
		$db->setQuery( $query );
		$db->Query();

		if( $db->getErrorNum() )
			return false;

		return true;
	}

	public function getVideosbyInterval($interval = 'week')
	{
		$db = $this->getDBO();

		switch ($interval)
		{
			case 'week':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_videos')
				.' WHERE WEEK('.$db->quoteName('created').') = WEEK(curdate())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastweek':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_videos')
				.' WHERE WEEK('.$db->quoteName('created').') = WEEK(curdate() - INTERVAL 7 DAY)'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'month':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_videos')
				.' WHERE YEAR('.$db->quoteName('created').') = YEAR(CURDATE()) AND MONTH('.$db->quoteName('created').') = MONTH(CURDATE())'
				.' GROUP BY DATE('.$db->quoteName('created').')';
				break;

			case 'lastmonth':
				$query = 'SELECT DATE('.$db->quoteName('created').') as created, COUNT('.$db->quoteName('created').') as count FROM '.$db->quoteName('#__community_videos')
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

}