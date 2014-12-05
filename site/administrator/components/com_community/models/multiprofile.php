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

class CommunityModelMultiProfile extends JModelLegacy
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
		$limitstart	= $mainframe->getUserStateFromRequest( 'com_community.multiprofile.limitstart', 'limitstart', 0, 'int' );

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

	/**
	 * Returns multi profiles
	 *
	 **/
	public function getMultiProfiles()
	{
		$mainframe	= JFactory::getApplication();

		static $fields;

		if( isset( $fields ) )
		{
			return $fields;
		}

		// Initialize variables
		$db			= JFactory::getDBO();

		// Get the limit / limitstart
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest('com_community.multiprofile.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart	= ($limit != 0) ? ($limitstart / $limit ) * $limit : 0;

		// Get the total number of records for pagination
		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_profiles' );
		$db->setQuery( $query );
		$total	= $db->loadResult();

		jimport('joomla.html.pagination');

		// Get the pagination object
		$this->_pagination	= new JPagination( $total , $limitstart , $limit );

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_profiles' ) . ' '
				. 'ORDER BY ' . $db->quoteName( 'ordering' );
		$db->setQuery( $query , $this->_pagination->limitstart , $this->_pagination->limit );

		$fields	= $db->loadObjectList();

		return $fields;
	}

	public function &getGroups()
	{
		static $fieldGroups;

		if( isset( $fieldGroups ) )
		{
			return $fieldGroups;
		}

		$db		= JFactory::getDBO();

		$query	= 'SELECT * '
				. 'FROM ' . $db->quoteName( '#__community_fields' )
				. 'WHERE ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' );

		$db->setQuery( $query );

		$fieldGroups	= $db->loadObjectList();

		return $fieldGroups;
	}

	public function &getFieldGroup( $fieldId )
	{
		static $fieldGroup;

		if( isset( $fieldGroup ) )
		{
			return $fieldGroup;
		}

		$db		= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_fields' )
				. 'WHERE ' . $db->quoteName( 'ordering' ) . '<' . $db->Quote( $fieldId ) . ' '
				. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'group' )
				. 'ORDER BY ordering DESC '
				. 'LIMIT 1';

		$db->setQuery( $query );

		$fieldGroup	= $db->loadObject();

		return $fieldGroup;
	}



	public function getGroupFields( $groupOrderingId )
	{
		$fieldArray	= array();
		$db			= JFactory::getDBO();

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__community_fields' )
				. ' WHERE ' . $db->quoteName( 'ordering' ) . '>' . $db->Quote( $groupOrderingId )
				. ' AND `published`=' . $db->Quote( 1 )
				. ' AND `registration`=' . $db->Quote( 1 )
				. ' ORDER BY `ordering` ASC ';

		$db->setQuery( $query );

		$fieldGroup	= $db->loadObjectList();

		if(count($fieldGroup) > 0)
		{
			foreach($fieldGroup as $field)
			{
				if($field->type == 'group')
					break;
				else
				 	$fieldArray[]	= $field;
			}
		}

		return $fieldArray;
	}


	public function getProfileTypes()
	{
		static $types = false;

		if( !$types )
		{
			$path	= JPATH_ROOT . '/components/com_community/libraries/fields/customfields.xml';

			$parser	= new SimpleXMLElement( $path , NULL , true );

			$fields = $parser->fields;

			foreach($fields->children() as $field)
			{
				$data[(string) $field->type] = $field->name;
			}

			$types = $data;
		}
		return $types;
	}
}