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

class CEventTrigger
{

	public function __call($name, $arguments)
	{
        // Note: value of $name is case sensitive.
		switch($name)
		{
			case 'onAfterConfigCreate':
				include_once (COMMUNITY_COM_PATH.'/events/config.trigger.php');
				$plgObj = new CConfigTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;
			/* profile event */
			case 'onAfterProfileUpdate':
				include_once (COMMUNITY_COM_PATH.'/events/profile.trigger.php');
				$plgObj = new CProfileTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;
			case 'onProfileStatusUpdate':
				include_once (COMMUNITY_COM_PATH.'/events/profile.trigger.php');
				$plgObj = new CProfileTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;
			/* Group event*/
			case 'onGroupCreate':
				include_once( COMMUNITY_COM_PATH .'/events/groups.trigger.php' );
				$plgObj	= new CGroupsTrigger();
				call_user_func_array( array(&$plgObj , $name) , $arguments );
				break;
			case 'onGroupJoin':
				include_once( COMMUNITY_COM_PATH .'/events/groups.trigger.php' );
				$plgObj	= new CGroupsTrigger();
				call_user_func_array( array(&$plgObj , $name) , $arguments );
				break;
			case 'onDiscussionDisplay':
				include_once( COMMUNITY_COM_PATH .'/events/groups.trigger.php' );
				$plgObj	= new CGroupsTrigger();
				call_user_func_array( array(&$plgObj , $name) , $arguments );
				break;
			case 'onBulletinDisplay':
				include_once( COMMUNITY_COM_PATH .'/events/groups.trigger.php' );
				$plgObj	= new CGroupsTrigger();
				call_user_func_array( array(&$plgObj , $name) , $arguments );
				break;

			/* Events */
			case 'onEventCreate':
				include_once (COMMUNITY_COM_PATH.'/events/events.trigger.php');
				$plgObj = new CEventsTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;

			/* Friends */
			case 'onFriendApprove':
				include_once (COMMUNITY_COM_PATH.'/events/friends.trigger.php');
				$plgObj = new CFriendsTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;

			/* Photos */
			case 'onAfterPhotoDelete':
				include_once (COMMUNITY_COM_PATH.'/events/photos.trigger.php');
				$plgObj = new CPhotosTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;

			/* Wall */
			case 'onWallDisplay':
				include_once (COMMUNITY_COM_PATH.'/events/wall.trigger.php');
				$plgObj = new CWallTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;
			case 'onAfterWallDelete':
				include_once (COMMUNITY_COM_PATH.'/events/wall.trigger.php');
				$plgObj = new CWallTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;
			/* Messaging */
			case 'onMessageDisplay':
				include_once (COMMUNITY_COM_PATH.'/events/inbox.trigger.php');
				$plgObj = new CInboxTrigger();
				call_user_func_array(array(&$plgObj, $name), $arguments);
				break;
			default:
				// do nothing
		}
    }

}