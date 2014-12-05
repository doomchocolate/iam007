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

class CommunityModelCommunity extends JModelLegacy
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
		if ($this->_pagination == null)
		{
			$this->getFields();
		}
		return $this->_pagination;
	}

	public function &getGroupsinfo()
	{
		$groups		= new stdClass();
		$db			= $this->getDBO();

		/**
		 * Get number of published groups
		 **/
		$query		= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups' ) . ' '
					. 'WHERE ' . $db->quoteName( 'published' ) . '=' . $db->Quote( '1' );

		$db->setQuery( $query );
		$groups->published	= $db->loadResult();

		$query		= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups' ) . ' '
					. 'WHERE ' . $db->quoteName( 'published' ) . '=' . $db->Quote( '0' );

		$db->setQuery( $query );
		$groups->unpublished	= $db->loadResult();

		$query		= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups_category' );

		$db->setQuery( $query );
		$groups->categories	= $db->loadResult();

		return $groups;
	}

	public function &getCommunityInfo()
	{
		$db		= $this->getDBO();
		$community	= new stdClass();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__users' );
		$db->setQuery( $query );
		$community->total	= $db->loadResult();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__users' ) . ' '
				. 'WHERE ' . $db->quoteName( 'block' ) . '=' . $db->Quote( '1' );
		$db->setQuery( $query );
		$community->blocked	= $db->loadResult();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( PLUGIN_TABLE_NAME ) . ' '
				. 'WHERE ' . $db->quoteName( 'folder' ) . '=' . $db->Quote( 'community' );
		$db->setQuery( $query );
		$community->applications	= $db->loadResult();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_activities' );
		$db->setQuery( $query );
		$community->updates	= $db->loadResult();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_photos' );
		$db->setQuery( $query );
		$community->photos	= $db->loadResult();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_videos' );
		$db->setQuery( $query );
		$community->videos	= $db->loadResult();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_groups_discuss' );
		$db->setQuery( $query );
		$community->groupDiscussion	= $db->loadResult();

		return $community;
	}
}