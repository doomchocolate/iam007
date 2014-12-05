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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );

class CMenuHelper
{
	/**
	 *	Returns an object of data containing user's address information
	 *
	 *	@access	static
	 *	@params	int	$userId
	 *	@return stdClass Object
	 **/
	static public function getComponentId()
	{
		$db		= JFactory::getDBO();

		//component id is retrieved from #__extensions table, overwrite query
		$query	= 'SELECT ' . $db->quoteName( 'extension_id' ) . ' FROM '
					. $db->quoteName( '#__extensions' ) . ' WHERE '
					. $db->quoteName( 'element' ) . '=' . $db->Quote( 'com_community' ) . ' '
					. 'AND ' . $db->quoteName( 'type' ) . '=' . $db->Quote( 'component' );


		$db->setQuery( $query );
		return $db->loadResult();
	}

	static public function getMenuIdByTitle($title){
		$db		= JFactory::getDBO();
		//component id is retrieved from #__extensions table, overwrite query
		$query	= 'SELECT ' . $db->quoteName( 'id' ) . ' FROM '
				. $db->quoteName( '#__menu' ) . ' WHERE '
				. $db->quoteName( 'title' ) . '=' . $db->Quote( $title );

		$db->setQuery( $query );
		return $db->loadResult();
	}


	//to update parent_id and level field in the menu table because the store funtion wont work
	static public function alterMenuTable($id){
		$db		= JFactory::getDBO();

		$data = new stdClass();
		$data -> id = $id;
		$data -> level = 1;
		$data -> parent_id = 1;
		$db->updateObject( '#__menu' , $data , 'id' );
	}
}