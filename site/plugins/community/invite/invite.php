<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

if(!class_exists('plgCommunityInvite'))
{
	class plgCommunityInvite extends CApplications
	{
		var $name		= 'Invite';
		var $_name		= 'invite';
	
	    function plgCommunityInvite(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
	    }
		
		// detect GET['invite'] and add cookies 
		function onSystemStart() {
			
			$inviteid = JRequest::getVar('invite', '', 'GET');
			if( !empty( $inviteid ) ){
				setcookie('inviteId', $inviteid, time()+60*60*24, '/');
			}
			
		}
		
		function onUserRegisterFormDisplay(&$text) {
			$invite = JRequest::getVar('inviteId', '', 'COOKIE');
			$text = CString::str_ireplace('</form>', '<input type="hidden" name="invite" value="'. $invite .'"></form>', $text);
		}
		
		function onAfterUserRegistration() {
		}
	}	
}

