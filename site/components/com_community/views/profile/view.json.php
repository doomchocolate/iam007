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
jimport( 'joomla.application.component.view');

class CommunityViewProfile extends CommunityView
{

	/**
	 * Displays the viewing profile page.
	 * 	 	
	 * @access	public
	 * @param	array  An associative array to display the fields
	 */	  	
	public function profile(& $data)
	{
        $mainframe      = JFactory::getApplication();
        $friendsModel	= CFactory::getModel('friends');
        
        $showfriends    = JRequest::getVar('showfriends', false);
        $userid         = JRequest::getVar('userid' , '');
        $user           = CFactory::getUser($userid);
		
		require_once (AZRUL_SYSTEM_PATH.'/pc_includes/JSON.php');
		
		$json = new Services_JSON();
		$str = $json->encode($user);
		echo $str;
		exit;
		
	}
}
?>
