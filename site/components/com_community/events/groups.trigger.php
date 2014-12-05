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

class CGroupsTrigger
{
	public function onGroupCreate( $group )
	{
		$config		= CFactory::getConfig();

		// Send an email notification to the site admin's when there is a new group created
		if( $config->get( 'moderategroupcreation' ) )
		{
			$userModel	= CFactory::getModel( 'User' );
			$my			= CFactory::getUser();
			$admins		= $userModel->getSuperAdmins();

			//Send notification email to administrators
			foreach( $admins as $row )
			{
				if( $row->sendEmail )
				{
					$params	= new CParameter( '' );
					$params->set('url' , JURI::root() . 'administrator/index.php?option=com_community&view=groups' );
					$params->set('groupName' , $group->name );
					$params->set('group' , $group->name );
					$params->set('group_url' , 'administrator/index.php?option=com_community&view=groups' );

					CNotificationLibrary::add( 'groups_notify_admin' , $my->id , $row->id , JText::sprintf( 'COM_COMMUNITY_GROUP_CREATED_MAIL_SUBJECT') , '' , 'groups.notifyadmin' , $params );
				}
			}
		}
	}

	public function onGroupJoin( $group , $userId )
	{
        //@rule: Clear existing invites fromt he invitation table once the user joined the group
		$groupInvite		= JTable::getInstance( 'GroupInvite' , 'CTable' );
		$keys = array('groupid' => $group->id , 'userid'=>$userId);

		if( $groupInvite->load($keys) )
		{
			$groupInvite->delete();
		}

		$member		= JTable::getInstance( 'GroupMembers' , 'CTable' );
		$keys = array('memberId' =>$userId , 'groupId' => $group->id);
		$member->load( $keys );

        $groupModel	= CFactory::getModel('groups');
        $admins = $groupModel->getAdmins($group->id, null);
		$params		= $group->getParams();

		//@rule: Send notification when necessary
		if($params->get('joinrequestnotification') || $params->get('newmembernotification') )
		{
			$user		= CFactory::getUser( $userId );
			$subject	=  JText::sprintf( 'COM_COMMUNITY_GROUPS_EMAIL_NEW_MEMBER_JOINED_SUBJECT' , '{user}' , '{group}' );

			if( !$member->approved )
			{
				$subject	= JText::sprintf( 'COM_COMMUNITY_NEW_MEMBER_REQUESTED_TO_JOIN_GROUP_EMAIL_SUBJECT' , '{user}' , '{group}' );
			}

			$params			= new CParameter( '' );
			$params->set('url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id );
			$params->set('group' , $group->name );
			$params->set('group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id );
			$params->set('user' , $user->getDisplayName() );
			$params->set('user_url' , 'index.php?option=com_community&view=profile&userid='.$user->id );
			$params->set('approved' , $member->approved );

            foreach($admins as $admin)
            {
                CNotificationLibrary::add( 'groups_member_join' , $user->id , $admin->id , $subject , '' , 'groups.memberjoin' , $params );
            }
		}
	}

	public function onBulletinDisplay( $row )
	{
		CError::assert( $row->message, '', '!empty', __FILE__ , __LINE__ );

		// @rule: Only nl2br text that doesn't contain html tags
		if( !CStringHelper::isHTML( $row->message ) )
		{
			$row->message	= CStringHelper::nl2br( $row->message );
		}
	}

	public function onDiscussionDisplay( $row )
	{
		CError::assert( $row->message, '', '!empty', __FILE__ , __LINE__ );

		// @rule: Only nl2br text that doesn't contain html tags
		if( !CStringHelper::isHTML( $row->message ) )
		{
			$row->message	= CStringHelper::nl2br( $row->message );
		}
	}
}