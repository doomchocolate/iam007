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

class CommunityActivitiesController extends CommunityBaseController {

    /**
     * Return all newer activities from the given streamid,
     * @param  int $streamid most recent stream id
     * @param  string $filter   public, mine, friends, groups, events
     * @return [type]           [description]
     */
    public function ajaxGetRecentActivities($streamid, $filter = null, $filterId = null, $filterValue = null) {

        $response = new JAXResponse();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $html = '';

        $activitiesLib = new CActivities();
        $html = $activitiesLib->getLatestStream($streamid, $filter, $filterId, $filterValue);

        $response->addScriptCall('joms.activities.appendNewStream', $html, $config->get('stream_refresh_interval'));

        return $response->sendResponse();
    }

    /**
     * Return the number of recent activities since the given id
     * @param  [type] $streamid [description]
     * @param  [type] $filter   [description]
     * @return [type]           [description]
     */
    public function ajaxGetRecentActivitiesCount($streamid, $filter = null, $filterId = null, $filterValue = null) {
        $response = new JAXResponse();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $html = '';

        $activitiesLib = new CActivities();
        $html = $activitiesLib->getLatestStreamCount($streamid, $filter, $filterId, $filterValue);

        $nextActivitiesCheck = $config->get('stream_refresh_interval');

        // if stream only for guest/disable dont load the auto refresh
        if ($my->id == 0 && ($this->get('showactivitystream') === 2 || $this->get('showactivitystream') === 0)) {
            return false;
        }

        // Only reload the next
        if (!$config->get('enable_refresh') || $config->get('showactivitystream') == '0') {
            $nextActivitiesCheck = 0;
        }

        $newMessage = $html == 1 ? JText::sprintf('COM_COMMUNITY_NEW_MESSAGES', $html) : JText::sprintf('COM_COMMUNITY_NEW_MESSAGES_MANY', $html);
        $response->addScriptCall('joms.activities.announceNewStream', $html, $nextActivitiesCheck, $newMessage);

        return $response->sendResponse();
    }

    public function ajaxGetOlderActivities($streamid, $filter, $filterId, $filterValue = null) {
        $response = new JAXResponse();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $html = '';

        $activitiesLib = new CActivities();
        $html = $activitiesLib->getOlderStream($streamid, $filter, $filterId, $filterValue);

        $response->addScriptCall('joms.jQuery("#activity-more .loading").remove');
        $response->addScriptCall('joms.activities.prependOldStream', $html);

        return $response->sendResponse();
    }

    /**
     * Method to retrieve activities via AJAX
     * */
    public function ajaxGetActivities($exclusions, $type, $userId, $latestId = 0, $isProfile = 'false', $filter = '', $app = '', $appId = '') {
        $response = new JAXResponse();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $filterInput = JFilterInput::getInstance();

        $exclusions = $filterInput->clean($exclusions, 'string');
        $type = $filterInput->clean($type, 'string');
        $userId = $filterInput->clean($userId, 'int');
        $latestId = $filterInput->clean($latestId, 'int');
        $isProfile = $filterInput->clean($isProfile, 'string');
        $app = $filterInput->clean($app, 'string');
        $appId = $filterInput->clean($appId, 'int');



        $act = new CActivityStream();

        if (($app == 'group' || $app) == 'event' && $appId > 0) {
            // for application stream
            $option = array(
                'app' => $app . 's',
                'apptype' => $app,
                'exclusions' => $exclusions,
            );

            $option[$app . 'id'] = $appId; //application id for the right application
            $option['latestId'] = ($latestId > 0) ? $latestId : 0;
            $html = $act->getAppHTML($option);
        } elseif (in_array($type, array('active-profile', 'me-and-friends', 'friends', 'self', 'active-profile-and-friends'))) {
            // For main and profile stream


            $friendsModel = CFactory::getModel('Friends');

            if ($isProfile != 'false') {
                //requested from profile
                $target = array($userId); //by default, target is self

                if ($filter == 'friends') {
                    $target = $friendsModel->getFriendIds($userId);
                }

                $html = $act->getHTML($userId, $target, null, $config->get('maxactivities'), 'profile', '', true, COMMUNITY_SHOW_ACTIVITY_MORE, $exclusions, COMMUNITY_SHOW_ACTIVITY_ARCHIVED, 'all', $latestId);
            } else {
                $html = $act->getHTML($userId, $friendsModel->getFriendIds($userId), null, $config->get('maxactivities'), '', '', true, COMMUNITY_SHOW_ACTIVITY_MORE, $exclusions, COMMUNITY_SHOW_ACTIVITY_ARCHIVED, 'all', $latestId);
            }
        } else {
            $html = $act->getHTML('', '', null, $config->get('maxactivities'), '', '', true, COMMUNITY_SHOW_ACTIVITY_MORE, $exclusions, COMMUNITY_SHOW_ACTIVITY_ARCHIVED, 'all', $latestId);
        }

        $html = trim($html, " \n\t\r");
        $text = JText::_('COM_COMMUNITY_ACTIVITIES_NEW_UPDATES');

        if ($latestId == 0) {
            // Append new data at bottom.
            $response->addScriptCall('joms.activities.append', $html);
        } else {
            if ($html != '') {
                // $response->addScriptCall('joms.activities.appendLatest', $html, $config->get('stream_refresh_interval'), $text );
            } else {
                // $response->addScriptCall('joms.activities.nextActivitiesCheck' ,$config->get('stream_refresh_interval') );
            }
        }


        return $response->sendResponse();
    }

    public function ajaxRemoveUserTag( $id, $userstring, $type ){
        $response = new JAXResponse();

        //verify if the userstring belongs to current user
        $my = CFactory::getUser();
        $pattern	= '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
        preg_match_all( $pattern , $userstring , $matches );

        if( isset($matches[1][0]) && $matches[1][0] == $my->id){
            $updatedMessage = CApiActivities::removeUserTag($id, $userstring, $type, $matches[3][0]);
        }

        $origValue = $updatedMessage;
        $value = CStringHelper::autoLink($origValue);
        $value = nl2br($value);
        $value = CLinkGeneratorHelper::replaceAliasURL($value);

        if ( $type === 'post' ) {
            $response->addScriptCall('joms.jQuery("li[data-streamid=' . $id . '] p > span:first").html', $value);
            $response->addScriptCall('joms.jQuery("li[data-streamid=' . $id . '] p").show');
            $response->addScriptCall('joms.jQuery("li[data-streamid=' . $id . '] [data-type=stream-editor] textarea").val', $origValue);
            $response->addScriptCall('joms.jQuery("li[data-streamid=' . $id . '] [data-type=stream-editor] textarea").removeData', 'initialized');
            $response->addScriptCall('joms.jQuery("li[data-streamid=' . $id . '] .dropdown-menu [data-action=remove-tag]").closest("li").remove', "");
        } else {
            $response->addScriptCall("joms.jQuery('div.stream-comment[data-commentid=" . $id . "] .cStream-Content span.comment').html", $value);
            $response->addScriptCall('joms.jQuery("div.stream-comment[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").val', $origValue);
            $response->addScriptCall('joms.jQuery("div.stream-comment[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").removeData', 'initialized');
            $response->addScriptCall('joms.jQuery("div.stream-comment[data-commentid=' . $id . '] [data-action=remove-tag]").closest("span").remove', "");
        }

        return $response->sendResponse();
    }

    /**
     * Ajax update user post status
     * @param type $activityId
     * @param type $newPrivacy
     * @return type
     */
    public function ajaxUpdatePrivacyActivity($activityId, $newPrivacy) {
        $response = new JAXResponse();
        if (CApiActivities::updatePrivacy($activityId, $newPrivacy)) {

        }
        return $response->sendResponse();
    }

    /**
     * Get content for activity based on the activity id.
     *
     * 	@params	$activityId	Int	Activity id
     * */
    public function ajaxGetContent($activityId) {
        $my = CFactory::getUser();
        $showMore = true;
        $objResponse = new JAXResponse();
        $model = CFactory::getModel('Activities');

        $filter = JFilterInput::getInstance();
        $activityId = $filter->clean($activityId, 'int');




        // These core apps has default privacy issues with it
        $coreapps = array('photos', 'walls', 'videos', 'groups');

        // make sure current user has access to the content item
        // For known apps, we can filter this manually
        $activity = $model->getActivity($activityId);

        if (in_array($activity->app, $coreapps)) {
            switch ($activity->app) {
                case 'walls':
                    // make sure current user has permission to the profile
                    $showMore = CPrivacy::isAccessAllowed($my->id, $activity->target, 'user', 'privacyProfileView');
                    break;
                case 'videos':
                    // Each video has its own privacy setting within the video itself
                    $video = JTable::getInstance('Video', 'CTable');
                    $video->load($activity->cid);
                    $showMore = CPrivacy::isAccessAllowed($my->id, $activity->actor, 'custom', $video->permissions);
                    break;

                case 'photos':
                    // for photos, we uses the actor since the target is 0 and he
                    // is doing the action himself
                    $album = JTable::getInstance('Album', 'CTable');
                    $album->load($activity->cid);
                    $showMore = CPrivacy::isAccessAllowed($my->id, $activity->actor, 'custom', $album->permissions);
                    break;
                case 'groups':
            }
        } else {
            // if it is not one of the core apps, we should allow plugins to decide
            // if they want to block the 'more' view
        }

        if ($showMore) {
            $act = $model->getActivity($activityId);
            $content = CActivityStream::getActivityContent($act);

            $objResponse->addScriptCall('joms.activities.setContent', $activityId, $content);
        } else {
            $content = JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
            $content = nl2br($content);
            $content = CString::str_ireplace("\n", '', $content);
            $objResponse->addScriptCall('joms.activities.setContent', $activityId, $content);
        }

        $objResponse->addScriptCall('joms.tooltip.setup();');

        return $objResponse->sendResponse();
    }

    /**
     * Hide the activity from the profile
     * @todo: we should also hide all aggregated activities
     */
    public function ajaxHideActivity($userId, $activityId, $app = '') {
        $objResponse = new JAXResponse();
        $model = $this->getModel('activities');
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $userId = $filter->clean($userId, 'int');
        $activityId = $filter->clean($activityId, 'int');
        $app = $filter->clean($app, 'string');

        // Guests should not be able to hide anything.
        if ($my->id == 0) {
            return false;
        }


        $id = $my->id;

        // Administrators are allowed to hide others activity.
        if (COwnerHelper::isCommunityAdmin()) {
            $id = $userId;
        }

        // to do user premission checking
        $user = CFactory::getUser();

        //if activity is within app, the only option is to delete, not to hide
        switch ($app) {
            case 'groups.wall':
                $act = JTable::getInstance('Activity', 'CTable');
                $act->load($activityId);
                $group_id = $act->groupid;

                $group = JTable::getInstance('Group', 'CTable');
                $group->load($group_id);

                //superadmin, group creator can delete all the activity while normal user can delete thier own post only
                if ($user->authorise('community.delete', 'activities.' . $activityId, $group)) {
                    $model->deleteActivity($app, $activityId, $group);
                }
                break;
            case 'events.wall':
                //to retrieve the event id
                $act = JTable::getInstance('Activity', 'CTable');
                $act->load($activityId);
                $event_id = $act->eventid;

                $event = JTable::getInstance('Event', 'CTable');
                $event->load($event_id);

                if ($user->authorise('community.delete', 'activities.' . $activityId, $event)) {
                    $model->deleteActivity($app, $activityId, $event);

                    $wall = $this->getModel('wall');
                    $wall->deleteAllChildPosts($activityId, $app);
                }
                break;
            default:
                //delete if this activity belongs to the current user
                if ($user->authorise('community.delete', 'activities.' . $activityId)) {
                    $model->deleteActivity($app, $activityId);
                } else {
                    $model->hide($id, $activityId);
                }
        }


        $objResponse->addScriptCall('joms.jQuery("#profile-newsfeed-item' . $activityId . '").fadeOut("5400");');
        $objResponse->addScriptCall('joms.jQuery("#mod_profile-newsfeed-item' . $activityId . '").fadeOut("5400");');

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));
        return $objResponse->sendResponse();
    }

    public function ajaxConfirmDeleteActivity($app, $activityId) {
        $objResponse = new JAXResponse();

        $header = JText::_('COM_COMMUNITY_ACTVITIES_REMOVE');
        $message = JText::_('COM_COMMUNITY_ACTVITIES_REMOVE_MESSAGE');

        $actions = '<button class="btn" onclick="cWindowHide();">' . JText::_('COM_COMMUNITY_NO') . '</button>';
        $actions .= '<button class="btn btn-primary pull-right" onclick="jax.call(\'community\', \'activities,ajaxDeleteActivity\', \'' . $app . '\', \'' . $activityId . '\' );">' . JText::_('COM_COMMUNITY_YES') . '</button>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', $header);
        $objResponse->addScriptCall('cWindowAddContent', $message, $actions);

        return $objResponse->sendResponse();
    }

    /**
     *
     * @param  [type] $app        [description]
     * @param  [type] $activityId [description]
     * @return [type]             [description]
     */
    public function ajaxDeleteActivity($app, $activityId) {
        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        $filter = JFilterInput::getInstance();
        $app = $filter->clean($app, 'string');
        $activityId = $filter->clean($activityId, 'int');

        $activity = JTable::getInstance('Activity', 'CTable');
        $activity->load($activityId);

        // @todo: do permission checking within the ACL
        if ($activity->allowDelete()) {

            $activity = JTable::getInstance('Activity', 'CTable');
            $activity->load($activityId);
            $activity->delete($app);

            if ($activity->app == 'profile') {
                $model = $this->getModel('activities');
                $data = $model->getAppActivities(array('app' => 'profile', 'cid' => $activity->cid, 'limit' => 1));
                $status = $this->getModel('status');
                $status->update($activity->cid, $data[0]->title, $activity->access);

                $user = CFactory::getUser($activity->cid);
                $today = JFactory::getDate();
                $user->set('_status', $data[0]->title);
                $user->set('_posted_on', $today->toSql());
            }

            $objResponse->addScriptCall('joms.jQuery("ul.list-unstyled [data-streamid=' . $activityId . ']").fadeOut("5400", function () { joms.jQuery(this).remove(); });');
            CUserPoints::assignPoint('wall.remove');
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));

        $objResponse->addScriptCall('cWindowHide();');


        return $objResponse->sendResponse();
    }

    /**
     * AJAX method to add predefined activity
     * */
    public function ajaxAddPredefined($key, $message = '') {
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();

        $filter = JFilterInput::getInstance();
        $key = $filter->clean($key, 'string');
        $message = $filter->clean($message, 'string');

        if (!COwnerHelper::isCommunityAdmin() || empty($message)) {
            return;
        }

        // Predefined system custom activity.
        $system = array('system.registered', 'system.populargroup', 'system.totalphotos', 'system.popularprofiles', 'system.popularphotos', 'system.popularvideos');

        $act = new stdClass();
        $act->actor = 0; //$my->id; System message should not capture actor. Otherwise the stream filter will be inaccurate
        $act->target = 0;
        $act->app = 'system';
        $act->access = PRIVACY_FORCE_PUBLIC;
        $params = new CParameter('');

        if (in_array($key, $system)) {
            switch ($key) {
                case 'system.registered':
                    // $usersModel   = CFactory::getModel( 'user' );
                    // $now          = new JDate();
                    // $date         = CTimeHelper::getDate();
                    // $title        = JText::sprintf('COM_COMMUNITY_TOTAL_USERS_REGISTERED_THIS_MONTH_ACTIVITY_TITLE', $usersModel->getTotalRegisteredByMonth($now->format('Y-m')) , $date->_monthToString($now->format('m')));
                    $act->app = 'system.members.registered';
                    $act->cmd = 'system.registered';
                    $act->title = '';
                    $act->content = '';
                    $params->set('action', 'registered_users');

                    break;
                case 'system.populargroup':
                    // $groupsModel = CFactory::getModel('groups');
                    // $activeGroup = $groupsModel->getMostActiveGroup();
                    // $title       = JText::sprintf('COM_COMMUNITY_MOST_POPULAR_GROUP_ACTIVITY_TITLE', $activeGroup->name);
                    // $act->cmd    = 'groups.popular';
                    // $act->cid    = $activeGroup->id;
                    // $act->title  = $title;
                    $act->app = 'system.groups.popular';
                    $params->set('action', 'top_groups');
                    // $params->set('group_url', CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$activeGroup->id));

                    break;
                case 'system.totalphotos':
                    // $photosModel = CFactory::getModel( 'photos' );
                    // $total       = $photosModel->getTotalSitePhotos();
                    $act->app = 'system.photos.total';
                    $act->cmd = 'photos.total';
                    $act->title = ''; //JText::sprintf('COM_COMMUNITY_TOTAL_PHOTOS_ACTIVITY_TITLE', $total);

                    $params->set('action', 'total_photos');
                    // $params->set('photos_url', CRoute::_('index.php?option=com_community&view=photos'));

                    break;
                case 'system.popularprofiles':

                    $act->app = 'system.members.popular';
                    $act->cmd = 'members.popular';
                    $act->title = ''; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_TOP_PROFILES', 5);

                    $params->set('action', 'top_users');
                    // $params->set('count', 5);

                    break;
                case 'system.popularphotos':
                    $act->app = 'system.photos.popular';
                    $act->cmd = 'photos.popular';
                    $act->title = ''; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_TOP_PHOTOS', 5);

                    $params->set('action', 'top_photos');
                    // $params->set('count', 5);

                    break;
                case 'system.popularvideos':
                    $act->app = 'system.videos.popular';
                    $act->cmd = 'videos.popular';
                    $act->title = ''; //JText::sprintf( 'COM_COMMUNITY_ACTIVITIES_TOP_VIDEOS', 5 );

                    $params->set('action', 'top_videos');
                    // $params->set('count', 5);

                    break;
            }
        } else {
            // For additional custom activities, we only take the content passed by them.
            if (!empty($message)) {
                // $message    = CStringHelper::escape($message);

                $app = explode('.', $key);
                $app = isset($app[0]) ? $app[0] : 'system';
                $act->app = 'system.message';
                $act->title = JText::_($message);

                $params->set('action', 'message');
            }
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));

        // Allow comments on all these
        $act->comment_id = CActivities::COMMENT_SELF;
        $act->comment_type = $key;

        // Allow like for all admin activities
        $act->like_id = CActivities::LIKE_SELF;
        $act->like_type = $key;

        // Add activity logging
        CActivityStream::add($act, $params->toString());

        $objResponse->addAssign('activity-stream-container', 'innerHTML', $this->_getActivityStream());
        $objResponse->addScriptCall("joms.jQuery('.jomTipsJax').addClass('jomTips');");
        $objResponse->addScriptCall('joms.tooltip.setup();');

        return $objResponse->sendResponse();
    }

    private function _getActivityStream() {


        $act = new CActivityStream();
        $html = $act->getHTML('', '', null, 0, '', '', true, COMMUNITY_SHOW_ACTIVITY_MORE);

        return $html;
    }

    /**
     * Function to call Popup window for share status
     * @param  [int] $activityId [activity stream id]
     * @return
     */
    public function ajaxSharePopup($activityId) {
        $objResponse = new JAXResponse();

        $my = CFactory::getUser();

        if ($my->id == 0)
            $this->ajaxBlockUnregister();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        $user = CFactory::getUser($act->creator);
        $act = CActivityStream::formatSharePopup($act);

        switch ($act->app) {
            case 'groups.discussion':
                $db = JFactory::getDbo();
                $query = ' SELECT `d`.`title`, `d`.`message`, `g`.`name`, `g`.`description`, `g`.`id` ';
                $query .= ' FROM ' . $db->quoteName('#__community_groups_discuss') . ' AS `d` ';
                $query .= ' INNER JOIN ' . $db->quoteName('#__community_groups') . ' AS `g` ON `g`.`id` = `d`.`groupid` ';
                $query .= ' WHERE `d`.`id` = ' . (int) $act->cid;
                $db->setQuery($query);               
                $data = $db->loadObject();
                break;
        }
        
        $tmpl = new CTemplate();
        isset($data) ? $tmpl->set('data', $data) : null;

        $tmpl->set('act', $act);
        $tmpl->set('user', $user);
        $html = $tmpl->fetch('ajax.showsharepopup');

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_SHARE_STATUS_TITLE'));
        $actions = '<button class="btn pull-left" onclick="cWindowHide();return false;">' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '</button> <button class="submit btn btn-primary pull-right">' . JText::_('COM_COMMUNITY_SHARE') . '</button>';
        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);
        return $objResponse->sendResponse();
    }

    public function ajaxAddShare($activityId, $attachment) {

        $objResponse = new JAXResponse();
        $data = CApiActivities::addShare($activityId, $attachment);

        if ($data) {

            $data->params = new CParameter($data->params);
            $data->userLiked = -1;
            $data->likeCount = 0;
            $data->commentCount = 0;

            $html = '<li class="joms-stream post feed-'.$data->app.'" data-streamid="' . $data->id . '">';
            $tmpl = new CTemplate();
            $tmpl->set('act', $data);
            $html .= $tmpl->fetch('activities.profile.status.share');
            $html .= $tmpl->fetch('activities.stream.options');
            $html .='</li>';
            $objResponse->addScriptCall('cWindowHide');
            $objResponse->addScriptCall("joms.jQuery('#activity-stream-container').find('#activity-more').prev('ul').prepend", $html);
        }

        return $objResponse->sendResponse();
    }

    /**
     * Function to show map in popup
     *
     * @param int $activityId
     * @param int $latitude
     * @param int $longitude
     * @param int $zoom
     * @param int $height
     * @access public
     *
     */
    public function ajaxShowMap($activityId) {

        $objResponse = new JAXResponse();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        $user = CFactory::getUser($act->creator);

        $tmpl = new CTemplate();
        $tmpl->set('lang', $act->latitude);
        $tmpl->set('long', $act->longitude);
        $html = $tmpl->fetch('ajax.showmap');

        $objResponse->addScriptCall('cWindowAddContent', $html);
        return $objResponse->sendResponse();
    }

    public function ajaxEditLocation($activityId) {
        $objResponse = new JAXResponse();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        $gService = new CServiceGoogle();

        $address = $gService->getAddressFromCoords($act->latitude, $act->longitude);
        $tmpl = new CTemplate();

        // $tmpl->set('address', $address->formatted_address);
        $tmpl->set('address', $act->location);
        $tmpl->set('latitude', $act->latitude);
        $tmpl->set('longitude', $act->longitude);

        $html = $tmpl->fetch('ajax.editLocation');
        $actions = '<button class="btn btn-danger pull-left" onclick="cWindowHide();return false;">' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '</button> <button class="btn btn-primary pull-right" onclick="joms.activities.saveLocation(' . $activityId . ',this)">' . JText::_('COM_COMMUNITY_EDIT') . '</button>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('Edit Location'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);
        return $objResponse->sendResponse();
    }

    public function ajaxSaveLocation($activityId, $location , $latitude, $longitude) {

        $objResponse = new JAXResponse();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        $act->latitude = $latitude;
        $act->longitude = $longitude;
        $act->location = $location;
        $act->save($act);

        $objResponse->addScriptCall('joms.jQuery(".joms-status-location a[data-stream-id='.$activityId.']").text',$location);
        $objResponse->addScriptCall('cWindowHide');
        return $objResponse->sendResponse();
    }

    public function ajaxRemoveLocation($activityId) {
        $objResponse = new JAXResponse();

        $header = JText::_('Delete location');
        $message = JText::_('Delete location ?');

        $actions = '<button class="btn" onclick="cWindowHide();">' . JText::_('COM_COMMUNITY_NO') . '</button>';
        $actions .= '<button class="btn btn-primary pull-right" onclick="jax.call(\'community\',\'activities,deleteLocation\',' . $activityId . ')">' . JText::_('COM_COMMUNITY_YES') . '</button>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', $header);
        $objResponse->addScriptCall('cWindowAddContent', $message, $actions);

        return $objResponse->sendResponse();
    }

    public function deleteLocation($activityId) {

        $objResponse = new JAXResponse();

        if (COwnerHelper::isCommunityAdmin()) {
            $act = JTable::getInstance('Activity', 'CTable');
            $act->load($activityId);

            $act->latitude = 255.0000;
            $act->longitude = 255.000;
            $act->location = '';

            $act->save($act);

            $objResponse->addScriptCall("joms.jQuery('#activity-stream-container > ul >li[data-streamid=" . $activityId . "] > .joms-stream-content > span.joms-status-location').remove");
        }
        $objResponse->addScriptCall('cWindowHide');
        return $objResponse->sendResponse();
    }

    public function ajaxEditStatus($activityId, $app) {

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        echo json_encode($act->title);
        exit;
    }

    /**
     * @since 3.2
     * @param Int $streamId
     * @param Int User Id
     * @param Int Group ID ( optional )
     *
     * */
    public function ajaxHideStatus($streamId, $userId, $groupId = null) {
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();
        $target = CFactory::getUser($userId);

        //do hide function
        $modal = CFactory::getModel('activities');
        $modal->hide($my->id, $streamId);
        //store to db etc here

        $act = new stdClass();
        $act->actor = $my->id;
        $act->targetStreamId = $streamId;
        $act->targetUserId = $userId;
        $act->targetGroupId = $groupId;
        $act->targetUsername = $target->getDisplayName();

        $tmpl = new CTemplate();
        $tmpl->set('act', $act);
        $html = $tmpl->fetch('activity.hide.status');

        $html = rawurlencode($html);

        $stat = new stdClass();
        $stat->html = $html;
        $stat->userid = $userId;
        $stat->streamId = $streamId;
        $stat->groupId = $groupId;

        $stat = json_encode($stat);

        //$objResponse->addScriptCall('joms.stream.updateHideStatus', $stat);
        $objResponse->addScriptCall('joms.stream.updateHideStatus', $stat);
        return $objResponse->sendResponse();
    }

    public function ajaxSaveStatus($actId, $value) {
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }

        $filter = JFilterInput::getInstance();
        $actId = $filter->clean($actId, 'int');
        $value = trim($filter->clean($value, 'string'));

        $activity = JTable::getInstance('Activity', 'CTable');
        $activity->load($actId);

        if(empty($value)){
            $objResponse->addAlert(JText::_('COM_COMMUNITY_CANNOT_EDIT_POST_ERROR'));
        }else{
            $activity->title = $value;
            $activity->store();
        }

        $params = new CParameter($activity->params);
        $mood = $params->get('mood', NULL);
        $value = CActivities::format($activity->title, $mood);
        $objResponse->addScriptCall('joms.jQuery("li[data-streamid=' . $actId . '] p > span:first").html', $value);
        $objResponse->addScriptCall('joms.jQuery("li[data-streamid=' . $actId . '] p").show');
        $objResponse->addScriptCall('joms.jQuery("li[data-streamid=' . $actId . '] [data-type=stream-editor] textarea").val', $activity->title);
        $objResponse->addScriptCall('joms.jQuery("li[data-streamid=' . $actId . '] [data-type=stream-editor] textarea").removeData', 'initialized');
        //$objResponse->addScriptCall('cWindowHide');

        return $objResponse->sendResponse();
    }

    public function reportActivities($link, $message, $id) {
        $report = new CReportingLibrary();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$config->get('enablereporting') || ( ( $my->id == 0 ) && (!$config->get('enableguestreporting') ) )) {
            return '';
        }

        if (strpos($link, 'actid') === false) {
            $link = $link . '&actid=' . $id;
        }

        $report->createReport(JText::_('COM_COMMUNITY_REPORT_ACTIVITY_CONTAIN'), $link, $message);

        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_ACTVITIES_REMOVE';
        $action->method = 'activities,deleteStream';
        $action->parameters = $id;
        $action->defaultAction = true;

        $report->addActions(array($action));

        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    /**
     * Function that is called from the back end
     * */
    public function deleteStream($activityId) {

        if (COwnerHelper::isCommunityAdmin()) {
            $model = $this->getModel('activities');
            $model->removeActivityById($activityId);

            return JText::_('COM_COMMUNITY_WALL_REMOVED');
        }
    }

    public function ajaxAddMood($activityId) {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }
        $objResponse = new JAXResponse();

        $tmpl = new CTemplate();
        $tmpl->set('activityId', $activityId);
        $html = $tmpl->fetch('ajax.mood');

        $objResponse->addScriptCall('cWindowAddContent', $html);
        return $objResponse->sendResponse();
    }

    public function ajaxSaveMood($activityId, $mood) {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        $act->addMood($mood);

        $html = CActivities::format($act->title, $mood);

        $objResponse = new JAXResponse();
        $objResponse->addScriptCall('joms.jQuery("#profile-newsfeed-item-' . $activityId . ' p").html', $html);
        $objResponse->addScriptCall('cWindowHide');

        return $objResponse->sendResponse();
    }

    public function ajaxConfirmRemoveMood($activityId) {

        $my = CFactory::getUser();

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }

        $objResponse = new JAXResponse();

        $header = JText::_('COM_COMMUNITY_ACTIVITY_REMOVE_MOOD');
        $message = JText::_('COM_COMMUNITY_ACTVITIES_REMOVE_MOOD_MESSAGE');

        $actions = '<button class="btn" onclick="cWindowHide();">' . JText::_('COM_COMMUNITY_NO') . '</button>';
        $actions .= '<button class="btn btn-primary pull-right" onclick="jax.call(\'community\', \'activities,ajaxRemoveMood\', \'' . $activityId . '\' );">' . JText::_('COM_COMMUNITY_YES') . '</button>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', $header);
        $objResponse->addScriptCall('cWindowAddContent', $message, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxRemoveMood($activityId) {

        $my = CFactory::getUser();

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);

        $act->removeMood();
        $html = CActivities::format($act->title);

        $objResponse = new JAXResponse();
        $objResponse->addScriptCall('joms.jQuery("#activity-stream-container > ul li[data-streamid=' . $activityId . '] .joms-stream-content p span").html', $html);
        $objResponse->addScriptCall('cWindowHide');

        return $objResponse->sendResponse();
    }

    public function ajaxShowOthers($id) {
        $my = CFactory::getUser();

        if ($my->id == 0) {
            $this->ajaxBlockUnregister();
        }

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($id);

        $params = new CParameter($act->params);

        $users = CLikesHelper::getActor($params);
        array_shift($users);

        foreach ($users as $key => $user) {
            $users[$key] = CFactory::getUser($user);
        }

        $tmpl = new CTemplate();
        $tmpl->set('users', $users);
        $html = $tmpl->fetch('ajax.stream.showothers');

        $objResponse = new JAXResponse();
        $objResponse->addScriptCall('cWindowAddContent', $html);

        return $objResponse->sendResponse();
    }

    /**
     * Display confirm dialog to ignore user
     * @param int $userId
     * @return type
     */
    public function ajaxConfirmIgnoreUser($userId) {
        $objResponse = new JAXResponse();

        $header = JText::_('COM_COMMUNITY_TITLE_CONFIRM_IGNORE_USER');
        $message = JText::_('COM_COMMUNITY_MESSAGE_CONFIRM_IGNORE_USER');

        $actions = '<button class="btn" onclick="cWindowHide();">' . JText::_('COM_COMMUNITY_NO') . '</button>';
        $actions .= '<button class="btn btn-primary pull-right" onclick="jax.call(\'community\', \'profile,ajaxIgnoreUser\', \'' . $userId . '\' );">' . JText::_('COM_COMMUNITY_YES') . '</button>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', $header);
        $objResponse->addScriptCall('cWindowAddContent', $message, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxshowLikedUser($wallId) {
        $objResponse = new JAXResponse();

        $like = new CLike();
        $users = $like->getWhoLikes('comment', $wallId);

        $tmpl = new CTemplate();
        $tmpl->set('users', $users);
        $html = $tmpl->fetch('ajax.stream.showothers');

        $objResponse->addScriptCall('cWindowAddContent', $html);
        return $objResponse->sendResponse();
    }

    public function ajaxGetStreamTitle($streamId){
        $objResponse = new JAXResponse();

        $table = JTable::getInstance('Activity', 'CTable');
        $table->load($streamId);

        $objResponse->addScriptCall('joms.stream.showTextarea',$table->title,$streamId);
        $objResponse->sendResponse();
    }

}
