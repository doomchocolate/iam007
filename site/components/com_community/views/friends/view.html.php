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
jimport('joomla.utilities.arrayhelper');
jimport('joomla.html.html');

class CommunityViewFriends extends CommunityView {

    public function _addSubmenu() {

        if (JFile::exists(JPATH_COMPONENT . '/libraries/advancesearch.php')) {
            require_once (JPATH_COMPONENT . '/libraries/advancesearch.php');
            $mySQLVer = CAdvanceSearch::getMySQLVersion();
        }

        $this->addSubmenuItem('index.php?option=com_community&view=friends', JText::_('COM_COMMUNITY_FRIENDS_VIEW_ALL'));

        $tmpl = new CTemplate();
        $tmpl->set('url', CRoute::_('index.php?option=com_community&view=search'));
        $html = $tmpl->fetch('search.submenu');
        $this->addSubmenuItem('index.php?option=com_community&view=search', JText::_('COM_COMMUNITY_SEARCH_FRIENDS'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);
        $this->addSubmenuItem('index.php?option=com_community&view=search&task=advancesearch', JText::_('COM_COMMUNITY_CUSTOM_SEARCH'));
        $this->addSubmenuItem('index.php?option=com_community&view=friends&task=invite', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));
        $this->addSubmenuItem('index.php?option=com_community&view=friends&task=sent', JText::_('COM_COMMUNITY_FRIENDS_REQUEST_SENT'));
        $this->addSubmenuItem('index.php?option=com_community&view=friends&task=pending', JText::_('COM_COMMUNITY_FRIENDS_PENDING_APPROVAL'));
    }

    public function showSubmenu() {
        $this->_addSubmenu();
        parent::showSubmenu();
    }

    /**
     * DIsplay list of friends
     *
     * if no $_GET['id'] is set, we're viewing our own friends
     */
    public function friends($data = null) {
        // Load necessary window css / javascript headers.
        CWindow::load();

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $document = JFactory::getDocument();
        $my = CFactory::getUser();
        $id = $jinput->get('userid', $my->id, 'INT');

        // Display mini header if user is viewing other user's friend
        if ($id != $my->id) {

            $this->attachMiniHeaderUser($id);
        }

        $feedLink = CRoute::_('index.php?option=com_community&view=friends&format=feed');
        $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_TO_FRIENDS_FEEDS') . '" href="' . $feedLink . '"/>';
        $document->addCustomTag($feed);

        $user = CFactory::getUser($id);
        $params = $user->getParams();
        $people = CFactory::getModel('search');
        $userModel = CFactory::getModel('user');
        $avatar = CFactory::getModel('avatar');
        $friends = CFactory::getModel('friends');
        $sorted = $jinput->get->get('sort', 'latest', 'STRING');
        $filter = JRequest::getWord('filter', 'all', 'GET');

        $rows = $friends->getFriends($id, $sorted, true, $filter);
        $isMine = ( ($id == $my->id) && ($my->id != 0) );
        $document = JFactory::getDocument();

        $this->addPathway(JText::_('COM_COMMUNITY_FRIENDS'), CRoute::_('index.php?option=com_community&view=friends'));

        if ($my->id == $id) {
            $this->addPathway(JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS'));
        } else {
            $this->addPathway(JText::sprintf('COM_COMMUNITY_FRIENDS_ALL_FRIENDS', $user->getDisplayName()));
        }

        // Hide submenu if we are viewing other's friends
        if ($isMine) {
            $this->showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS'));
        } else {
            $this->addSubmenuItem('index.php?option=com_community&view=profile&userid=' . $user->id, JText::_('COM_COMMUNITY_PROFILE_BACK_TO_PROFILE'));
            $this->addSubmenuItem('index.php?option=com_community&view=friends&userid=' . $user->id, JText::_('COM_COMMUNITY_FRIENDS_VIEW_ALL'));
            $this->addSubmenuItem('index.php?option=com_community&view=friends&task=mutualFriends&userid=' . $user->id . '&filter=mutual', JText::_('COM_COMMUNITY_MUTUAL_FRIENDS'));

            $tmpl = new CTemplate();
            $tmpl->set('view', "friends");
            $tmpl->set('url', CRoute::_('index.php?option=com_community&view=friends&task=viewfriends'));
            $html = $tmpl->fetch('friendsearch.submenu');
            $this->addSubmenuItem('index.php?option=com_community&view=friends&task=viewfriends', JText::_('COM_COMMUNITY_SEARCH_FRIENDS'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);

            parent::showSubmenu();

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_FRIENDS_ALL_FRIENDS', $user->getDisplayName()));
        }

        $sortItems = array(
            'latest' => JText::_('COM_COMMUNITY_SORT_RECENT_FRIENDS'),
            'online' => JText::_('COM_COMMUNITY_ONLINE'),
            'name' => JText::_('COM_COMMUNITY_SORT_ALPHABETICAL')
        );

        $config = CFactory::getConfig();
        $filterItems = array();

        if ($config->get('alphabetfiltering')) {
            $filterItems = array(
                'all' => JText::_('COM_COMMUNITY_JUMP_ALL'),
                'abc' => JText::_('COM_COMMUNITY_JUMP_ABC'),
                'def' => JText::_('COM_COMMUNITY_JUMP_DEF'),
                'ghi' => JText::_('COM_COMMUNITY_JUMP_GHI'),
                'jkl' => JText::_('COM_COMMUNITY_JUMP_JKL'),
                'mno' => JText::_('COM_COMMUNITY_JUMP_MNO'),
                'pqr' => JText::_('COM_COMMUNITY_JUMP_PQR'),
                'stu' => JText::_('COM_COMMUNITY_JUMP_STU'),
                'vwx' => JText::_('COM_COMMUNITY_JUMP_VWX'),
                'yz' => JText::_('COM_COMMUNITY_JUMP_YZ'),
                'others' => JText::_('COM_COMMUNITY_JUMP_OTHERS')
            );
        }

        // Check if friend is banned
        $blockModel = CFactory::getModel('block');

        $resultRows = array();

        // @todo: preload all friends
        foreach ($rows as $row) {
            $user = CFactory::getUser($row->id);

            $obj = clone($row);
            $obj->friendsCount = $user->getFriendCount();
            $obj->profileLink = CUrlHelper::userLink($row->id);
            $obj->isFriend = true;
            $obj->isBlocked = $blockModel->getBlockStatus($user->id, $my->id);
            $resultRows[] = $obj;
        }
        unset($rows);

        // Should not show recently added filter to otehr people
        $sortingHTML = '';

        if ($isMine) {
            $sortingHTML = CFilterBar::getHTML(CRoute::getURI(), $sortItems, 'latest', $filterItems, 'all');
        }

        $tmpl = new CTemplate();
        $html = $tmpl->set('isMine', $isMine)
                ->setRef('my', $my)
                ->setRef('friends', $resultRows)
                ->set('sortings', $sortingHTML)
                ->set('config', CFactory::getConfig())
                ->fetch('friends.list');

        $html .= '<div class="cPagination">';
        $pagination = $friends->getPagination();
        $html .= $pagination->getPagesLinks();
        $html .= '</div>';

        echo $html;
    }

    /**
     * Search list of friends
     *
     * if no $_GET['id'] is set, we're viewing our own friends
     */
    public function friendsearch($data) {

        require_once (JPATH_COMPONENT . '/libraries/profile.php');
        require_once (JPATH_COMPONENT . '/helpers/friends.php');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_SEARCH_FRIENDS_TITLE'));

        $avatarOnly = $jinput->get('avatar', '', 'NONE');
        $this->addPathway(JText::_('COM_COMMUNITY_SEARCH_FRIENDS_TITLE'));

        $my = CFactory::getUser();
        $friendsModel = CFactory::getModel('friends');
        $resultRows = array();

        $id = JRequest::getInt('userid', null);

        if ($id == null) {
            $id = $my->id;
        }

        $user = CFactory::getUser($id);
        $isMine = ( ($id == $my->id) && ($my->id != 0) );

        $pagination = (!empty($data)) ? $data->pagination : '';
        $alreadyfriend = array();

        $tmpl = new CTemplate();

        for ($i = 0; $i < count($data->result); $i++) {
            $row = $data->result[$i];
            $user = CFactory::getUser($row->id);
            $row->profileLink = CRoute::_('index.php?option=com_community&view=profile&userid=' . $row->id);
            $row->friendsCount = $user->getFriendCount();
            $isFriend = CFriendsHelper::isConnected($row->id, $my->id);

            $row->user = $user;
            $row->addFriend = ((!$isFriend) && ($my->id != 0) && $my->id != $row->id) ? true : false;
            if ($row->addFriend) {
                $alreadyfriend[$row->id] = $row->id;
            }

            $resultRows[] = $row;
        }

        $tmpl->set('alreadyfriend', $alreadyfriend);
        $tmpl->set('data', $resultRows);
        $tmpl->set('sortings', '');
        $tmpl->set('pagination', $pagination);

        $featured = new CFeatured(FEATURED_USERS);
        $featuredList = $featured->getItemIds();

        $tmpl->set('featuredList', $featuredList);

        //CFactory::load( 'helpers' , 'owner' );
        $tmpl->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin());
        $tmpl->set('showFeaturedList', false);
        $tmpl->set('my', $my);
        $resultHTML = $tmpl->fetch('people.browse');
        unset($tmpl);

        $searchLinks = parent::getAppSearchLinks('people');

        if ($isMine) {
            $this->showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS'));
        } else {
            $this->addSubmenuItem('index.php?option=com_community&view=profile&userid=' . $user->id, JText::_('COM_COMMUNITY_PROFILE_BACK_TO_PROFILE'));
            $this->addSubmenuItem('index.php?option=com_community&view=friends&userid=' . $user->id, JText::_('COM_COMMUNITY_FRIENDS_VIEW_ALL'));
            $this->addSubmenuItem('index.php?option=com_community&view=friends&task=mutualFriends&userid=' . $user->id . '&filter=mutual', JText::_('COM_COMMUNITY_MUTUAL_FRIENDS'));

            $tmpl = new CTemplate();
            $tmpl->set('view', "friends");
            $tmpl->set('url', CRoute::_('index.php?option=com_community&view=friends&task=viewfriends'));
            $html = $tmpl->fetch('friendsearch.submenu');
            $this->addSubmenuItem('index.php?option=com_community&view=friends&task=viewfriends', JText::_('COM_COMMUNITY_SEARCH_FRIENDS'), 'joms.videos.toggleSearchSubmenu(this)', SUBMENU_LEFT, $html);

            parent::showSubmenu();
            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_FRIENDS_ALL_FRIENDS', $user->getDisplayName()));
        }

        $tmpl = new CTemplate();
        $tmpl->set('avatarOnly', $avatarOnly);
        $tmpl->set('results', $data->result);
        $tmpl->set('resultHTML', $resultHTML);
        $tmpl->set('query', $data->query);
        $tmpl->set('searchLinks', $searchLinks);
        echo $tmpl->fetch('friendsearch');
    }

    public function add($data = null) {

        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIENDS_ADD_NEW_FRIEND'));
        ?>
        <div class="app-box">
            <p><?php echo JText::sprintf('COM_COMMUNITY_ADD_USER_AS_FRIEND', $data->name); ?></p>
            <form name="addfriend" method="post" action="">
                <div>
                    <label><?php echo JText::sprintf('COM_COMMUNITY_INVITE_PERSONAL_MESSAGE_TO', $data->name); ?></label>
                </div>

                <div>
                    <textarea name="msg" class="input textarea"></textarea>
                </div>

                <div>
                    <input type="submit" class="button" name="submit" value="<?php echo JText::_('COM_COMMUNITY_FRIENDS_ADD_BUTTON'); ?>"/>
                    <input type="submit" class="button" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>"/>
                </div>
                <input type="hidden" class="button" name="id" value="<?php echo $data->id; ?>"/>
            </form>
        </div>
        <?php
    }

    public function online($data = null) {
        // Load the toolbar
        $this->showHeader(JText::_('COM_COMMUNITY_FRIENDS_ONLINE_FRIENDS'), 'generic');
        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_ONLINE_FRIENDS_TITLE'));
    }

    public function sent($data = null) {
        $mainframe = JFactory::getApplication();

        // Load necessary window css / javascript headers.
        CWindow::load();

        $config = CFactory::getConfig();
        $my = JFactory::getUser();

        if ($my->id == 0) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PLEASE_LOGIN_WARNING'), 'error');
            return;
        }

        $this->addPathway(JText::_('COM_COMMUNITY_FRIENDS'), CRoute::_('index.php?option=com_community&view=friends'));
        $this->addPathway(JText::_("COM_COMMUNITY_FRIENDS_WAITING_AUTHORIZATION"), '');

        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIENDS_WAITING_AUTHORIZATION'));

        $this->showSubMenu();

        $friends = CFactory::getModel('friends');

        $rows = !empty($data->sent) ? $data->sent : array();

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $row->user = CFactory::getUser($row->id);
            $row->user->friendsCount = $row->user->getFriendCount();
            $row->user->profileLink = CUrlHelper::userLink($row->id);
        }

        $tmpl = new CTemplate();
        echo $tmpl->set('my', $my)
                ->set('config', $config)
                ->set('rows', $rows)
                ->fetch('friends.request');
    }

    public function deleteLink($controller, $method, $id) {
        $deleteLink = '<a class="remove" onClick="if(!confirm(\'' . JText::_('COM_COMMUNITY_CONFIRM_DELETE_FRIEND') . '\'))return false;" href="' . CUrl::build($controller, $method) . '&fid=' . $id . '">&nbsp;</a>';
        return $deleteLink;
    }

    /**
     * Display a list of pending friend requests
     * */
    public function pending($data = null) {
        if (!$this->accessAllowed('registered'))
            return;

        $mainframe = JFactory::getApplication();
        $config = CFactory::getConfig();

        CWindow::load();

        $my = CFactory::getUser();

        if ($my->id == 0) {
            $mainframe->enqueueMessage(JText::_('COM_COMMUNITY_PLEASE_LOGIN_WARNING'), 'error');
            return;
        }

        // Set pathway
        $this->addPathway(JText::_('COM_COMMUNITY_FRIENDS'), CRoute::_('index.php?option=com_community&view=friends'));
        $this->addPathway(JText::_('COM_COMMUNITY_FRIENDS_AWAITING_AUTHORIZATION'), '');

        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIENDS_AWAITING_AUTHORIZATION'));

        // Load submenu
        $this->showSubMenu();

        $rows = !empty($data->pending) ? $data->pending : array();

        for ($i = 0; $i < count($rows); $i++) {
            $row = $rows[$i];
            $row->user = CFactory::getUser($row->id);
            $row->user->friendsCount = $row->user->getFriendCount();
            $row->user->profileLink = CUrlHelper::userLink($row->id);
            $row->msg = $this->escape($row->msg);
        }

        $tmpl = new CTemplate();
        echo $tmpl->set('rows', $rows)
                ->setRef('my', $my)
                ->set('config', $config)
                ->set('pagination', $data->pagination)
                ->fetch('friends.pending');
    }

    public function addSuccess($data = null) {
        $this->addInfo(JText::sprintf('COM_COMMUNITY_FRIENDS_WILL_RECEIVE_REQUEST', $data->name));

        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIEND_ADDED_SUCCESSFULLY_TITLE'));
    }

    /**
     * Show the invite window
     */
    public function invite() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $jConfig = JFactory::getConfig();

        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_FRIENDS_INVITE_FRIENDS_TITLE', $jConfig->get('sitename')));

        $my = CFactory::getUser();

        $this->showSubmenu();

        $post = ($jinput->post->get('action', '', 'STRING') == 'invite') ? JRequest::get('POST') : array('message' => '', 'emails' => '');

        $pathway = $mainframe->getPathway();
        $this->addPathway(JText::_('COM_COMMUNITY_FRIENDS'), CRoute::_('index.php?option=com_community&view=friends'));
        $this->addPathway(JText::_('COM_COMMUNITY_INVITE_FRIENDS'), '');

        // Process the Suggest Friends
        // Load required filterbar library that will be used to display the filtering and sorting.

        $id = JRequest::getCmd('userid', $my->id);
        $user = CFactory::getUser($id);
        $sorted = $jinput->get->get('sort', 'suggestion', 'STRING');
        $filter = $jinput->get->get('filter', 'suggestion', 'STRING');
        $friends = CFactory::getModel('friends');

        $rows = $friends->getFriends($id, $sorted, true, $filter);
        $resultRows = array();

        foreach ($rows as $row) {
            $user = CFactory::getUser($row->id);

            $obj = clone($row);
            $obj->friendsCount = $user->getFriendCount();
            $obj->profileLink = CUrlHelper::userLink($row->id);
            $obj->isFriend = true;
            $resultRows[] = $obj;
        }

        unset($rows);

        $app = CAppPlugins::getInstance();
        $appFields = $app->triggerEvent('onFormDisplay', array('jsform-friends-invite'));
        $beforeFormDisplay = CFormElement::renderElements($appFields, 'before');
        $afterFormDisplay = CFormElement::renderElements($appFields, 'after');

        $tmpl = new CTemplate();
        echo $tmpl->set('beforeFormDisplay', $beforeFormDisplay)
                ->set('afterFormDisplay', $afterFormDisplay)
                ->set('my', $my)
                ->set('post', $post)
                ->setRef('friends', $resultRows)
                ->set('config', CFactory::getConfig())
                ->fetch('friends.invite');
    }

    public function news() {
        /**
         * Opengraph
         */
        CHeadHelper::setType('website', JText::_('COM_COMMUNITY_FRIENDS_FRIENDS_NEWS'));
    }

}
