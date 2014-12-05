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

class CFriendsHelper
{
	/**
	 * Check if 2 friends is connected or not
	 * @param	int userid1
	 * @param	int userid2
	 * @return	bool
	 */
	static public function isConnected($id1, $id2)
	{
		// Static caching for this session
		static $isFriend = array();
		if( !empty($isFriend[$id1.'-'.$id2]) ){
			return $isFriend[$id1.'-'.$id2];
		}

		if(($id1 == $id2) && ($id1 != 0))
			return true;

		if($id1 == 0 || $id2 == 0)
			return false;

			/*
		$db = JFactory::getDBO();
		$sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_connection')
			  .' WHERE ' . $db->quoteName('connect_from') .'=' . $db->Quote($id1) .' AND ' . $db->quoteName('connect_to') .'=' . $db->Quote($id2)
			  .' AND ' . $db->quoteName('status') .' = ' . $db->Quote(1);

		$db->setQuery($sql);
		$result = $db->loadResult();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$isFriend[$id1.'-'.$id2] = $result;
		*/

		// change method to get connection since list friends stored in community_users as well
		$user = CFactory::getUser($id1);
    	$isConnected = $user->isFriendWith($id2);

		return $isConnected;
	}

	static public function isWaitingApproval($id1,$id2){
		$db = JFactory::getDBO();
		$sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_connection')
			  .' WHERE ' . $db->quoteName('connect_from') .'=' . $db->Quote($id1) .' AND ' . $db->quoteName('connect_to') .'=' . $db->Quote($id2)
			  .' AND ' . $db->quoteName('status') .' = ' . $db->Quote(0);

		$db->setQuery($sql);
		$result = $db->loadResult();
		if($db->getErrorNum()) {
		JError::raiseError( 500, $db->stderr());
	}

	return !empty($result)?true:false;
}
}


/**
 * Deprecated since 1.8
 */
function friendIsConnected($id1, $id2)
{
	return CFriendsHelper::isConnected( $id1 , $id2 );
}

