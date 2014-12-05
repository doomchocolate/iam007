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

jimport( 'joomla.filesystem.file');

// Deprecated since 1.8.x to support older modules / plugins
//CFactory::load( 'tables' , 'connect' );

class CommunityModelConnect extends JCCModel
{

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

	public function updateConnectUserId( $connectid , $type , $userid )
	{
		$db		= JFactory::getDBO();

		$query	= 'UPDATE ' . $db->quoteName( '#__community_connect_users' ) . ' '
				. 'SET ' . $db->quoteName('userid') . '=' . $db->Quote( $userid ) . ' '
				. 'WHERE ' . $db->quoteName( 'connectid' ) . '=' . $db->Quote( $connectid ) . ' '
				. 'AND ' . $db->quoteName('type') . '=' . $db->Quote( $type );
		$db->setQuery( $query );
		$db->query();

		return $this;
	}

	public function isAssociated( $userId )
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(*) FROM ' . $db->quoteName( '#__community_connect_users' ) . ' '
				. 'WHERE ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userId );

		$db->setQuery( $query );

		$exist	= ( $db->loadResult() > 0 ) ? true : false;
		return $exist;
	}

	public function statusExists( $status , $userId )
	{
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->quoteName( 'actor' ) . '=' . $db->Quote( $userId ) . ' '
				. 'AND ' . $db->quoteName( 'app' ) . '=' . $db->Quote( 'profile' )
				. 'AND ' . $db->quoteName( 'title' ) . '=' . $db->Quote( $status );

		$db->setQuery( $query );

		$exist	= ( $db->loadResult() > 0 ) ? true : false;
		return $exist;
	}
}
