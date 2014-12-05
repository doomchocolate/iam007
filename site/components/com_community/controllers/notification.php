<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class CommunityNotificationController extends CommunityBaseController
{

	public function ajaxGetNotification()
	{
		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse = new JAXResponse ();

		$my	= CFactory::getUser();

		//$inboxModel		= CFactory::getModel( 'inbox' );
		$friendModel	= CFactory::getModel( 'friends' );
		$eventModel		= CFactory::getModel( 'events' );
		$groupModel		= CFactory::getModel( 'groups' );

		$inboxHtml			= '';
		$frenHtml			= '';
		$notiTotal			= 0;

		//getting pending event request
		$pendingEvent	= $eventModel->getPending($my->id);
		$eventHtml		= '';
		$event			= JTable::getInstance( 'Event' , 'CTable' );

		if(!empty($pendingEvent))
		{
			$notiTotal			+= count($pendingEvent);
			for($i = 0; $i < count($pendingEvent); $i++)
			{
				$row			= 	$pendingEvent[$i];
				$row->invitor           =	CFactory::getUser($row->invited_by);
				$event->load( $row->eventid );
				$row->eventAvatar	=	$event->getThumbAvatar();
				$row->url		=	CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $row->eventid. false);
			}

			$tmpl	= new CTemplate();

			$tmpl->set( 'rows' 	, $pendingEvent );
			$tmpl->setRef( 'my'	, $my );
			$eventHtml = $tmpl->fetch( 'notification.event.invitations' );
		}

		//getting pending group request
		$pendingGroup   = $groupModel->getGroupInvites($my->id);
		$groupHtml      = '';
		$group          = JTable::getInstance( 'Group' , 'CTable' );
		$groupNotiTotal =0;

		if(!empty($pendingGroup))
		{
			$groupNotiTotal     +=count($pendingGroup);

			for($i=0; $i< count($pendingGroup); $i++)
			{
				$gRow               =   $pendingGroup[$i];
				$gRow->invitor      =   CFactory::getUser($gRow->creator);
				$group->load( $gRow->groupid );
				$gRow->name         =   $group->name;
				$gRow->groupAvatar  =   $group->getThumbAvatar();
				$gRow->url          =   CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $gRow->groupid . false);
			}

			$tmpl	= new CTemplate();

			$tmpl->set( 'gRows' 	, $pendingGroup );
			$tmpl->setRef( 'my'	, $my );
			$groupHtml = $tmpl->fetch( 'notification.group.invitations' );
		}

		//geting pending private group join request
		//Find Users Groups Admin
		$allGroups = $groupModel->getAdminGroups( $my->id , COMMUNITY_PRIVATE_GROUP);
		$groupMemberApproveHTML = '';

		//Get unApproved member
		if(!empty($allGroups))
		{
			foreach($allGroups as $groups)
			{

				$member    =	$groupModel->getMembers( $groups->id , 0, false );

				if(!empty($member))
				{

					for($i=0; $i< count($member); $i++){

						$oRow =  $member[$i];
						$group->load($groups->id);
						$oRow->groupId  = $groups->id;
						$oRow->groupName = $groups->name;
						$oRow->groupAvatar  =   $group->getThumbAvatar();
						$oRow->url          =   CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id . false);
						$members[]=$member[$i];
					}
				}
			}
		}

		if(!empty($members))
		{
			$tmpl = new CTemplate();

			$tmpl->set( 'oRows' ,   $members );
			$tmpl->set( 'my'    ,   $my );
			$groupMemberApproveHTML = $tmpl->fetch('notification.group.request');
		}

		//non require action notification

		$itemHtml = '';
		$notifCount = 5;
		$notificationModel	= CFactory::getModel( 'notification' );
		$myParams			= 	$my->getParams();

		$notifications = $notificationModel->getNotification($my->id,'0',$notifCount,$myParams->get('lastnotificationlist',''));
		if(!empty($notifications)){

			for($i=0; $i< count($notifications); $i++){
				//add some inforamtion to the notification object
				$iRow               =   $notifications[$i];
				$iRow->actorUser    =   CFactory::getUser($iRow->actor);
				$iRow->actorAvatar  =   $iRow->actorUser->getThumbAvatar();
				$iRow->actorName	=   $iRow->actorUser->getDisplayName();
				$iRow->timeDiff		= 	CTimeHelper::timeLapse(CTimeHelper::getDate($iRow->created));
				$iRow->contentHtml 	=	CContentHelper::injectTags($iRow->content,$iRow->params,true);
				$params = new CParameter( $iRow->params );
				$iRow->url          =   $params->get('url','');
			}
			$tmpl	= new CTemplate();

			$tmpl->set( 'iRows' 	, $notifications );
			$tmpl->setRef( 'my'	, $my );
			$itemHtml = $tmpl->fetch( 'notification.item' );
		}

		$notiHtml	=  $inboxHtml . $frenHtml . $eventHtml . $groupHtml . $groupMemberApproveHTML . $itemHtml;

		if(empty($notiHtml)){
			$notiHtml .= '<div class="cEmpty cAlert">';
			$notiHtml .= 	JText::_('COM_COMMUNITY_NO_NOTIFICATION');
			$notiHtml .= '</div>';
		}
		//update the last notification viewing to user params
		$date = JFactory::getDate();
		$myParams->set('lastnotificationlist', $date->toSql());
		$my->save('params');
		//update notification counter
		//$newNotificationCount	= $notiTotal + $groupNotiTotal + $notificationModel->getNotificationCount($my->id,'liner',$myParams->get('lastnotificationlist',''));
		//$objResponse->addAssign('#jsMenuNotif .notifcount', 'innerHTML', $newNotificationCount);


		$url	= CRoute::_('index.php?option=com_community&view=profile&task=notifications');
		$notiHtml .= '<div class="cWindowNotification-Jumper">';
		$notiHtml .= '<a href="'.$url.'" class="btn btn-default btn-block">' . JText::_('COM_COMMUNITY_VIEW_ALL') .'</a>';
		$notiHtml .= '</div>';
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_NOTIFICATIONS'));
		$objResponse->addScriptCall('cWindowAddContent', $notiHtml);


		//$objResponse->addScriptCall('update_counter("#jsMenuNotif > .notifcount", -1);');
		$objResponse->sendResponse();
	}


	private function _getTimeDiffString($created,$daydiff=''){

		$timeDiff = '';
		if($daydiff== 0)
		{
			$date			= CTimeHelper::getDate($created);
			$timeDiff			= CTimeHelper::timeLapse($date);

		}
		else if($daydiff == 1)
		{
			$timeDiff = JText::_('COM_COMMUNITY_ACTIVITIES_YESTERDAY');
		}
		else if($daydiff< 7)
		{
			$timeDiff = JText::sprintf('COM_COMMUNITY_ACTIVITIES_DAYS_AGO', $daydiff);
		}
		else if(($daydiff >= 7) && ($daydiff < 30))
		{
			$dayinterval = ACTIVITY_INTERVAL_WEEK;
			$timeDiff = (intval($daydiff/$dayinterval) == 1 ? JText::_('COM_COMMUNITY_ACTIVITIES_WEEK_AGO') : JText::sprintf('COM_COMMUNITY_ACTIVITIES_WEEK_AGO_MANY', intval($daydiff/$dayinterval)));
		}
		else if(($daydiff >= 30))
		{
			$dayinterval = ACTIVITY_INTERVAL_MONTH;
			$timeDiff = (intval($daydiff/$dayinterval) == 1 ? JText::_('COM_COMMUNITY_ACTIVITIES_MONTH_AGO') : JText::sprintf('COM_COMMUNITY_ACTIVITIES_MONTH_AGO_MANY', intval($daydiff/$dayinterval)));
		}
		return $timeDiff;
	}
	/**
	 * Ajax function to reject a friend request
	 **/
	public function ajaxRejectRequest( $requestId )
	{
		$filter = JFilterInput::getInstance();
		$requestId = $filter->clean($requestId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	= new JAXResponse();
		$my				= CFactory::getUser();
		$friendsModel	= CFactory::getModel('friends');

		if( $friendsModel->isMyRequest( $requestId , $my->id) )
		{
			$pendingInfo = $friendsModel->getPendingUserId($requestId);

			if( $friendsModel->rejectRequest( $requestId ) )
			{
				//add user points - friends.request.reject removed @ 20090313

				$objResponse->addScriptCall( 'joms.jQuery("#msg-pending-' . $requestId . '").html("'.JText::_('COM_COMMUNITY_FRIENDS_REQUEST_REJECTED').'");');
				$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
				$objResponse->addScriptCall( 'joms.jQuery("#noti-pending-' . $requestId . '").fadeOut(1000, function() { joms.jQuery("#noti-pending-' . $requestId . '").remove();} );');

				$objResponse->addScriptCall('update_counter("#jsMenuNotif > .notifcount", -1);');
				$objResponse->addScriptCall('update_counter("#jsMenuFriend > .notifcount", -1);');

				//trigger for onFriendReject
				require_once(JPATH_ROOT .'/components/com_community/controllers/friends.php');
				$eventObject = new stdClass();
				$eventObject->profileOwnerId 	= $my->id;
				$eventObject->friendId 			= $pendingInfo->connect_from;
				CommunityFriendsController::triggerFriendEvents( 'onFriendReject' , $eventObject);
				unset($eventObject);
			}
			else
			{
				$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $requestId . '").html("' . JText::sprintf('COM_COMMUNITY_FRIEND_REQUEST_REJECT_FAILED', $requestId ) . '");' );
				$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $requestId . '").attr("class", "error");');
			}

		}
		else
		{
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $requestId . '").html("' . JText::_('COM_COMMUNITY_FRIENDS_NOT_YOUR_REQUEST') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $requestId . '").attr("class", "error");');
		}

		return $objResponse->sendResponse();
	}

	/**
	 * Ajax function to approve a friend request
	 **/
	public function ajaxApproveRequest( $requestId )
	{
		$filter = JFilterInput::getInstance();
		$requestId = $filter->clean($requestId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	= new JAXResponse();
		$my				= CFactory::getUser();
		$friendsModel	= CFactory::getModel( 'friends' );

		if( $friendsModel->isMyRequest( $requestId , $my->id) )
		{
			$connected		= $friendsModel->approveRequest( $requestId );

			if( $connected )
			{
				$act			= new stdClass();
				$act->cmd 		= 'friends.request.approve';
				$act->actor   	= $connected[0];
				$act->target  	= $connected[1];
				$act->title	  	= '';//JText::_('COM_COMMUNITY_ACTIVITY_FRIENDS_NOW');
				$act->content	= '';
				$act->app		= 'friends.connect';
				$act->cid		= 0;


				CActivityStream::add($act);

				//add user points - give points to both party
				//CFactory::load( 'libraries' , 'userpoints' );
				CUserPoints::assignPoint('friends.request.approve');

				$friendId		= ( $connected[0] == $my->id ) ? $connected[1] : $connected[0];
				$friend			= CFactory::getUser( $friendId );
				CUserPoints::assignPoint('friends.request.approve', $friendId);

				// Add the friend count for the current user and the connected user
				$friendsModel->addFriendCount( $connected[0] );
				$friendsModel->addFriendCount( $connected[1] );

				// Add notification
				//CFactory::load( 'libraries' , 'notification' );

				$params			= new CParameter( '' );
				$params->set( 'url' , 'index.php?option=com_community&view=profile&userid='.$my->id );
				$params->set( 'friend' , $my->getDisplayName() );
				$params->set( 'friend_url' , 'index.php?option=com_community&view=profile&userid='.$my->id );

				CNotificationLibrary::add( 'friends_create_connection' , $my->id , $friend->id , JText::sprintf('COM_COMMUNITY_FRIEND_REQUEST_APPROVED' ) , '' , 'friends.approve' , $params );

				$objResponse->addScriptCall( 'joms.jQuery("#msg-pending-' . $requestId . '").html("'.addslashes(JText::sprintf('COM_COMMUNITY_FRIENDS_NOW', $friend->getDisplayName())).'");');
				$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
				$objResponse->addScriptCall( 'joms.jQuery("#noti-pending-' . $requestId . '").fadeOut(1000, function() { joms.jQuery("#noti-pending-' . $requestId . '").remove();} );');

				$objResponse->addScriptCall('update_counter("#jsMenuNotif > .notifcount", -1);');
				$objResponse->addScriptCall('update_counter("#jsMenuFriend > .notifcount", -1);');

				//trigger for onFriendApprove
				require_once(JPATH_ROOT .'/components/com_community/controllers/friends.php');
				$eventObject = new stdClass();
				$eventObject->profileOwnerId 	= $my->id;
				$eventObject->friendId 			= $friendId;
				CommunityFriendsController::triggerFriendEvents( 'onFriendApprove' , $eventObject);
				unset($eventObject);
			}
		}
		else
		{
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $requestId . '").html("' . JText::_('COM_COMMUNITY_FRIENDS_NOT_YOUR_REQUEST') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $requestId . '").attr("class", "error");');
		}

		return $objResponse->sendResponse();
	}

	/**
	 * Popup all friend request
	 */
	public function ajaxGetRequest()
	{
		$objResponse	=	new JAXResponse();

		$my = CFactory::getUser();
		$friendModel = CFactory::getModel('friends');
		$rows = $friendModel->getPending($my->id);

		// format for template
		$data = array();
		$friendIdList = array();
		foreach( $rows as $row )
		{
			if(!in_array($row->id,$friendIdList)){
				$user	= CFactory::getUser($row->id);
				$obj	= new stdClass();
				$obj->user	= $user;
				$obj->title	= $row->msg;
				$obj->link	= null;
				$obj->created = null;
				$obj->rowid	  = 'pending-'. $row->connection_id;
				$obj->action  =
					'
						<a onclick="jax.call(\'community\',\'friends,ajaxRejectRequest\',\''.$row->connection_id.'\');" class="btn" href="javascript:void(0);">'.JText::_('COM_COMMUNITY_FRIENDS_PENDING_ACTION_REJECT').'</a>
						<a onclick="jax.call(\'community\' , \'friends,ajaxApproveRequest\' , \''.$row->connection_id.'\');" class="btn btn-primary" href="javascript: void(0)">'.JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE').'</a>
					';
				$data[] = $obj;
			}

			$friendIdList[] = $row->id;
		}

		$tmpl = new CTemplate();
		$html = $tmpl
				->set('notifications', $data)
				->set('link', CRoute::_('index.php?option=com_community&view=friends&task=pending') )
				->set('link_text', JText::_('COM_COMMUNITY_NOTIFICATIONS_SHOW_ALL_MSG'))
				->set('empty_notice', JText::_('COM_COMMUNITY_PENDING_APPROVAL_EMPTY') )
				->fetch('notification.list');

		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_NOTI_NEW_FRIEND_REQUEST'));
		$objResponse->addScriptCall('cWindowAddContent', $html);
		return $objResponse->sendResponse();
	}

	/**
	 * Popup message notification
	 */
	public function ajaxGetInbox()
	{
		$objResponse	=	new JAXResponse();

		$inboxModel = CFactory::getModel('inbox');
		//$messages = $inboxModel->getInbox(false);
		$messages = $inboxModel->getUnReadInbox();

		// format for template
		$data = array();
		foreach( $messages as $row )
		{
			$user	= CFactory::getUser($row->from);
			$obj	= new stdClass();
			$obj->user	= $user;
			$obj->title	= $row->subject;
			$obj->link	= CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid='.$row->parent);
			$obj->created = CTimeHelper::timeLapse(CTimeHelper::getDate($row->posted_on));
			$obj->action  = null;
			$data[] = $obj;
		}

		$tmpl = new CTemplate();
		$html = $tmpl
				->set('notifications', $data)
				->set('link', CRoute::_('index.php?option=com_community&view=inbox') )
				->set('link_text', JText::_('COM_COMMUNITY_NOTIFICATIONS_SHOW_ALL_MSG'))
				->set('empty_notice', JText::_('COM_COMMUNITY_NOTIFICATIONS_NO_MESSAGE') )
				->fetch('notification.list');

		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_MESSAGE'));
		$objResponse->addScriptCall('cWindowAddContent', $html);
		return $objResponse->sendResponse();
	}


	/**
	 * Ajax function to join an event invitation
	 *
	 **/
	public function ajaxJoinInvitation( $invitationId, $eventId){
		$filter = JFilterInput::getInstance();
		$invitationId = $filter->clean($invitationId, 'int');
		$eventId = $filter->clean($eventId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my				=	CFactory::getUser();

		// Get events model
		$model	= CFactory::getModel('events');

		if( $model->isInvitedMe( $invitationId , $my->id) ){
			$event	= JTable::getInstance( 'Event' , 'CTable' );
			$event->load( $eventId );

			$this->_updateInviteStatus($invitationId, $eventId, COMMUNITY_EVENT_STATUS_ATTEND);

			// Activity stream purpose
			$act = new stdClass();
			$act->cmd 		= 'event.join';
			$act->actor   	= $my->id;
			$act->target  	= 0;
			$act->title	  	= '';//JText::sprintf('COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND' , $event->title);
			$act->content	= '';
			$act->app		= 'events';
			$act->cid		= $event->id;

			$params 		= new CParameter('');
			$action_str  	= 'event.join';
			$params->set( 'eventid' , $event->id);
			$params->set( 'action', $action_str );
			$params->set( 'event_url', 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);

			// Add activity logging

			CActivityStream::addActor( $act, $params->toString() );

			$url	=	CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);

			$objResponse->addScriptCall( 'joms.jQuery("#msg-pending-' . $invitationId  . '").html("'.addslashes(JText::sprintf('COM_COMMUNITY_EVENTS_ACCEPTED', $event->title , $url )).'");');
			$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
			$objResponse->addScriptCall( 'joms.jQuery("#noti-pending-' . $invitationId  . '").fadeOut(1000, function() { joms.jQuery("#noti-pending-' . $invitationId . '").remove();} );');
			$objResponse->addScriptCall( 'aspan = joms.jQuery("#jsMenu .jsMenuIcon span"); aspan.html(parseInt(aspan.html())-1);');
		}else{
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $invitationId  . '").html("' . JText::_('COM_COMMUNITY_EVENTS_NOT_INVITED_NOTIFICATION') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $invitationId  . '").attr("class", "error");');
		}

		return $objResponse->sendResponse();
	}


	/**
	 * Ajax function to reject an event invitation
	 *
	 **/
	public function ajaxRejectInvitation( $invitationId, $eventId){
		$filter = JFilterInput::getInstance();
		$invitationId = $filter->clean($invitationId, 'int');
		$eventId = $filter->clean($eventId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my				=	CFactory::getUser();

		// Get events model
		$model	= CFactory::getModel('events');

		if( $model->isInvitedMe( $invitationId , $my->id) ){
			$event	= JTable::getInstance( 'Event' , 'CTable' );
			$event->load( $eventId );

			$this->_updateInviteStatus($invitationId, $eventId, COMMUNITY_EVENT_STATUS_WONTATTEND);

			$url	=	CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);

			$objResponse->addScriptCall( 'joms.jQuery("#msg-pending-' . $invitationId  . '").html("'.addslashes(JText::sprintf('COM_COMMUNITY_EVENTS_REJECTED', $event->title , $url )).'");');
			$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
			$objResponse->addScriptCall( 'joms.jQuery("#noti-pending-' . $invitationId  . '").fadeOut(1000, function() { joms.jQuery("#noti-pending-' . $invitationId . '").remove();} );');
			$objResponse->addScriptCall( 'aspan = joms.jQuery("#jsMenu .jsMenuIcon span"); aspan.html(parseInt(aspan.html())-1);');

		}else{
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $invitationId  . '").html("' . JText::_('COM_COMMUNITY_EVENTS_NOT_INVITED_NOTIFICATION') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $invitationId  . '").attr("class", "error");');
		}

		return $objResponse->sendResponse();
	}

	/**
	 * Update invitation status
	 */
	private function _updateInviteStatus($inviteId, $eventId, $status)
	{
		$my				=	CFactory::getUser();
		$event	= JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		$guest	= JTable::getInstance( 'EventMembers' , 'CTable' );
		$key['eventId'] = $eventId;
		$key['memberId'] = $my->id;
		$guest->load($key);

		// Set status to
		$guest->status = $status;
		$guest->store();

		// Update event stats count
		$event->updateGuestStats();
		$event->store();
	}


	/**
	 * Ajax function to join an group invitation
	 *
	 **/
	public function ajaxGroupJoinInvitation( $groupId )
	{
		$filter		= JFilterInput::getInstance();
		$groupId	= $filter->clean( $groupId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my		=	CFactory::getUser();

		// Get groups table
		$table		= JTable::getInstance( 'GroupInvite' , 'CTable' );
		$keys = array('groupid'=>$groupId, 'userid'=>$my->id);
		$table->load($keys);

		if( $table->isOwner() ){
			$group		= JTable::getInstance( 'Group' , 'CTable' );
			$member		= JTable::getInstance( 'GroupMembers' , 'CTable' );

			$group->load( $groupId );
			$params		= $group->getParams();

			// Set the properties for the members table
			$member->groupid	= $group->id;
			$member->memberid	= $my->id;

			// @rule: If approvals is required, set the approved status accordingly.
			$member->approved	= ( $group->approvals == COMMUNITY_PRIVATE_GROUP ) ? '0' : 1;

			// @rule: Special users should be able to join the group regardless if it requires approval or not
			$member->approved	= COwnerHelper::isCommunityAdmin() ? 1 : $member->approved;

			$groupModel		= CFactory::getModel( 'groups' );

			// @rule: If the Invitation is sent by group admin, do not need futher approval
			if($groupModel->isAdmin($table->creator,$groupId)){
				$member->approved = 1;
			}

			//@todo: need to set the privileges
			$member->permissions	= '0';

			$member->store();

			//trigger for onGroupJoin
			$this->triggerEvents('onGroupJoin' , $group , $my->id);

                        // Update user group list
                        $my->updateGroupList();

			// Test if member is approved, then we add logging to the activities.
			if( $member->approved )
			{
				// remove the notication count
				$table	= JTable::getInstance( 'GroupInvite' , 'CTable' );
				$keys = array('groupid'=>$groupId, 'userid'=>$my->id);
				$table->load( $keys);
				$table->delete();

				CGroups::joinApproved($groupId, $my->id);

				$url	=	CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

				$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
				$objResponse->addScriptCall( 'aspan = joms.jQuery(".cMenu-Icon b"); aspan.html(parseInt(aspan.html())-1);');
				$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $group->id  . '").html("'.(JText::sprintf('COM_COMMUNITY_GROUPS_ACCEPTED_INVIT', $group->name , htmlentities($url) )).'");');
				$objResponse->addScriptCall( 'joms.jQuery("#noti-pending-group-' . $group->id  . '").fadeOut(2000, function() { joms.jQuery("#noti-pending-group' . $group->id . '").remove();} );');
			}

		} else
		{
			$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
			$objResponse->addScriptCall( 'aspan = joms.jQuery(".cMenu-Icon b"); aspan.html(parseInt(aspan.html())-1);');
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $groupId  . '").html("' . JText::_('COM_COMMUNITY_GROUPS_NOT_INVITED_NOTIFICATION') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $groupId  . '").attr("class", "error");');
		}
		return $objResponse->sendResponse();
	}

		/**
	 * Ajax function to reject an event invitation
	 *
	 **/
	public function ajaxGroupRejectInvitation( $groupId ){
		$filter = JFilterInput::getInstance();
		$groupId = $filter->clean($groupId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my		=	CFactory::getUser();
		$table	= JTable::getInstance( 'GroupInvite' , 'CTable' );
		$keys = array('groupid'=>$groupId, 'userid'=>$my->id);
		$table->load( $keys);

		if( $table->isOwner() ){
			if( $table->delete() )
			{
				$group	= JTable::getInstance( 'Group' , 'CTable' );
				$group->load( $table->groupid );
				$url	= CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );

					   $objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $group->id  . '").html("'.addslashes(JText::sprintf('COM_COMMUNITY_GROUPS_REJECTED_INVIT', $group->name , $url )).'");');
				$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
			$objResponse->addScriptCall( 'joms.jQuery("#noti-pending-group-' . $group->id  . '").fadeOut(2000, function() { joms.jQuery("#noti-pending-group' . $group->id . '").remove();} );');
				$objResponse->addScriptCall( 'aspan = joms.jQuery(".cMenu-Icon b"); aspan.html(parseInt(aspan.html())-1);');
			}

		}else{
			// when the user is the owner group we need avoid the invitation
			$table->delete();

			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $groupId  . '").html("' . JText::_('COM_COMMUNITY_EVENTS_NOT_INVITED_NOTIFICATION') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-pending-' . $groupId  . '").attr("class", "error");');
		}
		return $objResponse->sendResponse();
	}

	public function triggerEvents( $eventName, &$args, $target = null)
	{
		CError::assert( $args , 'object', 'istype', __FILE__ , __LINE__ );

		require_once( COMMUNITY_COM_PATH.'/libraries/apps.php' );
		$appsLib	= CAppPlugins::getInstance();
		$appsLib->loadApplications();

		$params		= array();
		$params[]	= $args;

		if(!is_null($target))
			$params[]	= $target;

		$appsLib->triggerEvent( $eventName , $params);
		return true;
	}
		/**
	 * Ajax function to accept Private Group Request
	 *
	 **/
	public function ajaxGroupJoinRequest( $memberId , $groupId )
		{

				$filter = JFilterInput::getInstance();
				$groupId = $filter->clean($groupId, 'int');
				$memberId = $filter->clean($memberId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my		=	CFactory::getUser();
				$model		= $this->getModel( 'groups' );

				//CFactory::load( 'helpers' , 'owner' );

				if( !$model->isAdmin( $my->id , $groupId ) && !COwnerHelper::isCommunityAdmin() )
		{
			$objResponse->addScriptCall( JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION') );
		}
				else
				{
					//Load Necessary Table
					$member		= JTable::getInstance( 'GroupMembers' , 'CTable' );
					$group		= JTable::getInstance( 'Group' , 'CTable' );

					// Load the group and the members table
					$group->load( $groupId );
					$keys = array('groupId'=>$groupId, 'memberId'=>$memberId);
					$member->load( $keys );

					// Only approve members that is really not approved yet.
					if( $member->approved )
					{
			$objResponse->addScriptCall( 'joms.jQuery("#error-request-' . $group->id  . '").html("' . JText::_('COM_COMMUNITY_EVENTS_NOT_INVITED_NOTIFICATION') . '");' );
			$objResponse->addScriptCall( 'joms.jQuery("#error-request-' . $group->id  . '").attr("class", "error");');
					}
					else
					{
						$member->approve();

						$user	= CFactory::getUser( $memberId );
						$user->updateGroupList(true);



			// Add notification
			//CFactory::load( 'libraries' , 'notification' );

			$params			= new CParameter( '' );
			$params->set('url' , CRoute::getExternalURL( 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id ) );
			$params->set('group' , $group->name );
			$params->set('group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id  );

			CNotificationLibrary::add( 'groups_member_approved' , $group->ownerid , $user->id , JText::sprintf( 'COM_COMMUNITY_GROUP_MEMBER_APPROVED_EMAIL_SUBJECT' ) , '' , 'groups.memberapproved' , $params );

			$act = new stdClass();
			$act->cmd 		= 'group.join';
			$act->actor   	= $memberId;
			$act->target  	= 0;
			$act->title	  	= '';//JText::sprintf('COM_COMMUNITY_GROUPS_ACTIVITIES_MEMBER_JOIN_GROUP' , '{group_url}' , $group->name );
			$act->content	= '';
			$act->app		= 'groups.join';
			$act->cid		= $group->id;

			$params = new CParameter('');
			$params->set( 'action' , 'group.join');
			$params->set( 'group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );

			// Add activity logging

			CActivityStream::addActor( $act, $params->toString() );

			//add user points
			//CFactory::load( 'libraries' , 'userpoints' );
			CUserPoints::assignPoint('group.join', $memberId);

			//trigger for onGroupJoinApproved
			$this->triggerEvents( 'onGroupJoinApproved' , $group , $memberId);

			// UPdate group stats();
			$group->updateStats();
			$group->store();

			$url	=	CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

			$objResponse->addScriptCall( 'joms.jQuery("#msg-request-' . $memberId  . '").html("'.addslashes(JText::sprintf('COM_COMMUNITY_EVENTS_ACCEPTED', $group->name , $url )).'");');
			$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
			$objResponse->addScriptCall( 'joms.jQuery("#noti-request-group-' . $memberId  . '").fadeOut(1000, function() { joms.jQuery("#noti-request-group-' . $memberId . '").remove();} );');
			$objResponse->addScriptCall( 'aspan = joms.jQuery(".cMenu-Icon b"); aspan.html(parseInt(aspan.html())-1);');
					}

				}
			   return $objResponse->sendResponse();
	}
		/**
	 * Ajax function to decline Private Group Request
	 *
	 **/
	public function ajaxGroupRejectRequest( $memberId , $groupId )
		{

				$filter = JFilterInput::getInstance();
				$groupId = $filter->clean($groupId, 'int');
				$memberId = $filter->clean($memberId, 'int');

		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my		=	CFactory::getUser();
				$model		=       $this->getModel( 'groups' );
				$group		= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );
				//CFactory::load( 'helpers' , 'owner' );

				if( !$group->isAdmin( $my->id , $groupId ) && !COwnerHelper::isCommunityAdmin() )
		{
			$objResponse->addScriptCall( JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION') );
		}
				else
				{
					//Load Necessary Table
					$groupMember	= JTable::getInstance( 'GroupMembers' , 'CTable' );

					$data		= new stdClass();

					$data->groupid	= $groupId;
					$data->memberid	= $memberId;

					$model->removeMember($data);

					//add user points
					//CFactory::load( 'libraries' , 'userpoints' );
					CUserPoints::assignPoint('group.member.remove', $memberId);

					//trigger for onGroupLeave
					$this->triggerEvents( 'onGroupLeave' , $group , $memberId);

					$group->updateStats();
					$group->store();

					$url	=	CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);

					$objResponse->addScriptCall( 'joms.jQuery("#msg-request-' . $memberId  . '").html("'.addslashes(JText::sprintf('COM_COMMUNITY_EVENTS_REJECTED', $group->name , $url )).'");');
					$objResponse->addScriptCall( 'joms.notifications.updateNotifyCount();');
					$objResponse->addScriptCall( 'joms.jQuery("#noti-request-group-' . $memberId  . '").fadeOut(1000, function() { joms.jQuery("#noti-request-group-' . $memberId . '").remove();} );');
					$objResponse->addScriptCall( 'aspan = joms.jQuery("#jsMenu .jsMenuIcon span"); aspan.html(parseInt(aspan.html())-1);');
				}
			   return $objResponse->sendResponse();
	}
}
