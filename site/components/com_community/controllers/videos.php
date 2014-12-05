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
jimport('joomla.application.component.controller');

class CommunityVideosController extends CommunityBaseController {

    var $_name = 'videos';

    public function checkVideoAccess() {
        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();
        $jinput = $mainframe->input;
        $userId = $jinput->get('userid', 0, 'INT');

        $my = CFactory::getUser();
        $actor = CFactory::getuser($userId);

        if (!CPrivacy::isAccessAllowed($my->id, $actor->id, 'privacyVideoView', 'privacyVideoView')) {
            echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
            return;
        }

        if (!$config->get('enablevideos')) {
            $redirect = CRoute::_('index.php?option=com_community&view=frontpage', false);
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_VIDEOS_DISABLED'), 'warning');
        }
        return true;
    }

    public function ajaxRemoveFeatured($videoId) {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');

        $objResponse = new JAXResponse();
        //CFactory::load( 'helpers' , 'owner' );

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            //CFactory::load( 'libraries' , 'featured' );
            $featured = new CFeatured(FEATURED_VIDEOS);
            $my = CFactory::getUser();

            if ($featured->delete($videoId)) {
                $html = JText::_('COM_COMMUNITY_VIDEOS_REMOVED_FROM_FEATURED');
            } else {
                $html = JText::_('COM_COMMUNITY_VIDEOS_REMOVING_VIDEO_FROM_FEATURED_ERROR');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $actions = '<input type="button" class="btn" onclick="window.location.reload();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';

        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $objResponse->sendResponse();
    }

    public function ajaxAddFeatured($videoId) {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');

        $objResponse = new JAXResponse();
        //CFactory::load( 'helpers' , 'owner' );

        $my = CFactory::getUser();
        if ($my->id == 0) {
            return $this->ajaxBlockUnregister();
        }

        if (COwnerHelper::isCommunityAdmin()) {
            $model = CFactory::getModel('Featured');

            if (!$model->isExists(FEATURED_VIDEOS, $videoId)) {
                //CFactory::load( 'libraries' , 'featured' );
                //CFactory::load( 'models' , 'videos' );
                $featured = new CFeatured(FEATURED_VIDEOS);
                $table = JTable::getInstance('Video', 'CTable');
                $table->load($videoId);
                $config = CFactory::getConfig();
                $limit = $config->get('featured' . FEATURED_VIDEOS . 'limit', 10);

                if ($featured->add($videoId, $my->id) === true) {
                    $html = JText::sprintf('COM_COMMUNITY_VIDEOS_IS_FEATURED', $table->title);
                } else {
                    $html = JText::sprintf('COM_COMMUNITY_VIDEOS_LIMIT_REACHED_FEATURED', $table->title, $limit);
                }
            } else {
                $html = JText::_('COM_COMMUNITY_VIDEOS_FEATURED_ERROR');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION');
        }

        $actions = '<input type="button" class="btn" onclick="window.location.reload();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';

        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $objResponse->sendResponse();
    }

    /**
     * Method is called from the reporting library. Function calls should be
     * registered here.
     *
     * return    String    Message that will be displayed to user upon submission.
     * */
    public function reportVideo($link, $message, $id) {
        //CFactory::load( 'libraries' , 'reporting' );
        $report = new CReportingLibrary();
        $config = CFactory::getConfig();
        $my = CFactory::getUser();

        if (!$config->get('enablereporting') || (($my->id == 0) && (!$config->get('enableguestreporting')))) {
            return '';
        }

        // Pass the link and the reported message
        $report->createReport(JText::_('COM_COMMUNITY_VIDEOS_ERROR'), $link, $message);

        // Add the action that needs to be called.
        $action = new stdClass();
        $action->label = 'COM_COMMUNITY_VIDEOS_UNPUBLISH';
        $action->method = 'videos,deleteVideo';
        $action->parameters = array($id, 0);
        $action->defaultAction = false;

        $report->addActions(array($action));
        return JText::_('COM_COMMUNITY_REPORT_SUBMITTED');
    }

    /**
     * Show all video within the system
     */
    public function display($cacheable = false, $urlparams = false) {
        $this->checkVideoAccess();
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get(__FUNCTION__);
    }

    /**
     * Full application view
     */
    public function app() {
        $view = $this->getView('videos');
        echo $view->get('appFullView');
    }

    /**
     * Display all video by current user
     */
    public function myvideos() {
        $my = JFactory::getUser();
        if ($this->checkVideoAccess()) {
            $document = JFactory::getDocument();
            $viewType = $document->getType();
            $viewName = JRequest::getCmd('view', $this->getName());
            $view = $this->getView($viewName, '', $viewType);

            $userid = JRequest::getInt('userid', '');
            $user = CFactory::getUser($userid);

            echo $view->get(__FUNCTION__, $user->id);
        }
    }

    /**
     * Display all video by current user
     */
    public function mypendingvideos() {
        if ($this->blockUnregister())
            return;
        $this->checkVideoAccess();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $user = CFactory::getUser();
        echo $view->get(__FUNCTION__, $user->id);
    }

    /**
     * Show the  'add' video page. It should just link to either link or upload
     */
    public function add() {
        if ($this->blockUnregister())
            return;
        $this->checkVideoAccess();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        echo $view->get(__FUNCTION__);
    }

    /**
     * Show the add video link form
     * @return unknown_type
     */
    public function link() {
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        if ($this->blockUnregister())
            return;
        $this->checkVideoAccess();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        // Preset the redirect url according to group type or user type
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $redirect = CVideosHelper::getVideoReturnUrlFromRequest();
        $my = CFactory::getUser();

        // @rule: Do not allow users to add more videos than they are allowed to
        if (CLimitsLibrary::exceedDaily('videos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED'), 'error');
        }

        // Without CURL library, there's no way get the video information
        // remotely
        if (!CRemoteHelper::curlExists()) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
        }

        // Determine if the video belongs to group or user and
        // assign specify value for checking accordingly
        $config = CFactory::getConfig();
        $creatorType = $jinput->get('creatortype', VIDEO_USER_TYPE, 'NONE'); //JRequest::getVar('creatortype', VIDEO_USER_TYPE);
        $groupid = ($creatorType == VIDEO_GROUP_TYPE) ? JRequest::getInt('groupid', 0) : 0;
        list($creatorType, $videoLimit) = $this->_manipulateParameter($groupid, $config);

        $group = JTable::getInstance('Group', 'CTable');
        $group->load($groupid);

        if ($group->approvals) {
            $permission = 40;
        }

        $permission = $jinput->post->get('permissions', '', 'NONE');

        // Do not allow video upload if user's video exceeded the limit
        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $message = JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit);
            $mainframe->redirect($redirect, $message);
            exit;
        }

        // Create the video object and save
        $videoUrl = $jinput->get('videoLinkUrl', '', 'STRING');

        if (empty($videoUrl)) {
            $view->addWarning(JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_LINKS'));
            echo $view->get(__FUNCTION__);
            exit;
        }

        $video = JTable::getInstance('Video', 'CTable');
        $isValid = $video->init($videoUrl);

        if (!$isValid) {
            $mainframe->redirect($redirect, $video->getProvider()->getError(), 'error');
            return;
        }

        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('permissions', $permission);
        $video->set('category_id', $jinput->post->get('category_id', '1', 'INT'));
        $video->set('location', $jinput->post->get('location', '', 'STRING'));
        $video->set('groupid', $groupid);

        /* make sure this video is not added before ( IE issue ) */
        if (!$video->isExist()) {
            if (!$video->store()) {
                $message = JText::_('COM_COMMUNITY_VIDEOS_ADD_LINK_FAILED');
                $mainframe->redirect($redirect, $message);
            }

            //add notification: New group album is added
            if ($video->groupid != 0) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($video->groupid);

                $modelGroup = $this->getModel('groups');
                $groupMembers = array();
                $groupMembers = $modelGroup->getMembersId($video->groupid, true);

                $params = new CParameter('');
                $params->set('title', $video->title);
                $params->set('group', $group->name);
                $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                $params->set('video', $video->title);
                $params->set('video_url', 'index.php?option=com_community&view=videos&task=videos&groupid=' . $group->id . '&videoid=' . $video->id);
                $params->set('url', 'index.php?option=com_community&view=videos&task=video&groupid=' . $group->id . '&videoid=' . $video->id);
                CNotificationLibrary::add('groups_create_video', $my->id, $groupMembers, JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION'), '', 'groups.video', $params);
            }

            // Trigger for onVideoCreate
            $this->_triggerEvent('onVideoCreate', $video);

            // Fetch the thumbnail and store it locally,
            // else we'll use the thumbnail remotely
            CError::assert($video->thumb, '', '!empty');
            $this->_fetchThumbnail($video->id);

            // Add activity logging
            $url = $video->getViewUri(false);

            $act = new stdClass();
            $act->cmd = 'videos.upload';
            $act->actor = $my->id;
            $act->access = $video->permissions;
            $act->target = 0;
            $act->title = '';
            $act->app = 'videos';
            $act->content = '';
            $act->cid = $video->id;
            $act->location = $video->location;

            $act->comment_id = $video->id;
            $act->comment_type = 'videos';

            $act->like_id = $video->id;
            $act->like_type = 'videos';

            $act->groupid = ($video->groupid != 0) ? $video->groupid : 0;

            $params = new CParameter('');
            $params->set('video_url', $url);
            $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style

            CActivityStream::add($act, $params->toString());
            CUserPoints::assignPoint('video.add', $video->creator);

            $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));

            // Redirect user to his/her video page
            $message = JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title);
            $mainframe->redirect($redirect, $message);
        }

        $id = $video->isExist();
        $video->load($id);

        $message = JText::sprintf('COM_COMMUNITY_VIDEOS_LINK_EXIST_WARNING', $video->getPermalink());
        $mainframe->redirect($redirect, $message);
    }

    private function _triggerEvent($event, $args) {
        // Trigger for onVideoCreate
        //CFactory::load( 'libraries' , 'apps' );
        $apps = CAppPlugins::getInstance();
        $apps->loadApplications();
        $params = array();
        $params[] = $args;
        $apps->triggerEvent($event, $params);
    }

    public function uploadVideo(){
        if ($this->blockUnregister())
            return;
        $this->checkVideoAccess();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $creatorType = $jinput->get('creatortype', VIDEO_USER_TYPE, 'NONE'); //can be a user or group
        //JRequest::getVar('creatortype', VIDEO_USER_TYPE);
        $groupid = ($creatorType == VIDEO_GROUP_TYPE) ? JRequest::getInt('groupid', 0) : 0;
        $config = CFactory::getConfig();

        $returnStats = array();

        //check
        if (CLimitsLibrary::exceedDaily('videos')) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED');
        }

        // Process according to video creator type

        if($groupid != 0){
            list($creatorType, $videoLimit) = $this->_manipulateParameter($groupid, $config);
            $group = JTable::getInstance('Group', 'CTable');

            $group->load($groupid);

            if ($group->approvals) {
                $permission = 40;
            }
        }

        $permission = JRequest::getInt('permissions', 0, 'post');

        // Check is video upload is permitted

        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR');
            echo json_encode($returnStats);
            exit;
        }else if (!$config->get('enablevideos')) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_VIDEO_DISABLED');
            echo json_encode($returnStats);
            exit;
        }else if (!$config->get('enablevideosupload')) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_DISABLED');
            echo json_encode($returnStats);
            exit;
        }

        // Check if the video file is valid
        $files = JRequest::get('files');
        $videoFile = !empty($files['file']) ? $files['file'] : array();

        if (empty($files) || (empty($videoFile['name']) && $videoFile['size'] < 1)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR');
        }

        // Check file type.

        $fileType = CVideosHelper::getMIMEType($videoFile);

        $allowable = CVideosHelper::getValidMIMEType();
        if (!in_array(strtolower($fileType), $allowable)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileType);
            echo json_encode($returnStats);
            exit;
        }

        $fileExtension = pathinfo($videoFile['name']);
        $allowextension = CVideosHelper::getValidExtensionType();

        if (!in_array(strtolower($fileExtension['extension']), $allowextension)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileExtension['extension']);
            echo json_encode($returnStats);
            exit;
        }

        // Check if the video file exceeds file size limit
        $uploadLimit = $config->get('maxvideouploadsize'); /* MB unit */
        $videoFileSize = sprintf("%u", filesize($videoFile['tmp_name']));

        //convert to MB
        $videoFileSize = number_format($videoFileSize / 1048576, 2);

        if (($uploadLimit > 0) && ($videoFileSize > $uploadLimit)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_FILE_SIZE_EXCEEDED', $uploadLimit);
            echo json_encode($returnStats);
            exit;
        }

        // Passed all checking, attempt to save the video file

        $folderPath = CVideoLibrary::getPath($my->id, 'original');
        $randomFileName = CFileHelper::getRandomFilename($folderPath, $videoFile['name'], '');
        $destination = JPATH::clean($folderPath . '/' . $randomFileName);

        if (!CFileHelper::upload($videoFile, $destination)) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR');
            echo json_encode($returnStats);
            exit;
        }

        $config = CFactory::getConfig();
        $videofolder = $config->get('videofolder');

        //get the file name from the uploaded item
        $ext = explode('.',$videoFile['name']);
        $ext = '.'.$ext[count($ext)-1];
        $filename = basename($videoFile['name'],$ext);
        $filename = ($filename == '') ? JText::_('COM_COMMUNITY_VIDEOS_TITLE_EMPTY') : $filename;

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $video->set('path', $videofolder . '/originalvideos/' . $my->id . '/' . $randomFileName);
        $video->set('title', $filename);
        $video->set('description', $jinput->get('description', '', 'post'));
        $video->set('category_id', JRequest::getInt('category_id', 0, 'post'));
        $video->set('permissions', $permission);
        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('groupid', $groupid);
        $video->set('location', $jinput->get('location', '', 'STRING'));
        $video->set('filesize', $videoFileSize);

        if (!$video->store()) {
            $returnStats['status'] = 'error';
            $returnStats['message'] = JText::_('COM_COMMUNITY_VIDEOS_SAVE_ERROR');
            echo json_encode($returnStats);
            exit;
        }
        //add notification: New group album is added
        if ($video->groupid != 0) {

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($video->groupid);

            $modelGroup = $this->getModel('groups');
            $groupMembers = array();
            $groupMembers = $modelGroup->getMembersId($video->groupid, true);

            $params = new CParameter('');
            $params->set('title', $video->title);
            $params->set('group', $group->name);
            $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
            $params->set('url', 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id);
            CNotificationLibrary::add('groups_create_video', $my->id, $groupMembers, JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION', '{actor}', '{group}'), '', 'groups.video', $params);
        }

        // Trigger for onVideoCreate
        $this->_triggerEvent('onVideoCreate', $video);

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));

        // Video saved, redirect

        if(!isset($returnStats['status'])){
            $returnStats['status'] = 'success';
            $returnStats['message'] = JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title);
            $returnStats['videoid'] = $video->id;
            $returnStats['processing_str'] = JText::_('COM_COMMUNITY_VIDEOS_IN_PROGRESS');
        }

        echo json_encode($returnStats);
        exit;
    }

    public function upload() {
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        if ($this->blockUnregister())
            return;
        $this->checkVideoAccess();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $creatorType = $jinput->get('creatortype', VIDEO_USER_TYPE, 'NONE'); //JRequest::getVar('creatortype', VIDEO_USER_TYPE);
        $groupid = ($creatorType == VIDEO_GROUP_TYPE) ? JRequest::getInt('groupid', 0) : 0;
        $config = CFactory::getConfig();



        $redirect = CVideosHelper::getVideoReturnUrlFromRequest();

        // @rule: Do not allow users to add more videos than they are allowed to
        //CFactory::load( 'libraries' , 'limits' );

        if (CLimitsLibrary::exceedDaily('videos')) {
            $mainframe->redirect($redirect, JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED'), 'error');
        }

        // Process according to video creator type
        list($creatorType, $videoLimit) = $this->_manipulateParameter($groupid, $config);
        $group = JTable::getInstance('Group', 'CTable');

        $group->load($groupid);

        if ($group->approvals) {
            $permission = 40;
        }
        $permission = JRequest::getInt('permissions', 0, 'post');

        // Check is video upload is permitted

        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $message = JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit);
            $mainframe->redirect($redirect, $message);
            exit;
        }
        if (!$config->get('enablevideos')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_VIDEO_DISABLED', 'notice'));
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=frontpage', false));
            exit;
        }
        if (!$config->get('enablevideosupload')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_DISABLED', 'notice'));
            $mainframe->redirect(CRoute::_('index.php?option=com_community&view=videos', false));
            exit;
        }

        // Check if the video file is valid
        $files = JRequest::get('files');
        $videoFile = !empty($files['videoFile']) ? $files['videoFile'] : array();
        if (empty($files) || (empty($videoFile['name']) && $videoFile['size'] < 1)) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR', 'error'));
            $mainframe->redirect($redirect, false);
            exit;
        }

        // Check file type.

        $fileType = CVideosHelper::getMIMEType($videoFile);

        $allowable = CVideosHelper::getValidMIMEType();
        if (!in_array(strtolower($fileType), $allowable)) {
            $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileType));
            exit;
        }

        $fileExtension = pathinfo($videoFile['name']);
        $allowextension = CVideosHelper::getValidExtensionType();

        if (!in_array(strtolower($fileExtension['extension']), $allowextension)) {
            $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_FILETYPE_ERROR', $fileExtension['extension']));
            exit;
        }

        // Check if the video file exceeds file size limit
        $uploadLimit = $config->get('maxvideouploadsize'); /* MB unit */
        $videoFileSize = sprintf("%u", filesize($videoFile['tmp_name']));

        //convert to MB
        $videoFileSize = number_format($videoFileSize / 1048576, 2);

        if (($uploadLimit > 0) && ($videoFileSize > $uploadLimit)) {
            $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_FILE_SIZE_EXCEEDED', $uploadLimit));
        }

        // Passed all checking, attempt to save the video file

        $folderPath = CVideoLibrary::getPath($my->id, 'original');
        $randomFileName = CFileHelper::getRandomFilename($folderPath, $videoFile['name'], '');
        $destination = JPATH::clean($folderPath . '/' . $randomFileName);

        if (!CFileHelper::upload($videoFile, $destination)) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_ERROR', 'error'));
            $mainframe->redirect($redirect, false);
            exit;
        }

        $config = CFactory::getConfig();
        $videofolder = $config->get('videofolder');

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $video->set('path', $videofolder . '/originalvideos/' . $my->id . '/' . $randomFileName);
        $video->set('title', $jinput->get('title', '', 'STRING'));
        $video->set('description', $jinput->get('description', '', 'STRING'));
        $video->set('category_id', JRequest::getInt('category_id', 0, 'post'));
        $video->set('permissions', $permission);
        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('groupid', $groupid);
        $video->set('location', $jinput->get('location', '', 'STRING'));
        $video->set('filesize', $videoFileSize);

        if (!$video->store()) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_VIDEOS_SAVE_ERROR', 'error'));
            $mainframe->redirect($redirect, false);
            exit;
        }
        //add notification: New group album is added
        if ($video->groupid != 0) {

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($video->groupid);

            $modelGroup = $this->getModel('groups');
            $groupMembers = array();
            $groupMembers = $modelGroup->getMembersId($video->groupid, true);

            $params = new CParameter('');
            $params->set('title', $video->title);
            $params->set('group', $group->name);
            $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
            $params->set('url', 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id);
            CNotificationLibrary::add('groups_create_video', $my->id, $groupMembers, JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION', '{actor}', '{group}'), '', 'groups.video', $params);
        }

        // Trigger for onVideoCreate
        $this->_triggerEvent('onVideoCreate', $video);

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES, COMMUNITY_CACHE_TAG_GROUPS_DETAIL));

        // Video saved, redirect
        $redirect = CVideosHelper::getVideoReturnUrlFromRequest('pending');
        $mainframe->redirect($redirect, JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));
    }

    /**
     * Displays the video
     * */
    public function video() {
        $this->checkVideoAccess();
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);
		$my = CFactory::getUser();

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED));

		// Log user engagement
		CEngagement::log('video.display', $my->id);

        echo $view->get(__FUNCTION__);
    }

    /**
     * Controller method to remove a video
     */
    public function removevideo() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $task = $jinput->post->get('currentTask', '', 'STRING');
        if ($task == 'video')
            $task = 'myvideos'; /* after video deleted we can't display this video, than we redirect back to this user videos */
        $videoId = $jinput->post->get('videoid', '', 'INT'); //JRequest::getVar('videoid', '', 'POST');
        $userId = $jinput->post->get('userid', null, 'INT');
        $message = $this->deleteVideo($videoId, false); /* We will handle redirect with below code */

        if ($userId === null) {
            $url = CRoute::_('index.php?option=com_community&view=videos&task=' . $task, false);
        } else {
            /* redirect back to current requesting user */
            $url = CRoute::_('index.php?option=com_community&view=videos&task=' . $task . '&userid=' . (int) $userId, false);
        }

        if ($message != false) {
            // Remove from activity stream
            CActivityStream::remove('videos', $videoId);
            $mainframe->redirect($url, $message);
        } else {
            $message = JText::_('COM_COMMUNITY_VIDEOS_DELETING_VIDEO_ERROR');
            $mainframe->redirect($url, $message);
        }
    }

    /**
     * Controller method to remove a video
     * @param type $videoId
     * @param type $redirect
     * @return type
     */
    public function deleteVideo($videoId = 0, $redirect = true) {
        if ($this->blockUnregister())
            return;

        // Load libraries
        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $mainframe = JFactory::getApplication();
        $video->load((int) $videoId);

        if (!empty($video->groupid)) {
            //CFactory::load( 'helpers' , 'group' );
            $allowManageVideos = CGroupHelper::allowManageVideo($video->groupid);
            CError::assert($allowManageVideos, '', '!empty', __FILE__, __LINE__);
        }

        // @rule: Add point when user removes a video
        //CFactory::load( 'libraries' , 'userpoints' );
        CUserPoints::assignPoint('video.remove', $video->creator);

        if ($video->delete()) {
            // Delete all videos related data
            $this->_deleteVideoWalls($video->id);
            $this->_deleteVideoActivities($video->id);
            $this->_deleteFeaturedVideos($video->id);
            $this->_deleteVideoFiles($video);
            $this->_deleteProfileVideo($video->creator, $video->id);

            if (!empty($video->groupid)) {
                $message = JText::sprintf('COM_COMMUNITY_VIDEOS_REMOVED', $video->title);
                $redirect = CRoute::_('index.php?option=com_community&view=videos&groupid=' . $video->groupid, false);
            } else {
                $message = JText::sprintf('COM_COMMUNITY_VIDEOS_REMOVED', $video->title);
                $redirect = CRoute::_('index.php?option=com_community&view=videos', false);
            }
        }

        /**
         * @todo Should we return bool instead message string ?
         * @todo There is no process if $video->delete failed
         */
        if ($redirect === true)
            $mainframe->redirect($redirect, $message);

        return $message;
    }

    public function saveVideo() {
        // Check for request forgeries
        JRequest::checkToken() or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

        if ($this->blockUnregister())
            return;

        $my = CFactory::getUser();
        $postData = JRequest::get('post');
        $mainframe = JFactory::getApplication();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($postData['id']);


        $redirect = CVideosHelper::getVideoReturnUrlFromRequest();

        if (!($video->bind($postData) && $video->store())) {
            $message = JText::_('COM_COMMUNITY_VIDEOS_SAVE_VIDEO_FAILED', 'error');
            $mainframe->redirect($redirect, $message);
        }

        // update permissions in activity streams as well
        $activityModel = CFactory::getModel('activities');
        $activityModel->updatePermission($video->permissions, null, $my->id, 'videos', $video->id);

        //update location in activity stream
        $data = array('app' => 'videos', 'cid' => $video->id);
        $update = array('location' => $postData['location']);
        $activityModel->update($data, $update);

        $message = JText::sprintf('COM_COMMUNITY_VIDEOS_SAVED', $video->title);
        $mainframe->redirect($redirect, $message);
    }

    public function ajaxFetchThumbnail($id) {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');

        if (!COwnerHelper::isRegisteredUser())
            return $this->ajaxBlockUnregister();

        $thumbnail = $this->_fetchThumbnail($id, true);
        if (!$thumbnail) {
            $content = $this->getError() ? $this->getError() : '<div>Failed to fetch video thumbnail.</div>';
        } else {
            $content = '<div>' . JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL_SUCCESS') . '</div>';
            // I'm not sure this code is a right way to resolve  issue in case #5006
            $content .= '<div style="text-align:center;height:150px"><img style="border: 1px solid rgb(204, 204, 204); padding: 2px;" src="' . $thumbnail . '"/></div>';
        }

        $response = new JAXResponse();
        $buttons = '<input type="button" class="btn" onclick="cWindowHide()" value="' . JText::_('COM_COMMUNITY_DONE_BUTTON') . '">';

        $response->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL'));
        $response->addScriptCall('cWindowAddContent', $content, $buttons);

        return $response->sendResponse();
    }

    /**
     * Ajax function to save a new wall entry
     *
     * @param message    A message that is submitted by the user
     * @param uniqueId    The unique id for this group
     *
     * */
    public function ajaxSaveWall($message, $uniqueId) {
        $filter = JFilterInput::getInstance();
        $uniqueId = $filter->clean($uniqueId, 'int');

        if (!COwnerHelper::isRegisteredUser())
            return $this->ajaxBlockUnregister();

        $response = new JAXResponse();
        $my = CFactory::getUser();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($uniqueId);

        // If the content is false, the message might be empty.
        if (empty($message)) {
            $response->addAlert(JText::_('COM_COMMUNITY_WALL_EMPTY_MESSAGE'));
        } else {
            $config = CFactory::getConfig();

            // @rule: Spam checks
            if ($config->get('antispam_akismet_walls')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($message);
                $filter->setEmail($my->email);
                $filter->setURL(CRoute::_('index.php?option=com_community&view=videos&task=video&videoid=' . $uniqueId));
                $filter->setType('message');
                $filter->setIP($_SERVER['REMOTE_ADDR']);

                if ($filter->isSpam()) {
                    $response->addAlert(JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM'));
                    return $response->sendResponse();
                }
            }

            //CFactory::load( 'libraries' , 'wall' );
            $wall = CWallLibrary::saveWall($uniqueId, $message, 'videos', $my, ($my->id == $video->creator), 'videos,video');

            // Add activity logging
            $url = $video->getViewUri(false);

            $params = new CParameter('');
            $params->set('videoid', $uniqueId);
            $params->set('action', 'wall');
            $params->set('wallid', $wall->id);
            $params->set('video_url', $url);

            $act = new stdClass();
            $act->cmd = 'videos.wall.create';
            $act->actor = $my->id;
            $act->access = $video->permissions;
            $act->target = 0;
            $act->title = JText::sprintf('COM_COMMUNITY_VIDEOS_ACTIVITIES_WALL_POST_VIDEO', '{video_url}', $video->title);
            $act->app = 'videos.comment';
            $act->cid = $uniqueId;
            $act->params = $params->toString();

            CActivityStream::add($act);
            // Add notification
            //CFactory::load( 'libraries' , 'notification' );

            $params = new CParameter('');
            $params->set('url', $url);
            $params->set('message', $message);
            $params->set('video', $video->title);
            $params->set('video_url', $url);
            if ($my->id !== $video->creator) {
                CNotificationLibrary::add('videos_submit_wall', $my->id, $video->creator, JText::sprintf('COM_COMMUNITY_VIDEO_WALL_EMAIL_SUBJECT'), '', 'videos.wall', $params);
            } else {
                //for activity reply action
                //get relevent users in the activity
                $wallModel = CFactory::getModel('wall');
                $users = $wallModel->getAllPostUsers('videos', $video->id, $video->creator);
                if (!empty($users)) {
                    CNotificationLibrary::add('videos_reply_wall', $my->id, $users, JText::sprintf('COM_COMMUNITY_VIDEO_WALLREPLY_EMAIL_SUBJECT'), '', 'videos.wallreply', $params);
                }
            }

			// Add user points
            CUserPoints::assignPoint('videos.wall.create');

			// Log user engagement
			CEngagement::log('video.comment', $my->id);

            $response->addScriptCall('joms.walls.insert', $wall->content);
        }

        $response->addScriptCall('joms.jQuery("#wallContent .wall-comment-view-all-bottom a").html("' . JText::_('COM_COMMUNITY_VIEW_ALL') . ' ("+joms.jQuery("#wallContent .cComments").length+")");');
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));
        return $response->sendResponse();
    }

    public function ajaxRemoveWall($wallId) {
        require_once(JPATH_COMPONENT . '/libraries/activities.php');

        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        //CFactory::load( 'helpers' , 'owner' );

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        // Only allow wall removal by admin or owner of the video.
        $response = new JAXResponse();
        $wallsModel = $this->getModel('wall');
        $wall = $wallsModel->get($wallId);
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($wall->contentid);
        $my = CFactory::getUser();

        if (COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator)) {
            if ($wallsModel->deletePost($wallId)) {
                // Remove activity wall.
                CActivities::removeWallActivities(array('app' => 'videos', 'cid' => $wall->contentid, 'createdAfter' => $wall->date), $wallId);

                if ($wall->post_by != 0) {
                    //CFactory::load( 'libraries' , 'userpoints' );
                    CUserPoints::assignPoint('wall.remove', $wall->post_by);
                }
            } else {
                $response->addAlert(JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR'));
            }
        } else {
            $response->addAlert(JText::_('COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR'));
        }

        $response->addScriptCall('joms.jQuery("#wallContent .wall-comment-view-all-bottom a").html("' . JText::_('COM_COMMUNITY_VIEW_ALL') . ' ("+joms.jQuery("#wallContent .cComments").length+")");');
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_ACTIVITIES));
        return $response->sendResponse();
    }

    /**
     * Ajax method to display remove a video notice
     * @param int $id Video id
     * @param string $currentTask
     * @param int $userId
     * @return json
     */
    public function ajaxRemoveVideo($id, $currentTask, $userId) {
        $filter = JFilterInput::getInstance();
        $id = $filter->clean($id, 'int');
        $userId = $filter->clean($userId, 'int');
        $currentTask = $filter->clean($currentTask, 'string');

        if (!COwnerHelper::isRegisteredUser())
            return $this->ajaxBlockUnregister();

        $response = new JAXResponse();
        // Load models / libraries
        //CFactory::load( 'models' , 'videos' );
        $my = CFactory::getUser();
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($id);
        $content = '<div>' . JText::sprintf('COM_COMMUNITY_VIDEOS_REMOVE_VIDEO_CONFIRM', $video->title) . '</div>';
        $buttons = '<form class="reset-gap" name="jsform-videos-ajaxremovevideo" method="post" action="' . CRoute::_('index.php?option=com_community&view=videos&task=removevideo') . '">'
                . '<input type="submit" class="btn btn-primary pull-right" name="submit" value="' . JText::_('COM_COMMUNITY_YES_BUTTON') . '">'
                . '<input type="hidden" name="videoid" value="' . $video->id . '">'
                . '<input type="hidden" name="currentTask" value="' . $currentTask . '">'
                . '<input type="hidden" name="userid" value="' . $userId . '">'
                . '<input type="button" class="btn" onclick="cWindowHide()" value="' . JText::_('COM_COMMUNITY_NO_BUTTON') . '">'
                . '</form>';

        $response->addScriptCall('joms.jQuery("#cwin_logo").html("' . JText::_('COM_COMMUNITY_VIDEOS_DELETE_VIDEO') . '");');
        $response->addScriptCall('cWindowAddContent', $content, $buttons);
        //This is final
        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $response->sendResponse();
    }

    public function ajaxEditVideo($videoId, $redirectUrl = '') {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $redirectUrl = $filter->clean($redirectUrl, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $objResponse = new JAXResponse();

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $my = CFactory::getUser();

        $video->load($videoId);

        $group = JTable::getInstance('Group', 'CTable');
        // Load the group, based on video's groupid, NOT the url
        $group->load($video->groupid);

        if (COwnerHelper::isCommunityAdmin() || $video->creator == $my->id || $group->isAdmin($my->id)) {
            $model = CFactory::getModel('videos');
            $category = $model->getAllCategories();


            $cTree = CCategoryHelper::getCategories($category);
            $categoryHTML = CCategoryHelper::getSelectList('videos', $cTree, $video->category_id);

            $showPrivacy = $video->groupid != 0 ? false : true;
            $cWindowsHeight = $video->groupid != 0 ? 280 : 350;

            $redirectUrl = empty($redirectUrl) ? '' : base64_encode($redirectUrl);
            $config = CFactory::getConfig();


            $tmpl = new CTemplate();
            $tmpl->set('video', $video);
            $tmpl->set('showPrivacy', $showPrivacy);
            $tmpl->set('categoryHTML', $categoryHTML);
            $tmpl->set('redirectUrl', $redirectUrl);
            $tmpl->set('enableLocation', $config->get('videosmapdefault'));
            $contents = $tmpl->fetch('videos.edit');

            // Change cWindow title

            $action = '<input type="button" class="btn" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '" value="' . JText::_('COM_COMMUNITY_CANCEL_BUTTON') . '" />';
            $action .= '<input type="button" onclick="document.editvideo.submit();" class="btn btn-primary pull-right" name="' . JText::_('COM_COMMUNITY_SAVE_BUTTON') . '" value="' . JText::_('COM_COMMUNITY_SAVE_BUTTON') . '" />';


            $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_EDIT_VIDEO'));
            $objResponse->addScriptCall('cWindowAddContent', $contents, $action);
            $objResponse->addScriptCall('joms.privacy.init();');
        } else {
            $objResponse->addScriptCall('cWindowHide');
            $objResponse->addScriptCall('alert', JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION'));
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

        return $objResponse->sendResponse();
    }

    public function ajaxAddVideo($creatorType = VIDEO_USER_TYPE, $groupid = 0) {
        $filter = JFilterInput::getInstance();
        $groupid = $filter->clean($groupid, 'int');
        $creatorType = $filter->clean($creatorType, 'string');


        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }
        $my = CFactory::getUser();

        $objResponse = new JAXResponse();
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_ADD'));

        // @rule: Do not allow users to add more videos than they are allowed to
        $this->_checkUploadLimit();

        if ($creatorType != VIDEO_GROUP_TYPE) {
            $groupid = 0;
        }

        if ($creatorType == VIDEO_GROUP_TYPE) {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupid);
            $isBanned = $group->isBanned($my->id);

            if ($isBanned) {
                $objResponse->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_GROUPS_VIDEO_BANNED'));
                return $objResponse->sendResponse();
            }
        }

        $config = CFactory::getConfig();

        $videoUpload = '';
        $linkUpload = $this->getLinkVideoHtml($creatorType, $groupid);

        if ($config->get('enablevideosupload')) {
            $videoUpload = $this->getUploadVideoHtml($creatorType, $groupid);
        }

        $uploadLimit = $config->get('maxvideouploadsize', ini_get('upload_max_filesize'));

        $tmpl = new CTemplate();
        $tmpl->set('enableVideoUpload', $config->get('enablevideosupload'));
        $tmpl->set('uploadLimit', $uploadLimit);
        $tmpl->set('linkUploadHtml', $linkUpload);
        $tmpl->set('videoUploadHtml', $videoUpload);
        $tmpl->set('creatorType', $creatorType);
        $tmpl->set('groupid', $groupid);
        $html = $tmpl->fetch('videos.add');

        $objResponse->addScriptCall('cWindowAddContent', $html);


        return $objResponse->sendResponse();
    }

    public function ajaxLinkVideo($creatorType = VIDEO_USER_TYPE, $groupid = 0) {
        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $html = $this->getLinkVideoHtml($creatorType, $groupid);
        $action = '<button class="btn" onclick="joms.videos.submitLinkVideo();">' . JText::_('COM_COMMUNITY_VIDEOS_LINK') . '</button>';

        $objResponse = new JAXResponse();
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_LINK'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $action);
        $objResponse->addScriptCall('joms.privacy.init();');

        return $objResponse->sendResponse();
    }

    public function getLinkVideoHtml($creatorType = VIDEO_USER_TYPE, $groupid = 0) {
        $filter      = JFilterInput::getInstance();
        $creatorType = $filter->clean($creatorType, 'string');
        $groupid     = $filter->clean($groupid, 'int');

        $user        = CFactory::getRequestUser();
        $params      = $user->getParams();
        $permissions = $params->get('privacyVideoView');

        $model       = CFactory::getModel('videos');
        $category    = $model->getAllCategories();


        $cTree = CCategoryHelper::getCategories($category);
        $list['category'] = CCategoryHelper::getSelectList('videos', $cTree);

        $config = CFactory::getConfig();
        list($totalVideos, $videoUploadLimit) = $this->_getParameter($creatorType, $config);

        //CFactory::load( 'libraries' , 'privacy' );
        $tmpl = new CTemplate();
        $tmpl->set('list', $list);
        $tmpl->set('creatorType', $creatorType);
        $tmpl->set('groupid', $groupid);
        $tmpl->set('videoUploaded', $totalVideos);
        $tmpl->set('permissions', $permissions);
        $tmpl->set('videoUploadLimit', $videoUploadLimit);
        $tmpl->set('enableLocation', $config->get('videosmapdefault'));

        $html = $tmpl->fetch('videos.link');
        return $html;
    }

    public function ajaxLinkVideoPreview($videoUrl) {
        $filter = JFilterInput::getInstance();
        $videoUrl = $filter->clean($videoUrl, 'string');

        $objResponse = new JAXResponse();

        if (!JRequest::checkToken()) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_INVALID_TOKEN'));
            $objResponse->sendResponse();
        }

        $config = CFactory::getConfig();

        if (!$config->get('enablevideos')) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_VIDEOS_DISABLED'));
            $objResponse->sendResponse();
        }



        $my = CFactory::getUser();

        // @rule: Do not allow users to add more videos than they are allowed to
        // rule removed in 3.2
        if (CLimitsLibrary::exceedDaily('videos')) {
            //$objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED'));
           // $objResponse->sendResponse();
        }

        // Without CURL library, there's no way get the video information
        // remotely
        if (!CRemoteHelper::curlExists()) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
            $objResponse->sendResponse();
        }

        // Determine if the video belongs to group or user and
        // assign specify value for checking accordingly
        $creatorType = VIDEO_USER_TYPE;

        $videoLimit = $config->get('videouploadlimit');

        if (CLimitsHelper::exceededVideoUpload($my->id, $creatorType)) {
            $objResponse->addScriptCall('__throwError', JText::sprintf('COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit));
            $objResponse->sendResponse();
        }

        // Create the video object and save
        if (empty($videoUrl)) {
            $objResponse->addScriptCall('__throwError', JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_LINKS'));
            $objResponse->sendResponse();
        }


        $videoLib = new CVideoLibrary();

        $video = JTable::getInstance('Video', 'CTable');
        $isValid = $video->init($videoUrl);

        if (!$isValid) {
            $objResponse->addScriptCall('__throwError', $video->getProvider()->getError());
            $objResponse->sendResponse();
        }

        $video->set('creator', $my->id);
        $video->set('creator_type', $creatorType);
        $video->set('category_id', 1);
        $video->set('status', 'temp');

        if (!$video->store()) {
            $objResponse->addScript('__throwError', JText::_('COM_COMMUNITY_VIDEOS_ADD_LINK_FAILED'));
            $objResponse->sendResponse();
        }

        // Fetch the thumbnail and store it locally,
        // else we'll use the thumbnail remotely
        CError::assert($video->thumb, '', '!empty');
        $this->_fetchThumbnail($video->id);



        $model = CFactory::getModel('videos');
        $category = $model->getAllCategories();


        $cTree = CCategoryHelper::getCategories($category);
        $categoryHTML = CCategoryHelper::getSelectList('videos', $cTree, null, true, true);

        $tmpl = new CTemplate();
        $tmpl->set('video', $video);
        $tmpl->set('videoThumbWidth', CVideoLibrary::thumbSize('width'));
        $tmpl->set('videoThumbHeight', CVideoLibrary::thumbSize('height'));
        $tmpl->set('categoryHTML', $categoryHTML);
        $html = $tmpl->fetch('status.video.item');

        $attachment = new stdClass();
        $attachment->video = $video;
        $attachment->category = $category;

        $objResponse->addScriptCall('__callback', $attachment);

        $objResponse->sendResponse();
    }

    public function ajaxUploadVideo($creatorType = VIDEO_USER_TYPE, $groupid = 0) {
        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        // @rule: Do not allow users to add more videos than they are allowed to
        $this->_checkUploadLimit();

        $html = $this->getUploadVideoHtml($creatorType, $groupid);

        $action = '<button class="btn btn-primary" onclick="joms.videos.submitUploadVideo();">' . JText::_('COM_COMMUNITY_VIDEOS_UPLOAD') . '</button>';

        $objResponse = new JAXResponse();
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_VIDEOS_UPLOAD'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $action);
        $objResponse->addScriptCall('joms.privacy.init();');

        return $objResponse->sendResponse();
    }

    public function getUploadVideoHtml($creatorType = VIDEO_USER_TYPE, $groupid = 0) {
        $filter = JFilterInput::getInstance();
        $creatorType = $filter->clean($creatorType, 'string');
        $groupid = $filter->clean($groupid, 'int');
        $my = CFactory::getUser();
        $user = CFactory::getRequestUser();
        $params = $user->getParams();
        $permissions = $params->get('privacyVideoView');

        $model = CFactory::getModel('videos');
        $category = $model->getAllCategories();


        $cTree = CCategoryHelper::getCategories($category);
        $list['category'] = CCategoryHelper::getSelectList('videos', $cTree);

        $config = CFactory::getConfig();
        $uploadLimit = $config->get('maxvideouploadsize', ini_get('upload_max_filesize'));

        list($totalVideos, $videoUploadLimit) = $this->_getParameter($creatorType, $config);

        $tmpl = new CTemplate();
        $tmpl->set('list', $list);
        $tmpl->set('uploadLimit', $uploadLimit);
        $tmpl->set('creatorType', $creatorType);
        $tmpl->set('groupid', $groupid);
        $tmpl->set('permissions', $permissions);
        $tmpl->set('videoUploaded', $totalVideos);
        $tmpl->set('videoUploadLimit', $videoUploadLimit);
        $tmpl->set('enableLocation', $config->get('videosmapdefault'));

        $html = $tmpl->fetch('videos.upload');
        return $html;
    }

    /**
     * Display searching form for videos
     * */
    public function search() {
        $config = CFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $my = JFactory::getUser();

        if ($my->id == 0 && !$config->get('enableguestsearchvideos')) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'), 'notice');
            return $this->blockUnregister();
        }

        $this->checkVideoAccess();
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = JRequest::getCmd('view', $this->getName());
        $view = $this->getView($viewName, '', $viewType);

        echo $view->get(__FUNCTION__);
    }

    public function _fetchThumbnail($id = 0, $returnThumb = false) {
        if (!COwnerHelper::isRegisteredUser())
            return;
        if (!$id)
            return false;


        $table = JTable::getInstance('Video', 'CTable');
        $table->load($id);



        $config = CFactory::getConfig();

        if ($table->type == 'file') {
            // We can only recreate the thumbnail for local video file only
            // it's not possible to process remote video file with ffmpeg
            if ($table->storage != 'file') {
                $this->setError(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . 'FFmpeg cannot process remote video.');
                return false;
            }

            $videoLib = new CVideoLibrary();

            $videoFullPath = JPATH::clean(JPATH_ROOT . '/' . $table->path);
            if (!JFile::exists($videoFullPath)) {
                return false;
            }

            // Read duration
            $videoInfo = $videoLib->getVideoInfo($videoFullPath);

            if (!$videoInfo) {
                return false;
            } else {
                $videoFrame = CVideosHelper::formatDuration((int) ($videoInfo['duration']['sec'] / 2), 'HH:MM:SS');

                // Create thumbnail
                $oldThumb = $table->thumb;
                $thumbFolder = CVideoLibrary::getPath($table->creator, 'thumb');
                $thumbSize = CVideoLibrary::thumbSize();
                $thumbFilename = $videoLib->createVideoThumb($videoFullPath, $thumbFolder, $videoFrame, $thumbSize);
            }

            if (!$thumbFilename) {
                return false;
            }
        } else {

            if (!CRemoteHelper::curlExists()) {
                $this->setError(JText::_('COM_COMMUNITY_CURL_NOT_EXISTS'));
                return false;
            }

            $videoLib = new CVideoLibrary();
            $videoObj = $videoLib->getProvider($table->path);
            if ($videoObj == false) {
                $this->setError($videoLib->getError());
                return false;
            }
            if (!$videoObj->isValid()) {
                $this->setError($videoObj->getError());
                return false;
            }

            $remoteThumb = $videoObj->getThumbnail();
            $thumbData = CRemoteHelper::getContent($remoteThumb, true);

            if (empty($thumbData)) {
                $this->setError(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . $remoteThumb);
                return false;
            }

            // split the header and body
            list($headers, $body) = explode("\r\n\r\n", $thumbData, 2);
            preg_match('/Content-Type: image\/(.*)/i', $headers, $matches);

            if (!empty($matches)) {
                $thumbPath = CVideoLibrary::getPath($table->creator, 'thumb');
                $thumbFileName = CFileHelper::getRandomFilename($thumbPath);
                $tmpThumbPath = $thumbPath . '/' . $thumbFileName;
                if (!JFile::write($tmpThumbPath, $body)) {
                    $this->setError(JText::_('COM_COMMUNITY_INVALID_FILE_REQUEST') . ': ' . $thumbFileName);
                    return false;
                }

                // We'll remove the old or none working thumbnail after this
                $oldThumb = $table->thumb;

                // Get the image type first so we can determine what extensions to use
                $info = getimagesize($tmpThumbPath);
                $mime = image_type_to_mime_type($info[2]);
                $thumbExtension = CImageHelper::getExtension($mime);

                $thumbFilename = $thumbFileName . $thumbExtension;
                $thumbPath = $thumbPath . '/' . $thumbFilename;
                if (!JFile::move($tmpThumbPath, $thumbPath)) {
                    $this->setError(JText::_('WARNFS_ERR02') . ': ' . $thumbFileName);
                    return false;
                }

                // Resize the thumbnails
                //CImageHelper::resizeProportional( $thumbPath , $thumbPath , $mime , CVideoLibrary::thumbSize('width') , CVideoLibrary::thumbSize('height') );

                list($width, $height) = explode('x', $config->get('videosThumbSize'));
                CImageHelper::resizeAspectRatio($thumbPath, $thumbPath, $width, $height);
            } else {
                $this->setError(JText::_('COM_COMMUNITY_PHOTOS_IMAGE_NOT_PROVIDED_ERROR'));
                return false;
            }
        }

        // Update the DB with new thumbnail
        $thumb = $config->get('videofolder') . '/'
                . VIDEO_FOLDER_NAME . '/'
                . $table->creator . '/'
                . VIDEO_THUMB_FOLDER_NAME . '/'
                . $thumbFilename;

        $table->set('thumb', $thumb);
        $table->store();

        // If this video storage is not on local, we move it to remote storage
        // and remove the old thumb if existed
        if (($table->storage != 'file')) { // && ($table->storage == $storageType))
            $config = CFactory::getConfig();
            $storageType = $config->getString('videostorage');

            $storage = CStorage::getStorage($storageType);
            $storage->delete($oldThumb);

            $localThumb = JPATH::clean(JPATH_ROOT . '/' . $table->thumb);
            $tempThumbname = JPATH::clean(JPATH_ROOT . '/' . md5($table->thumb));
            if (JFile::exists($localThumb)) {
                JFile::copy($localThumb, $tempThumbname);
            }
            if (JFile::exists($tempThumbname)) {
                $storage->put($table->thumb, $tempThumbname);
                JFile::delete($localThumb);
                JFile::delete($tempThumbname);
            }
        } else {
            if (JFile::exists(JPATH_ROOT . '/' . $oldThumb)) {
                JFile::delete(JPATH_ROOT . '/' . $oldThumb);
            }
        }


        if ($returnThumb) {
            return $table->getThumbnail();
        }
        return true;
    }

    /**
     * Delete video's wall
     *
     * @param    int        $id        The id of the video
     * @return    True on success
     * @since    1.2
     * */
    private function _deleteVideoWalls($id = 0) {


        if (!COwnerHelper::isRegisteredUser())
            return;
        $video = CFactory::getModel('Videos');
        $video->deleteVideoWalls($id);
    }

    /**
     * Delete video's activity stream
     *
     * @params    int        $id        The video id
     * @return    True on success
     * @since    1.2
     *
     * */
    private function _deleteVideoActivities($id = 0) {
        if (!COwnerHelper::isRegisteredUser())
            return;
        $video = CFactory::getModel('Videos');
        $video->deleteVideoActivities($id);
    }

    /**
     * Delete video's files and thumbnails
     *
     * @params    object    $video    The video object
     * @return    True on success
     * @since    1.2
     *
     * */
    private function _deleteVideoFiles($video) {
        // We passed in the video object because of
        // the table row of $video->id coud be deleted
        // thus, there's no way to retrive the thumbnail path
        // and also the flv file path
        if (!$video)
            return;
        if (!COwnerHelper::isRegisteredUser())
            return;


        $storage = CStorage::getStorage($video->storage);

        if ($storage->exists($video->thumb)) {
            $storage->delete($video->thumb);
        }

        if ($storage->exists($video->path)) {
            $storage->delete($video->path);
        }
        /*
          jimport('joomla.filesystem.file');
          $files		= array();

          $thumb	= JPATH::clean(JPATH_ROOT .'/'. $video->thumb);
          if (JFile::exists( $thumb ))
          {
          $files[]= $thumb;
          }

          if ($video->type == 'file')
          {
          $flv	= JPATH::clean(JPATH_ROOT .'/'. $video->path);
          if (JFile::exists($flv))
          {
          $files[]= $flv;
          }
          }

          if (!empty($files))
          {
          return JFile::delete($files);
          }
         */

        return true;
    }

    /**
     * Delete featured videos
     *
     * @param    int        $id        The id of the video
     * @return    True on success
     * @since    1.2
     * */
    private function _deleteFeaturedVideos($id = 0) {
        if (!COwnerHelper::isRegisteredUser())
            return;


        $featuredVideo = new CFeatured(FEATURED_VIDEOS);
        $featuredVideo->delete($id);

        return;
    }

    /**
     * Delete profile video
     *
     * @param    int        $creator        The id of the video creator
     * @return    True on success or unsuccess
     * */
    private function _deleteProfileVideo($creator, $deletedvideoid) {
        if (!COwnerHelper::isRegisteredUser())
            return;

        // Only the video creator can use the video as his/her profile video
        $user = CFactory::getUser($creator);

        // Set params to default(0 for no profile video)
        $params = $user->getParams();

        $videoid = $params->get('profileVideo', 0);

        // Check if the current profile video id same with the deleted video id
        if ($videoid == $deletedvideoid) {
            $params->set('profileVideo', 0);
            $user->save('params');
        }

        return;
    }

    public function streamer() {
        $document = JFactory::getDocument();
        $document->setType('raw');
        $document->setMimeEncoding('video/x-flv');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $table = JTable::getInstance('Video', 'CTable');
        if (!$table->load($jinput->get('vid', NULL, 'NONE'))) {
            $this->setError($table->getError());
            return false;
        }

        $pos = JRequest::getInt('target', 0);

        $file = CString::str_ireplace('/', '\\', JPATH_ROOT . '/' . $table->path);
        if (!JFile::exists($file))
            return 'video file not found.';

        $fileName = JFile::getName($file);
        $fileSize = filesize($file) - (($pos > 0) ? $pos + 1 : 0);

        $fh = fopen($file, 'rb') or die('cannot open file: ' . $file);
        $fileSize = filesize($file) - (($pos > 0) ? $pos + 1 : 0);
        fseek($fh, $pos);

        $binary_header = strtoupper(JFile::getExt($file)) . pack('C', 1) . pack('C', 1) . pack('N', 9) . pack('N', 9);

        $contentLength = ($pos > 0) ? $fileSize + 13 : $fileSize;

        /*
          session_cache_limiter('none');
          JResponse::clearHeaders(); /*
          JResponse::setHeader( 'Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true );
          JResponse::setHeader( 'Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT', true );
          //JResponse::setHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true );
          //JResponse::setHeader( 'Pragma', 'no-cache', true );
          //JResponse::setHeader( 'Content-Disposition', 'attachment; filename="'.$fileName.'"', true);
          JResponse::setHeader( 'Content-Length', ($pos > 0) ? $fileSize + 13 : $fileSize, true );
          JResponse::setHeader( 'Content-Type', 'video/x-flv', true );
          JResponse::sendHeaders();
         */

        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Length: ' . $contentLength);
        //header('Connection: close');
        header('Content-Type: video/x-flv; name=' . $filename);
        //header('Cache-Control: store, cache');
        //header('Pragma: cache');


        if ($pos > 0) {
            print $binary_header;
        }

        $limit_bw = true;
        $packet_size = 90 * 1024;
        $packet_interval = 0.3;

        while (!feof($fh)) {
            if (!$limit_bw) {
                print(fread($fh, filesize($file)));
            } else {
                $time_start = microtime(true);
                print(fread($fh, $packet_size));
                $time_stop = microtime(true);
                $time_difference = $time_stop - $time_start;
                if ($time_difference < $packet_interval) {
                    usleep($packet_interval * 1000000 - $time_difference * 1000000);
                }
            }
        }
    }

    public function ajaxSetVideoCategory($videoId, $catId) {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $catId = $filter->clean($catId, 'int');

        $response = new JAXResponse();

        $my = CFactory::getUser();

        //CFactory::load( 'models' , 'videos' );
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($videoId);

        $video->category_id = $catId;
        $video->store();

        return $response->sendResponse();
    }

    // check group id and return the creator type and it's upload number limit
    private function _manipulateParameter($groupid, $config) {
        if (empty($groupid)) {
            $creatorType = VIDEO_USER_TYPE;
            $videoLimit = $config->get('videouploadlimit');
        } else {

            $allowManageVideos = CGroupHelper::allowManageVideo($groupid);
            CError::assert($allowManageVideos, '', '!empty', __FILE__, __LINE__);

            $creatorType = VIDEO_GROUP_TYPE;
            $videoLimit = $config->get('groupvideouploadlimit');
        }

        return array($creatorType, $videoLimit);
    }

    // check creator type and return total uploaded number and limit per user
    private function _getParameter($creatorType, $config) {
        $model = CFactory::getModel('videos');
        $my = CFactory::getUser();
        if ($creatorType != VIDEO_GROUP_TYPE) {
            $groupid = 0;
            $totalVideos = $model->getVideosCount($my->id, VIDEO_USER_TYPE);
            $videoUploadLimit = $config->get('videouploadlimit');
        } else {
            $totalVideos = $model->getVideosCount($my->id, VIDEO_GROUP_TYPE);
            $videoUploadLimit = $config->get('groupvideouploadlimit');
        }

        return array($totalVideos, $videoUploadLimit);
    }

    private function _checkUploadLimit() {
        //CFactory::load( 'libraries' , 'limits' );

        if (CLimitsLibrary::exceedDaily('videos')) {
            $actions = '<form method="post" action="" style="float:right;">';
            $actions .= '<input type="button" class="btn" onclick="cWindowHide();return false;" name="cancel" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '" />';
            $actions .= '</form>';
            $html = JText::_('COM_COMMUNITY_VIDEOS_LIMIT_REACHED');

            $objResponse = new JAXResponse();
            $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

            return $objResponse->sendResponse();
        }
    }

    public function ajaxShowStreamVideoWindow($activity_id){
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();

        $allowToView = true;
        if($activity_id == ''){
            $allowToView = false;
        }

        $activityTable = JTable::getInstance('Activity', 'CTable');

        if(!$activityTable->load($activity_id)){
            $allowToView = false;
        }

        /* === Start Premission Checking === */
        $user = CFactory::getUser($activityTable->actor);
        $blocked = $user->isBlocked();

        if ($blocked && !COwnerHelper::isCommunityAdmin()) {
            $allowToView = false;
        }

        $activity = new CActivity($activityTable);

        $headMetas = $activity->getParams('headMetas');

        $headMetaParams = '';
        /* We do convert into JRegistry to make it easier to use */
        if ($headMetas) {
            $headMetaParams = new JRegistry($headMetas);
        }

        $providerName	= JString::strtolower($headMetaParams->get('video_provider'));

        if($headMetaParams->get('type') == 'video' && $providerName != ''){
            $libraryPath	= COMMUNITY_COM_PATH .'/libraries/videos' .'/'. $providerName . '.php';
            require_once($libraryPath);
            $className		 = 'CTableVideo' . JString::ucfirst($providerName);
            $provider = new $className();
        }

        if (!CPrivacy::isAccessAllowed($my->id, $activityTable->actor, 'custom', $activityTable->access)) {
            switch ($activityTable->access) {
                case '40':
                    $allowToView = false;
                    break;
                case '30':
                    $allowToView = false;
                    break;
                default:
                    $allowToView = false;
                    break;
            }
        }

        $objResponse->addScriptCall('cWindowShow', '', '', 640, 360);

        if($allowToView){
            $objResponse->addScriptCall('cWindowAddContent', $provider->getViewHTML($headMetaParams->get('video_id'),640,360));
        }else{
            $objResponse->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_VIDEO_UNABLE_VIEW'));
        }


        $objResponse->sendResponse();

    }

    public function ajaxShowVideoWindow($video_id) {
        $objResponse = new JAXResponse();

        $allowToView = true; //determine the view premission
        $my = CFactory::getUser();

        /*
          echo $tmpl	->setMetaTags( 'video'	, $video )
          ->set('likesHTML'		, $likesHTML )
          ->set('redirectUrl'	, $redirectUrl )
          ->set('wallForm' 		, $wallForm)
          ->set('wallContent' 	, $wallContent)
          ->set('bookmarksHTML'	, $bookmarksHTML)
          ->set('reportHTML' 	, $reportHTML)
          ->set('video' 		, $video)
          ->fetch('videos.video');

         */
        //$notiHtml = '<div style="width: 640px; height: 360px; display: block; margin: 0pt auto;" id="player"><object width="100%" height="100%" type="application/x-shockwave-flash" data="http://localhost/dev/components/com_community/assets/flowplayer/flowplayer-3.2.7.swf" id="player_api"><param value="true" name="allowfullscreen"><param value="always" name="allowscriptaccess"><param value="high" name="quality"><param value="false" name="cachebusting"><param value="#000000" name="bgcolor"><param value="opaque" name="wmode"><param value="config={&quot;streamingServer&quot;:&quot;lighttpd&quot;,&quot;playlist&quot;:[{&quot;url&quot;:&quot;http://localhost/dev/images/videos/42/thumbs/7tsI5DlnBfn.jpg&quot;,&quot;scaling&quot;:&quot;scale&quot;},{&quot;url&quot;:&quot;http://localhost/dev/components/com_community/libraries/streamer.php/aW1hZ2VzL3ZpZGVvcy80Mi95bTZpZjFYNnB6ay5mbHY= quot;,&quot;title&quot;:&quot;aaaa&quot;,&quot;autoPlay&quot;:false,&quot;autoBuffering&quot;:true,&quot;provider&quot;:&quot;lighttpd&quot;,&quot;scaling&quot;:&quot;scale&quot;}],&quot;plugins&quot;:{&quot;lighttpd&quot;:{&quot;url&quot;:&quot;http://localhost/dev/components/com_community/assets/flowplayer/flowplayer.pseudostreaming-3.2.7.swf&quot;,&quot;queryString&quot;:&quot;%3Ftarget%3D%24%7Bstart%7D&quot;},&quot;controls&quot;:{&quot;url&quot;:&quot;http://localhost/dev/components/com_community/assets/flowplayer/flowplayer.controls-3.2.5.swf&quot;}},&quot;playerId&quot;:&quot;player&quot;,&quot;clip&quot;:{}}" name="flashvars"></object></div>';

        $video = JTable::getInstance('Video', 'CTable');

        if (!$video->load($video_id)) {
            $allowToView = false;
        }

        /* === Start Premission Checking === */
        $user = CFactory::getUser($video->creator);
        $blocked = $user->isBlocked();

        if ($blocked && !COwnerHelper::isCommunityAdmin()) {
            $allowToView = false;
        }

        if ($video->creator_type == VIDEO_GROUP_TYPE) {
            //CFactory::load( 'helpers' , 'group' );

            if (!CGroupHelper::allowViewMedia($video->groupid)) {
                $allowToView = false;
            }
        } else {
            if (!CPrivacy::isAccessAllowed($my->id, $video->creator, 'custom', $video->permissions)) {
                switch ($video->permissions) {
                    case '40':
                        $allowToView = false;
                        break;
                    case '30':
                        $allowToView = false;
                        $this->noAccess(JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName()));
                        break;
                    default:
                        $allowToView = false;
                        break;
                }
            }
        }
        if ( $video->published == 0 )
            $allowToView = false;
        /* === End Permission Checking === */

        if ($allowToView) {
            // Hit counter + 1
            $video->hit();

            $notiHtml = '<div class="cVideo-Player video-player">
							' . $video->getPlayerHTML() . '
						</div>';
        } else {
            $notiHtml = JText::_('COM_COMMUNITY_VIDEO_UNABLE_VIEW');
        }

        //to get the width and height of the iframe
        preg_match('/< *[^>]*width *= *["\']?([^"\']*)/i', $notiHtml, $width);
        preg_match('/< *[^>]*width *= *["\']?([^"\']*)/i', $notiHtml, $height);

        //to match the window dimension with the iframe
        if ($height) {
            if ($height[1] > 0 && $width[1] > 0) {
                $objResponse->addScriptCall('cWindowShow', '', '', (int) $width[1] + 60, (int) $height[1]);
            }
        } else {
            $objResponse->addScriptCall('cWindowShow', '', '', $video->_width + 60, $video->_height);
        }

        $objResponse->addScriptCall('cWindowAddContent', $notiHtml);

        $objResponse->sendResponse();
    }

    public function ajaxShowVideoFeatured($video_id) {
        $objResponse = new JAXResponse();

        $allowToView = true; //determine the view premission
        $my = CFactory::getUser();
        $video = JTable::getInstance('Video', 'CTable');

        if (!$video->load($video_id)) {
            $allowToView = false;
        }

        /* === Start Premission Checking === */
        $user = CFactory::getUser($video->creator);
        $blocked = $user->isBlocked();

        if ($blocked && !COwnerHelper::isCommunityAdmin()) {
            $allowToView = false;
        }

        if ($video->creator_type == VIDEO_GROUP_TYPE) {
            //CFactory::load( 'helpers' , 'group' );

            if (!CGroupHelper::allowViewMedia($video->groupid)) {
                $allowToView = false;
            }
        } else {

            if (!CPrivacy::isAccessAllowed($my->id, $video->creator, 'custom', $video->permissions)) {
                switch ($video->permissions) {
                    case '40':
                        $allowToView = false;
                        break;
                    case '30':
                        $allowToView = false;
                        $this->noAccess(JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName()));
                        break;
                    default:
                        $allowToView = false;
                        break;
                }
            }
        }

        /* === End Permission Checking === */

        if ($allowToView) {
            // Hit counter + 1
            $video->hit();
            $notiHtml = '<div class="cVideo-Player video-player">
							' . $video->getPlayerHTML() . '
						</div>';
        } else {
            $notiHtml = JText::_('COM_COMMUNITY_VIDEO_UNABLE_VIEW');
        }

        // Get like
        //CFactory::load( 'libraries' , 'like' );
        $likes = new CLike();
        $likesHTML = $likes->getHTML('videos', $video->id, $my->id);
        // Get wall count
        //CFactory::load( 'libraries' , 'wall' );
        $wallCount = CWallLibrary::getWallCount('videos', $video->id);
        // Get video link
        $videoCommentLink = CRoute::_('index.php?option=com_community&view=videos&task=video&videoid=' . $video->id . '&groupid=' . $video->groupid . '&userid=' . $video->creator . '#comments');
        $videoLink = CRoute::_('index.php?option=com_community&view=videos&task=video&videoid=' . $video->id . '&groupid=' . $video->groupid . '&userid=' . $video->creator);
        $creatorName = $video->getCreatorName();
        $creatorLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $video->creator);

        $objResponse->addScriptCall('updatePlayer', $notiHtml, $video->title, $likesHTML, $video->getHits(), $wallCount, $videoLink, $videoCommentLink, $creatorName, $creatorLink);
        $objResponse->sendResponse();
    }

    public function ajaxAddVideoTag($videoId, $userId) {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $userId = $filter->clean($userId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $response = new JAXResponse();
        $my = CFactory::getUser();
        $videoModel = CFactory::getModel('videos');
        $tagging = new CVideoTagging();

        $video = JTable::getInstance('Video', 'CTable');
        $video->load($videoId);

        $user = CFactory::getUser($userId);
        if (empty($video->id) || !$video->tagAllow($my->id)) {
            $html = JText::_('COM_COMMUNITY_VIDEO_TAGGING_INVALID_VIDEO');
            $response->addScriptCall('cWindowShow', '', JText::_('COM_COMMUNITY_NOTICE'), 450, 200, 'warning');
            $response->addScriptCall('cWindowAddContent', $html, $actions);
        } else if (empty($userId) || empty($user->id)) {
            $html = JText::_('COM_COMMUNITY_VIDEO_TAGGING_INVALID_USER');
            $response->addScriptCall('cWindowShow', '', JText::_('COM_COMMUNITY_NOTICE'), 450, 200, 'warning');
            $response->addScriptCall('cWindowAddContent', $html, $actions);
        } else {
            $tag = new stdClass();
            $tag->videoId = $videoId;
            $tag->userId = $userId;

            $tagId = $tagging->addTag($tag);

            $jsonString = '{}';
            if ($tagId > 0) {
                $jsonString = '{'
                        . 'id:' . $tagId . ','
                        . 'videoId:' . $videoId . ','
                        . 'userId:' . $userId . ','
                        . 'displayName:\'' . addslashes($user->getDisplayName()) . '\','
                        . 'profileUrl:\'' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId, false) . '\','
                        . 'canRemove:true'
                        . '}';
                $response->addScriptCall('joms.videos.addVideoTextTag', $jsonString, JText::_('COM_COMMUNITY_REMOVE'));
                $response->addScriptCall('cWindowHide');

                //send notification emails
                if ($video->groupid) {
                    $url = 'index.php?option=com_community&view=videos&task=video&groupid=' . $video->groupid . '&videoid=' . $video->id;
                } else {
                    $url = 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id;
                }

                if ($my->id != $userId) {
                    // Add notification
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('video', $video->title);
                    $params->set('video_url', $url);

                    CNotificationLibrary::add('videos_tagging', $my->id, $userId, JText::sprintf('COM_COMMUNITY_VIDEO_TAG_YOU'), '', 'videos.tagging', $params);
                }
            } else {
                $html = $tagging->getError();
                $actions = '<button class="btn" onclick="cWindowHide();joms.gallery.cancelNewPhotoTag();" name="close">' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '</button>';

                //remove the existing cwindow (for friend selection)
                $response->addScriptCall('joms.jQuery(\'#cWindow\').remove();');

                //recreate the warning cwindow
                $response->addScriptCall('cWindowShow', '', JText::_('COM_COMMUNITY_NOTICE'), 450, 200, 'warning');
                $response->addScriptCall('cWindowAddContent', $html, $actions);
            }
        }
        return $response->sendResponse();
    }

    public function ajaxRemoveVideoTag($videoId, $userId) {
        $filter = JFilterInput::getInstance();
        $videoId = $filter->clean($videoId, 'int');
        $userId = $filter->clean($userId, 'int');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $response = new JAXResponse();

        if (!$my->authorise('community.remove', 'videos.tag.' . $videoId)) {
            $response->addScriptCall('alert', JText::_('ACCESS FORBIDDEN'));
            return $response->sendResponse();
        }


        $tagging = new CVideoTagging();

        if (!$tagging->removeTag($videoId, $userId)) {
            $html = $tagging->getError();

            $response->addScriptCall('cWindowShow', '', JText::_('COM_COMMUNITY_NOTICE'), 450, 200, 'warning');
            $response->addAssign('cWindowAddContent', $html);
        }

        return $response->sendResponse();
    }

    /**
     * [ajaxCheckFileSize description]
     * @param  [iint] $fileSize [uploaded file size]
     * @return [type]           [description]
     */
    public function ajaxCheckFileSize($fileSize) {
        $filter = JFilterInput::getInstance();
        $fileSize = $filter->clean($fileSize, 'int');
        $maxPost = (int) (ini_get('post_max_size'));
        $response = new JAXResponse();

        // Convert to Mb
        $fileSize = (int) (round($fileSize / 1048576));

        if ($fileSize > $maxPost) {
            $response->addScriptCall('alert', 'File size exceeded post_max_size');
        } else {
            $response->addScriptCall('joms.jQuery(\'form#uploadVideo button\').prop("disabled", false);');
        }

        return $response->sendResponse();
    }

    /**
     * Edit video comments
     * @param int $wallId Wall id
     * @return bool
     */
    public function editVideoWall($wallId) {
        $my = CFactory::getUser();
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);
        return $my->authorise('community.edit', 'photos.wall.' . $wallId, $wall);
    }

}