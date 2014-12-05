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

if (!class_exists('CommunityViewVideos')) {

    class CommunityViewVideos extends CommunityView {

        var $_videoLib = null;
        var $model = '';

        public function CommunityViewVideos() {
            $this->model = CFactory::getModel('videos');
            $this->videoLib = new CVideoLibrary();
        }

        private function _getVideosHTML($videos, $pagination = NULL) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $videoEntries = array();

            if ($videos) {
                foreach ($videos as $videoEntry) {
                    $video = JTable::getInstance('Video', 'CTable');
                    $video->bind($videoEntry);
                    $videoEntries[] = $video;
                }
            }

            $my = CFactory::getUser();
            $user = CFactory::getUser(JRequest::getInt('userid', $my->id));

            // for featured/unfeatured link
            $featured = new CFeatured(FEATURED_VIDEOS);
            $featuredVideos = $featured->getItemIds();
            $featuredList = array();

            foreach ($featuredVideos as $videoId) {
                $featuredList[] = $videoId;
            }

            $allowManageVideos = true;
            $groupVideo = false;
            $groupId = $jinput->get->get('groupid', '', 'INT');

            $task = $jinput->get->get('task', '', 'WORD');
            $redirectUrl = CRoute::getURI(false);

            if (!empty($groupId)) {
                $allowManageVideos = CGroupHelper::allowManageVideo($groupId);
                $groupVideo = true;
            }

            $config = CFactory::getConfig();
            $tmpl = new CTemplate();
            return $tmpl->set('sort', $jinput->get('sort', 'latest', 'STRING'))
                            ->set('currentTask', JRequest::getCmd('task', ''))
                            ->set('videos', $videoEntries)
                            ->set('videoThumbWidth', CVideoLibrary::thumbSize('width'))
                            ->set('videoThumbHeight', CVideoLibrary::thumbSize('height'))
                            ->set('redirectUrl', $redirectUrl)
                            ->set('my', $my)
                            ->set('user', $user)
                            ->set('featuredList', $featuredList)
                            ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                            ->set('allowManageVideos', $allowManageVideos)
                            ->set('groupVideo', $groupVideo)
                            ->set('pagination', $pagination)
                            ->set('showFeatured', $config->get('show_featured'))
                            ->fetch('videos.list');
        }

        /**
         * 	Get Featured Videos
         *
         * 	@return		array	Objects of random featured videos
         * 	@since		1.5
         */
        private function _getFeatVideos() {
            $featured = new CFeatured(FEATURED_VIDEOS);
            $featuredVideos = $featured->getItemIds();
            $featuredList = array();

            foreach ($featuredVideos as $videoId) {
                $table = JTable::getInstance('Video', 'CTable');
                $table->load($videoId);

                if (empty($table->id))
                    continue;

                $featuredList[] = $table;
            }

            return $featuredList;
        }

        /**
         * Return sort options for videos
         */
        private function _getSortOptions() {
            $sortItems = array
                (
                'latest' => JText::_('COM_COMMUNITY_VIDEOS_SORT_LATEST'),
                'mostwalls' => JText::_('COM_COMMUNITY_VIDEOS_SORT_MOST_WALL_POST'),
                'mostviews' => JText::_('COM_COMMUNITY_VIDEOS_SORT_POPULAR'),
                'title' => JText::_('COM_COMMUNITY_VIDEOS_SORT_TITLE')
            );

            return $sortItems;
        }

        /**
         * 	Generate Featured Videos HTML
         *
         * 	@param		array	Array of video objects
         * 	@return		string	HTML
         * 	@since		1.2
         */
        private function _getFeatHTML($videos) {
            $config = CFactory::getConfig();
            $tmpl = new CTemplate();
            return $tmpl->set('videos', $videos)
                            ->set('showFeatured', $config->get('show_featured'))
                            ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                            ->set('videoThumbWidth', CVideoLibrary::thumbSize('width'))
                            ->set('videoThumbHeight', CVideoLibrary::thumbSize('height'))
                            ->fetch('videos.featured');
        }

        /**
         * Display all videos in the whole system
         * */
        public function display($id = null) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            $my = CFactory::getUser();
            $model = CFactory::getModel('videos');

            // Get category id from the query string if there are any.
            $categoryId = $jinput->get('catid', 0, 'INT');
            $category = JTable::getInstance('VideosCategory', 'CTable');
            $category->load($categoryId);

            $groupId = $jinput->get->get('groupid', '', 'INT');
            if (!empty($groupId)) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                // @rule: Test if the group is unpublished, don't display it at all.
                if (!$group->published) {
                    $this->_redirectUnpublishGroup();
                    return;
                }

                // Set pathway for group videos
                // Community > Groups > Group Name > Videos
                $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
                $this->addPathway($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
            }

            $this->_addSubmenu();
            $this->showSubmenu();

            // If we are browing by category, add additional breadcrumb and add
            // category name in the page title
            if ($categoryId != null) {
                if (!empty($groupId)) {
                    $this->addPathway(JText::_('COM_COMMUNITY_VIDEOS'), CRoute::_('index.php?option=com_community&view=videos&groupid=' . $groupId));
                } else {
                    $this->addPathway(JText::_('COM_COMMUNITY_VIDEOS'), CRoute::_('index.php?option=com_community&view=videos'));
                }

                $this->addPathway(JText::_($this->escape($category->name)), '');
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_VIDEOS_CATEGORIES') . ' : ' . str_replace('&amp;', '&', JText::_($this->escape($category->name))));
            } else {
                $this->addPathway(JText::_('COM_COMMUNITY_VIDEOS'));
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_VIDEOS_BROWSE_ALL_VIDEOS'));
            }

            $groupLink = !empty($groupId) ? '&groupid=' . $groupId : '';
            $feedLink = CRoute::_('index.php?option=com_community&view=videos' . $groupLink . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_ALL_VIDEOS_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // Featured Videos
            $featVideos = '';
            $featuredHTML = '';
            $sorted = $jinput->get('sort', 'latest', 'STRING');
            $limitstart = $jinput->get('limitstart', 0, 'INT');
            $permissions = ($my->id == 0) ? 0 : 20;
            $permissions = COwnerHelper::isCommunityAdmin() ? 40 : $permissions;

            $cat_id = isset($category->id) ? $category->id : "";

            if (!empty($groupId)) {
                $isMember = $group->isMember($my->id);
                $isMine = ($my->id == $group->ownerid);
                $isBanned = $group->isBanned($my->id);

                if (!$isMember && !$isMine && !COwnerHelper::isCommunityAdmin() && $group->approvals == COMMUNITY_PRIVATE_GROUP) {
                    $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                    return;
                }

                // cache video list.
                $videosData = $this->getVideosShowAllVideos($cat_id, $permissions, $sorted, $groupId);
                $videosHTML = $videosData['HTML'];

                $allVideosUrl = 'index.php?option=com_community&view=videos&groupid=' . $groupId;
                $catVideoUrl = 'index.php?option=com_community&view=videos&groupid=' . $groupId . '&catid=';
            } else {
                // cache video list.
                $videosData = $this->getVideosShowAllVideos($cat_id, $permissions, $sorted);
                $videosHTML = $videosData['HTML'];

                $allVideosUrl = 'index.php?option=com_community&view=videos';
                $catVideoUrl = 'index.php?option=com_community&view=videos&task=display&catid=';

                // Featured Videos
                // Cache featured video.
                // Hide featured videos if we're viewing inside a particular category
                if (empty($cat_id)) {
                    $featuredData = $this->_cachedCall('getVideosFeaturedList', array(), '', array(COMMUNITY_CACHE_TAG_FEATURED));
                    $featuredHTML = $featuredData['HTML'];
                } else {
                    $featuredHTML = '';
                }
            }

            //Cache for category
            //This is local file
            $categories = $this->getVideosCategories($categoryId);
            $sortItems = $this->_getSortOptions();
            $featuredVideoUsers = $this->_getFeatVideos();

            $tmpl = new CTemplate();
            echo $tmpl->set('sort', $jinput->get('sort', 'latest', 'STRING'))
                    ->set('currentTask', JRequest::getCmd('task', ''))
                    ->set('featuredHTML', $featuredHTML)
                    ->set('videosHTML', $videosHTML)
                    ->set('categories', $categories)
                    ->set('category', $category)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->set('allVideosUrl', $allVideosUrl)
                    ->set('catVideoUrl', $catVideoUrl)
                    ->set('featuredVideoUsers', $featuredVideoUsers)
                    ->fetch('videos.index');
        }

        public function getUserTotalVideos($userId) {
            $model = CFactory::getModel('videos');
            return count($model->getUserTotalVideos($userId));
        }

        /**
         * List All FEATURED VIDEO
         * */
        public function getVideosFeaturedList() {
            $featVideos = $this->_getFeatVideos();
            $featuredHTML['HTML'] = $this->_getFeatHTML($featVideos);
            return $featuredHTML;
        }

        /**
         * List All category
         * */
        public function getVideosCategories($categoryId) {
            $model = CFactory::getModel('videos');
            $categories = $model->getCategories($categoryId);
            return $categories;
        }

        /**
         * List All Videos
         * */
        public function getVideosShowAllVideos($category, $permissions, $sorted, $groupId = null) {

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $model = CFactory::getModel('videos');
            $limitstart = $jinput->get('limitstart', 0, 'INT');

            // Get group in category and it's children.
            $categories = $model->getAllCategories();
            $categoryIds = CCategoryHelper::getCategoryChilds($categories, $category);

            $friendsVideos = true;

            if ((int) $category > 0) {
                $categoryIds[] = (int) $category;
                $friendsVideos = false;
            }

            if (is_null($groupId)) {
                $filters = array
                    (
                    'status' => 'ready',
                    'category_id' => $categoryIds,
                    'permissions' => $permissions,
                    'or_group_privacy' => 0,
                    'sorting' => $sorted,
                    'limitstart' => $limitstart,
                    'friendsvideos' => $friendsVideos
                );
                $videos = $model->getVideos($filters);
            } else {
                $videos = $model->getGroupVideos($groupId, $categoryIds);
            }

            $pagination = $model->getPagination();
            $videosHTML['HTML'] = $this->_getVideosHTML($videos, $pagination);

            return $videosHTML;
        }

        /**
         * Application full view
         * */
        public function appFullView() {
            $document = JFactory::getDocument();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', (JText::_('COM_COMMUNITY_VIDEOS_WALL_TITLE')));


            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $applicationName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($applicationName)) {
                JError::raiseError(500, JText::_('COM_COMMUNITY_APP_ID_REQUIRED'));
            }

            $output = '';

            if ($applicationName == 'walls') {
                $limit = $jinput->request->get('limit', 5, 'INT');
                $limitstart = $jinput->request->get('limitstart', 0, 'INT');
                $videoId = JRequest::getInt('videoid', '', 'GET');
                $my = CFactory::getUser();
                $config = CFactory::getConfig();

                $video = JTable::getInstance('Video', 'CTable');
                $video->load($videoId);

                if (!$config->get('lockvideoswalls') || ($config->get('lockvideoswalls') && CFriendsHelper::isConnected($my->id, $video->creator) ) || COwnerHelper::isCommunityAdmin()) {
                    $output .= CWallLibrary::getWallInputForm($video->id, 'videos,ajaxSaveWall', 'videos,ajaxRemoveWall');
                }

                // Get the walls content
                $viewAllLink = false;
                $wallCount = false;
                if ($jinput->request->get('task', '') != 'app') {
                    $viewAllLink = CRoute::_('index.php?option=com_community&view=videos&task=app&videoid=' . $video->id . '&app=walls');
                    $wallCount = CWallLibrary::getWallCount('videos', $video->id);
                }
                $output .='<div id="wallContent">';
                $output .= CWallLibrary::getWallContents('videos', $video->id, ( COwnerHelper::isCommunityAdmin() || COwnerHelper::isMine($my->id, $video->creator)), $limit, $limitstart);
                $output .= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);
                $output .= '</div>';

                jimport('joomla.html.pagination');
                $wallModel = CFactory::getModel('wall');
                $pagination = new JPagination($wallModel->getCount($video->id, 'videos'), $limitstart, $limit);

                $output .= '<div class="cPagination">' . $pagination->getPagesLinks() . '</div>';
            } else {
                $model = CFactory::getModel('apps');
                $applications = CAppPlugins::getInstance();
                $applicationId = $model->getUserApplicationId($applicationName);

                $application = $applications->get($applicationName, $applicationId);

                if (is_callable(array($application, 'onAppDisplay'), true)) {
                    // Get the parameters
                    $manifest = CPluginHelper::getPluginPath('community', $applicationName) . '/' . $applicationName . '/' . $applicationName . '.xml';

                    $params = new CParameter($model->getUserAppParams($applicationId), $manifest);

                    $application->params = $params;
                    $application->id = $applicationId;

                    $output = $application->onAppDisplay($params);
                } else {
                    JError::raiseError(500, JText::_('COM_COMMUNITY_APPS_NOT_FOUND'));
                }
            }

            echo $output;
        }

        /**
         * View to display the search form
         * */
        public function search() {
            $document = JFactory::getDocument();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $this->addPathway(JText::_('COM_COMMUNITY_VIDEOS'), CRoute::_('index.php?option=com_community&view=videos'));
            $this->addPathway(JText::_('COM_COMMUNITY_VIDEOS_SEARCH_VIDEOS'), '');
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_VIDEOS_SEARCH_VIDEOS'));


            $this->_addSubmenu();
            $this->showSubmenu();

            $search = $jinput->request->get('search-text', '', 'STRING');
            $result = array();
            $pagination = null;
            $total = 0;

            if (!empty($search)) {
                $searchModel = CFactory::getModel('Search');
                $result = $searchModel->searchVideo($search);
                $pagination = $searchModel->getPagination();
                $total = $searchModel->getTotal();
            }

            $searchLinks = parent::getAppSearchLinks('videos');

            $pagination = is_null($pagination) ? '' : $pagination->getPagesLinks();

            $videosHTML = $this->_getVideosHTML($result);

            $tmpl = new CTemplate();
            echo $tmpl->set('videosHTML', $videosHTML)
                    ->set('pagination', $pagination)
                    ->set('videosCount', $total)
                    ->set('search', $search)
                    ->set('searchLinks', $searchLinks)
                    ->fetch('videos.search');
        }

        public function myvideos($id = null) {
            $document = JFactory::getDocument();
            $my = CFactory::getUser();
            $userid = JRequest::getInt('userid', $my->id);
            $user = CFactory::getUser($userid);

            // Set document title
            $blocked = $user->isBlocked();

            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                $tmpl = new CTemplate();
                echo $tmpl->fetch('profile.blocked');
                return;
            }

            if ($my->id == $user->id)
                $title = JText::_('COM_COMMUNITY_VIDEOS_MY');
            else
                $title = JText::sprintf('COM_COMMUNITY_VIDEOS_USERS_VIDEO_TITLE', $user->getDisplayName());

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $title);

            // Set pathway
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $this->addPathway(JText::_('COM_COMMUNITY_VIDEOS'), CRoute::_('index.php?option=com_community&view=videos'));
            $this->addPathway($title);

            $feedLink = CRoute::_('index.php?option=com_community&view=videos&userid=' . $user->id . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_MY_VIDEOS_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // Show the mini header when viewing other's photos
            if ($my->id != $user->id)
                $this->attachMiniHeaderUser($user->id);

            // Display submenu
            $this->_addSubmenu();
            $this->showSubmenu();

            // Get data from DB
            $model = CFactory::getModel('videos');
            //CFactory::load( 'helpers' , 'friends' );
            if ($my->id == $user->id || COwnerHelper::isCommunityAdmin()) {
                $permission = 40;
            } elseif (CFriendsHelper::isConnected($my->id, $user->id)) {
                $permission = 30;
            } elseif ($my->id != 0) {
                $permission = 20;
            } else {
                $permission = 10;
            }

            $filters = array
                (
                'creator' => $user->id,
                'status' => 'ready',
                'sorting' => $jinput->get('sort', 'latest', 'STRING'),
                'permissions' => $permission
            );
            $videos = $model->getVideos($filters);

            $sortItems = $this->_getSortOptions();

            //pagination
            $pagination = $model->getPagination();

            $videosHTML = $this->_getVideosHTML($videos, $pagination);

            $tmpl = new CTemplate();
            echo $tmpl->set('user', $user)
                    ->set('sort', $jinput->get('sort', 'latest', 'STRING'))
                    ->set('currentTask', JRequest::getCmd('task', ''))
                    ->set('videosHTML', $videosHTML)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->set('pagination', $pagination)
                    ->fetch('videos.myvideos');
        }

        public function mypendingvideos($id = null) {
            $document = JFactory::getDocument();
            $my = CFactory::getUser();

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $userid = JRequest::getInt('userid', '');
            $user = CFactory::getUser($userid);

            $this->_addSubmenu();
            $this->showSubmenu();

            // Set pathway
            $mainframe = JFactory::getApplication();
            $pathway = $mainframe->getPathway();
            $pathway->addItem('My Pending Videos', '');

            // Get data from DB
            $model = CFactory::getModel('videos');

            // Group video pending
            $groupid = $jinput->get('groupid', '0', 'INT');
            if (!empty($groupid)) {
                $filters = array
                    (
                    'groupid' => $groupid,
                    'status' => 'pending'
                );
            } else {
                $filters = array
                    (
                    'creator' => $user->id,
                    'groupid' => 0,
                    'status' => 'pending'
                );
            }

            $pendingVideos = $model->getVideos($filters);

            // Substitute permission in text form
            foreach ($pendingVideos as $video) {
                //$video		= $this->_getExtra($video);
                $video->isOwner = COwnerHelper::isMine($my->id, $video->creator);
            }

            $videosHTML = $this->_getVideosHTML($pendingVideos);

            $pagination = $model->getPagination();


            $tmpl = new CTemplate();

            echo $tmpl->set('videosHTML', $videosHTML)
                    ->set('sort', $jinput->get('sort', 'latest', 'STRING'))
                    ->set('currentTask', JRequest::getCmd('task', ''))
                    ->set('pendingVideos', $pendingVideos)
                    ->set('pagination', $pagination)
                    ->set('params', $this->videoLib)
                    ->fetch('videos.pending');
        }

        /**
         * Method to display video
         * @return void
         */
        public function video() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $document = JFactory::getDocument();
            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $requestUser = CFactory::getRequestUser();
            $videoId = $jinput->get->get('videoid', '', 'INT');
            $groupId = $jinput->get->get('groupid', '', 'INT');
            $task = $jinput->getCmd('task');

            // Get show video location map by default
            $videoMapsDefault = $config->get('videosmapdefault');

            // Load window library
            CWindow::load();

            $video = JTable::getInstance('Video', 'CTable');
            if (empty($videoId)) {
                if ($jinput->get('videoid', '', 'INT')) {
                    $videoId = $jinput->get('videoid', '', 'INT');
                } else {
                    $url = CRoute::_('index.php?option=com_community&view=videos', false);
                    $mainframe->redirect($url, JText::_('COM_COMMUNITY_VIDEOS_ID_ERROR'), 'warning');
                }
            }
            if (!$video->load($videoId)) {
                $url = CRoute::_('index.php?option=com_community&view=videos', false);
                $mainframe->redirect($url, JText::_('COM_COMMUNITY_VIDEOS_NOT_AVAILABLE'), 'warning');
            }

            // Setting up the sub menu

            if (COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0))) {
                $this->addSubmenuItem('', JText::_('COM_COMMUNITY_VIDEOS_FETCH_THUMBNAIL'), 'joms.videos.fetchThumbnail(\'' . $video->id . '\')', true);

                // Only add the set as profile video for video owner
                if ($my->id == $video->creator && $config->get('enableprofilevideo')) {
                    $this->addSubmenuItem('', JText::_('COM_COMMUNITY_VIDEOS_SET_AS_PROFILE'), 'joms.videos.linkConfirmProfileVideo(\'' . $video->id . '\')', true);
                }
                $redirectUrl = CRoute::getURI(false);
                $this->addSubmenuItem('', JText::_('COM_COMMUNITY_EDIT'), 'joms.videos.showEditWindow(\'' . $video->id . '\',\'' . $redirectUrl . '\');', true);
                $this->addSubmenuItem('', JText::_('COM_COMMUNITY_DELETE'), 'joms.videos.deleteVideo(\'' . $video->id . '\', \'' . $task . '\',\'' . $requestUser->id . '\')', true);
            }
            $this->_addSubmenu();
            $this->showSubmenu();

            // Show the mini header when viewing other's photos
            if (($video->creator_type == VIDEO_USER_TYPE) && ($my->id != $video->creator)) {
                $this->attachMiniHeaderUser($video->creator);
            }

            // Check permission
            $user = CFactory::getUser($video->creator);
            $blocked = $user->isBlocked();
            if ($blocked && !COwnerHelper::isCommunityAdmin()) {
                $tmpl = new CTemplate();
                echo $tmpl->fetch('profile.blocked');
                return;
            }


            $sorted = $jinput->get('sort', 'latest', 'STRING');
            $limit = $jinput->get('limitstart', 6, 'INT');
            $permissions = ($my->id == 0) ? 0 : 20;
            $cat_id = $jinput->get('cat_id', '', 'INT');
            $model = CFactory::getModel('videos');

            if ($video->creator_type == VIDEO_GROUP_TYPE) {

                if (!CGroupHelper::allowViewMedia($groupId)) {
                    /**
                     * Opengraph
                     */
                    CHeadHelper::setType('website', JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'));
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS', 'notice'));
                    echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_MEMBER_PERMISSION');
                    return;
                }

                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                // Set pathway
                $pathway = $mainframe->getPathway();
                $pathway->addItem(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
                $pathway->addItem($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
                $pathway->addItem(JText::_('COM_COMMUNITY_VIDEOS'), CRoute::_('index.php?option=com_community&view=videos&groupid=' . $groupId));
                $pathway->addItem($video->getTitle(), '');


                $otherVideos = $model->getGroupVideos($groupId, $cat_id, $limit);
            } else {
                if (!$this->isPermitted($my->id, $video->creator, $video->permissions)) {
                    /**
                     * Opengraph
                     */
                    CHeadHelper::setType('website', JText::_('COM_COMMUNITY_RESTRICTED_ACCESS'));
                    $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_RESTRICTED_ACCESS', 'notice'));

                    switch ($video->permissions) {
                        case '40':
                            $this->noAccess(JText::_('COM_COMMUNITY_VIDEOS_OWNER_ONLY', 'notice'));
                            break;
                        case '30':
                            $owner = CFactory::getUser($video->creator);
                            $this->noAccess(JText::sprintf('COM_COMMUNITY_VIDEOS_FRIEND_PERMISSION_MESSAGE', $owner->getDisplayName()));
                            break;
                        default:
                            $this->noAccess();
                            break;
                    }
                    return;
                }
                // Set pathway
                $pathway = $mainframe->getPathway();
                $pathway->addItem('Video', CRoute::_('index.php?option=com_community&view=videos'));
                $pathway->addItem($video->getTitle(), '');

                $filters = array
                    (
                    'status' => 'ready',
                    'category_id' => $cat_id,
                    'creator' => $user->id,
                    'permissions' => $permissions,
                    'or_group_privacy' => 0,
                    'sorting' => $sorted,
                    'limit' => $limit
                );
                $otherVideos = $model->getVideos($filters);
            }
            // Set the current user's active profile
            CFactory::setActiveProfile($video->creator);

            // Hit counter + 1
            $video->hit();

            // Get reporting html
            $reportHTML = '';

            $report = new CReportingLibrary();
            if($user->id != $my->id){
                $reportHTML = $report->getReportingHTML(JText::_('COM_COMMUNITY_VIDEOS_REPORT_VIDEOS'), 'videos,reportVideo', array($video->id));
            }

            // Get bookmark html
            $bookmarks = new CBookmarks($video->getPermalink());
            $bookmarksHTML = $bookmarks->getHTML();

            // Get the walls
            $wallContent = CWallLibrary::getWallContents('videos', $video->id, ( COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0))), 10, 0, 'wall.content', 'videos,video');
            $wallCount = CWallLibrary::getWallCount('videos', $video->id);

            $viewAllLink = false;

            if ($jinput->request->get('task', '') != 'app') {
                $viewAllLink = CRoute::_('index.php?option=com_community&view=videos&task=app&videoid=' . $video->id . '&app=walls');
            }
            $wallContent .= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);

            $wallForm = '';

            if ($this->isPermitted($my->id, $video->creator, PRIVACY_FRIENDS) || !$config->get('lockvideoswalls')) {
                $wallForm = CWallLibrary::getWallInputForm($video->id, 'videos,ajaxSaveWall', 'videos,ajaxRemoveWall', $viewAllLink);
            }

            $redirectUrl = CRoute::getURI(false);

            // Get like
            $likes = new CLike();
            $likesHTML = $likes->getHTML('videos', $video->id, $my->id);

            $tmpl = new CTemplate();

            if ($video->creator_type == VIDEO_GROUP_TYPE) {
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($groupId);

                $document = JFactory::getDocument();
                $document->addHeadLink($group->getThumbAvatar(), 'image_src', 'rel');
            }

            if ($video->location !== '' && $videoMapsDefault) {

                $zoomableMap = CMapping::drawZoomableMap($video->location, 220, 150);
            } else {
                $zoomableMap = "";
            }

            //friend list for video tag
            $tagging = new CVideoTagging();
            $taggedList = $tagging->getTaggedList($video->id);

            for ($t = 0; $t < count($taggedList); $t++) {
                $tagItem = $taggedList[$t];
                $tagUser = CFactory::getUser($tagItem->userid);

                $canRemoveTag = 0;
                // 1st we check the tagged user is the video owner.
                //	If yes, canRemoveTag == true.
                //	If no, then check on user is the tag creator or not.
                //	If yes, canRemoveTag == true
                //	If no, then check on user whether user is being tagged
                if (COwnerHelper::isMine($my->id, $video->creator) || COwnerHelper::isMine($my->id, $tagItem->created_by) || COwnerHelper::isMine($my->id, $tagItem->userid)) {
                    $canRemoveTag = 1;
                }

                $tagItem->user = $tagUser;
                $tagItem->canRemoveTag = $canRemoveTag;
            }

            $video->tagged = $taggedList;
            echo $tmpl->setMetaTags('video', $video)
                    ->set('user', $user)
                    ->set('zoomableMap', $zoomableMap)
                    ->set('likesHTML', $likesHTML)
                    ->set('redirectUrl', $redirectUrl)
                    ->set('wallForm', $wallForm)
                    ->set('wallContent', $wallContent)
                    ->set('bookmarksHTML', $bookmarksHTML)
                    ->set('reportHTML', $reportHTML)
                    ->set('video', $video)
                    ->set('otherVideos', $otherVideos)
                    ->set('videoMapsDefault', $videoMapsDefault)
                    ->set('wallCount', $wallCount)
                    ->set('isGroup' , $groupId )
                    ->fetch('videos.video');
        }

        /**
         * 	Check if permitted to play the video
         *
         * 	@param	int		$myid		The current user's id
         * 	@param	int		$userid		The active profile user's id
         * 	@param	int		$permission	The video's permission
         * 	@return	bool	True if it's permitted
         * 	@since	1.2
         */
        public function isPermitted($myid = 0, $userid = 0, $permissions = 0) {

            return CPrivacy::isAccessAllowed($myid, $userid, 'custom', $permissions);
        }

        public function _addSubmenu() {
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $task = $jinput->request->get('task', '', 'WORD');
            $groupId = $jinput->get->get('groupid', '', 'INT');

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);
            $isBanned = $group->isBanned($my->id);

            if (!empty($groupId)) {
                $this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId, JText::_('COM_COMMUNITY_GROUPS_BACK_TO_GROUP'));

                $videos = $this->model->hasPendingVideos($groupId, VIDEO_GROUP_TYPE);

                if ($videos) {
                    $this->addSubmenuItem('index.php?option=com_community&view=videos&task=mypendingvideos&groupid=' . $groupId, JText::_('COM_COMMUNITY_VIDEOS_GROUP_PENDING'), '', SUBMENU_LEFT);
                }

                $allowManageVideos = CGroupHelper::allowManageVideo($groupId);

                if ($allowManageVideos && !$isBanned) {
                    $this->addSubmenuItem('', JText::_('COM_COMMUNITY_ADD'), 'joms.videos.addVideo(\'' . VIDEO_GROUP_TYPE . '\', \'' . $groupId . '\')', SUBMENU_RIGHT);
                }
            } else {
                $this->addSubmenuItem('index.php?option=com_community&view=videos&task=display', JText::_('COM_COMMUNITY_VIDEOS_ALL_DESC'), '', SUBMENU_LEFT);

                if (!empty($my->id)) {
                    $this->addSubmenuItem('index.php?option=com_community&view=videos&task=myvideos&userid=' . $my->id, JText::_('COM_COMMUNITY_VIDEOS_MY'), '', SUBMENU_LEFT);
                    $this->addSubmenuItem('', JText::_('COM_COMMUNITY_ADD'), 'joms.videos.addVideo()', SUBMENU_RIGHT);
                }

                if ((!$config->get('enableguestsearchvideos') && COwnerHelper::isRegisteredUser() ) || $config->get('enableguestsearchvideos')) {
                    $tmpl = new CTemplate();
                    $tmpl->set('url', CRoute::_('index.php?option=com_community&view=videos&task=search'));
                    $html = $tmpl->fetch('videos.search.submenu');
                    $this->addSubmenuItem('index.php?option=com_community&view=videos&task=search', JText::_('COM_COMMUNITY_SEARCH'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);
                }

                $videos = $this->model->hasPendingVideos($my->id, VIDEO_USER_TYPE);

                if (!empty($my->id) && $videos) {
                    $this->addSubmenuItem('index.php?option=com_community&view=videos&task=mypendingvideos&userid=' . $my->id, JText::_('COM_COMMUNITY_VIDEOS_PENDING'), '', SUBMENU_LEFT);
                }
            }
        }

    }

}
