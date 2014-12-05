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

class CNotificationTypes {
	var $_notificationTypes		= array();
	var $_adminonlygroups = array();

	public function CNotificationTypes()
	{
		$this->_notificationTypes	= array();
		//load default email types
		$this->loadDefault();
	}

	public function isAdminOnlyGroup($group){
		return (isset($this->_adminonlygroups[$group])) ? TRUE : FALSE;
	}

	public function getTypes(){
		return $this->_notificationTypes;
	}

	public function loadDefault(){

		$config = CConfig::getInstance();

		//add default group
		$groups = array(
						'ADMIN'		=> array('COM_COMMUNITY_NOTIFICATIONGROUP_ADMIN', TRUE),
						'PROFILE'	=> array('COM_COMMUNITY_NOTIFICATIONGROUP_PROFILE', FALSE),
						'GROUPS'	=> array('COM_COMMUNITY_NOTIFICATIONGROUP_GROUPS', FALSE),
						'EVENTS'	=> array('COM_COMMUNITY_NOTIFICATIONGROUP_EVENTS', FALSE),
						'VIDEOS'	=> array('COM_COMMUNITY_NOTIFICATIONGROUP_VIDEOS', FALSE),
						'PHOTOS'	=> array('COM_COMMUNITY_NOTIFICATIONGROUP_PHOTOS', FALSE),
						'OTHERS'	=> array('COM_COMMUNITY_NOTIFICATIONGROUP_OTHERS', FALSE)
					);
		foreach ($groups as $key => $desc){

			if(is_null($config->get('enable'.strtolower($key),null)) || $config->get('enable'.strtolower($key),null) ){
				$this->addGroup($key,$desc[0], $desc[1]);
			}
		}
		//exit;
		$types = array();

		//Admin
		$types[]	= array('ADMIN','groups_notify_admin','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_CREATION_MODERATION_REQUIRED','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_CREATION_MODERATION_REQUIRED_TIPS', TRUE);
		$types[]	= array('ADMIN','user_profile_delete','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_DELETE','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_DELETE_TIPS', TRUE);
		$types[]	= array('ADMIN','system_reports_threshold','COM_COMMUNITY_NOTIFICATIONTYPE_REPORTS_THRESHOLD','COM_COMMUNITY_NOTIFICATIONTYPE_REPORTS_THRESHOLD_TIPS', TRUE);
		$types[]	= array('EVENTS','events_notify_admin','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_CREATION_MODERATION_REQUIRED','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_CREATION_MODERATION_REQUIRED_TIPS',TRUE);

		//Profile
		$types[]	= array('PROFILE','profile_activity_add_comment','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_ACTIVITYCOMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_ACTIVITYCOMMENT_TIPS');
		$types[]	= array('PROFILE','profile_activity_reply_comment','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_ACTIVITYREPLY','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_ACTIVITYREPLY_TIPS');
		$types[]	= array('PROFILE','profile_status_update','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_STATUSUPDATE','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_STATUSUPDATE_TIPS');
		$types[]	= array('PROFILE','profile_like','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_LIKE','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_LIKE_TIPS');
		$types[]	= array('PROFILE','profile_stream_like','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_STREAM_LIKE','COM_COMMUNITY_NOTIFICATIONTYPE_PROFILE_STREAM_LIKE_TIPS');

		//Friends
		$types[]	= array('PROFILE','friends_request_connection','COM_COMMUNITY_NOTIFICATIONTYPE_FRIENDS_INVITE','COM_COMMUNITY_NOTIFICATIONTYPE_FRIENDS_INVITE_TIPS',false,true);
		$types[]	= array('PROFILE','friends_create_connection','COM_COMMUNITY_NOTIFICATIONTYPE_FRIENDS_CONNECTION','COM_COMMUNITY_NOTIFICATIONTYPE_FRIENDS_CONNECTION_TIPS');
		$types[]	= array('PROFILE','inbox_create_message','COM_COMMUNITY_NOTIFICATIONTYPE_OTHERS_INBOXMSG','COM_COMMUNITY_NOTIFICATIONTYPE_OTHERS_INBOXMSG_TIPS');

		if($config->get('enablegroups')){

			//Groups
	//		$types[]	= array('GROUPS','groups_submit_wall_comment','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_WALLCOMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_WALLCOMMENT_TIPS');
			$types[]	= array('GROUPS','groups_invite','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_INVITE','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_INVITE_TIPS',false,true);
			$types[]	= array('GROUPS','groups_discussion_reply','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_DISCUSSIONREPLY','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_DISCUSSIONREPLY_TIPS');
			$types[]	= array('GROUPS','groups_wall_create','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_WALLUPDATE','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_WALLUPDATE_TIPS');
			$types[]	= array('GROUPS','groups_create_discussion','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWDISCUSSION','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWDISCUSSION_TIPS');
			$types[]	= array('GROUPS','groups_create_news','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWBULLETIN','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWBULLETIN_TIPS');
			$types[]	= array('GROUPS','groups_create_album','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWALBUM','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWALBUM_TIPS');
			$types[]	= array('GROUPS','groups_create_video','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWVIDEO','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWVIDEO_TIPS');
			$types[]	= array('GROUPS','groups_create_event','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWEVENT','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWEVENT_TIPS');
			$types[]	= array('GROUPS','groups_sendmail','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_MASSEMAIL','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_MASSEMAIL_TIPS');
			$types[]	= array('GROUPS','groups_member_approved','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWMEMBER','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWMEMBER_TIPS');
			$types[]	= array('GROUPS','groups_member_join','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWMEMBER_REQUEST','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEWMEMBER_REQUEST_TIPS',false,true);
			$types[]	= array('GROUPS','groups_notify_creator','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_CREATION_APPROVED','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_CREATION_APPROVED_TIPS');
			$types[]	= array('GROUPS','groups_discussion_newfile','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_DISCUSSION_NEWFILE','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_DISCUSSION_NEWFILE_TIPS');
			$types[]	= array('GROUPS','groups_activity_add_comment','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEW_COMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_GROUPS_NEW_COMMENT_TIPS');

		}

		if($config->get('enableevents')){
			//Events
	//		$types[]	= array('EVENTS','events_submit_wall_comment','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_WALLCOMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_WALLCOMMENT_TIPS');
			$types[]	= array('EVENTS','events_invite','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_INVITATION','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_INVITATION_TIPS',false,true);
			$types[]	= array('EVENTS','events_invitation_approved','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_INVITATION_APPROVED','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_INVITATION_APPROVED_TIPS');
			$types[]	= array('EVENTS','events_sendmail','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_MASSEMAIL','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_MASSEMAIL_TIPS');
			$types[]	= array('EVENTS','event_notify_creator','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_CREATION_APPROVED','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_CREATION_APPROVED_TIPS');
			$types[]	= array('EVENTS','event_join_request','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_JOIN_REQUEST','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_JOIN_REQUEST_TIPS');
			$types[]	= array('EVENTS','events_activity_add_comment','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_NEW_COMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_EVENTS_NEW_COMMENT_TIPS');

		}

		if($config->get('enablevideos')){
			//Videos
			$types[]	= array('VIDEOS','videos_submit_wall','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEOS_WALLCOMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEOS_WALLCOMMENT_TIPS');
			$types[]	= array('VIDEOS','videos_reply_wall','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEOS_WALLCOMMENT_REPLY','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEOS_WALLCOMMENT_REPLY_TIPS');
			$types[]	= array('VIDEOS','videos_tagging','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEOS_TAG','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEOS_TAG_TIPS');
			$types[]	= array('VIDEOS','videos_like','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEO_LIKE','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEO_LIKE_TIPS');
			$types[]	= array('VIDEOS','videos_convert_success','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEO_CONVERT_SUCCESS','COM_COMMUNITY_NOTIFICATIONTYPE_VIDEO_CONVERT_SUCCESS_TIPS');

		}

		if($config->get('enablephotos')){
			//Photos
			$types[]	= array('PHOTOS','photos_submit_wall','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_WALLCOMMENT','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_WALLCOMMENT_TIPS');
			$types[]	= array('PHOTOS','photos_reply_wall','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_WALLCOMMENT_REPLY','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_WALLCOMMENT_REPLY_TIPS');
			$types[]	= array('PHOTOS','photos_tagging','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_TAG','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_TAG_TIPS');
			$types[]	= array('PHOTOS','photos_like','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_LIKE','COM_COMMUNITY_NOTIFICATIONTYPE_PHOTOS_LIKE_TIPS');

		}

		//Others
		//$types[]	= array('OTHERS','system_bookmarks_email','COM_COMMUNITY_NOTIFICATIONTYPE_OTHERS_BOOKMARKS','COM_COMMUNITY_NOTIFICATIONTYPE_OTHERS_BOOKMARKS_TIPS');
		$types[]	= array('OTHERS','system_messaging','COM_COMMUNITY_NOTIFICATIONTYPE_OTHERS_SYSTEMMSG','COM_COMMUNITY_NOTIFICATIONTYPE_OTHERS_SYSTEMMSG_TIPS');
		foreach ($types as $type){

			$adminOnly=(isset($type[4])) ? $type[4] : FALSE;
			$requiredAction=(isset($type[5])) ? $type[5] : FALSE;
			$this->addType($type[0],$type[1],$type[2],$config->get($type[1],0),$type[3],$adminOnly, $requiredAction);

		}
	}
	/**
	 * Function to add new email type.
	 * param - groupKey : string - the key of the group
	 *       - TypeID : string - the unique key of the email type
	 *       - description : string - the label of the email type
	 *       - value	: int - the configured value (enable/disable) of the email type
	 *       - tips	: string - the tips of the email type
	 */
	public function addType($groupKey, $TypeID, $description='', $value=0, $tips='',$adminOnly=FALSE, $requiredAction=FALSE){
		if(array_key_exists($groupKey, $this->_notificationTypes))
		{
			$tbGroup	=  $this->_notificationTypes[strtoupper($groupKey)];

			$child	= new stdClass();
			$child->description		= $description;
			$child->value			= $value;
			$child->tips			= $tips;
			$child->adminOnly		= $adminOnly;
			$child->requiredAction	= $requiredAction;

			$tbGroup->child[$TypeID]	= $child;
		}
	}
	/**
	 * Function to get a notification type.
	 * param - groupKey : string - the key of the group
	 *       - TypeID : string - the unique key of the notification type
	 */
	public function getType($groupKey='', $TypeID){
		if(array_key_exists($groupKey, $this->_notificationTypes))
		{
			$tbGroup	=  $this->_notificationTypes[strtoupper($groupKey)];
			if(array_key_exists($TypeID, $tbGroup->child)){
				return $tbGroup->child[$TypeID];
			}
		} else {
			foreach ($this->_notificationTypes as $tbGroup){
				if(array_key_exists($TypeID, $tbGroup->child)){
					return $tbGroup->child[$TypeID];
				}
			}
		}
		return null;
	}
	/**
	 * Function to add new notification group.
	 * param - key : string - the key of the group
	 *       - description : string - the label of the group name
	 */
	public function addGroup($key, $description='', $adminOnly=FALSE)
	{
		if(! array_key_exists($key, $this->_notificationTypes))
		{
	    	$newGroup				= new stdClass();
			$newGroup->description	= $description;
			$newGroup->child		= array();
			if($adminOnly) {
				$this->_adminonlygroups[$description] = $description;
			}

			$this->_notificationTypes[strtoupper($key)]	= $newGroup;
		}
	}

	/**
	 * Function used to remove notification group and its associated notification types.
	 * param - key : string - the key of the group
	 */
	public function removeGroup($key)
	{
		if(array_key_exists($key, $this->_notificationTypes))
		{
			unset($this->_notificationTypes[strtoupper($key)]);
		}
	}

	/**
	 * Function used to remove an notification type
	 * param - groupKey : string - the key of the group
	 *       - TypeID : string - the unique key of the notification type
	 */
	public function removeType($groupKey, $TypeID)
	{
		if(array_key_exists($groupKey, $this->_notificationTypes))
		{
			$tbGroup	=  $this->_notificationTypes[strtoupper($groupKey)];
			$childItem	=  $tbGroup->child;
			if (is_array($TypeID)){
				if(array_key_exists($TypeID, $childItem))
				{
					unset($childItem[$TypeID]);
				}
			}
		}
	}

	/**
	 * Function used to convert notification types to params string
	 */
	public function convertToParamsString(){
		$config = CConfig::getInstance();

		$ret = "";
		foreach($this->_notificationTypes as $group){
			foreach($group->child as $id => $type){
				$emailTypeString = CNotificationTypesHelper::convertNotifId($id);
				$notiTypeString = CNotificationTypesHelper::convertEmailId($id);
				//email notification
				$ret .=  $emailTypeString . "=" .  $config->get($emailTypeString,0). "\n";
				//internal notification
				$ret .= $notiTypeString . "=" . $config->get($notiTypeString,0) . "\n";
			}
		}
		return $ret;
	}
	public function convertEmailId($id){


		if($id){
			return CNotificationTypesHelper::convertEmailId($id) ;
		}
		return '';
	}
	public function convertNotifId($id){


		if($id){
			return CNotificationTypesHelper::convertNotifId( $id );
		}
		return '';
	}
}