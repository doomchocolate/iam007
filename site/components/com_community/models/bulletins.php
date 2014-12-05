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

class CommunityModelBulletins extends JCCModel
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination	= '';

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $total			= '';

	/**
	 * Constructor
	 */
	public function CommunityModelBulletins()
	{
		parent::JCCModel();

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Get pagination request variables
 	 	$limit		= ($mainframe->getCfg('list_limit') == 0) ? 5 : $mainframe->getCfg('list_limit');
	    $limitstart = $jinput->request->get('limitstart', 0, 'INT'); //JRequest::getVar('limitstart', 0, 'REQUEST');

	    if(empty($limitstart))
 	 	{
 	 		$limitstart = $jinput->get('start', 0, 'INT');
 	 	}
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	/**
	 * Method to retrieve a list of bulletins
	 *
	 * @param	$id	The id of the group if necessary
	 *
	 * @return	$result	An array of bulletins
	 **/
	public function getBulletins( $groupId = null , $limit = 0 )
	{
		$db			= $this->getDBO();

		$where 		= ( !is_null($groupId) ) ? 'WHERE a.' . $db->quoteName('groupid') .'=' . $db->Quote( $groupId ) : '';
		$limitSQL	= '';

		$limit		= ($limit == 0) ? $this->getState('limit') : $limit;
		$limitstart = $this->getState('limitstart');

		$query	= 'SELECT COUNT(*) '
				. 'FROM ' . $db->quoteName( '#__community_groups_bulletins') . ' AS a '
				. $where;

		$db->setQuery( $query );
		$total	= $db->loadResult();
		$this->total	= $total;

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
	    }

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total , $limitstart , $limit);
		}

		$limitSQL	.= ' LIMIT ' . $limitstart . ',' . $limit;

		$query	= 'SELECT * '
				. 'FROM ' . $db->quoteName('#__community_groups_bulletins') . ' AS a '
				. $where . ' '
				. 'ORDER BY a.' . $db->quoteName('date') .' DESC'
				. $limitSQL;

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
	    }

		return $result;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getPagination()
	{
		return $this->_pagination;
	}
}
