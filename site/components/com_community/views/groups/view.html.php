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

jimport('joomla.application.component.view');

if (!class_exists("CommunityViewGroups")) {

    class CommunityViewGroups extends CommunityView {

        public function _addGroupInPathway($groupId) {
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
        }

        public function sendmail() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_SEND_EMAIL_TO_GROUP_MEMBERS'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $id = JRequest::getInt('groupid', 0);

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($id);
            $group->updateStats(); //ensure that stats are up-to-date
            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            if ($id == 0) {
                echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
                return;
            }

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
            $this->addPathway(JText::_('COM_COMMUNITY_SEND_EMAIL_TO_GROUP_MEMBERS'));

            if (!$this->accessAllowed('registered')) {
                return;
            }

            // Display the submenu
            $this->showSubmenu();

            $my = CFactory::getUser();
            $config = CFactory::getConfig();

            $editor = new CEditor($config->get('htmleditor'));

            if (!$group->isAdmin($my->id) && !COwnerHelper::isCommunityAdmin()) {
                $this->noAccess();
                return;
            }

            $message = JRequest::getVar('message', '', 'post', 'string', JREQUEST_ALLOWRAW);
            $title = $jinput->get('title', '', 'STRING');

            $tmpl = new CTemplate();
            echo $tmpl->set('editor', $editor)
                    ->set('group', $group)
                    ->set('message', $message)
                    ->set('title', $title)
                    ->fetch('groups.sendmail');
        }

        public function _addSubmenu() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $task = $jinput->get('task', '');
            $config = CFactory::getConfig();
            $groupid = JRequest::getInt('groupid', '', 'GET');
            $categoryid = JRequest::getInt('categoryid', '', 'GET');
            $my = CFactory::getUser();


            $backLink = array('sendmail', 'invitefriends', 'viewmembers', 'viewdiscussion', 'viewdiscussions', 'editdiscussion', 'viewbulletins', 'adddiscussion', 'addnews', 'viewbulletin', 'uploadavatar', 'edit', 'banlist');

            $groupsModel = CFactory::getModel('groups');
            $isAdmin = $groupsModel->isAdmin($my->id, $groupid);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            // Load the group table.
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupid);
            $isBanned = $group->isBanned($my->id);

            if (in_array($task, $backLink)) {
                $this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, JText::_('COM_COMMUNITY_GROUPS_BACK_TO_GROUP'));
                if ($task == 'viewdiscussion' && !$isBanned)
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $groupid, JText::_('COM_COMMUNITY_GROUPS_VIEW_ALL_DISCUSSIONS'));

                $this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $groupid, JText::_('COM_COMMUNITY_GROUPS_ALL_MEMBERS'));

                if ($isAdmin || $isSuperAdmin)
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=banlist&list=' . COMMUNITY_GROUP_BANNED . '&groupid=' . $groupid, JText::_('COM_COMMUNITY_GROUPS_BAN_LIST'));

                if ($task == 'viewdiscussions' && !$isBanned)
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&groupid=' . $groupid . '&task=adddiscussion', JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE'), '', SUBMENU_RIGHT);
                if ($task == 'viewbulletins' && ($isAdmin || $isSuperAdmin))
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&groupid=' . $groupid . '&task=addnews', JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE'), '', SUBMENU_RIGHT);
                if ($task == 'viewmembers' && !$isBanned) {

                    $friends = $groupsModel->getInviteFriendsList($my->id, $groupid);
                    $userIds = '';
                    $i = 0;

                    if ($friends) {
                        foreach ($friends as $friend) {
                            if ($friend instanceof CUser) {
                                $userIds .= $friend->id;
                            } else {
                                $userIds .= $friend;
                            }

                            if (( $i + 1 ) <= count($friend)) {
                                $userIds .= ',';
                            }
                            $i++;
                        }
                    }

                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=invitefriends&groupid=' . $groupid, JText::_('COM_COMMUNITY_TAB_INVITE'), 'joms.invitation.showForm(\'' . $userIds . '\', \'groups,inviteUsers\',' . $group->id . ',1,1);', SUBMENU_RIGHT);
                }
            } else {
                $this->addSubmenuItem('index.php?option=com_community&view=groups&task=display', JText::_('COM_COMMUNITY_GROUPS_ALL_GROUPS'));

                if (COwnerHelper::isRegisteredUser()) {
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=mygroups&userid=' . $my->id, JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'));
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=mygroupupdate&userid=' . $my->id, JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS_UPDATE'));
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=myinvites&userid=' . $my->id, JText::_('COM_COMMUNITY_GROUPS_PENDING_INVITES'));
                }

                if ($config->get('creategroups') && ( $isSuperAdmin || (COwnerHelper::isRegisteredUser() && $my->canCreateGroups() ) )) {
                    $creationLink = $categoryid ? 'index.php?option=com_community&view=groups&task=create&categoryid=' . $categoryid : 'index.php?option=com_community&view=groups&task=create';
                    $this->addSubmenuItem($creationLink, JText::_('COM_COMMUNITY_GROUPS_CREATE'), '', SUBMENU_RIGHT);
                }

                if ((!$config->get('enableguestsearchgroups') && COwnerHelper::isRegisteredUser() ) || $config->get('enableguestsearchgroups')) {
                    $tmpl = new CTemplate();
                    $html = $tmpl->set('url', CRoute::_('index.php?option=com_community&view=groups&task=search'))
                            ->fetch('groups.search.submenu');
                    $this->addSubmenuItem('index.php?option=com_community&view=groups&task=search', JText::_('COM_COMMUNITY_GROUPS_SEARCH'), 'joms.groups.toggleSearchSubmenu(this)', false, $html);
                }
            }
        }

        public function showSubmenu() {
            $this->_addSubmenu();
            parent::showSubmenu();
        }

        /**
         * Display invite form
         * */
        public function invitefriends() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_INVITE_FRIENDS_TO_GROUP_TITLE'));

            if (!$this->accessAllowed('registered')) {
                return;
            }

            $this->showSubmenu();

            $my = CFactory::getUser();
            $groupId = JRequest::getInt('groupid', '', 'GET');
            $this->_addGroupInPathway($groupId);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_INVITE_FRIENDS_TO_GROUP_TITLE'));

            $friendsModel = CFactory::getModel('Friends');
            $groupsModel = CFactory::getModel('Groups');

            $tmpFriends = $friendsModel->getFriends($my->id, 'name', false);

            $friends = array();

            for ($i = 0; $i < count($tmpFriends); $i++) {
                $friend = $tmpFriends[$i];
                $groupInvite = JTable::getInstance('GroupInvite', 'CTable');
                $keys = array('groupId' => $groupId, 'userId' => $friend->id);
                $groupInvite->load($keys);

                if (!$groupsModel->isMember($friend->id, $groupId) && !$groupInvite->exists()) {
                    $friends[] = $friend;
                }
            }
            unset($tmpFriends);

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            $tmpl = new CTemplate();
            echo $tmpl->set('friends', $friends)
                    ->set('group', $group)
                    ->fetch('groups.invitefriends');
        }

        /**
         * Edit a group
         */
        public function edit() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_EDIT_TITLE'));

            $config = CFactory::getConfig();

            $this->showSubmenu();

            $js = 'assets/validate-1.5.min.js';
            CAssets::attach($js, 'js');

            $groupId = JRequest::getInt('groupid', '', 'REQUEST');
            $groupModel = CFactory::getModel('Groups');
            $categories = $groupModel->getCategories();
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            $this->_addGroupInPathway($group->id);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_EDIT_TITLE'));

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-groups-forms'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            // Load category tree
            $cTree = CCategoryHelper::getCategories($categories);
            $lists['categoryid'] = CCategoryHelper::getSelectList('groups', $cTree, $group->categoryid);

            $editorType = ($config->get('allowhtml') ) ? $config->get('htmleditor', 'none') : 'none';
            $editor = new CEditor($editorType);

            $params = $group->getParams();
            $photopermission = ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ADMINS || $params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL ) ? 1 : 0;
            $videopermission = ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_ADMINS || $params->get('videopermission') == GROUP_VIDEO_PERMISSION_ADMINS ) ? 1 : 0;
            $eventpermission = ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_ADMINS || $params->get('eventpermission') == GROUP_EVENT_PERMISSION_ADMINS ) ? 1 : 0;

            $group->discussordering = JRequest::getInt('discussordering', $params->get('discussordering'), 'POST');
            $group->grouprecentphotos = JRequest::getInt('grouprecentphotos', $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT), 'POST');
            $group->grouprecentvideos = JRequest::getInt('grouprecentvideos', $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT), 'POST');
            $group->grouprecentevents = JRequest::getInt('grouprecentevents', $params->get('grouprecentevents', GROUP_EVENT_RECENT_LIMIT), 'POST');
            $group->photopermission = JRequest::getInt('photopermission-admin', $photopermission, 'POST');
            $group->videopermission = JRequest::getInt('videopermission-admin', $videopermission, 'POST');
            $group->eventpermission = JRequest::getInt('eventpermission-admin', $eventpermission, 'POST');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('config', $config)
                    ->set('lists', $lists)
                    ->set('categories', $categories)
                    ->set('group', $group)
                    ->set('params', $group->getParams())
                    ->set('isNew', false)
                    ->set('editor', $editor)
                    ->fetch('groups.forms');
        }

        /**
         * Method to display group creation form
         * */
        public function create($data) {

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_CREATE_NEW_GROUP'));

            $config = CFactory::getConfig();

            $js = 'assets/validate-1.5.min.js';
            CAssets::attach($js, 'js');

            $this->showSubmenu();

            $my = CFactory::getUser();
            $model = CFactory::getModel('groups');
            $totalGroup = $model->getGroupsCreationCount($my->id);

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            //initialize default value
            $group = JTable::getInstance('Group', 'CTable');
            $group->name = $jinput->post->get('name', '', 'STRING'); //JRequest::getVar('name', '', 'POST');
            $group->description = $jinput->post->get('description', '', 'STRING'); //JRequest::getVar('description', '', 'POST');
            $group->email = $jinput->post->get('email', '', 'STRING'); //JRequest::getVar('email', '', 'POST');
            $group->website = $jinput->post->get('website', '', 'STRING'); //JRequest::getVar('website', '', 'POST');
            $group->categoryid = $jinput->get('categoryid', '', 'INT'); //JRequest::getVar('categoryid', '');

            $params = $group->getParams();

            $photopermission = ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ADMINS || $params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL || $params->get('photopermission') == '') ? 1 : 0;
            $videopermission = ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_ADMINS || $params->get('videopermission') == GROUP_VIDEO_PERMISSION_ADMINS || $params->get('videopermission') == '') ? 1 : 0;
            $eventpermission = ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_ADMINS || $params->get('eventpermission') == GROUP_EVENT_PERMISSION_ADMINS || $params->get('eventpermission') == '') ? 1 : 0;

            $group->discussordering = JRequest::getInt('discussordering', $params->get('discussordering'), 'POST');
            $group->grouprecentphotos = JRequest::getInt('grouprecentphotos', $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT), 'POST');
            $group->grouprecentvideos = JRequest::getInt('grouprecentvideos', $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT), 'POST');
            $group->grouprecentevents = JRequest::getInt('grouprecentevents', $params->get('grouprecentevents', GROUP_EVENT_RECENT_LIMIT), 'POST');
            $group->photopermission = JRequest::getInt('photopermission-admin', $photopermission, 'POST');
            $group->videopermission = JRequest::getInt('videopermission-admin', $videopermission, 'POST');
            $group->eventpermission = JRequest::getInt('eventpermission-admin', $eventpermission, 'POST');

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-groups-form'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            // Load category tree

            $cTree = CCategoryHelper::getCategories($data->categories);

            $lists['categoryid'] = CCategoryHelper::getSelectList('groups', $cTree, $group->categoryid);

            $editorType = ($config->get('allowhtml') ) ? $config->get('htmleditor', 'none') : 'none';

            $editor = new CEditor($editorType);

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('config', $config)
                    ->set('lists', $lists)
                    ->set('categories', $data->categories)
                    ->set('group', $group)
                    ->set('groupCreated', $totalGroup)
                    ->set('groupCreationLimit', $config->get('groupcreatelimit'))
                    ->set('params', $group->getParams())
                    ->set('isNew', true)
                    ->set('editor', $editor)
                    ->fetch('groups.forms');
        }

        /**
         * A group has just been created, should we just show the album ?
         */
        public function created() {

            $groupid = JRequest::getCmd('groupid', 0);
            $group = JTable::getInstance('Group', 'CTable');

            $group->load($groupid);
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $group->name);

            $uri = JURI::base();
            $this->showSubmenu();

            $tmpl = new CTemplate();
            echo $tmpl
                    ->setMetaTags('group', $group)
                    ->set('link', CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid))
                    ->set('linkBulletin', CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $groupid))
                    ->set('linkUpload', CRoute::_('index.php?option=com_community&view=groups&task=uploadavatar&groupid=' . $groupid))
                    ->set('linkEdit', CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $groupid))
                    ->set('linkDiscussion', CRoute::_('index.php?option=com_community&view=groups&task=adddiscussion&groupid=' . $groupid))
                    ->fetch('groups.created');
        }

        /**
         * Method to display output after saving group
         *
         * @param	JTable	Group JTable object
         * */
        public function save($group) {
            $mainframe = JFactory::getApplication();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_AVATAR_UPLOAD'));

            // Load submenus
            $this->showSubmenu();

            if (!$group->id) {
                $this->addWarning('COM_COMMUNITY_GROUPS_SAVE_ERROR');
                return;
            }
            $mainframe->enqueueMessage(JText::sprintf('COM_COMMUNITY_GROUPS_NEW_MESSAGE', $group->name));

            $tmpl = new CTemplate();
            echo $tmpl->set('group', $group)
                    ->fetch('groups.save');
        }

        /**
         * Method to display listing of groups from the site
         * */
        public function display($data = NULL) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            // Load required filterbar library that will be used to display the filtering and sorting.
            require_once( JPATH_COMPONENT . '/libraries/activities.php');

            $model = CFactory::getModel('groups');
            $avatarModel = CFactory::getModel('avatar');
            $wallsModel = CFactory::getModel('wall');

            // Get category id from the query string if there are any.
            $categoryId = JRequest::getInt('categoryid', 0);
            $category = JTable::getInstance('GroupCategory', 'CTable');
            $category->load($categoryId);


            if ($categoryId != 0) {
                $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups&task=display'));
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_CATEGORIES') . ' : ' . str_replace('&amp;', '&', JText::_($this->escape($category->name))));
            } else {
                $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'));
                /**
                 * Opengraph
                 */
                CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_BROWSE_TITLE'));
            }

            // If we are browing by category, add additional breadcrumb and add
            // category name in the page title
            /* begin: UNLIMITED LEVEL BREADCRUMBS PROCESSING */
            if ($category->parent == COMMUNITY_NO_PARENT) {
                $this->addPathway(JText::_($this->escape($category->name)), CRoute::_('index.php?option=com_community&view=groups&task=display&categoryid=' . $category->id));
            } else {
                // Parent Category
                $parentsInArray = array();
                $n = 0;
                $parentId = $category->id;

                $parent = JTable::getInstance('GroupCategory', 'CTable');

                do {
                    $parent->load($parentId);
                    $parentId = $parent->parent;

                    $parentsInArray[$n]['id'] = $parent->id;
                    $parentsInArray[$n]['parent'] = $parent->parent;
                    $parentsInArray[$n]['name'] = JText::_($this->escape($parent->name));

                    $n++;
                } while ($parent->parent > COMMUNITY_NO_PARENT);

                for ($i = count($parentsInArray) - 1; $i >= 0; $i--) {
                    $this->addPathway($parentsInArray[$i]['name'], CRoute::_('index.php?option=com_community&view=groups&task=display&categoryid=' . $parentsInArray[$i]['id']));
                }
            }
            /* end: UNLIMITED LEVEL BREADCRUMBS PROCESSING */


            $config = CFactory::getConfig();
            $my = CFactory::getUser();
            $uri = JURI::base();
            $discussionModel = CFactory::getModel('discussions');

            $this->showSubmenu();

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_LATEST_GROUPS_FEED') . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewlatestdiscussions&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_LATEST_GROUP_DISCUSSIONS_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $data = new stdClass();
            $sorted = $jinput->get->get('sort', 'latest', 'STRING'); //JRequest::getVar( 'sort' , 'latest' , 'GET' );
            $limitstart = $jinput->get('limitstart', 0, 'INT'); //JRequest::getVar( 'limitstart' , 0 );
            //cache groups categories
            $data->categories = $this->_cachedCall('getGroupsCategories', array($category->id), '', array(COMMUNITY_CACHE_TAG_GROUPS_CAT));

            // cache groups list.
            $user = JFactory::getUser();
            $username = $user->get('username');
            $featured = (!is_null($username) ) ? true : false;

            $groupsData = $this->_cachedCall('getShowAllGroups', array($category->id, $sorted, $featured), COwnerHelper::isCommunityAdmin($my->id), array(COMMUNITY_CACHE_TAG_GROUPS));
            $groupsHTML = $groupsData['HTML'];

            $act = new CActivityStream();

            //Cache Group Featured List
            $featuredGroups = $this->_cachedCall('_getGroupsFeaturedList', array(), '', array(COMMUNITY_CACHE_TAG_FEATURED));
            $featuredHTML = $featuredGroups['HTML'];

            //no Featured Group headline slideshow on Category filtered page
            if (!empty($categoryId))
                $featuredHTML = '';

            $tmpl = new CTemplate($this);

            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_GROUPS_SORT_LATEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL'),
                'mostactive' => JText::_('COM_COMMUNITY_GROUPS_SORT_MOST_ACTIVE'));

            echo $tmpl->set('featuredHTML', $featuredHTML)
                    ->set('index', true)
                    ->set('categories', $data->categories)
                    ->set('groupsHTML', $groupsHTML)
                    ->set('config', $config)
                    ->set('category', $category)
                    ->set('categoryId', $categoryId)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->set('my', $my)
                    ->set('discussionsHTML', $this->modPublicDiscussion($categoryId))
                    ->fetch('groups.index');
        }

        /**
         * List All FEATURED GROUPS
         * @ since 2.6
         * */
        public function _getGroupsFeaturedList() {
            $featGroups = $this->getGroupsFeaturedList();
            $featuredHTML['HTML'] = $this->_getFeatHTML($featGroups);

            return $featuredHTML;
        }

        /**
         * 	Generate Featured Groups HTML
         *
         * 	@param		array	Array of events objects
         * 	@return		string	HTML
         * 	@since		2.6
         */
        private function _getFeatHTML($groups) {
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $group = JTable::getInstance('Group', 'CTable');

            $tmpl = new CTemplate();
            return $tmpl->set('groups', $groups)
                            ->set('showFeatured', $config->get('show_featured'))
                            ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                            ->set('my', $my)
                            ->fetch('groups.featured');
        }

        /**
         * @since 2.6
         * mygroupupdates page
         *
         */
        public function myGroupUpdate() {
            $groupModel = CFactory::getModel('groups');

            $jinput = JFactory::getApplication()->input;
            $userId = $jinput->get->get('userid', NULL, 'INT');

            $my = CFactory::getUser($userId);

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS_UPDATE'));

            // Add the miniheader if necessary
            if ($my->id > 0)
                $this->attachMiniHeaderUser($my->id);

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS_UPDATE'), '');

            $this->showSubmenu();

            //get the groups of current user
            $userGroupArr = $groupModel->getGroupIds($my->id);

            $groupInfoArr = array(); //to store all the groups info that belongs to current user

            foreach ($userGroupArr as $userGrp) {
                $table = JTable::getInstance('Group', 'CTable');
                $table->load($userGrp);


                $groupInfoArr[] = array('thumb' => $table->getThumbAvatar());
                //$groupInfoArr[]	= $table->getThumbAvatar();
            //
		}

            $tmpl = new CTemplate();
            echo $tmpl->set('userid', $my->id)
                    ->set('my', $my)
                    ->fetch('groups.updates');
        }

        /**
         * @since 2.6
         * module for user participated discussion
         */
        public function modUserParticipatedDiscussion($userId, $limit = 10) {
            return $this->_getUserParticipatedDiscussionUpdates($userId, $limit);
        }

        public function modGetUserParticipatedDiscussion($userId, $limit = 10) {
            $groupsModel = CFactory::getModel('groups');
            $latestParticipatedDiscussion = $groupsModel->getGroupDiscussionLastActive($userId);

            return (array) $latestParticipatedDiscussion;
        }

        /**
         * @since 2.6
         * module for user participated discussion
         */
        private function _getUserParticipatedDiscussionUpdates($userId, $limit) {
            $groupsModel = CFactory::getModel('groups');
            $latestParticipatedDiscussion = $groupsModel->getGroupDiscussionLastActive($userId);

            $tmpl = new CTemplate();
            return $tmpl->set('discussions', $latestParticipatedDiscussion)
                            ->fetch('groups.discussion.updates');
        }

        /**
         * @since 2.6
         * module for user participated discussion
         */
        public function modUserAnnouncement($userId, $limit = 5) {
            return $this->_getUserGroupAnnouncementUpdates($userId, $limit);
        }

        public function modGetUserAnnouncement($userId, $limit = 5) {
            $groupsModel = CFactory::getModel('groups');
            $latestAnnouncement = $groupsModel->getGroupAnnouncementUpdate($userId, $limit);
            return $latestAnnouncement;
        }

        /**
         * @since 2.6
         * module for user participated discussion
         */
        private function _getUserGroupAnnouncementUpdates($userId, $limit) {
            $groupsModel = CFactory::getModel('groups');
            $latestAnnouncement = $groupsModel->getGroupAnnouncementUpdate($userId, $limit);

            $tmpl = new CTemplate();
            return $tmpl->set('announcements', $latestAnnouncement)
                            ->fetch('groups.announcement.updates');
        }

        /**
         * @since 2.6
         * module for user's participated group upcoming events
         */
        public function modUserGroupUpcomingEvents($userId, $limit = 5) {
            return $this->_getUserGroupUpcomingEvents($userId, $limit);
        }

        /**
         * @since 2.6
         * to get user's participated group upcoming events
         */
        private function _getUserGroupUpcomingEvents($userId, $limit) {
            $groupsModel = CFactory::getModel('groups');
            $latestEvents = $groupsModel->getGroupUpcomingEvents($userId, $limit);

            $tmpl = new CTemplate();
            return $tmpl->set('events', $latestEvents)
                            ->fetch('groups.events.updates');
        }

        /**
         * @since 2.6
         * module for user's participated group album updates
         */
        public function modUserAlbumsUpdate($userId, $limit = 5) {
            return $this->_getUserAlbumsUpdate($userId, $limit);
        }

        private function _getUserAlbumsUpdate($userId, $limit) {
            $groupsModel = CFactory::getModel('groups');
            $latestAlbumUpdate = $groupsModel->getGroupLatestAlbumUpdate($userId, $limit);

            $tmpl = new CTemplate();
            return $tmpl->set('albums', $latestAlbumUpdate)
                            ->fetch('groups.album.updates');
        }

        /**
         * @since 2.6
         * module for user's participated group videos updates
         */
        public function modUserGroupVideosUpdate($userId, $limit = 5) {
            return $this->_getUserGroupVideosUpdate($userId, $limit);
        }

        private function _getUserGroupVideosUpdate($userId, $limit) {
            $groupsModel = CFactory::getModel('groups');
            $groupVideos = $groupsModel->getGroupVideosUpdate($userId, $limit);

            $tmpl = new CTemplate();
            return $tmpl->set('videos', $groupVideos)
                            ->fetch('groups.videos.updates');
        }

        /**
         * @since 2.6
         * module for user's group
         */
        public function modUserGroups($userId, $limit = 12) {
            return $this->_getUserGroups($userId, $limit);
        }

        public function modGetUserGroups($userId, $limit = 5) {
            $groupsModel = CFactory::getModel('groups');
            $groupsId = $groupsModel->getGroupIds($userId, $limit);

            $groupsDetail = array();
            $count = 1;
            foreach ($groupsId as $group) {
                if ($count == $limit) {
                    break;
                }

                $count++;

                $table = JTable::getInstance('Group', 'CTable');
                $table->load($group);
                $groupsDetail[] = array('group_url' => CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group), 'avatar' => $table->getThumbAvatar(), 'group_name' => $table->name);
            }

            $tmpl = new CTemplate();
            return $groupsDetail;
        }

        private function _getUserGroups($userId, $limit) {
            $groupsModel = CFactory::getModel('groups');
            $groupsId = $groupsModel->getGroupIds($userId);

            $groupsDetail = array();
            $count = 1;
            foreach ($groupsId as $group) {
                if ($count == $limit) {
                    break;
                }

                $count++;

                $table = JTable::getInstance('Group', 'CTable');
                $table->load($group);
                $groupsDetail[] = array('group_url' => CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group), 'avatar' => $table->getThumbAvatar(), 'group_name' => $table->name);
            }

            $tmpl = new CTemplate();
            return $tmpl->set('usergroups', $groupsDetail)
                            ->fetch('groups.user.group');
        }

        public function modUserGroupPending($userid) {
            return $this->_getPendingListHTML($userid);
        }

        /**
         * showGroupsFeaturedList
         * */
        public function getGroupsFeaturedList() {
            $featured = new CFeatured(FEATURED_GROUPS);
            $featuredGroups = $featured->getItemIds();
            $featuredList = array();

            foreach ($featuredGroups as $group) {
                $table = JTable::getInstance('Group', 'CTable');
                $table->load($group);
                $featuredList[] = $table;
            }
            return $featuredList;
        }

        /**
         * showGroupsCategory
         * */
        public function getGroupsCategories($category) {

            $model = CFactory::getModel('groups');
            $categories = $model->getCategoriesCount();

            $categories = CCategoryHelper::getParentCount($categories, $category);

            return $categories;
        }

        /**
         * showAllGroups
         * */
        public function getShowAllGroups($category, $sorted) {
            $model = CFactory::getModel('groups');

            // Get group in category and it's children.
            $categories = $model->getAllCategories();
            $categoryIds = CCategoryHelper::getCategoryChilds($categories, $category);
            if ((int) $category > 0) {
                $categoryIds[] = (int) $category;
            }

            // It is safe to pass 0 as the category id as the model itself checks for this value.
            $data = new StdClass;
            $data->groups = $model->getAllGroups($categoryIds, $sorted);

            // Get pagination object
            $data->pagination = $model->getPagination();

            // Get the template for the group lists
            $groupsHTML['HTML'] = $this->_getGroupsHTML($data->groups, $data->pagination);

            return $groupsHTML;
        }

        /**
         * Application full view
         * */
        public function discussAppFullView() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_REPLY'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $applicationName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($applicationName)) {
                JError::raiseError(500, 'COM_COMMUNITY_APP_ID_REQUIRED');
            }

            $output = '';
            $topicId = $jinput->get('topicid', '', 'INT');

            $model = CFactory::getModel('discussions');
            $discussion = JTable::getInstance('Discussion', 'CTable');
            $discussion->load($topicId);

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($discussion->groupid);

            $this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $topicId, JText::_('COM_COMMUNITY_BACK_TO_TOPIC'));
            parent::showSubmenu();

            //@todo: Since group walls doesn't use application yet, we process it manually now.
            if ($applicationName == 'walls') {
                $limit = $jinput->request->get('limit', 5, 'INT');
                $limitstart = $jinput->request->get('limitstart', 0, 'INT');

                $my = CFactory::getUser();
                $config = CFactory::getConfig();
                $isBanned = $group->isBanned($my->id);

                if (!$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && $group->isMember($my->id) && !$isBanned ) || COwnerHelper::isCommunityAdmin()) {
                    $outputLock = '<div class="cAlert">' . JText::_('COM_COMMUNITY_DISCUSSION_LOCKED_NOTICE') . '</div>';
                    $outputUnLock = CWallLibrary::getWallInputForm($discussion->id, 'groups,ajaxSaveDiscussionWall', 'groups,ajaxRemoveWall');
                    $wallForm = $discussion->lock ? $outputLock : $outputUnLock;

                    $output .= $wallForm;
                }

                // Get the walls content
                $output .='<div id="wallContent">';
                $output .= CWallLibrary::getWallContents('discussions', $discussion->id, ($my->id == $discussion->creator), $limit, $limitstart, 'wall.content', 'groups,discussion');
                $output .= '</div>';

                jimport('joomla.html.pagination');
                $wallModel = CFactory::getModel('wall');
                $pagination = new JPagination($wallModel->getCount($discussion->id, 'discussions'), $limitstart, $limit);

                $output .= '<div class="cPagination">' . $pagination->getPagesLinks() . '</div>';
            } else {
                $model = CFactory::getModel('apps');
                $applications = CAppPlugins::getInstance();
                $applicationId = $model->getUserApplicationId($applicationName);

                $application = $applications->get($applicationName, $applicationId);

                // Get the parameters
                $manifest = CPluginHelper::getPluginPath('community', $applicationName) . '/' . $applicationName . '/' . $applicationName . '.xml';

                $params = new CParameter($model->getUserAppParams($applicationId), $manifest);

                $application->params = $params;
                $application->id = $applicationId;

                $output = $application->onAppDisplay($params);
            }

            echo $output;
        }

        /**
         * Application full view
         * */
        public function appFullView() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_WALL_TITLE'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $applicationName = JString::strtolower($jinput->get->get('app', '', 'STRING'));

            if (empty($applicationName)) {
                JError::raiseError(500, 'COM_COMMUNITY_APP_ID_REQUIRED');
            }

            $output = '';

            $groupModel = CFactory::getModel('groups');
            $groupId = JRequest::getInt('groupid', '', 'GET');
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            //@todo: Since group walls doesn't use application yet, we process it manually now.
            if ($applicationName == 'walls') {
                $limit = JRequest::getInt('limit', 5, 'REQUEST');
                $limitstart = JRequest::getInt('limitstart', 0, 'REQUEST');

                $my = CFactory::getUser();
                $config = CFactory::getConfig();

                $isBanned = $group->isBanned($my->id);
                // Test if the current browser is a member of the group
                $isMember = $group->isMember($my->id);
                $waitingApproval = $groupModel->isWaitingAuthorization($my->id, $group->id);

                if (!$isMember && !COwnerHelper::isCommunityAdmin() && $group->approvals == COMMUNITY_PRIVATE_GROUP) {
                    $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                    return;
                }

                if (!$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && ($isMember && !$isBanned) && !($waitingApproval) ) || COwnerHelper::isCommunityAdmin()) {
                    $output .= CWallLibrary::getWallInputForm($group->id, 'groups,ajaxSaveWall', 'groups,ajaxRemoveWall');
                }

                // Get the walls content
                $output .='<div id="wallContent">';
                if (!$isBanned) {
                    $output .= CWallLibrary::getWallContents('groups', $group->id, ($my->id == $group->ownerid), $limit, $limitstart, 'wall.content', 'groups,group');
                } else {
                    $output .= CWallLibrary::getWallContents('groups', $group->id, ($my->id == $group->ownerid), $limit, $limitstart, 'wall.content', 'groups,group', null, 1);
                }
                $output .= '</div>';

                jimport('joomla.html.pagination');
                $wallModel = CFactory::getModel('wall');
                $pagination = new JPagination($wallModel->getCount($group->id, 'groups'), $limitstart, $limit);

                $output .= '<div class="cPagination">' . $pagination->getPagesLinks() . '</div>';
            } else {
                $model = CFactory::getModel('apps');
                $applications = CAppPlugins::getInstance();
                $applicationId = $model->getUserApplicationId($applicationName);

                $application = $applications->get($applicationName, $applicationId);

                if (!$application) {
                    JError::raiseError(500, 'COM_COMMUNITY_APPS_NOT_FOUND');
                }

                // Get the parameters
                $manifest = CPluginHelper::getPluginPath('community', $applicationName) . '/' . $applicationName . '/' . $applicationName . '.xml';

                $params = new CParameter($model->getUserAppParams($applicationId), $manifest);

                $application->params = $params;
                $application->id = $applicationId;

                $output = $application->onAppDisplay($params);
            }

            echo $output;
        }

        public function _getUnapproved($members) {
            $return = array();
            foreach ($members as $member) {
                if ($member->approved == 0) {
                    $return[] = $member;
                }
            }
            return $return;
        }

        public function _getApproved($members) {
            $return = array();
            foreach ($members as $member) {
                if ($member->approved == 1) {
                    $return[] = $member;
                }
            }
            return $return;
        }

        public function _isBanned($members, $myId) {
            foreach ($members as $member) {
                if ($member->id == $myId && $member->permission == COMMUNITY_GROUP_BANNED) {
                    return true;
                }
            }
        }

        /**
         * Displays specific groups
         * */
        public function viewGroup($group) {
            CWindow::load();

            $config        = CFactory::getConfig();
            $document      = JFactory::getDocument();
            $groupLib      = new CGroups();
            $mainframe     = JFactory::getApplication();
            $jinput        = $mainframe->input;

            // Load appropriate models
            $groupModel    = CFactory::getModel('groups');
            $wallModel     = CFactory::getModel('wall');
            $userModel     = CFactory::getModel('user');
            $discussModel  = CFactory::getModel('discussions');
            $bulletinModel = CFactory::getModel('bulletins');
            $photosModel   = CFactory::getModel('photos');
            $activityModel = CFactory::getModel('activities');
            $fileModel     = CFactory::getModel('files');

            $editGroup     = $jinput->get->get('edit', FALSE, 'NONE');
            $editGroup     = ( $editGroup == 1 ) ? true : false;
            $params        = $group->getParams();

            /**
             * Opengraph
             * @todo Support group avatar og:image
             */
            CHeadHelper::setType('website', CStringHelper::escape($group->name), CStringHelper::escape(strip_tags($group->description)));
            $document->addCustomTag('<link rel="image_src" href="' . JURI::root() . $group->thumb . '" />');

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            // Show submenu
            $this->showSubmenu();
            $group->hit();

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway(JText::sprintf('COM_COMMUNITY_GROUPS_NAME_TITLE', $group->name), '');

            // Load the current browsers data
            $my = CFactory::getUser();

            // If user are invited
            $isInvited = $groupModel->isInvited($my->id, $group->id);

            // Get members list for display
            //$members	= $groupModel->getAllMember($group->id);
            //Set limit for member listing on view group page
            $limit = 12;
            $approvedMembers = $groupModel->getMembers($group->id, $limit, true, false, true);
            CError::assert($approvedMembers, 'array', 'istype', __FILE__, __LINE__);
            $membersCount = $groupModel->total;

            // Is there any my friend is the member of this group?
            $join = '';
            $friendsCount = 0;
            if ($isInvited) {
                // Get the invitors
                $invitors = $groupModel->getInvitors($my->id, $group->id);

                if (count($invitors) == 1) {
                    $user = CFactory::getUser($invitors[0]->creator);
                    $join = '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                } else {
                    for ($i = 0; $i < count($invitors); $i++) {
                        $user = CFactory::getUser($invitors[$i]->creator);

                        if (($i + 1 ) == count($invitors)) {
                            $join .= ' ' . JText::_('COM_COMMUNITY_AND') . ' ' . '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                        } else {
                            $join .= ', ' . '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
                        }
                    }
                }

                // Get users friends in this group
                $friendsCount = $groupModel->getFriendsCount($my->id, $group->id);
            }

            // Get list of unapproved members
            $groupModel->getMembers($group->id, null, false);
            $unapproved = $groupModel->total;

            // Test if the current user is admin
            $isAdmin = $groupModel->isAdmin($my->id, $group->id);

            // Test if the current browser is a member of the group
            $isMember = $groupModel->isMember($my->id, $group->id);
            $waitingApproval = false;

            // Test if the current user is banned from this group
            $isBanned = $group->isBanned($my->id);

            // Attach avatar of the member
            // Pre-load multiple users at once
            $userids = array();
            $limitloop = $limit;
            foreach ($approvedMembers as $uid) {
                $userids[] = $uid->id;
                if (--$limitloop < 1)
                    break;
            }
            CFactory::loadUsers($userids);

            $limitloop = $limit;
            for ($i = 0; ($i < count($approvedMembers)); $i++) {
                $row = $approvedMembers[$i];
                $approvedMembers[$i] = CFactory::getUser($row->id);
                if (--$limitloop < 1)
                    break;
            }

            if ($isBanned) {
                $mainframe = JFactory::getApplication();
                $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_MEMBER_BANNED'), 'error');
            }

            // If I have tried to join this group, but not yet approved, display a notice
            if ($groupModel->isWaitingAuthorization($my->id, $group->id)) {
                $waitingApproval = true;
            }

            // Get like
            $likes = new CLike();
            $isUserLiked = false;

            if ($isLikeEnabled = $likes->enabled('groups')) {
                $isUserLiked = $likes->userLiked('groups', $group->id, $my->id);
            }

            $totalLikes = $likes->getLikeCount('groups', $group->id);

            // Get discussions data
            $discussionData = $this->_cachedCall('_getDiscussionListHTML', array($params, $group->id), $group->id, array(COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
            $discussionsHTML = $discussionData['HTML'];
            $totalDiscussion = $discussionData['total'];
            $discussions = $discussionData['data'];

            // Get bulletins data
            $bulletinData = $this->_cachedCall('_getBulletinListHTML', array($group->id), $group->id, array(COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
            $totalBulletin = $bulletinData['total'];
            $bulletinsHTML = $bulletinData['HTML'];
            $bulletins = $bulletinData['data'];

            // Get album data
            $albumData = $this->_cachedCall('_getAlbums', array($params, $group->id), $group->id, array(COMMUNITY_CACHE_TAG_GROUPS_DETAIL));
            $albums = $albumData['data'];
            $totalAlbums = $albumData['total'];

            // Get video data
            $videoData = $this->_getVideos($params, $group->id);
            $videos = $videoData['data'];
            $totalVideos = $videoData['total'];

            $tmpl = new CTemplate();

            $isMine = ($my->id == $group->ownerid);
            $isSuperAdmin = COwnerHelper::isCommunityAdmin();

            if ($group->approvals == '1' && !$isMine && !$isMember && !$isSuperAdmin) {
                $this->addWarning(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
            }

            $eventsModel = CFactory::getModel('Events');
            $tmpEvents = $eventsModel->getGroupEvents($group->id, $params->get('grouprecentevents', GROUP_EVENT_RECENT_LIMIT));
            $totalEvents = $eventsModel->getTotalGroupEvents($group->id);

            $events = array();
            foreach ($tmpEvents as $event) {
                $table = JTable::getInstance('Event', 'CTable');
                $table->bind($event);
                $events[] = $table;
            }

            $allowCreateEvent = CGroupHelper::allowCreateEvent($my->id, $group->id);

            if ($group->approvals == '0' || $isMine || ($isMember && !$isBanned) || $isSuperAdmin) {
                // Set feed url
                $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id . '&format=feed');
                $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_BULLETIN_FEEDS') . '" href="' . $feedLink . '"/>';
                $document->addCustomTag($feed);

                $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id . '&format=feed');
                $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_DISCUSSION_FEEDS') . '" href="' . $feedLink . '"/>';
                $document->addCustomTag($feed);

                $feedLink = CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&format=feed');
                $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_GROUP_PHOTOS_FEEDS') . '" href="' . $feedLink . '"/>';
                $document->addCustomTag($feed);

                $feedLink = CRoute::_('index.php?option=com_community&view=videos&groupid=' . $group->id . '&format=feed');
                $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_GROUP_VIDEOS_FEEDS') . '"  href="' . $feedLink . '"/>';
                $document->addCustomTag($feed);

                $feedLink = CRoute::_('index.php?option=com_community&view=events&groupid=' . $group->id . '&format=feed');
                $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_GROUP_EVENTS_FEEDS') . '"  href="' . $feedLink . '"/>';
                $document->addCustomTag($feed);
            }

            // Upgrade wall to stream @since 2.5
            if (!$params->get('stream', FALSE)) {
                $group->upgradeWallToStream();
            }

            $group->coverPostion = $params->get('coverPosition', 0);
            $group->defaultCover = (empty($group->cover)) ? true : false;

            // Add custom stream
            $activities = new CActivities();
            $streamHTML = $activities->getOlderStream(1000000000, 'active-group', $group->id);
            $totalStream = $activityModel->getTotalActivities(array("`groupid` = '{$group->id}'"));

            $creators = array();
            $creators[] = CUserStatusCreator::getMessageInstance();
            if (( ($isAdmin || $isSuperAdmin) && $params->get('photopermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('photopermission') == 2) || $isSuperAdmin)
                $creators[] = CUserStatusCreator::getPhotoInstance();
            if (( ($isAdmin || $isSuperAdmin) && $params->get('videopermission') == 1) || (($isMember || $isSuperAdmin) && $params->get('videopermission') == 2) || $isSuperAdmin)
                $creators[] = CUserStatusCreator::getVideoInstance();
            if (($allowCreateEvent || $isSuperAdmin ) && $config->get('group_events') && $config->get('enableevents') && ($config->get('createevents') ) || $isSuperAdmin)
                $creators[] = CUserStatusCreator::getEventInstance();

            $status = new CUserStatus($group->id, 'groups', $creators);

            // Get Event Admins
            $groupAdmins = $group->getAdmins(12, CC_RANDOMIZE);
            $adminsInArray = array();

            // Attach avatar of the admin
            for ($i = 0; ($i < count($groupAdmins)); $i++) {
                $row = $groupAdmins[$i];
                $admin = CFactory::getUser($row->id);
                array_push($adminsInArray, '<a href="' . CUrlHelper::userLink($admin->id) . '">' . $admin->getDisplayName() . '</a>');
            }

            $totalPhotos = 0;
            foreach ($albums as $album) {
                $albumParams = new CParameter($album->params);
                $totalPhotos = $totalPhotos + $albumParams->get('count');
            }

            $adminsList = ltrim(implode(', ', $adminsInArray), ',');

            $showMoreActivity = ($totalStream <= $config->get('maxactivities')) ? false : true;


            /* Opengraph */
            CHeadHelper::addOpengraph('og:image', $group->getAvatar('avatar'), true);
            CHeadHelper::addOpengraph('og:image', $group->getCover(), true);

            echo $tmpl->setMetaTags('group', $group)
                    ->set('streamHTML', $streamHTML)
                    ->set('showMoreActivity', $showMoreActivity)
                    ->set('status', $status)
                    ->set('events', $events)
                    ->set('totalEvents', $totalEvents)
                    ->set('showEvents', $config->get('group_events') && $config->get('enableevents') && $params->get('eventpermission',1) >= 1)
                    ->set('showPhotos', ( $params->get('photopermission') != -1 ) && $config->get('enablephotos') && $config->get('groupphotos'))
                    ->set('showVideos', ( $params->get('videopermission') != -1 ) && $config->get('enablevideos') && $config->get('groupvideos'))
                    ->set('eventPermission', $params->get('eventpermission'))
                    ->set('photoPermission', $params->get('photopermission'))
                    ->set('videoPermission', $params->get('videopermission'))
                    ->set('allowCreateEvent', $allowCreateEvent)
                    ->set('videos', $videos)
                    ->set('totalVideos', $totalVideos)
                    ->set('albums', $albums)
                    ->set('editGroup', $editGroup)
                    ->set('waitingApproval', $waitingApproval)
                    ->set('config', $config)
                    ->set('isMine', $isMine)
                    ->set('isAdmin', $isAdmin)
                    ->set('isSuperAdmin', $isSuperAdmin)
                    ->set('isMember', $isMember)
                    ->set('isInvited', $isInvited)
                    ->set('friendsCount', $friendsCount)
                    ->set('join', $join)
                    ->set('unapproved', $unapproved)
                    ->set('membersCount', $membersCount)
                    ->set('group', $group)
                    ->set('totalBulletin', $totalBulletin)
                    ->set('totalDiscussion', $totalDiscussion)
                    ->set('totalVideos', $totalVideos)
                    ->set('members', $approvedMembers)
                    ->set('bulletins', $bulletins)
                    ->set('discussions', $discussions)
                    ->set('discussionsHTML', $discussionsHTML)
                    ->set('bulletinsHTML', $bulletinsHTML)
                    ->set('isBanned', $isBanned)
                    ->set('isPrivate', $group->approvals)
                    ->set('limit', $limit)
                    ->set('adminsList', $adminsList)
                    ->set('isFile', $fileModel->isfileAvailable($group->id, 'group') )
                    /* Set notification counts */
                    ->set('alertNewDiscussion', $my->count('group_discussion_' . $group->id) != $totalDiscussion)
                    ->set('alertNewBulletin', $my->count('group_bulletin_' . $group->id) != $totalBulletin)
                    ->set('alertNewStream', $my->count('group_activity_' . $group->id) != $totalStream)
                    ->set('isUserLiked', $isUserLiked)
                    ->set('totalLikes', $totalLikes)
                    ->set('isLikeEnabled', $isLikeEnabled)
                    ->set('totalPhotos', $totalPhotos)
                    ->set('totalAlbums', $totalAlbums)
                    ->fetch('groups.viewgroup');

            // Update stream count cache, can only set this after we've set the alert aove
            $my->setCount('group_activity_' . $group->id, $totalStream);
        }

        public function uploadAvatar($data) {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_AVATAR_UPLOAD'));

            $this->_addGroupInPathway($data->id);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_AVATAR_UPLOAD'));

            $this->showSubmenu();

            $config = CFactory::getConfig();
            $uploadLimit = (double) $config->get('maxuploadsize');
            $uploadLimit .= 'MB';

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($data->id);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-groups-uploadavatar'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('groupId', $data->id)
                    ->set('avatar', $group->getAvatar('avatar'))
                    ->set('thumbnail', $group->getAvatar())
                    ->set('uploadLimit', $uploadLimit)
                    ->fetch('groups.uploadavatar');
        }

        /**
         * Method to display groups that belongs to a user.
         *
         * @access public
         */
        public function mygroups($userid) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $document = JFactory::getDocument();
            //$userid   	= JRequest::getInt('userid', null );
            $user = CFactory::getUser($userid);
            $my = CFactory::getUser();

            // Respect profile privacy setting.
            if (!CPrivacy::isAccessAllowed($my->id, $user->id, 'user', 'privacyGroupsView')) {
                //echo JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN');
                echo "<div class=\"cEmpty cAlert\">" . JText::_('COM_COMMUNITY_PRIVACY_ERROR_MSG') . "</div>";
                return;
            }

            $title = ($my->id == $user->id) ? JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS') : JText::sprintf('COM_COMMUNITY_GROUPS_USER_TITLE', $user->getDisplayName());
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $title);

            // Add the miniheader if necessary
            if ($my->id != $user->id)
                $this->attachMiniHeaderUser($user->id);

            // Load required filterbar library that will be used to display the filtering and sorting.

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'), '');

            $this->showSubmenu();

            $uri = JURI::base();

            //@todo: make mygroups page to contain several admin tools for owner?

            $groupsModel = CFactory::getModel('groups');
            $avatarModel = CFactory::getModel('avatar');
            $wallsModel = CFactory::getModel('wall');
            $activityModel = CFactory::getModel('activities');
            $discussionModel = CFactory::getModel('discussions');
            $sorted = $jinput->get->get('sort', 'latest', 'STRING'); //JRequest::getVar( 'sort' , 'latest' , 'GET' );
            // @todo: proper check with CError::assertion
            // Make sure the sort value is not other than the array keys

            $groups = $groupsModel->getGroups($user->id, $sorted);
            $pagination = $groupsModel->getPagination(count($groups));

            require_once( JPATH_COMPONENT . '/libraries/activities.php');
            $act = new CActivityStream();

            // Attach additional properties that the group might have
            $groupIds = '';
            if ($groups) {
                foreach ($groups as $group) {
                    $groupIds = (empty($groupIds)) ? $group->id : $groupIds . ',' . $group->id;
                }
            }

            // Get the template for the group lists
            $groupsHTML = $this->_getGroupsHTML($groups, $pagination);

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid=' . $userid . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_LATEST_MY_GROUPS_FEED') . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewmylatestdiscussions&groupids=' . $groupIds . '&userid=' . $userid . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_LATEST_MY_GROUP_DISCUSSIONS_FEED') . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_GROUPS_SORT_LATEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL'),
                'mostactive' => JText::_('COM_COMMUNITY_GROUPS_SORT_MOST_ACTIVE'));

            $tmpl = new CTemplate();
            echo $tmpl->set('groupsHTML', $groupsHTML)
                    ->set('pagination', $pagination)
                    ->set('my', $my)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->set('discussionsHTML', $this->modUserDiscussion($user->id))
                    ->fetch('groups.mygroups');
        }

        public function myinvites() {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $userId = $jinput->get('userid', '', 'INT');

            $config = CFactory::getConfig();
            // Load required filterbar library that will be used to display the filtering and sorting.
            $document = JFactory::getDocument();

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_PENDING_INVITES'), '');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_PENDING_INVITES'));

            $this->showSubmenu();

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid=' . $userId . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_PENDING_INVITATIONS_FEED') . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            $my = CFactory::getUser();
            $model = CFactory::getModel('groups');
            $discussionModel = CFactory::getModel('discussions');
            $sorted = $jinput->get->get('sort', 'latest', 'STRING');

            $rows = $model->getGroupInvites($my->id);
            $pagination = $model->getPagination(count($rows));
            $groups = array();
            $ids = '';

            if ($rows) {
                foreach ($rows as $row) {
                    $table = JTable::getInstance('Group', 'CTable');
                    $table->load($row->groupid);
                    $table->description = CStringHelper::clean(JHTML::_('string.truncate', $table->description, $config->get('tips_desc_length')));
                    $groups[] = $table;
                    $ids = (empty($ids)) ? $table->id : $ids . ',' . $table->id;
                }
            }

            $sortItems = array(
                'latest' => JText::_('COM_COMMUNITY_GROUPS_SORT_LATEST'),
                'alphabetical' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL'),
                'mostactive' => JText::_('COM_COMMUNITY_GROUPS_SORT_MOST_ACTIVE'));

            $tmpl = new CTemplate();
            echo $tmpl->set('groups', $groups)
                    ->set('pagination', $pagination)
                    ->set('count', $pagination->total)
                    ->set('my', $my)
                    ->set('sortings', CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest'))
                    ->fetch('groups.myinvites');
        }

        /**
         * @since 2.4
         */
        public function modPublicDiscussion($categoryId = null) {
            $groupsModel = CFactory::getModel('groups');
            $discussionModel = CFactory::getModel('discussions');

            // getting group's latest discussion activities.
            $templateParams = CTemplate::getTemplateParams();
            $discussions = $groupsModel->getGroupLatestDiscussion($categoryId, '', $templateParams->get('sidebarTotalDiscussions'));

            return $this->_getSidebarDiscussions($discussions);
        }

        /**
         * @since 2.4
         */
        public function modUserDiscussion($userid) {
            $user = CFactory::getUser($userid);
            $groupsModel = CFactory::getModel('groups');
            $discussionModel = CFactory::getModel('discussions');
            $groupIds = $user->_groups;

            // getting group's latest discussion activities.
            $templateParams = CTemplate::getTemplateParams();
            $discussions = $groupsModel->getGroupLatestDiscussion('', $groupIds, $templateParams->get('sidebarTotalDiscussions'));

            return $this->_getSidebarDiscussions($discussions);
        }

        private function _getSidebarDiscussions($discussions) {

            if (!empty($discussions)) {
                $discussionModel = CFactory::getModel('discussions');

                for ($i = 0; $i < count($discussions); $i++) {
                    $row = $discussions[$i];
                    $creator = CFactory::getUser($row->creator);
                    $commentorName = '';

                    /**
                     * need to retrieve last replier's id
                     * if there is lastreplied (date) for corresponding discussion
                     */
                    if (!empty($discussions[$i]->lastreplied)) {
                        $lastReplier = $discussionModel->getLastReplier($discussions[$i]->id);
                        // Add is_null check to avoid earlier wall post being removed but lastreplied is with valid date
                        if (!is_null($lastReplier)) {
                            $discussions[$i]->lastReplier = $lastReplier->post_by;
                        }
                    }

                    if ($creator->block) {
                        $row->title = JText::_('COM_COMMUNITY_CENSORED');
                    }

                    /**
                     * Modified by Adam Lim on 14 July 2011
                     * Check for lastReplier and get lastReplier's name to display
                     * Note: Check for lastReplier and in mygroups function already checked for lastreplied (date)
                     */
                    if (!empty($row->lastReplier)) {
                        $commentor = CFactory::getUser($row->lastReplier);
                        $commentorName = $commentor->getDisplayName();
                    }

                    $row->creatorName = $creator->getDisplayName();
                    $row->commentorName = $commentorName;
                }


                $tmpl = new CTemplate();
                return $tmpl->set('discussions', $discussions)
                                ->fetch('groups.module.discussions');
            }
            return '';
        }

        public function viewbulletin() {
            $document = JFactory::getDocument();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            // Load necessary libraries
            $groupsModel = CFactory::getModel('groups');
            $bulletin = JTable::getInstance('Bulletin', 'CTable');
            $group = JTable::getInstance('Group', 'CTable');
            $my = CFactory::getUser();
            $bulletinId = $jinput->get('bulletinid', '', 'INT');
            $bulletin->load($bulletinId);
            $group->load($bulletin->groupid);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            if ($group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin()) {
                $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', $bulletin->title);

            // Santinise output
            $bulletin->title = strip_tags($bulletin->title);
            $bulletin->title = CStringHelper::escape($bulletin->title);

            // Add pathways
            $this->_addGroupInPathway($group->id);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_BULLETIN'), CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id));
            $this->addPathway(JText::sprintf('COM_COMMUNITY_GROUPS_BULLETIN_PATHWAY_TITLE', $bulletin->title));

            if ($groupsModel->isAdmin($my->id, $group->id) || COwnerHelper::isCommunityAdmin()) {
                $this->addSubmenuItem('', JText::_('COM_COMMUNITY_DELETE'), "joms.groups.removeBulletin('" . JText::_('COM_COMMUNITY_DELETE') . "','" . $bulletin->groupid . "','" . $bulletin->id . "');", true);
                $this->addSubmenuItem('', JText::_('COM_COMMUNITY_EDIT'), "joms.groups.editBulletin();", true);
            }
            $this->showSubMenu();

            $config = CFactory::getConfig();
            $editor = new CEditor($config->get('htmleditor', 'none'));

            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $args[] = $bulletin;
            $editorMessage = $bulletin->message;

            // Format the bulletins
            $appsLib->triggerEvent('onBulletinDisplay', $args);

            $bookmarks = new CBookmarks(CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id));
            $bookmarksHTML = $bookmarks->getHTML();

            $creator = CFactory::getUser($bulletin->created_by);

            //filesharing
            $filesharingHTML = '';
            $permission = CGroupHelper::getMediaPermission($bulletin->groupid);
            if ($config->get('groupbulletinfilesharing') && $permission->params->get('groupannouncementfilesharing')) {

                $file = new CFilesLibrary();
                $filesharingHTML = $file->getFileHTML('bulletin', $bulletin->id);
            }
            $params = $bulletin->getParams();
            $gparams = $group->getParams();
            $tmpl = new CTemplate();
            echo $tmpl->set('bookmarksHTML', $bookmarksHTML)
                    ->set('creator', $creator)
                    ->set('bulletin', $bulletin)
                    ->set('editor', $editor)
                    ->set('config', $config)
                    ->set('editorMessage', $editorMessage)
                    ->set('filesharingHTML', $filesharingHTML)
                    ->set('params', $params)
                    ->set('gparams', $gparams)
                    ->fetch('groups.viewbulletin');
        }

        /**
         * Display a list of bulletins from the specific group
         * */
        public function viewbulletins() {
            $document = JFactory::getDocument();

            $id = JRequest::getInt('groupid', '', 'GET');
            $my = CFactory::getUser();

            // Load the group
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($id);
            $this->_addGroupInPathway($group->id);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_BULLETIN'));

            if ($group->id == 0) {
                echo JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
                return;
            }

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            //display notice if the user is not a member of the group
            if ($group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin()) {
                $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_GROUPS_VIEW_ALL_BULLETINS_TITLE', $group->name));

            // Load submenu
            $this->showSubMenu();

            $model = CFactory::getModel('bulletins');
            $bulletins = $model->getBulletins($group->id);

            // Set feed url
            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_BULLETIN_FEEDS') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // Get the creator of the bulletins
            for ($i = 0; $i < count($bulletins); $i++) {
                $row = $bulletins[$i];

                $row->creator = CFactory::getUser($row->created_by);
            }

            // Only trigger the bulletins if there is really a need to.
            if (!empty($bulletins) && isset($bulletins)) {
                $appsLib = CAppPlugins::getInstance();
                $appsLib->loadApplications();

                // Format the bulletins
                // the bulletins need to be an array or reference to work around
                // PHP 5.3 pass by value
                $args = array();
                foreach ($bulletins as &$b) {
                    $args[] = $b;
                }
                $appsLib->triggerEvent('onBulletinDisplay', $args);
            }

            // Process bulletins HTML output
            $tmpl = new CTemplate();
            $bulletinsHTML = $tmpl->set('bulletins', $bulletins)
                    ->set('groupId', $group->id)
                    ->fetch('groups.bulletinlist');

            unset($tmpl);

            $tmpl = new CTemplate();
            echo $tmpl->set('group', $group)
                    ->set('bulletinsHTML', $bulletinsHTML)
                    ->set('pagination', $model->getPagination())
                    ->fetch('groups.viewbulletins');
        }

        public function banlist($data) {
            $this->viewmembers($data);
        }

        /**
         * View method to display members of the groups
         *
         * @access	public
         * @param	string 	Group Id
         * @returns object  An object of the specific group
         */
        public function viewmembers($data) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $groupsModel = CFactory::getModel('groups');
            $friendsModel = CFactory::getModel('friends');
            $userModel = CFactory::getModel('user');
            $my = CFactory::getUser();
            $config = CFactory::getConfig();
            $type = $jinput->get->get('approve', '', 'NONE'); //JRequest::getVar( 'approve' , '' , 'GET' );
            $group = JTable::getInstance('Group', 'CTable');
            $list = $jinput->get->get('list', '', 'NONE'); //JRequest::getVar( 'list' , '' , 'GET' );

            if (!$group->load($data->id)) {
                echo JText::_('COM_COMMUNITY_GROUPS_NOT_FOUND_ERROR');
                return;
            }

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_GROUPS_MEMBERS_TITLE', $group->name));

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway($group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id));
            $this->addPathway(JText::_('COM_COMMUNITY_MEMBERS'));


            $isSuperAdmin = COwnerHelper::isCommunityAdmin();
            $isAdmin = $groupsModel->isAdmin($my->id, $group->id);
            $isMember = $group->isMember($my->id);
            $isMine = ($my->id == $group->ownerid);
            $isBanned = $group->isBanned($my->id);

            if ($group->approvals == '1' && !$isMine && !$isMember && !$isSuperAdmin) {
                $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                return;
            }

            switch ($list) {
                case COMMUNITY_GROUP_ADMIN :
                    $members = $groupsModel->getAdmins($data->id);
                    break;
                case COMMUNITY_GROUP_BANNED :
                    $members = $groupsModel->getBannedMembers($data->id);
                    break;
                default :
                    if (!empty($type) && ( $type == '1' )) {
                        $members = $groupsModel->getMembers($data->id, 0, false);
                    } else {
                        $members = $groupsModel->getMembers($data->id, 0, true, false, SHOW_GROUP_ADMIN);
                    }
            }

            $this->showSubmenu();

            // Attach avatar of the member
            // Pre-load multiple users at once
            $userids = array();
            foreach ($members as $uid) {
                $userids[] = $uid->id;
            }
            CFactory::loadUsers($userids);

            $membersList = array();
            foreach ($members as $member) {
                $user = CFactory::getUser($member->id);

                $user->friendsCount = $user->getFriendCount();
                $user->approved = $member->approved;
                $user->isMe = ( $my->id == $member->id ) ? true : false;
                $user->isAdmin = $groupsModel->isAdmin($user->id, $group->id);
                $user->isOwner = ( $member->id == $group->ownerid ) ? true : false;

                // Check user's permission
                $groupmember = JTable::getInstance('GroupMembers', 'CTable');
                $keys['groupId'] = $group->id;
                $keys['memberId'] = $member->id;
                $groupmember->load($keys);
                $user->isBanned = ( $groupmember->permissions == COMMUNITY_GROUP_BANNED ) ? true : false;

                $membersList[] = $user;
            }
            $pagination = $groupsModel->getPagination();

            $tmpl = new CTemplate();
            echo $tmpl->set('members', $membersList)
                    ->set('type', $type)
                    ->set('isMine', $groupsModel->isCreator($my->id, $group->id))
                    ->set('isAdmin', $isAdmin)
                    ->set('isMember', $isMember)
                    ->set('isSuperAdmin', $isSuperAdmin)
                    ->set('pagination', $pagination)
                    ->set('groupid', $group->id)
                    ->set('my', $my)
                    ->set('config', $config)
                    ->set('group', $group)
                    ->fetch('groups.viewmembers');
        }

        /**
         * View method to display discussions from a group
         *
         * @access	public
         */
        public function viewdiscussions() {
            $document = JFactory::getDocument();

            $id = JRequest::getInt('groupid', '', 'GET');
            $my = CFactory::getUser();
            $model = CFactory::getModel('discussions');

            // Load the group
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($id);
            $this->_addGroupInPathway($group->id);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION'));
            $params = $group->getParams();

            //check if group is valid
            if ($group->id == 0) {
                echo JText::_('COM_COMMUNITY_GROUPS_ID_NOITEM');
                return;
            }

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            //display notice if the user is not a member of the group
            if ($group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin()) {
                $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                return;
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_GROUPS_VIEW_ALL_DISCUSSIONS_TITLE', $group->name));

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id . '&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_DISCUSSION_FEEDS') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // Load submenu
            $this->showSubMenu();

            $discussions = $model->getDiscussionTopics($group->id, 0, $params->get('discussordering', DISCUSSION_ORDER_BYLASTACTIVITY));

            for ($i = 0; $i < count($discussions); $i++) {
                $row = $discussions[$i];

                $row->user = CFactory::getUser($row->creator);
                $row->lastreplyuser = CFactory::getUser($row->lastmessageby);

                if (isset($row->lastreplyuser->block) && $row->lastreplyuser->block == 1) {
                    $row->title = $row->lastmessage = JText::_('COM_COMMUNITY_CENSORED');
                }
            }

            // Process discussions HTML output
            $tmpl = new CTemplate();
            $discussionsHTML = $tmpl->set('discussions', $discussions)
                    ->set('groupId', $group->id)
                    ->fetch('groups.discussionlist');
            unset($tmpl);

            $tmpl = new CTemplate();
            echo $tmpl->set('group', $group)
                    ->set('discussions', $discussions)
                    ->set('discussionsHTML', $discussionsHTML)
                    ->set('pagination', $model->getPagination())
                    ->fetch('groups.viewdiscussions');
        }

        /*
         * @since 2.4
         */

        public function modRelatedDiscussion($keywords = null, $exclude = null) {
            $discussModel = CFactory::getModel('discussions');
            $relatedDiscussions = $discussModel->getRelatedDiscussion($keywords, $exclude);

            return $this->_getSidebarRelatedDiscussions($relatedDiscussions);
        }

        /*
         * @since 2.4
         */

        private function _getSidebarRelatedDiscussions($discussions) {
            if (!empty($discussions)) {
                $tmpl = new CTemplate();
                return $tmpl->set('discussions', $discussions)
                                ->fetch('groups.module.relateddiscussion');
            }

            return '';
        }

        /**
         * View method to display specific discussion from a group
         * @since 2.4
         * @access	public
         * @param	Object	Data object passed from controller
         */
        public function viewdiscussion() {
            $mainframe = JFactory::getApplication();
            $document  = JFactory::getDocument();
            $config    = CFactory::getConfig();

            CWindow::load();
            $jinput = JFactory::getApplication()->input;

            $my = CFactory::getUser();
            $groupId = $jinput->get('groupid', 0, 'INT');
            $topicId = $jinput->get('topicid', 0, 'INT');

            // Load necessary library and objects
            $groupModel = CFactory::getModel('groups');
            $group      = JTable::getInstance('Group', 'CTable');
            $discussion = JTable::getInstance('Discussion', 'CTable');

            $group->load($groupId);
            $discussion->load($topicId);
            $isBanned = $group->isBanned($my->id);

            $document->addCustomTag('<link rel="image_src" href="' . $group->getThumbAvatar() . '" />');

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            $feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $topicId . '&format=feed');
            $feed     = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_GROUPS_LATEST_FEED') . '"  href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            if ($group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin()) {
                $this->noAccess(JText::_('COM_COMMUNITY_GROUPS_PRIVATE_NOTICE'));
                return;
            }

            // Execute discussion onDisplay filter
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();
            $args = array();
            $args[] = $discussion;
            $appsLib->triggerEvent('onDiscussionDisplay', $args);

            // Get the discussion creator info
            $creator = CFactory::getUser($discussion->creator);

            // Format the date accordingly.
            //$discussion->created	= CTimeHelper::getDate( $discussion->created );
            $dayinterval = ACTIVITY_INTERVAL_DAY;
            $timeFormat = $config->get('activitiestimeformat');
            $dayFormat = $config->get('activitiesdayformat');

            if ($config->get('activitydateformat') == COMMUNITY_DATE_FIXED) {
                $discussion->created = CTimeHelper::getDate($discussion->created)->format(JText::_('DATE_FORMAT_LC2'), true);
            } else {
                $discussion->created = CTimeHelper::timeLapse(CTimeHelper::getDate($discussion->created));
            }

            if ($creator->block) {
                $discussion->title = $discussion->message = JText::_('COM_COMMUNITY_CENSORED');
            }

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_TITTLE', $discussion->title));

            // Add pathways
            $this->_addGroupInPathway($group->id);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION'), CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id));
            $this->addPathway(JText::sprintf('COM_COMMUNITY_GROUPS_DISCUSSION_TITTLE', $discussion->title));

            $isGroupAdmin = $groupModel->isAdmin($my->id, $group->id);

            if ($my->id == $creator->id || $isGroupAdmin || COwnerHelper::isCommunityAdmin()) {
                $title = JText::_('COM_COMMUNITY_DELETE_DISCUSSION');

                $titleLock = $discussion->lock ? JText::_('COM_COMMUNITY_UNLOCK_DISCUSSION') : JText::_('COM_COMMUNITY_LOCK_DISCUSSION');
                $actionLock = $discussion->lock ? JText::_('COM_COMMUNITY_UNLOCK') : JText::_('COM_COMMUNITY_LOCK');

                $this->addSubmenuItem('', $actionLock, "joms.groups.lockTopic('" . $titleLock . "','" . $group->id . "','" . $discussion->id . "');", SUBMENU_RIGHT);
                $this->addSubmenuItem('', JText::_('COM_COMMUNITY_DELETE'), "joms.groups.removeTopic('" . $title . "','" . $group->id . "','" . $discussion->id . "');", SUBMENU_RIGHT);
                $this->addSubmenuItem('index.php?option=com_community&view=groups&task=editdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id, JText::_('COM_COMMUNITY_EDIT'), '', SUBMENU_RIGHT);
            }

            $this->showSubmenu();

            $wallContent = CWallLibrary::getWallContents('discussions', $discussion->id, $isGroupAdmin, $mainframe->getCfg('list_limit'), 0, 'wall.content', 'groups,discussion');
            $wallCount = CWallLibrary::getWallCount('discussions', $discussion->id);

            $viewAllLink = CRoute::_('index.php?option=com_community&view=groups&task=discussapp&topicid=' . $discussion->id . '&app=walls');
            $wallContent .= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);

            // Test if the current browser is a member of the group
            $isMember = $group->isMember($my->id);
            $waitingApproval = false;

            // If I have tried to join this group, but not yet approved, display a notice
            if ($groupModel->isWaitingAuthorization($my->id, $group->id)) {
                $waitingApproval = true;
            }

            $wallForm = '';
            $config = CFactory::getConfig();
            // Only get the wall form if user is really allowed to see it.
            if (!$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && ($isMember) && (!$isBanned) && !($waitingApproval) ) || COwnerHelper::isCommunityAdmin()) {
                $outputLock = '<div class="cAlert">' . JText::_('COM_COMMUNITY_DISCUSSION_LOCKED_NOTICE') . '</div>';
                $outputUnLock = CWallLibrary::getWallInputForm($discussion->id, 'groups,ajaxSaveDiscussionWall', 'groups,ajaxRemoveReply');
                $wallForm = '<div class="cWall-Header">' . JText::_('COM_COMMUNITY_REPLIES') . '</div>';
                $wallForm .= $discussion->lock ? $outputLock : $outputUnLock;
            }

            if (empty($wallForm)) {
                //user must join in order to see this page
                $tmpl = new CTemplate();
                $wallForm = $tmpl->set('groupid', $groupId)
                        ->fetch('groups.joingroup');

                $outputLock   = '<div class="cAlert">' . JText::_('COM_COMMUNITY_DISCUSSION_LOCKED_NOTICE') . '</div>';
                $outputUnLock = CWallLibrary::getWallInputForm($discussion->id, 'groups,ajaxSaveDiscussionWall', 'groups,ajaxRemoveReply');
                $wallForm2    = '<div class="cWall-Header">' . JText::_('COM_COMMUNITY_REPLIES') . '</div>';
                $wallForm2    .= $discussion->lock ? $outputLock : $outputUnLock;
                $wallForm     = $wallForm . '<div style="display:none" class="reply-form">' . $wallForm2 . '</div>';
            }

            $config = CFactory::getConfig();

            // Get creator link
            $creatorLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id);

            // Get reporting html
            $report = new CReportingLibrary();
            $reportHTML = $report->getReportingHTML(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_REPORT'), 'groups,reportDiscussion', array($discussion->id));
            $bookmarks = new CBookmarks(CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id));
            $bookmarksHTML = $bookmarks->getHTML();

            //filesharing
            $filesharingHTML = '';
            $permission = CGroupHelper::getMediaPermission($groupId);
            if ($config->get('groupdiscussfilesharing') && $permission->params->get('groupdiscussionfilesharing')) {

                $file = new CFilesLibrary();
                $filesharingHTML = $file->getFileHTML('discussion', $discussion->id);
            }

            $tmpl = new CTemplate();
            echo $tmpl->set('bookmarksHTML', $bookmarksHTML)
                    ->set('discussion', $discussion)
                    ->set('creator', $creator)
                    ->set('wallContent', $wallContent)
                    ->set('wallForm', $wallForm)
                    ->set('creatorLink', $creatorLink)
                    ->set('reportHTML', $reportHTML)
                    ->set('filesharingHTML', $filesharingHTML)
                    ->set('groupid', $groupId)
                    ->set('isTimeLapsed', $config->get('activitydateformat'))
                    ->fetch('groups.viewdiscussion');
        }

        /**
         * View method to display new discussion form
         *
         * @access	public
         * @param	Object	Data object passed from controller
         */
        public function adddiscussion(&$discussion) {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $groupId = $jinput->get->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'GET');

            $this->_addGroupInPathway($groupId);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE'));
            $this->showSubmenu();

            $config = CFactory::getConfig();
            $editorType = ($config->get('allowhtml') ) ? $config->get('htmleditor', 'none') : 'none';
            $editor = new CEditor($editorType);

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }
            $params = $group->getParams();
            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-groups-discussionform'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('config', $config)
                    ->set('editor', $editor)
                    ->set('group', $group)
                    ->set('discussion', $discussion)
                    ->set('params', $params)
                    ->fetch('groups.adddiscussion');
        }

        /**
         * View method to display new discussion form
         *
         * @access	public
         * @param	Object	Data object passed from controller
         */
        public function editdiscussion($discussion) {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_EDIT_DISCUSSION'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $groupId = $jinput->get->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'GET');
            $topicId = $jinput->get->get('topicid', '', 'INT'); //JRequest::getVar('topicid' , '' , 'GET');

            $this->_addGroupInPathway($groupId);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_EDIT_DISCUSSION'));

            $this->showSubmenu();

            $config = CFactory::getConfig();
            $editorType = ($config->get('allowhtml') ) ? $config->get('htmleditor', 'none') : 'none';
            $editor = new CEditor($editorType);

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            // Santinise output
            $discussion->title = strip_tags($discussion->title);
            $discussion->title = CStringHelper::escape($discussion->title);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-groups-discussionform'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $params = $discussion->getParams();
            $gparams = $group->getParams();
            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('config', $config)
                    ->set('editor', $editor)
                    ->set('group', $group)
                    ->set('discussion', $discussion)
                    ->set('params', $params)
                    ->set('gparams', $gparams)
                    ->fetch('groups.editdiscussion');
        }

        /**
         * View method to search groups
         *
         * @access	public
         *
         * @returns object  An object of the specific group
         */
        public function search() {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_SEARCH_TITLE'));

            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
            $this->addPathway(JText::_("COM_COMMUNITY_SEARCH"), '');

            // Display the submenu
            $this->showSubmenu();

            $search = $jinput->get('search', '', 'STRING');
            $catId = $jinput->get('catid', '', 'INT');
            $groups = '';
            $pagination = null;
            $posted = false;
            $count = 0;

            $model = CFactory::getModel('groups');

            $categories = $model->getCategories();

            // Test if there are any post requests made
            if ((!empty($search) || !empty($catId))) {
                JRequest::checkToken('get') or jexit(JText::_('COM_COMMUNITY_INVALID_TOKEN'));

                $appsLib = CAppPlugins::getInstance();
                $saveSuccess = $appsLib->triggerEvent('onFormSave', array('jsform-groups-search'));

                if (empty($saveSuccess) || !in_array(false, $saveSuccess)) {
                    $posted = true;

                    $groups = $model->getAllGroups($catId, null, $search);
                    $pagination = $model->getPagination();
                    $count = count($groups);
                }
            }

            // Get the template for the group lists
            $groupsHTML = $this->_getGroupsHTML($groups, $pagination);

            $app = CAppPlugins::getInstance();
            $appFields = $app->triggerEvent('onFormDisplay', array('jsform-groups-search'));
            $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
            $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

            $searchLinks = parent::getAppSearchLinks('groups');

            $tmpl = new CTemplate();
            echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                    ->set('afterFormDisplay', $afterFormDisplay)
                    ->set('posted', $posted)
                    ->set('groupsCount', $count)
                    ->set('groupsHTML', $groupsHTML)
                    ->set('search', $search)
                    ->set('categories', $categories)
                    ->set('catId', $catId)
                    ->set('searchLinks', $searchLinks)
                    ->fetch('groups.search');
        }

        /**
         * Method to display add new bulletin form
         *
         * @param	$title	The title of the bulletin if the adding failed
         * @param	$message	The message of the bulletin if adding failed
         * */
        public function addNews($bulletin) {
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_GROUPS_ADD_BULLETIN'));

            $this->showSubmenu();

            $config = CFactory::getConfig();
            $groupId = JRequest::getInt('groupid', '', 'GET');

            $group = JTable::getInstance('Group', 'CTable');
            $group->load($groupId);

            // @rule: Test if the group is unpublished, don't display it at all.
            if (!$group->published) {
                $this->_redirectUnpublishGroup();
                return;
            }

            // Add pathways
            $this->_addGroupInPathway($groupId);
            $this->addPathway(JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE'));

            $editor = new CEditor($config->get('htmleditor', 'none'));
            $title = ( $bulletin ) ? $bulletin->title : '';
            $message = ( $bulletin ) ? $bulletin->message : '';
            $params = $group->getParams();

            $tmpl = new CTemplate();
            echo $tmpl->set('config', $config)
                    ->set('title', $title)
                    ->set('message', $message)
                    ->set('groupid', $groupId)
                    ->set('editor', $editor)
                    ->set('params', $params)
                    ->fetch('groups.addnews');
        }

        public function _getGroupsHTML($tmpGroups, $tmpPagination = NULL) {
            $config = CFactory::getConfig();
            $tmpl = new CTemplate();
            $featured = new CFeatured(FEATURED_GROUPS);
            $featuredList = $featured->getItemIds();

            $groups = array();

            if ($tmpGroups) {
                foreach ($tmpGroups as $row) {
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->bind($row);
                    $group->updateStats(); //ensure that stats are up-to-date
                    $group->description = CStringHelper::clean(JHTML::_('string.truncate', $group->description, $config->get('tips_desc_length')));
                    $groups[] = $group;
                }
                unset($tmpGroups);
            }

            $groupsHTML = $tmpl->set('showFeatured', $config->get('show_featured'))
                    ->set('featuredList', $featuredList)
                    ->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                    ->set('groups', $groups)
                    ->set('pagination', $tmpPagination)
                    ->fetch('groups.list');
            unset($tmpl);

            return $groupsHTML;
        }

        /**
         * Return the video list for viewGroup display
         */
        protected function _getVideos($params, $groupId) {
            $result = array();
            $videoModel = CFactory::getModel('videos');
            $tmpVideos = $videoModel->getGroupVideos($groupId, '', $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT));
            $videos = array();

            if ($tmpVideos) {
                foreach ($tmpVideos as $videoEntry) {
                    $video = JTable::getInstance('Video', 'CTable');
                    $video->bind($videoEntry);
                    $videos[] = $video;
                }
            }

            $totalVideos = $videoModel->total ? $videoModel->total : 0;
            $result['total'] = $totalVideos;
            $result['data'] = $videos;
            return $result;
        }

        /**
         * Return the albu list for viewGroup display
         */
        protected function _getAlbums($params, $groupId) {
            $result = array();

            $photosModel = CFactory::getModel('photos');

            $albums = $photosModel->getGroupAlbums($groupId, true, false, $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT));

            $db = JFactory::getDBO();
            $where = 'WHERE a.' . $db->quoteName('groupid') . ' = ' . $db->quote($groupId);

            $totalAlbums = $photosModel->getAlbumCount($where);

            $result['total'] = $totalAlbums;
            $result['data'] = $albums;

            return $result;
        }

        /**
         * Return the an array of HTML part of bulletings in viewGroups
         * and the total number of bulletin
         */
        protected function _getDiscussionListHTML($params, $groupId) {
            $result = array();

            $discussModel = CFactory::getModel('discussions');

            $discussions = $discussModel->getDiscussionTopics($groupId, '10', $params->get('discussordering', DISCUSSION_ORDER_BYLASTACTIVITY));
            $totalDiscussion = $discussModel->total;

            // Attach avatar of the member to the discussions
            for ($i = 0; $i < count($discussions); $i++) {
                $row = $discussions[$i];
                $row->user = CFactory::getUser($row->creator);

                // Get last replier for the discussion
                $row->lastreplier = $discussModel->getLastReplier($row->id);
                if ($row->lastreplier) {
                    $row->lastreplier->post_by = CFactory::getUser($row->lastreplier->post_by);
                }

                if ($row->lastmessageby) {
                    $row->lastreplyuser = CFactory::getUser($row->lastmessageby);
                }

                if ($row->user->block) {
                    $row->title = JText::_('COM_COMMUNITY_CENSORED');
                }

                if (isset($row->lastreplyuser->block)  && $row->lastreplyuser->block == 1) {
                    $row->lastmessage = JText::_('COM_COMMUNITY_CENSORED');
                }
            }

            // Process discussions HTML output
            $tmpl = new CTemplate();
            $discussionsHTML = $tmpl->set('discussions', $discussions)
                    ->set('groupId', $groupId)
                    ->fetch('groups.discussionlist');
            unset($tmpl);

            $result['HTML'] = $discussionsHTML;
            $result['total'] = $totalDiscussion;
            $result['data'] = $discussions;

            return $result;
        }

        /**
         * Return the an array of HTML part of bulletings in viewGroups
         * and the total number of bulletin
         */
        protected function _getBulletinListHTML($groupId) {

            $result = array();

            $bulletinModel = CFactory::getModel('bulletins');
            $bulletins = $bulletinModel->getBulletins($groupId);
            $totalBulletin = $bulletinModel->total;


            // Get the creator of the discussions
            for ($i = 0; $i < count($bulletins); $i++) {
                $row = $bulletins[$i];

                $row->creator = CFactory::getUser($row->created_by);
            }

            // Only trigger the bulletins if there is really a need to.
            if (!empty($bulletins)) {
                $appsLib = CAppPlugins::getInstance();
                $appsLib->loadApplications();

                // Format the bulletins
                // the bulletins need to be an array or reference to work around
                // PHP 5.3 pass by value
                $args = array();
                foreach ($bulletins as &$b) {
                    $args[] = $b;
                }
                $appsLib->triggerEvent('onBulletinDisplay', $args);
            }

            // Process bulletins HTML output
            $tmpl = new CTemplate();
            $bulletinsHTML = $tmpl->set('bulletins', $bulletins)
                    ->set('groupId', $groupId)
                    ->fetch('groups.bulletinlist');
            unset($tmpl);

            $result['HTML'] = $bulletinsHTML;
            $result['total'] = $totalBulletin;
            $result['data'] = $bulletins;

            return $result;
        }

        private function _getPendingListHTML($userId) {
            $model = CFactory::getModel('groups');
            $rows = $model->getGroupInvites($userId);
            $groups = array();

            if ($rows) {
                foreach ($rows as $row) {
                    $table = JTable::getInstance('Group', 'CTable');
                    $table->load($row->groupid);

                    $groups[] = $table;
                }
            }
            if (count($rows) > 0) {

                $tmpl = new CTemplate();
                return $tmpl->set('groups', $groups)
                                ->fetch('groups.pendinginvitelist');
            } else {
                return '';
            }
        }

    }

}
