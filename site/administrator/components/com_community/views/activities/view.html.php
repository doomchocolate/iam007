<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewActivities extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$model				= $this->getModel( 'Activities' );
		$activities			= $model->getActivities();
		foreach($activities as $key=>$activity)
		{
			$param = new CParameter($activity->params);

			switch ($activity->app) {
				case 'users.featured':
					$user = CFactory::getUser($param->get('userid'));
					$activities[$key]->title =  JText::sprintf('COM_COMMUNITY_MEMBER_IS_FEATURED','<a href="'.CRoute::_(JUri::root().$param->get('owner_url')).'" class="cStream-Author">'.$user->getDisplayName().'</a>');
					break;
				case 'events.wall':
				case 'groups.wall':
				case 'profile':
						$user = CFactory::getUser($activity->actor);
						$html = '<a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> ';
						if($activity->eventid)
						{
							$event = JTable::getInstance('Event','cTable');
							$event->load($activity->eventid);

							$html .=  '➜ <a class="cStream-Reference" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id.'">'. $event->title .'</a>';
						}
						elseif($activity->groupid)
						{

							$group = JTable::getInstance('Group','cTable');
							$group->load($activity->groupid);

							$html .= '➜ <a class="cStream-Reference" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id.'">'.$group->name.'</a>';
						}
						elseif( !empty($activity->target) && $activity->target != $activity->actor)
						{
							$target = CFactory::getUser($activity->target);
							$html .= '➜ <a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$target->id.'">'.$target->getDisplayName().'</a> ';
							$html .= CActivities::format($activity->title);
						}
						else
						{
							$html .= ' posted: "'.CActivities::format($activity->title).'"';
						}
						$activities[$key]->title = $html;
					break;
				case 'profile.avatar.upload':
						$user = CFactory::getUser($activity->actor);
						$html = '<a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> ';
						$html .=	JText::_('COM_COMMUNITY_ACTIVITIES_NEW_AVATAR');
						$activities[$key]->title = $html;
					break;
				case 'albums.comment':
				case 'albums':
						$album	= JTable::getInstance( 'Album' , 'CTable' );
						$album->load( $activity->cid );

						$user = CFactory::getUser($activity->actor);
						$html = '<a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> ';
						$html .= JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_ALBUM', CRoute::_($album->getURI()), $this->escape($album->name) );
						$activities[$key]->title = $html;
					break;
				case 'profile.like':
				case 'groups.like':
				case 'events.like':
				case 'photo.like':
				case 'videos.like':
				case 'album.like':
						$actors = $param->get('actors');
						$user 		= CFactory::getUser($activity->actor);
						$users 		= explode(',', $actors);
						$userCount 	= count($users);
						switch($activity->app){
							case 'profile.like':
								$cid 		= CFactory::getUser($activity->cid);
								$urlLink 	= JUri::root().'index.php?option=com_community&view=profile&userid='.$cid->id;
								$name 		= $cid->getDisplayName();
								$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PROFILE';
							break;
							case 'groups.like':
								$cid = JTable::getInstance('Group', 'CTable');
								$cid->load($activity->groupid);
								$urlLink 	= JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$cid->id;
								$name 		= $cid->name;
								$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_GROUP';
							break;
							case 'events.like':
								$cid = JTable::getInstance('Event','CTable');
								$cid->load($activity->eventid);
								$urlLink 	= JUri::root().'index.php?option=com_community&view=events&task=viewevent&eventid='.$cid->id;
								$name 		= $cid->title;
								$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_EVENT';
							break;
							case 'photo.like':
								$cid = JTable::getInstance('Photo','CTable');
								$cid->load($activity->cid);

								$urlLink 	= JUri::root().'index.php?option=com_community&view=photos&task=photo&albumid='.$cid->albumid.'&userid='.$cid->creator.'&photoid='.$cid->id;
								$name 		= $cid->caption;
								$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PHOTO';
							break;
							case 'videos.like':
								$cid = JTable::getInstance('Video','CTable');
								$cid->load($activity->cid);

								$urlLink 	= JURI::root().'index.php?option=com_community&view=videos&task=video&userid='.$cid->creator.'&videoid='.$cid->id;
								$name 		= $cid->getTitle();
								$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_VIDEO';
							break;
							case 'album.like':
								$cid = JTable::getInstance('Album','CTable');
								$cid->load($activity->cid);

								$urlLink 	= Juri::root().'index.php?option=com_community&view=photos&task=album&albumid='.$cid->id.'&userid='.$cid->creator;
								$name 		= $cid->name;
								$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_ALBUM';
							break;
						}

						$slice 		= 2;
						if($userCount > 2)
						{
							$slice = 1;
						}

						$users = array_slice($users,0,$slice);

						$actorsHTML = array();
						foreach($users as $actor)
						{
							$user = CFactory::getUser($actor);
							$actorsHTML[] = '<a class="cStream-Author" target="_blank" href="'. JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'. $user->getDisplayName().'</a>';
						}

						$others = '';

						if($userCount > 2)
						{
							$others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_GROUP' , $userCount-1);
						}

						$jtext =($userCount>1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR';

						$activities[$key]->title = implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others.JText::sprintf($jtext,$urlLink,$name,JText::_($element)) ;
					break;
				case 'cover.upload':
						$user 	= CFactory::getUser($activity->actor);
						$type 	= $param->get('type');
						$extraMessage = '';
						if(strtolower($type) !=='profile')
						{
							$id = strtolower($type.'id');

							$cTable = JTable::getInstance(ucfirst($type),'CTable');
							$cTable->load($activity->$id);

							if($type == 'group')
							{
								$extraMessage = ', <a target="_blank" href="'.JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$cTable->id.'">'.$cTable->name.'</a>';
							}
							if($type == 'event')
							{
								$extraMessage = ', <a target="_blank" href="'.JUri::root().'index.php?option=com_community&view=events&task=viewevent&eventid='.$cTable->id.'">'.$cTable->title.'</a>';
							}

						}
						$html = '<a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> ';
						$html .= JText::sprintf('COM_COMMUNITY_PHOTOS_COVER_UPLOAD',Jtext::_('COM_COMMUNITY_COVER_'.strtoupper($type))).$extraMessage;
						$activities[$key]->title = $html;
					break;
				case 'events.attend':
				case 'events':
						$user = CFactory::getUser($activity->actor);
						$action = $param->get('action');
						$event = JTable::getInstance('Event', 'CTable');
						$event->load($activity->eventid);
						switch ($action) {
							case 'events.create':
								 $html = '<a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> -';
								 $html .= JText::sprintf('COM_COMMUNITY_EVENTS_ACTIVITIES_NEW_EVENT' , JUri::root().'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, $event->title);
								 $activities[$key]->title = $html;
								break;
							case 'events.attendence.attend':
								$users = explode(',',$param->get('actors'));
								$actorsHTML = array();
								foreach($users as $actor)
								{
									if (!$actor)
									{
											$actor = $activity->actor;
									}
									$user = CFactory::getUser($actor);
									$actorsHTML[] = '<a class="cStream-Author" target="_blank" href="'. JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'. $user->getDisplayName().'</a>';
								}

								$activities[$key]->title =  implode(', ', $actorsHTML) .' - '. JText::sprintf('COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND' , JUri::root().'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, $event->title);
								break;

						}
					break;
				case 'friends.connect':
						$user1 = CFactory::getUser($activity->actor);
						$user2 = CFactory::getUser($activity->target);

						$html =JText::sprintf('COM_COMMUNITY_STREAM_OTHER_FRIENDS', $user1->getDisplayName(),$user2->getDisplayName(), JUri::root().'index.php?option=com_community&view=profile&userid='.$user1->id, JUri::root().'index.php?option=com_community&view=profile&userid='.$user2->id);
						$activities[$key]->title =$html;
					break;
				case 'groups.bulletin':
						$user = CFactory::getUser($activity->actor);

						$bulletin = JTable::getInstance('Bulletin', 'CTable');
						$bulletin->load($activity->cid);

						$group = JTable::getInstance('Group','CTable');
						$group->load($bulletin->groupid);


						$html = '<a class="cStream-Author" target="_blank" href="' .JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a>'
								. JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_NEWS' , CRoute::_(JUri::root().'index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id ), $bulletin->title );
						$activities[$key]->title =$html;
					break;
				case 'groups.discussion':
						$user = CFactory::getUser($activity->actor);
						$discussion = JTable::getInstance('Discussion' , 'CTable' );
						$discussion->load($activity->cid);

						$discussionLink = CRoute::_(JUri::root().'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $discussion->id );

						$html = '<a class="cStream-Author" target="_blank" href="' .JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a>'
								. JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION' , $discussionLink, $discussion->title ) ;
						$activities[$key]->title =$html;
					break;
				case 'groups.discussion.reply':
						$user = CFactory::getUser($activity->actor);

						$discussion = JTable::getInstance('Discussion' , 'CTable' );
						$discussion->load($activity->cid);

						$html = '<a class="cStream-Author" target="_blank" href="' .JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a>'
								. JText::sprintf('COM_COMMUNITY_GROUPS_REPLY_DISCUSSION' , CRoute::_(JUri::root().'index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$discussion->groupid.'&topicid='.$discussion->id), $discussion->title );
						$activities[$key]->title =$html;
					break;
				case 'groups.join':
						$user = CFactory::getUser($activity->actor);
						$users = explode(',', $param->get('actors'));
						$userCount = count($users);

						$group = JTable::getInstance('Group','CTable');
						$group->load($activity->cid);

						$slice = 2;

						if($userCount > 2)
						{
							$slice = 1;
						}

						$users = array_slice($users,0,$slice);
						$actorsHTML = array();
						foreach($users as $actor)
						{
							$user = CFactory::getUser($actor);
							$actorsHTML[] = '<a class="cStream-Author" target="_blank" href="'. JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'. $user->getDisplayName().'</a>';
						}

						$others = '';

						if($userCount > 2)
						{
							$others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_GROUP' , $userCount-1);
						}

						$html = implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML) . $others . JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_JOIN' , JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id, $group->name);
						$activities[$key]->title =$html;
					break;
				case 'groups.update':
						$user = CFactory::getUser($activity->actor);
						$group = JTable::getInstance('Group','CTable');
						$group->load($activity->cid);

						$html = '<a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> - '. JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_UPDATED' , JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id , $group->name );
						$activities[$key]->title =$html;
					break;
				case 'photos':
						$user = CFactory::getUser($activity->actor);
						$album	= JTable::getInstance( 'Album' , 'CTable' );
						$album->load( $activity->cid );
						$html = '';

						if($activity->groupid)
						{
							$group = JTable::getInstance( 'Group' , 'CTable' );
							$group->load($activity->groupid);

							$html .= '<a class="cStream-Reference" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id.'">'.$group->name .'</a> -';
						}

						$html .= ' <a class="cStream-Author" target="_blank" href="'.JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a>';

						$count = $param->get('count', 1);
						$url = Juri::root().'index.php?option=com_community&view=photos&task=album&albumid='.$album->id.'&userid='.$user->id;
						if(CStringHelper::isPlural($count))
						{
							$html .= JText::sprintf( 'COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE_MANY' , $count, $url, CStringHelper::escape($album->name) );
						}else
						{
							$html .= JText::sprintf( 'COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE' , $url, CStringHelper::escape($album->name) );;
						}
						$activities[$key]->title =$html;
					break;
				case 'photos.comment':
						$user = CFactory::getUser($activity->actor);
						$wall = JTable::getInstance('Wall', 'CTable');
						$wall->load($param->get('wallid'));

						$photo = JTable::getInstance('Photo','CTable');
						$photo->load($activity->cid);

						$url =  JUri::root().'index.php?option=com_community&view=photos&task=photo&albumid='.$photo->albumid.'&userid='.$photo->creator.'&photoid='.$photo->id;

						$html = '<a class="cStream-Author" target="_blank" href="'. JUri::root().'index.php?option=com_community&view=profile&userid='.$user->id.'">'.$user->getDisplayName().'</a> ' . JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_PHOTO', $url, $this->escape($photo->caption)  );
						$activities[$key]->title =$html;
					break;
				case 'system.message':
				case 'system.videos.popular':
				case 'system.photos.popular':
				case 'system.members.popular':
				case 'system.photos.total':
				case 'system.groups.popular':
				case 'system.members.registered':
						$action = $param->get('action');
						switch ($action) {
							case 'registered_users':
									$usersModel			= CFactory::getModel( 'user' );
									$now				= new JDate();
									$date				= CTimeHelper::getDate();
									$users				= $usersModel->getLatestMember(10);
									$totalRegistered	= count($users);
									$title				= JText::sprintf('COM_COMMUNITY_TOTAL_USERS_REGISTERED_THIS_MONTH_ACTIVITY_TITLE', $totalRegistered , $date->monthToString($now->format('%m')));
									$activities[$key]->title = $title;
								break;
							case 'total_photos':
									$photosModel = CFactory::getModel( 'photos' );
									$total       = $photosModel->getTotalSitePhotos();
									$activities[$key]->title = JText::sprintf('COM_COMMUNITY_TOTAL_PHOTOS_ACTIVITY_TITLE', CRoute::_(JURI::root().'index.php?option=com_community&view=photos') ,$total);
								break;
							case 'top_videos':
									$activities[$key]->title = JText::_('COM_COMMUNITY_ACTIVITIES_TOP_VIDEOS');
								break;
							case 'top_photos':
									$activities[$key]->title = JText::_('COM_COMMUNITY_ACTIVITIES_TOP_PHOTOS');
								break;
							case 'top_users':
									$activities[$key]->title = JText::_('COM_COMMUNITY_ACTIVITIES_TOP_MEMBERS');
								break;
							case 'top_groups':
									$groupsModel = $this->getModel('groups');
									$activeGroup = $groupsModel->getMostActiveGroup();

									if( is_null($activeGroup)) {
										$title = JText::_('COM_COMMUNITY_GROUPS_NONE_CREATED');
									} else {
										$title = JText::sprintf('COM_COMMUNITY_MOST_POPULAR_GROUP_ACTIVITY_TITLE', CRoute::_(JUri::root().'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$activeGroup->id), $activeGroup->name);

									}
									$activities[$key]->title = $title;
								break;
							case 'message':
								break;
						}
					break;
				case 'videos':
					$video = JTable::getInstance('Video','CTable');
					$video->load($activity->cid);

					$actor = CFactory::getUser($activity->actor);
					$html = $this->_getUserLink($activity->actor);
					$html .= JText::sprintf('COM_COMMUNITY_ACTIVITY_VIDEO_SHARE_TITLE',JUri::root().$video->getViewURI(false),$this->escape($video->title));

					$activities[$key]->title = $html;
					break;
				case 'videos.featured':
						$video = JTable::getInstance('Video','CTable');
						$video->load($activity->cid);
						$activities[$key]->title =JText::sprintf('COM_COMMUNITY_VIDEOS_IS_FEATURED','<a href="'.CRoute::_(JUri::root().'index.php?option=com_community&view=videos&task=video&userid='.$video->creator.'&videoid='.$video->id).'" class="cStream-Title">'.$this->escape($video->title).'</a>');
					break;
				case 'albums.featured':
						$album = JTable::getInstance('Album','CTable');
						$album->load($activity->cid);
						$activities[$key]->title = JText::sprintf('COM_COMMUNITY_ALBUM_IS_FEATURED','<a href="'.CRoute::_(JUri::root().'index.php?option=com_community&view=photos&task=album&albumid='.$album->id.'&userid='.$album->creator).'" class="cStream-Title">'.$this->escape($album->name).'</a>');
					break;
			}
		}
		//exit;
		$userModel			= $this->getModel( 'Users' , false );
		$currentUser		= $jinput->request->get('actor' , JText::_('COM_COMMUNITY_ACTIVITIES_ENTER_NAME_VALUE'), 'NONE');
		$currentArchived	= $jinput->request->get('archived' , 0, 'NONE');
		$currentApp			= $jinput->request->get('app' , 'none', 'NONE');
		$filterApps			= $model->getFilterApps();

		$this->assignRef( 'filterApps'		, $filterApps );
		$this->assignRef( 'currentApp'		, $currentApp );
		$this->assignRef( 'currentUser'		, $currentUser );
		$this->assignRef( 'currentArchive'	, $currentArchived );
		$this->assignRef( 'pagination'		, $model->getPagination() );
		$this->assignRef( 'activities' 		, $activities );
		parent::display( $tpl );
	}

	public function _getUserLink( $id )
	{
		$user	= CFactory::getUser( $id );

		return '<a href="' . JURI::root() . 'index.php?option=com_community&view=profile&userid=' . $user->id . '" target="_blank">' . $user->getDisplayName() . '</a>';
	}

	/**
	 * Private method to set the toolbar for this view
	 *
	 * @access private
	 *
	 * @return null
	 **/
	public function setToolBar()
	{
		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_ACTIVITIES'), 'activities' );
 		JToolBarHelper::divider();
 		JToolBarHelper::trash('delete', JText::_('COM_COMMUNITY_DELETE'));
 		JToolBarHelper::trash('purge', JText::_('COM_COMMUNITY_ACTIVITIES_PURGE_ALL') , false );
 		JToolBarHelper::archiveList('archiveSelected',JText::_('COM_COMMUNITY_ARCHIVE_SELECTED'));
 		JToolBarHelper::custom('archiveall','','',JText::_('COM_COMMUNITY_ARCHIVE_ALL'),false);
	}
}