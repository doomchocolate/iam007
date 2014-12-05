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
//CFactory::load( 'libraries' , 'comment' );

class CGroups implements
	CCommentInterface, CStreamable
{
	static public function getActivityTitleHTML($act)
	{
		return "GROUP";
	}

	static public function getActivityContentHTML($act)
	{
		// Ok, the activity could be an upload OR a wall comment. In the future, the content should
		// indicate which is which
		$html = '';
		$param = new CParameter( $act->params );
		$action = $param->get('action' , false);

		$config = CFactory::getConfig();

		$groupModel		= CFactory::getModel( 'groups' );

		if( $action == CGroupsAction::DISCUSSION_CREATE )
		{
			// Old discussion might not have 'action', and we can't display their
			// discussion summary
			$topicId = $param->get('topic_id', false);
			if( $topicId ){

				$group			= JTable::getInstance( 'Group' , 'CTable' );
				$discussion		= JTable::getInstance( 'Discussion' , 'CTable' );

				$group->load( $act->cid );
				$discussion->load( $topicId );

				$discussion->message = strip_tags($discussion->message);
				$topic = CStringHelper::escape($discussion->message);
				$tmpl	= new CTemplate();
				$tmpl->set( 'comment' , JString::substr($topic, 0, $config->getInt('streamcontentlength')) );
				$html	= $tmpl->fetch( 'activity.groups.discussion.create' );
			}
			return $html;
		}
		else if ($action == CGroupsAction::WALLPOST_CREATE )
		{
			// a new wall post for group
			// @since 1.8
			$group	= JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $act->cid );

			$wallModel	= CFactory::getModel( 'Wall' );
			$wall		= JTable::getInstance( 'Wall' , 'CTable' );
			$my			= CFactory::getUser();

			// make sure the group is a public group or current use is
			// a member of the group
			if( ($group->approvals == 0) || $group->isMember($my->id))
			{
				//CFactory::load( 'libraries' , 'comment' );
				$wall->load( $param->get('wallid' ));
				$comment	= strip_tags( $wall->comment , '<comment>');
				$comment	= CComment::stripCommentData( $comment );
				$tmpl	= new CTemplate();
				$tmpl->set( 'comment' , JString::substr($comment, 0, $config->getInt('streamcontentlength')) );
				$html	= $tmpl->fetch( 'activity.groups.wall.create' );
			}
			return $html;
		}
		else if($action == CGroupsAction::DISCUSSION_REPLY)
		{
			// @since 1.8
			$group	= JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $act->cid );

			$wallModel	= CFactory::getModel( 'Wall' );
			$wall		= JTable::getInstance( 'Wall' , 'CTable' );
			$my			= CFactory::getUser();

			// make sure the group is a public group or current use is
			// a member of the group
			if( ($group->approvals == 0) || $group->isMember($my->id))
			{
				$wallid = $param->get('wallid' );
				//CFactory::load( 'libraries' , 'wall' );
				$html = CWallLibrary::getWallContentSummary($wallid);
			}
			return $html;
		}
		else if ($action == CGroupsAction::CREATE)
		{
			$group	= JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $act->cid );

			$tmpl	= new CTemplate();
			$tmpl->set( 'group' , $group );
			$html	= $tmpl->fetch( 'activity.groups.create' );
		}


		return $html;
	}

	/**
	 * Return an array of valid 'app' code to fetch from the stream
	 * @return array
	 */
	static public function getStreamAppCode(){
		return array('groups.wall', 'groups.attend', 'events.wall', 'videos',
			'groups.discussion', 'groups.discussion.reply', 'groups.bulletin',
				'photos', 'events');
	}


	static public function sendCommentNotification( CTableWall $wall , $message )
	{
		//CFactory::load( 'libraries' , 'notification' );

		$my			= CFactory::getUser();
		$targetUser	= CFactory::getUser( $wall->post_by );
		$url		= 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $wall->contentid;
		$params 	= $targetUser->getParams();

		$params		= new CParameter( '' );
		$params->set( 'url' , $url );
		$params->set( 'message' , $message );

		CNotificationLibrary::add( 'groups_submit_wall_comment' , $my->id , $targetUser->id , JText::sprintf('PLG_WALLS_WALL_COMMENT_EMAIL_SUBJECT' , $my->getDisplayName() ) , '' , 'groups.wallcomment' , $params );

		return true;
	}

	/**
	 *
	 */
	static public function joinApproved($groupId, $userid)
	{
		$group		= JTable::getInstance( 'Group' , 'CTable' );
		$member		= JTable::getInstance( 'GroupMembers' , 'CTable' );

		$group->load( $groupId );

		$act = new stdClass();
		$act->cmd 		= 'group.join';
		$act->actor   	= $userid;
		$act->target  	= 0;
		$act->title	  	= '';//JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_JOIN' , '{group_url}' , $group->name );
		$act->content	= '';
		$act->app		= 'groups.join';
		$act->cid		= $group->id;
		$act->groupid	= $group->id;

		$params = new CParameter('');
		$params->set( 'group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );
		$params->set( 'action', 'group.join');

		// Add logging

		CActivityStream::addActor($act, $params->toString() );

		//add user points
		//CFactory::load( 'libraries' , 'userpoints' );
		CUserPoints::assignPoint('group.join');

		// Store the group and update stats
		$group->updateStats();
		$group->store();
	}


	/**
	 * Return HTML formatted stream for groups
	 * @param object $group
	 * @deprecated use activities library instead
	 */
	public function getStreamHTML( $group)
	{

		$activities = new CActivities();
		$streamHTML = $activities->getOlderStream(1000000000, 'active-group', $group->id);

		// $streamHTML = $activities->getAppHTML(
		// 			array(
		// 				'app' => CActivities::APP_GROUPS,
		// 				'groupid' => $group->id,
		// 				'apptype' => 'group'
		// 			)
		// 		);

		return $streamHTML;
	}

	/**
	 * Return true is the user can post to the stream
	 **/
	public function isAllowStreamPost( $userid, $option )
	{
		// Guest cannot post.
		if( $userid == 0){
			return false;
		}

		// Admin can comment on any post
		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}

		// if the groupid not specified, obviously stream comment is not allowed
		if(empty($option['groupid'])){
			return false;
		}

		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $option['groupid'] );
		return $group->isMember($userid);
	}
	/**
	 * Return true is the user is a group admin
	 **/
	public function isAdmin($userid,$groupid)
	{
		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupid );
		return $group->isAdmin($userid);
	}
}

class CGroupsAction
{
	const DISCUSSION_CREATE	= 'group.discussion.create';
	const DISCUSSION_REPLY	= 'group.discussion.reply';
	const WALLPOST_CREATE		= 'group.wall.create';
	const CREATE						= 'group.create';
}