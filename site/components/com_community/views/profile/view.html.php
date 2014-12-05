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
jimport('joomla.application.component.view');

if (!class_exists('CommunityViewProfile')) {

    class CommunityViewProfile extends CommunityView {

        private function _addSubmenu() {
            $config = CFactory::getConfig();

            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=uploadAvatar', JText::_('COM_COMMUNITY_PROFILE_AVATAR_EDIT'));

            if ($config->get('enableprofilevideo')) {
                $this->addSubmenuItem('index.php?option=com_community&view=profile&task=linkVideo', JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'));
            }

            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=edit', JText::_('COM_COMMUNITY_PROFILE_EDIT'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=preferences', JText::_('COM_COMMUNITY_EDIT_PREFERENCES'));
            $this->addSubmenuItem('index.php?option=com_community&view=profile&task=notifications', JText::_('COM_COMMUNITY_PROFILE_NOTIFICATIONS'));

            if ($config->get('profile_deletion')) {
                $this->addSubmenuItem('index.php?option=com_community&view=profile&task=deleteProfile', JText::_('COM_COMMUNITY_DELETE_PROFILE'), '', SUBMENU_RIGHT);
            }
        }

        /**
         * Return friends html block
         * @since 2.2.4
         * @return string
         */
        public function modGetFriendsHTML($userid = null) {
            $html = '';
            $tmpl = new CTemplate ( );

            $friendsModel = CFactory::getModel('friends');

            $my = CFactory::getUser($userid);
            $user = CFactory::getRequestUser();

            $params = $user->getParams();

            // site visitor
            $relation = 10;

            // site members
            if ($my->id != 0)
                $relation = 20;

            // friends
            if (CFriendsHelper::isConnected($my->id, $user->id))
                $relation = 30;

            // mine
            if (COwnerHelper::isMine($my->id, $user->id))
                $relation = 40;

            // @todo: respect privacy settings
            if ($relation >= $params->get('privacyFriendsView')) {
                $friends = $friendsModel->getFriends($user->id, 'latest', false, '', PROFILE_MAX_FRIEND_LIMIT + PROFILE_MAX_FRIEND_LIMIT);

                // randomize the friend count
                if ($friends)
                    shuffle($friends);

                $html = $tmpl->setRef('friends', $friends)
                        ->set('total', $user->getFriendCount())
                        ->setRef('user', $user)
                        ->fetch('profile.friends');
            }

            return $html;
        }

        public function modGetFriendsFeaturedHTML($userid = null) {
            $html = '';
            $tmpl = new CTemplate ( );

            $friendsModel = CFactory::getModel('friends');

            $my = CFactory::getUser($userid);
            $user = CFactory::getUser($userid);

            $params = $user->getParams();

            // site visitor
            $relation = 10;

            // site members
            if ($my->id != 0)
                $relation = 20;

            // friends
            if (CFriendsHelper::isConnected($my->id, $user->id))
                $relation = 30;

            // mine
            if (COwnerHelper::isMine($my->id, $user->id))
                $relation = 40;

            // @todo: respect privacy settings
            if ($relation >= $params->get('privacyFriendsView')) {
                $friends = $friendsModel->getFriends($user->id, 'latest', false, '', PROFILE_MAX_FRIEND_LIMIT + PROFILE_MAX_FRIEND_LIMIT);

                // randomize the friend count
                if ($friends)
                    shuffle($friends);


                if (count($friends) > 0) {
                    $html = '<div id="cPhotoItems" class="photo-list-item">
					<p><strong>' . JText::_('COM_COMMUNITY_FRIENDS') . ':</strong></p>';

                    for ($i = 0; ($i < 4) && ($i < count($friends)); $i++) {
                        $friend = $friends[$i];
                        $html .= '<div class="cPhotoItem">
						<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $friend->id) . '"><img src="' . $friend->getThumbAvatar() . '" alt="' . $friend->getDisplayName() . '" /></a>
					</div>';
                    }
                    $html .= '</div>';
                }
            }

            return $html;
        }

        /**
         * @deprecated
         * @param type $userid
         * @return type
         *
         */
        private function _getFriendsHTML($userid = null) {
            return $this->modGetFriendsHTML($userid);
        }

        /**
         * Return groups html block
         * @since 2.4
         */
        public function modGetGroupsHTML($userid = null) {
            $html = '';
            $my = CFactory::getUser($userid);
            $user = CFactory::getRequestUser();

            $params = $user->getParams();

            // site visitor
            $relation = 10;

            // site members
            if ($my->id != 0)
                $relation = 20;

            // friends
            if (CFriendsHelper::isConnected($my->id, $user->id))
                $relation = 30;

            // mine
            if (COwnerHelper::isMine($my->id, $user->id))
                $relation = 40;

            // Respect privacy settings
            if ($relation < $params->get('privacyGroupsView')) {
                return '';
            }

            $tmpl = new CTemplate();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $model = CFactory::getModel('groups');
            $userid = $jinput->get('userid', $my->id, 'INT'); //JRequest::getVar('userid', $my->id);
            $user = CFactory::getUser($userid);

            $groups = $model->getGroups($user->id);
            $total = count($groups);

            // Randomize groups
            if ($groups)
                shuffle($groups);

            //CFactory::load( 'helpers' , 'url' );
            // Load the groups as proper CTableGroup object
            foreach ($groups as &$gr) {
                $groupTable = JTable::getInstance('Group', 'CTable');
                $groupTable->load($gr->id);
                $gr = $groupTable;
            }

            for ($i = 0; $i < count($groups); $i++) {
                $row = $groups[$i];
                $row->avatar = $row->getThumbAvatar();

                $row->link = CUrl::build('groups', 'viewgroup', array('groupid' => $row->id), true);
            }

            $html = $tmpl->set('user', $user)
                    ->set('total', $total)
                    ->set('groups', $groups)
                    ->fetch('profile.groups');

            return $html;
        }

        /**
         * @deprecated
         * @return type
         */
        private function _getGroupsHTML($userid = null) {
            return $this->modGetGroupsHTML($userid);
        }

        /**
         * Return the 'about us' html block
         */
        private function _getProfileHTML(&$profile) {
            $tmpl         = new CTemplate();
            $mainframe    = JFactory::getApplication();
            $jinput       = $mainframe->input;
            $profileModel = CFactory::getModel('profile');
            $my           = CFactory::getUser();
            $config       = CFactory::getConfig();
            $userid       = $jinput->get('userid', $my->id, 'INT');
            $user         = CFactory::getUser($userid);
            $profileField = $profile['fields'];

            $isAdmin = COwnerHelper::isCommunityAdmin();

            // Allow search only on profile with type text and not empty
            foreach ($profileField as $key => $val) {

                foreach ($profileField[$key] as $pKey => $pVal) {
                    $field = $profileField[$key][$pKey];
                    //check for admin only fields
                    if (!$isAdmin && $field['visible'] == 2) {
                        unset($profileField[$key][$pKey]);
                    } else {
                        // Remove this info if we don't want empty field displayed
                        if (!$config->get('showemptyfield') && ( empty($field['value']) && $field['value'] != "0")) {
                            unset($profileField[$key][$pKey]);
                        } else {
                            if ((!empty($field['value']) || $field['value'] == "0" ) && $field['searchable']) {
                                switch ($field['type']) {
                                    case 'birthdate':
                                        $params = new CParameter($field['params']);
                                        $format = $params->get('display');
                                        if ($format == 'age') {
                                            $profileField[$key][$pKey]['name'] = JText::_('COM_COMMUNITY_AGE');
                                        }

                                        break;
                                    case 'text':
                                        if (CValidateHelper::email($field['value'])) {
                                            $profileField[$key][$pKey]['value'] = CLinkGeneratorHelper::getEmailURL($field['value']);
                                        } else if (CValidateHelper::url($field['value'])) {
                                            $profileField[$key][$pKey]['value'] = CLinkGeneratorHelper::getHyperLink($field['value']);
                                        } else if (!CValidateHelper::phone($field['value']) && !empty($field['fieldcode'])) {
                                            $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode($field['value']));
                                        }
                                        break;
                                    case 'select':
                                    case 'singleselect':
                                        $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode($field['value']) . '&type=' . $field['type']);
                                        $profileField[$key][$pKey]['value'] = JText::_($field['value']);
                                        break;
                                    case 'radio':
                                    case 'checkbox':
                                        $profileField[$key][$pKey]['searchLink'] = array();
                                        $checkboxArray = explode(',', $field['value']);
                                        foreach ($checkboxArray as $item) {
                                            if (!empty($item))
                                                $profileField[$key][$pKey]['searchLink'][$item] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode($item) . '&type=' . $field['type']);
                                        }
                                        break;
                                    case 'country':
                                        $lang = JFactory::getLanguage();
                                        $lang->load('com_community.country');

                                        $profileField[$key][$pKey]['searchLink'] = CRoute::_('index.php?option=com_community&view=search&task=field&' . $field['fieldcode'] . '=' . urlencode(JText::_($field['value'])));
                                        $profileField[$key][$pKey]['value'] = JText::_($field['value']);
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                }
            }

            $profile['fields'] = $profileField;
            $html = $tmpl->set('profile', $profile)
                    ->set('isMine', COwnerHelper::isMine($my->id, $user->id))
                    ->fetch('profile.about');

            return $html;
        }

        /**
         * Return newsfeed html block
         */
        private function _getNewsfeedHTML() {
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userId = $jinput->get('userid', $my->id, 'INT');

            return CActivities::getActivitiesByFilter('active-profile', $userId, 'profile');
        }

        private function _getCurrentProfileVideo() {
            $my = CFactory::getUser();
            $params = $my->getParams();
            $videoid = $params->get('profileVideo', 0);

            // Return if 0(No profile video)
            if ($videoid == 0)
                return;

            $video = JTable::getInstance('Video', 'CTable');

            // If the video does not exists, set the profile video to 0(No profile video)
            if (!$video->load($videoid)) {
                $params->set('profileVideo', 0);
                $my->save('params');
                return;
            }

            return $video;
        }

        public function showSubmenu() {
            $this->_addSubmenu();
            parent::showSubmenu();
        }

        private function _getAdminControlHTML($userid) {
            $adminControlHTML = '';

            if (COwnerHelper::isCommunityAdmin()) {
                $user = CFactory::getUser($userid);
                $params = $user->getParams();
                $videoid = $params->get('profileVideo', 0);

                $tmpl = new CTemplate();

                $isDefaultPhoto = ( $user->getThumbAvatar() == rtrim(JURI::root(), '/') . '/components/com_community/assets/default_thumb.jpg' ) ? true : false;

                //CFactory::load( 'libraries' , 'featured' );
                $featured = new CFeatured(FEATURED_USERS);
                $isFeatured = $featured->isFeatured($user->id);
                $jConfig = JFactory::getConfig();
                $config = CFactory::getConfig();
                $showFeatured = $config->get('show_featured');

                $adminControlHTML = $tmpl->set('userid', $userid)
                        ->set('videoid', $videoid)
                        ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                        ->set('blocked', $user->isBlocked())
                        ->set('showFeatured', $showFeatured)
                        ->set('isFeatured', $isFeatured)
                        ->set('isDefaultPhoto', $isDefaultPhoto)
                        ->set('jConfig', $jConfig)
                        ->fetch('admin.controls');
            }

            return $adminControlHTML;
        }

        /**
         * Show the main profile header
         */
        private function _showHeader(& $data) {
            jimport('joomla.utilities.arrayhelper');

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $my = JFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT'); //JRequest::getVar('userid', $my->id);
            $user = CFactory::getUser($userid);
            $params = $user->getParams();
            $userModel = CFactory::getModel('user');

            //CFactory::load ( 'libraries', 'messaging' );

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            // Get the admin controls HTML data
            $adminControlHTML = '';

            $tmpl = new CTemplate ();

            // get how many unread message
            $filter = array();
            $inboxModel = CFactory::getModel('inbox');
            $filter['user_id'] = $my->id;
            $unread = $inboxModel->countUnRead($filter);

            // get how many pending connection
            $friendModel = CFactory::getModel('friends');
            $pending = $friendModel->countPending($my->id);

            $profile = JArrayHelper::toObject($data->profile);
            $profile->largeAvatar = $user->getAvatar();
            $profile->status = $user->getStatus();

            if ($profile->status !== '') {
                $postedOn = new JDate($user->_posted_on);
                $postedOn = CActivityStream::_createdLapse($postedOn);
                $profile->posted_on = $user->_posted_on == '0000-00-00 00:00:00' ? '' : $postedOn;
            } else {
                $profile->posted_on = '';
            }

            // Assign videoId
            $profile->profilevideo = $data->videoid;
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($profile->profilevideo);
            $profile->profilevideoTitle = $video->getTitle();

            $addbuddy = "joms.friends.connect('{$profile->id}')";
            $sendMsg = CMessaging::getPopup($profile->id);

            $config = CFactory::getConfig();

            $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
            if ($user->lastvisitDate != '0000-00-00 00:00:00') {
                //$now = JFactory::getDate();
                $userLastLogin = new JDate($user->lastvisitDate);
                //CFactory::load( 'libraries' , 'activities');
                $lastLogin = CActivityStream::_createdLapse($userLastLogin);
            }

            // @todo : beside checking the owner, maybe we want to check for a cookie,
            // say every few hours only the hit get increment by 1.
            if (!$isMine) {
                $user->viewHit();
            }

            // @rule: myblog integrations
            $showBlogLink = false;

            //CFactory::load( 'libraries' , 'myblog' );
            $myblog = CMyBlog::getInstance();
            if ($config->get('enablemyblogicon') && $myblog) {
                if ($myblog->userCanPost($user->id)) {
                    $showBlogLink = true;
                }
                $tmpl->set('blogItemId', $myblog->getItemId());
            }

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($user->getProfileType());

            // Get like
            $likesHTML = '';
            if ($user->getParams()->get('profileLikes', true)) {
                //CFactory::load( 'libraries' , 'like' );
                $likes = new CLike();
                $likesHTML = ($my->id == 0) ? $likes->getHtmlPublic('profile', $user->id) : $likes->getHTML('profile', $user->id, $my->id);
            }

            $status = new CUserStatus($user->id, 'profile');

            //respect wall setting
            if ($my->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($my->id, $profile->id) ) ) || COwnerHelper::isCommunityAdmin()) {

                // Add default status box
                CUserHelper::addDefaultStatusCreator($status);
            }

            $isblocked = $user->isBlocked();

            return $tmpl->set('karmaImgUrl', CUserPoints::getPointsImage($user))
                            ->set('isMine', $isMine)
                            ->set('lastLogin', $lastLogin)
                            ->set('user', $user)
                            ->set('addBuddy', $addbuddy)
                            ->set('sendMsg', $sendMsg)
                            ->set('config', $config)
                            ->set('multiprofile', $multiprofile)
                            ->set('showBlogLink', $showBlogLink)
                            ->set('isFriend', CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id)
                            ->set('isWaitingApproval', CFriendsHelper::isWaitingApproval($my->id, $user->id))
                            ->set('isBlocked', $isblocked)
                            ->set('profile', $profile)
                            ->set('unread', $unread)
                            ->set('pending', $pending)
                            ->set('registerDate', $user->registerDate)
                            ->set('adminControlHTML', $adminControlHTML)
                            ->set('likesHTML', $likesHTML)
                            ->set('userstatus', $status)
                            ->fetch('profile.header');
        }

        /**
         * Displays the viewing profile page.
         *
         * @access	public
         * @param	array  An associative array to display the fields
         */
        public function profile($data) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $userId = $jinput->get->get('userid', '', 'INT');

            /**
             * Opengraph
             */
            CHeadHelper::setType('profile', JText::sprintf('COM_COMMUNITY_USER_PROFILE_PAGE_TITLE', $user->getDisplayName()), $user->_status, array($user->getAvatar(), $user->getCover()));

            if ($my->id != 0 && empty($userId)) {
                CFactory::setActiveProfile($my->id);
                $user = $my;
            }

            // Display breadcrumb regardless whether the user is blocked or not
            $pathway = $mainframe->getPathway();
            $pathway->addItem($user->getDisplayName(), '');

            $getBlockStatus = new blockUser();
            $blocked = $getBlockStatus->isUserBlocked($userId, 'profile');
            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                return;
            }

            // If the current browser is a site admin, display some notice that user is blocked.
            if ($blocked) {
                $this->addWarning(JText::_('COM_COMMUNITY_USER_ACCOUNT_BANNED'));
            }

            // access check
            //if(!$this->accessAllowed('privacyProfileView'))
            if (!$my->authorise('community.view', 'profile.' . $my->id, $user)) {
                // @todo: display the no access box like the old time
                $this->showLimitedProfile($user->id);
                return;
            }
            // Load user application
            $apps = $data->apps;

            // Load community applications plugin
            $app = CAppPlugins::getInstance();
            $appsModel = CFactory::getModel('apps');
            $tmpAppData = $app->triggerEvent('onProfileDisplay', '', true);

            $appData = array();

            // @rule: Only display necessary apps.
            $count = count($tmpAppData);

            for ($i = 0; $i < $count; $i++) {
                $app = $tmpAppData[$i];

                $privacy = $appsModel->getPrivacy($user->id, $app->name);

                if ($this->appPrivacyAllowed($privacy)) {
                    $appData[] = $app;
                }
            }
            unset($tmpAppData);

            // Split the apps into different list for different positon
            $appsInPositions = array();
            foreach ($appData as &$app) {
                // @rule: Try to get proper app id from #__community_users table first.
                $app_id = $appsModel->getUserApplicationId($app->name, $my->id);

                // @rule: If there aren't any records, we need to get it from #__plugins table.
                if (empty($id)) {
                    $app_id = $appsModel->getPluginId($app->name, null, true);
                }

                $params = new CParameter($appsModel->getPluginParams($app_id, null));
                $isCoreApp = $params->get('coreapp');

                if (!in_array($app->position, array('content', 'sidebar-top', 'sidebar-bottom'))) {
                    $app->position = 'content';
                }

                $corePosition = $params->get('position');
                $app->position = $isCoreApp ? $corePosition : $app->position;

                $appsInPositions[$app->position][] = $app;
            }


            $tmpl = new CTemplate();
            $contenHTML = array();
            $contenHTML['content'] = '';
            $contenHTML['sidebar-top'] = '';
            $contenHTML['sidebar-bottom'] = '';
            $jscript = '';

            foreach ($appsInPositions as $position => $appData) {
                ob_start();

                foreach ($appData as $app) {
                    // If the apps content is empty, we ignore this app from showing
                    // the header in profile page.
                    if (JString::trim($app->data) == "")
                        continue;

                    $tmpl->set('app', $app)
                            ->set('isOwner', COwnerHelper::isMine($my->id, $user->id));

                    switch ($position) {
                        case 'sidebar-top':
                        case 'sidebar-bottom':
                            echo $tmpl->fetch('application.widget');
                            break;
                        default:
                            echo $tmpl->fetch('application.box');
                    }
                }

                $contenHTML[$position] = ob_get_contents();
                ob_end_clean();
            }

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            $tmpl = new CTemplate( );
            echo $tmpl->set('newsfeed', $this->_getNewsfeedHTML())
                    ->set('content', $contenHTML['content'])
                    ->set('sidebarTop', $contenHTML['sidebar-top'])
                    ->set('sidebarBottom', $contenHTML['sidebar-bottom'])
                    ->set('isMine', $isMine)
                    ->fetch('profile.index');
        }

        public function editPage() {
            if (!$this->accessAllowed('registered')) {
                return;
            }

            $my = CFactory::getUser();
            $appsModel = CFactory::getModel('apps');

            //------ pre-1.8 ------//
            // Get coreapps
            $coreApps = $appsModel->getCoreApps();
            for ($i = 0; $i < count($coreApps); $i++) {
                $appInfo = $appsModel->getAppInfo($coreApps[$i]->apps);

                // @rule: Try to get proper app id from #__community_users table first.
                $id = $appsModel->getUserApplicationId($coreApps[$i]->apps, $my->id);

                // @rule: If there aren't any records, we need to get it from #__plugins table.
                if (empty($id)) {
                    $id = $appsModel->getPluginId($coreApps[$i]->apps, null, true);
                }

                $coreApps[$i]->id = $id;
                $coreApps[$i]->title = $appInfo->title;
                $coreApps[$i]->description = $appInfo->description;
                $coreApps[$i]->name = $coreApps[$i]->apps;
                //$coreApps[$i]->coreapp		= $params->get( 'coreapp' );
                //Get application favicon
                if (JFile::exists(CPluginHelper::getPluginPath('community', $coreApps[$i]->apps) . '/' . $coreApps[$i]->apps . '/favicon_64.png')) {
                    $coreApps[$i]->appFavicon = rtrim(JURI::root(), '/') . CPluginHelper::getPluginURI('community', $coreApps[$i]->apps) . '/' . $coreApps[$i]->apps . '/favicon_64.png';
                } else {
                    $coreApps[$i]->appFavicon = rtrim(JURI::root(), '/') . '/components/com_community/assets/app_favicon.png';
                }
            }
            //------ pre-1.8 ------//
            // Get user apps
            $userApps = $appsModel->getUserApps($my->id);

            $appItems = array();
            $appItems['sidebar-top-core'] = '';
            $appItems['sidebar-bottom-core'] = '';
            $appItems['sidebar-top'] = '';
            $appItems['sidebar-bottom'] = '';
            $appItems['content'] = '';
            $appItems['content-core'] = '';

            $appsList = array();

            for ($i = 0; $i < count($userApps); $i++) {
                // TODO: getUserApps should return all this value already
                $id = $appsModel->getPluginId($userApps[$i]->apps, null, true);
                $appInfo = $appsModel->getAppInfo($userApps[$i]->apps);
                $params = new CParameter($appsModel->getPluginParams($id, null));
                $isCoreApp = $params->get('coreapp');
                $corePosition = $params->get('position');

                $userApps[$i]->title = isset($appInfo->title) ? $appInfo->title : '';
                $userApps[$i]->description = isset($appInfo->description) ? $appInfo->description : '';
                $userApps[$i]->coreapp = $isCoreApp; // Pre 1.8x
                $userApps[$i]->isCoreApp = $isCoreApp;
                $userApps[$i]->name = $userApps[$i]->apps;

                //------ pre-1.8 ------//

                if (JFile::exists(CPluginHelper::getPluginPath('community', $userApps[$i]->apps) . '/favicon.png')) {
                    $userApps[$i]->favicon['16'] = rtrim(JURI::root(), '/') . CPluginHelper::getPluginURI('community', $userApps[$i]->apps) . '/' . $userApps[$i]->apps . '/favicon.png';
                } else {
                    $userApps[$i]->favicon['16'] = rtrim(JURI::root(), '/') . '/components/com_community/assets/app_favicon.png';
                }
                $position = !empty($userApps[$i]->position) ? $userApps[$i]->position : 'content' . (($isCoreApp) ? '-core' : '');
                // if this is core app, use the config from coreposition instead
                $position = $isCoreApp ? $corePosition : $position;

                $appsList[$position][] = $userApps[$i];
            }

            foreach ($appsList as $position => $apps) {
                $tmpl = new CTemplate();
                if (isset($appItems[$position])) {
                    $appItems[$position] .= $tmpl->set('apps', $apps)->set('itemType', 'edit')->fetch('application.item');
                }
            }

            // Get available apps for comparison
            $appsModel = CFactory::getModel('apps');
            $apps = $appsModel->getAvailableApps(false);
            $appsname = array();
            $availableApps = array();
            if (!empty($apps)) {
                foreach ($apps as $data) {
                    array_push($availableApps, $data->name);
                }
            }

            // Check if apps exist, if not delete it.
            $obsoleteApps = array();
            $obsoleteApps = array_diff($appsname, $availableApps);
            if (!empty($obsoleteApps)) {
                foreach ($obsoleteApps as $key => $obsoleteApp) {
                    $appRecords = $appsModel->checkObsoleteApp($obsoleteApp);

                    if (empty($appRecords)) {
                        if ($appRecords == NULL) {
                            $appsModel->removeObsoleteApp($obsoleteApp);
                        }

                        unset($userApps[$key]);
                    }
                }
                $userApps = array_values($userApps);
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_APPS_MINE'));

            $this->addPathway(JText::_('COM_COMMUNITY_APPS_MINE'));
            $this->showSubMenu(); // pre-1.8
            //CFactory::load( 'libraries' , 'window' );
            CWindow::load();
            CAssets::attach('assets/jquery.tablednd_0_5.js', 'js'); // pre-1.8
            CAssets::attach('assets/ui.core.js', 'js');
            //CAssets::attach('assets/ui.sortable.js', 'js');
            CAssets::attach('assets/applayout.js', 'js');

            $tmpl = new CTemplate();
            echo $tmpl->set('coreApplications', $coreApps) // pre-1.8
                    ->set('applications', $userApps) // pre-1.8
                    ->set('appItems', $appItems)
                    ->fetch('applications.edit');
        }

        public function editLayout() {
            $tmpl = new CTemplate( );

            $content = '<div class="app-box-sortable"></div><div  class="app-box-sortable"><div>';

            echo $tmpl->set('content', $content)
                    ->fetch('profile.editlayout');
        }

        /**
         * Edits a user profile
         *
         * @access	public
         * @param	array  An associative array to display the editing of the fields
         */
        public function edit($data) {

            $mainframe = JFactory::getApplication();

            // access check
            CFactory::setActiveProfile();
            if (!$this->accessAllowed('registered'))
                return;

            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $userParams = $my->getParams();

            $pathway = $mainframe->getPathway();
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_PROFILE_EDIT'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_EDIT'));

            $js = 'assets/validate-1.5.min.js';
            CAssets::attach($js, 'js');

            $this->showSubmenu();

            $jConfig = JFactory::getConfig();
            $app = CAppPlugins::getInstance();

            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-edit'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($my->getProfileType());

            $model = CFactory::getModel('Profile');
            $profileTypes = $model->getProfileTypes();

            // @rule: decide to show multiprofile or not.
            $showProfileType = ( $config->get('profile_multiprofile') && $profileTypes && count($profileTypes) >= 1 && !$multiprofile->profile_lock);

            $isAdmin = COwnerHelper::isCommunityAdmin();
            $profileField = $data->profile ['fields'];

            if (!is_null($profileField)) {
                foreach ($profileField as $key => $val) {
                    foreach ($val as $pkey => $field) {
                        if (!$isAdmin && $field['visible'] == 2) {
                            unset($profileField[$key][$pkey]);
                        }
                    }
                }
            }

            $fbHtml = '';
            $connectModel = CFactory::getModel('Connect');
            $associated = $connectModel->isAssociated($my->id);

            if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {

                $facebook = new CFacebook();
                $fbHtml = $facebook->getLoginHTML();
            }


            if ($config->get('usejfbc')) {
                if (class_exists('JFBCFactory')) {
                   $providers = JFBCFactory::getAllProviders();

                   foreach($providers as $p){
                        $fbHtml .= $p->loginButton();
                   }
                }
            }

            $isUseFirstLastName = CUserHelper::isUseFirstLastName();

            $data->profile ['fields'] = $profileField;
            $tmpl = new CTemplate();
            echo $tmpl->set('showProfileType', $showProfileType)
                    ->set('multiprofile', $multiprofile)
                    ->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('fields', $data->profile ['fields'])
                    ->set('user', $my)
                    ->set('fbHtml', $fbHtml)
                    ->set('fbPostStatus', $userParams->get('postFacebookStatus'))
                    ->set('jConfig', $jConfig)
                    ->set('params', $data->params)
                    ->set('config', $config)
                    ->set('associated', $associated)
                    ->set('isAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('offsetList', $data->offsetList)
                    ->set('isUseFirstLastName', $isUseFirstLastName)
                    ->fetch('profile.edit');
        }

        /**
         * Edits a user details
         *
         * @access	public
         * @param	array  An associative array to display the editing of the fields
         */
        public function editDetails(& $data) {
            $mainframe = JFactory::getApplication();

            // access check
            CFactory::setActiveProfile();
            if (!$this->accessAllowed('registered'))
                return;

            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $userParams = $my->getParams();

            $pathway = $mainframe->getPathway();
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_EDIT_DETAILS'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EDIT_DETAILS'));

            $js = 'assets/validate-1.5.min.js';
            CAssets::attach($js, 'js');

            $this->showSubmenu();

            $connectModel = CFactory::getModel('Connect');
            $associated = $connectModel->isAssociated($my->id);

            $fbHtml = '';

            if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
                //CFactory::load( 'libraries' , 'facebook' );
                $facebook = new CFacebook();
                $fbHtml = $facebook->getLoginHTML();
            }

            if ($config->get('usejfbc')) {
                if (class_exists('JFBCFactory')) {
                   $providers = JFBCFactory::getAllProviders();

                   foreach($providers as $p){
                        $fbHtml .= $p->loginButton();
                   }
                }
            }
            // If FIELD_GIVENNAME & FIELD_FAMILYNAME is in use

            $isUseFirstLastName = CUserHelper::isUseFirstLastName();

            $jConfig = JFactory::getConfig();
            //CFactory::load( 'libraries' , 'apps' );
            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-editdetails'));

            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('fbHtml', $fbHtml)
                    ->set('fbPostStatus', $userParams->get('postFacebookStatus'))
                    ->set('jConfig', $jConfig)
                    ->set('params', $data->params)
                    ->set('user', $my)
                    ->set('config', $config)
                    ->set('associated', $associated)
                    ->set('isAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('offsetList', $data->offsetList)
                    ->set('isUseFirstLastName', $isUseFirstLastName)
                    ->fetch('profile.edit.details');
        }

        public function connect() {

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_CONNECT_REQUEST'));
            ?>
            <form name="jsform-profile-connect" method="post" action="">
                <input type="submit" name="yes" class="button" id="button_yes" value="<?php echo JText::_('COM_COMMUNITY_YES_BUTTON'); ?>" />
                <input type="submit" name="no" class="button" id="button_no" value="<?php echo JText::_('COM_COMMUNITY_NO_BUTTON'); ?>" />
            </form>

            <?php
        }

        public function connect_sent() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_CONNECT_REQUEST_SENT'));
        }

        public function appFullView() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $userid = JRequest::getInt('userid', null);
            $user = CFactory::getUser($userid);
            $profileModel = CFactory::getModel('profile');
            $avatarModel = CFactory::getModel('avatar');
            $applications = CAppPlugins::getInstance();
            $appName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($appName)) {
                JError::raiseError(500, 'COM_COMMUNITY_APPS_ID_REQUIRED');
            }

            if (is_null($userid)) {
                JError::raiseError(500, 'COM_COMMUNITY_USER_ID_REQUIRED');
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $user->getDisplayName() . ' : ' . $user->getStatus());

            $appsModel = CFactory::getModel('apps');
            $appId = $appsModel->getUserApplicationId($appName);
            $plugin = $applications->get($appName, $appId);

            if (!$plugin) {
                JError::raiseError(500, 'COM_COMMUNITY_APPS_NOT_FOUND');
            }

            $appObj = new stdClass();
            $data = new stdClass();
            $appObj->name = $plugin->name;
            $appObj->html = $plugin->onAppDisplay();
            $data->html = $appObj->html;

            $this->attachMiniHeaderUser($user->id);

            echo $data->html;
        }

        /**
         * Display Upload avatar form for user
         * */
        public function uploadAvatar() {
            $mainframe = JFactory::getApplication();
            if (!$this->accessAllowed('registered')) {
                echo JText::_('COM_COMMUNITY_MEMBERS_AREA');
                return;
            }

            $my = CFactory::getUser();
            $firstLogin = false;

            $pathway = $mainframe->getPathway();

            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_CHANGE_AVATAR'), '');

            // Load the toolbar
            $this->showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_CHANGE_AVATAR'));

            $config = CFactory::getConfig();
            $uploadLimit = (double) $config->get('maxuploadsize');
            $uploadLimit .= 'MB';

            $tmpl = new CTemplate();
            $skipLink = CRoute::_('index.php?option=com_community&view=frontpage&doSkipAvatar=Y&userid=' . $my->id);

            $largeAvatar = $my->getAvatar();
            $fileName = JFile::getName($largeAvatar);
            $avatarImageDir = $config->getString('imagefolder') . '/avatar/';

            if (JFile::exists($avatarImageDir . 'profile-' . $fileName)) {
                $largeAvatar = str_replace($fileName, 'profile-' . $fileName, $largeAvatar);
            }

            echo $tmpl->set('user', $my)
                    ->set('largeAvatar', $largeAvatar)
                    ->set('profileType', $my->getProfileType())
                    ->set('uploadLimit', $uploadLimit)
                    ->set('firstLogin', $firstLogin)
                    ->set('skipLink', $skipLink)
                    ->fetch('profile.uploadavatar');
        }

        /**
         * Display Upload video form for user
         * */
        public function linkVideo() {
            if (!$this->accessAllowed('registered')) {
                echo JText::_('COM_COMMUNITY_MEMBERS_AREA');
                return;
            }

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $videoModel = CFactory::getModel('videos');

            $pathway = $mainframe->getPathway();
            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'), '');

            // Load the toolbar
            $this->showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_VIDEOS_EDIT_PROFILE_VIDEO'));

            $video = $this->_getCurrentProfileVideo();

            $filters = array
                (
                'creator' => $my->id,
                'status' => 'ready',
                'sorting' => $jinput->get('sort', 'latest', 'STRING')
            );
            $videos = $videoModel->getVideos($filters, true);

            $sortItems = array
                (
                'latest' => JText::_('COM_COMMUNITY_VIDEOS_SORT_LATEST'),
                'mostwalls' => JText::_('COM_COMMUNITY_VIDEOS_SORT_MOST_WALL_POST'),
                'mostviews' => JText::_('COM_COMMUNITY_VIDEOS_SORT_POPULAR'),
                'title' => JText::_('COM_COMMUNITY_VIDEOS_SORT_TITLE')
            );

            // Pagination
            $pagination = $videoModel->getPagination();

            $redirectUrl = CRoute::getURI(false);

            $tmpl = new CTemplate();
            echo $tmpl->set('my', $my)
                    ->set('video', $video)
                    ->set('sort', $jinput->get('sort', 'latest', 'STRING'))
                    ->set('videos', $videos)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->set('pagination', $pagination)
                    ->set('videoThumbWidth', CVideoLibrary::thumbSize('width'))
                    ->set('videoThumbHeight', CVideoLibrary::thumbSize('height'))
                    ->set('redirectUrl', $redirectUrl)
                    ->fetch('profile.linkvideo');
        }

        public function video() {
            $tmpl = new CTemplate();
            echo $tmpl->fetch('videos.video');
        }

        /**
         *
         */
        public function privacy() {
            $mainframe = JFactory::getApplication();

            if (!$this->accessAllowed('registered'))
                return;

            $pathway = $mainframe->getPathway();
            $my = CFactory::getUser();

            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_PROFILE_PRIVACY_EDIT'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_PRIVACY_EDIT'));

            $this->showSubmenu();
            $user = CFactory::getUser();
            $params = $user->getParams();
            $config = CFactory::getConfig();

            //Get blocked list
            $model = CFactory::getModel('block');
            $blocklists = $model->getBanList($my->id);

            foreach ($blocklists as $user) {
                $blockedUser = CFactory::getUser($user->blocked_userid);
                $user->avatar = $blockedUser->getThumbAvatar();
            }


            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-privacy'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            //user's email privacy setting
            $notificationTypes = new CNotificationTypes();

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('blocklists', $blocklists)
                    ->set('params', $params)
                    ->set('config', $config)
                    ->set('notificationTypes', $notificationTypes)
                    //->set('emailtypes', $emailtypes->getEmailTypes())
                    ->fetch('profile.privacy');
        }

        public function preferences() {
            $mainframe = JFactory::getApplication();

            if (!$this->accessAllowed('registered')) {
                return;
            }
            $this->showSubmenu();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_EDIT_PREFERENCES'));

            $my = CFactory::getUser();
            $params = $my->getParams();
            $jConfig = JFactory::getConfig();
            $pathway = $mainframe->getPathway();

            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_EDIT_PREFERENCES'), '');

            $prefixURL = $my->getAlias();

            if ($mainframe->getCfg('sef')) {
                $juriRoot = JURI::root(false);
                $juriPathOnly = JURI::root(true);
                $juriPathOnly = rtrim($juriPathOnly, '/');
                $profileURL = rtrim(str_replace($juriPathOnly, '', $juriRoot), '/');

                $profileURL .= CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
                $alias = $my->getAlias();

                $inputHTML = '<input id="alias" name="alias" type="text" value="' . $alias . '" />';
                $prefixURL = str_replace($alias, $inputHTML, $profileURL);

                // For backward compatibility issues, as we changed from ID-USER to ID:USER in 2.0,
                // we also need to test older urls.
                if ($prefixURL == $profileURL) {
                    $prefixURL = CString::str_ireplace(CString::str_ireplace(':', '-', $alias), $inputHTML, $profileURL);
                }
            }

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-preferences'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            //Get blocked list
            $blockLists = $my->getBlockedUsers();

            //user's email privacy setting
            $notificationTypes = new CNotificationTypes();

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('params', $params)
                    ->set('prefixURL', $prefixURL)
                    ->set('user', $my)
                    ->set('blockedUsers', $blockLists)
                    ->set('jConfig', $jConfig)
                    ->set('notificationTypes', $notificationTypes)
                    ->fetch('profile.preferences');
        }

        public function deleteProfile() {
            if (!$this->accessAllowed('registered'))
                return;

            $config = CFactory::getConfig();

            if (!$config->get('profile_deletion')) {
                echo JText::_('COM_COMMUNITY_RESTRICTED_ACCESS');
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_DELETE_PROFILE'));

            $my = CFactory::getUser();
            $this->addPathWay(JText::_('COM_COMMUNITY_PROFILE'), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $this->addPathWay(JText::_('COM_COMMUNITY_EDIT_PREFERENCES'), '');

            $tmpl = new CTemplate();
            echo $tmpl->fetch('profile.deleteprofile');
        }

        /**
         *
         */
        public function notifications() {
            $mainframe = JFactory::getApplication();

            if (!$this->accessAllowed('registered'))
                return;

            $pathway = $mainframe->getPathway();
            $my = CFactory::getUser();

            $pathway->addItem(JText::_($my->getDisplayName()), CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $pathway->addItem(JText::_('COM_COMMUNITY_PROFILE_NOTIFICATIONS'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_PROFILE_NOTIFICATIONS'));

            $this->showSubmenu();
            $user = CFactory::getUser();
            $params = $user->getParams();
            $config = CFactory::getConfig();

            $modelNotification = CFactory::getModel('notification');
            $notifications = $modelNotification->getNotification($my->id, '0', 0);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-profile-notifications'));

            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('params', $params)
                    ->set('config', $config)
                    ->set('pagination', $modelNotification->getPagination())
                    ->set('notifications', $notifications)
                    ->fetch('profile.notification');
        }

        /* Jomsocial 3.0 - Modules */

        // User info
        public function modProfileUserinfo() {
            jimport('joomla.utilities.arrayhelper');

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $my = JFactory::getUser();
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $params = $user->getParams();
            $userModel = CFactory::getModel('user');
            $profileModel = CFactory::getModel('profile');

            //Reassign needed variable
            $data = new stdClass();
            $data->user = $user;
            $data->profile = $profileModel->getViewableProfile($userid, $user->getProfileType());
            $data->videoid = $params->get('profileVideo', 0);


            CFactory::load('libraries', 'messaging');

            $isMine = COwnerHelper::isMine($my->id, $user->id);

            // Get the admin controls HTML data
            $adminControlHTML = '';

            $tmpl = new CTemplate ();

            // get how many unread message
            $filter = array();
            $inboxModel = CFactory::getModel('inbox');
            $filter['user_id'] = $my->id;
            $unread = $inboxModel->countUnRead($filter);

            // get how many pending connection
            $friendModel = CFactory::getModel('friends');
            $pending = $friendModel->countPending($my->id);

            $profile = JArrayHelper::toObject($data->profile);
            $profile->largeAvatar = $user->getAvatar();

            $profile->status = $user->getStatus();
            $profile->defaultCover = $user->isDefaultCover();
            $profile->cover = $user->getCover();
            $profile->coverPostion = $params->get('coverPosition', 0);

            $groupmodel = CFactory::getModel('groups');
            $profile->_groups = $groupmodel->getGroupsCount($profile->id);

            $eventmodel = CFactory::getModel('events');
            $profile->_events = $eventmodel->getEventsCount($profile->id);

            $profile->_friends = $user->_friendcount;

            $videoModel = CFactory::getModel('Videos');
            $profile->_videos = $videoModel->getVideosCount($profile->id);

            $photosModel = CFactory::getModel('photos');
            $profile->_photos = $photosModel->getPhotosCount($profile->id);

            if ($profile->status !== '') {
                $postedOn = new JDate($user->_posted_on);
                $postedOn = CActivityStream::_createdLapse($postedOn);
                $profile->posted_on = $user->_posted_on == '0000-00-00 00:00:00' ? '' : $postedOn;
            } else {
                $profile->posted_on = '';
            }

            /* is featured */
            $modelFeatured = CFactory::getModel('Featured');
            $profile->featured = $modelFeatured->isExists(FEATURED_USERS, $profile->id);

            // Assign videoId
            $profile->profilevideo = $data->videoid;

            $video = JTable::getInstance('Video', 'CTable');
            $video->load($profile->profilevideo);
            $profile->profilevideoTitle = $video->getTitle();

            $addbuddy = "joms.friends.connect('{$profile->id}')";
            $sendMsg = CMessaging::getPopup($profile->id);

            $config = CFactory::getConfig();
            $jConfig = JFactory::getConfig();

            $lastLogin = JText::_('COM_COMMUNITY_PROFILE_NEVER_LOGGED_IN');
            if ($user->lastvisitDate != '0000-00-00 00:00:00') {
                $userLastLogin = new JDate($user->lastvisitDate);
                $lastLogin = CActivityStream::_createdLapse($userLastLogin);
            }

            // @todo : beside checking the owner, maybe we want to check for a cookie,
            // say every few hours only the hit get increment by 1.
            if (!$isMine) {
                $user->viewHit();
            }

            // @rule: myblog integrations
            $showBlogLink = false;
            $myblog = CMyBlog::getInstance();

            if ($config->get('enablemyblogicon') && $myblog) {
                if ($myblog->userCanPost($user->id)) {
                    $showBlogLink = true;
                }
                $tmpl->set('blogItemId', $myblog->getItemId());
            }

            $photoEnabled = ($config->get('enablephotos')) ? true : false;
            $eventEnabled = ($config->get('enableevents')) ? true : false;
            $groupEnabled = ($config->get('enablegroups')) ? true : false;
            $videoEnabled = ($config->get('enablevideos')) ? true : false;
            $isSEFEnabled = ($jConfig->get('sef')) ? true : false;

            $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
            $multiprofile->load($user->getProfileType());

            CFactory::load('libraries', 'like');

            $like = new Clike();
            $isUserLiked = false;

            if ($isLikeEnabled = $like->enabled('profile')) {
                $isUserLiked = $like->userLiked('profile', $user->id, $my->id);
            }
            /* likes count */
            $likes = $like->getLikeCount('profile', $user->id);

            /* User status */
            $status = new CUserStatus($user->id, 'profile');

            //respect wall setting
            if ($my->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($my->id, $profile->id) ) ) || COwnerHelper::isCommunityAdmin()) {
                // Add default status box

                CUserHelper::addDefaultStatusCreator($status);
            }

            $isblocked = $user->isBlocked();

            $isMine = COwnerHelper::isMine($my->id, $user->id);
            $isCommunityAdmin = COwnerHelper::isCommunityAdmin($user->id);

            // Check if user is blocked
            $blockUserModel = CFactory::getModel('block');
            $isBlocked = $blockUserModel->getBlockStatus($user->id, $my->id);

            // Get block user html
            $blockUserHTML = $isMine || $isCommunityAdmin ? '' : CUserHelper::getBlockUserHTML($user->id, $isBlocked);

            $isMine = COwnerHelper::isMine($my->id, $user->id);
            $isCommunityAdmin = COwnerHelper::isCommunityAdmin($user->id);

            // Get reporting html
            $report = new CReportingLibrary();
            $reportsHTML = $isMine ? '' : $report->getReportingHTML(JText::_('COM_COMMUNITY_REPORT_USER'), 'profile,reportProfile', array($user->id));

            $tmpl = new CTemplate();
            echo $tmpl->set('karmaImgUrl', CUserPoints::getPointsImage($user))
                    ->set('blockUserHTML', $blockUserHTML)
                    ->set('reportsHTML', $reportsHTML)
                    ->set('isMine', $isMine)
                    ->set('lastLogin', $lastLogin)
                    ->set('addBuddy', $addbuddy)
                    ->set('sendMsg', $sendMsg)
                    ->set('config', $config)
                    ->set('multiprofile', $multiprofile)
                    ->set('showBlogLink', $showBlogLink)
                    ->set('isFriend', CFriendsHelper::isConnected($user->id, $my->id) && $user->id != $my->id)
                    ->set('isWaitingApproval', CFriendsHelper::isWaitingApproval($my->id, $user->id))
                    ->set('isBlocked', $isblocked)
                    ->set('profile', $profile)
                    ->set('unread', $unread)
                    ->set('pending', $pending)
                    ->set('registerDate', $user->registerDate)
                    ->set('adminControlHTML', $adminControlHTML)
                    ->set('userstatus', $status)
                    ->set('user', $user)
                    ->set('isUserLiked', $isUserLiked)
                    ->set('likes', $likes)
                    ->set('isLikeEnabled', $isLikeEnabled)
                    ->set('photoEnabled', $photoEnabled)
                    ->set('eventEnabled', $eventEnabled)
                    ->set('groupEnabled', $groupEnabled)
                    ->set('videoEnabled', $videoEnabled)
                    ->set('about', $this->_getProfileHTML($data->profile))
                    ->set('isSEFEnabled', $isSEFEnabled)
                    ->set('blocked', $user->isBlocked())
                    ->set('isBlocked', $isBlocked)
                    ->fetch('modules/profile/userinfo');
        }

        // User Status
        public function modProfileUserstatus() {
            $my = JFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userid = $jinput->get('userid', $my->id, 'INT'); //JRequest::getVar('userid', $my->id);
            $user = CFactory::getUser($userid);
            $config = CFactory::getConfig();

            //respect wall setting
            $status = new CUserStatus($user->id, 'profile');


            if ($my->id && ((!$config->get('lockprofilewalls')) || ( $config->get('lockprofilewalls') && CFriendsHelper::isConnected($my->id, $user->id) ) ) || COwnerHelper::isCommunityAdmin()) {
                // Add default status box
                CUserHelper::addDefaultStatusCreator($status);
            }

            echo $status->render();
        }

        public function modProfileActivities() {
            $this->_getNewsfeedHTML();
        }

        public function modProfileUserVideo() {
            $my = JFactory::getUser();
            $jinput = JFactory::getApplication()->input;
            $userid = $jinput->get('userid', $my->id, 'INT');
            $user = CFactory::getUser($userid);
            $config = CFactory::getConfig();

            $params = $user->getParams();

            $profilevideoId = $params->get('profileVideo', 0);

            if ($config->get('enablevideos') && $config->get('enableprofilevideo') && $profilevideoId) {
                $video = JTable::getInstance('Video', 'CTable');
                $video->load($profilevideoId);

                $tmpl = new CTemplate();

                echo $tmpl->set('video', $video)
                        ->set('videoThumbWidth', CVideoLibrary::thumbSize('width'))
                        ->set('videoThumbHeight', CVideoLibrary::thumbSize('height'))
                        ->fetch('profile.video');
            }

            return false;
        }

    }

}
