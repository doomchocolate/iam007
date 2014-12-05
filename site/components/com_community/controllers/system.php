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

class CommunitySystemController extends CommunityBaseController {

    public function ajaxShowInvitationForm($friends, $callback, $cid, $displayFriends, $displayEmail) {
        // pending filter
        $objResponse = new JAXResponse();
        $displayFriends = (bool) $displayFriends;

        $config = CFactory::getConfig();
        $limit = $config->get('friendloadlimit', 8);

        $tmpl = new CTemplate();

        $tmpl->set('displayFriends', $displayFriends);
        $tmpl->set('displayEmail', $displayEmail);
        $tmpl->set('cid', $cid);
        $tmpl->set('callback', $callback);
        $tmpl->set('limit', $limit);

        $html = $tmpl->fetch('ajax.showinvitation');
        $actions = '<input type="button" class="btn btn-primary" onclick="joms.invitation.send(\'' . $callback . '\',\'' . $cid . '\');" value="' . JText::_('COM_COMMUNITY_SEND_INVITATIONS') . '"/>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));

        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        // Call addScriptCall using the correct implementation
        $objResponse->addScriptCall('joms.friends.loadFriend', "", $callback, $cid, '0', $limit);

        return $objResponse->sendResponse();
    }

    public function ajaxShowFriendsForm($friends, $callback, $cid, $displayFriends, $onClickAction) {
        // pending filter

        $objResponse = new JAXResponse();
        $displayFriends = (bool) $displayFriends;

        $config = CFactory::getConfig();
        $limit = $config->get('friendloadlimit', 8);

        $tmpl = new CTemplate();
        $tmpl->set('displayFriends', $displayFriends);
        $tmpl->set('cid', $cid);
        $tmpl->set('callback', $callback);
        $tmpl->set('limit', $limit);
        $html = $tmpl->fetch('ajax.showfriends');

        $actions = '<input type="button" class="btn" onclick="' . $onClickAction . '" value="' . JText::_('COM_COMMUNITY_SELECT_FRIENDS') . '"/>';

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_SELECT_FRIENDS_CAPTION'));

        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        // Call addScriptCall using the correct implementation
        $objResponse->addScriptCall('joms.friends.loadFriend', "", $callback, $cid, '0', $limit);

        return $objResponse->sendResponse();
    }

    public function ajaxLoadFriendsList($namePrefix, $callback, $cid, $limitstart = 0, $limit = 8) {
        // pending filter
        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $callback = $filter->clean($callback, 'string');
        $cid = $filter->clean($cid, 'int');
        $namePrefix = $filter->clean($namePrefix, 'string');
        $my = CFactory::getUser();
        //get the handler
        $handlerName = '';

        $callbackOptions = explode(',', $callback);

        if (isset($callbackOptions[0])) {
            $handlerName = $callbackOptions[0];
        }

        $handler = CFactory::getModel($handlerName);

        $handlerFunc = 'getInviteListByName';
        $friends = '';
        $args = array();
        $friends = $handler->$handlerFunc($namePrefix, $my->id, $cid, $limitstart, $limit);

        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->load($callback, $cid);

        $tmpl = new CTemplate();
        $tmpl->set('friends', $friends);
        $tmpl->set('selected', $invitation->getInvitedUsers());
        $tmplName = 'ajax.friend.list.' . $handlerName;
        $html = $tmpl->fetch($tmplName);
        //calculate pending friend list
        $loadedFriend = $limitstart + count($friends);
        if ($handler->total > $loadedFriend) {
            //update limitstart
            $limitstart = $limitstart + count($friends);
            $moreCount = $handler->total - $loadedFriend;
            //load more option
            $loadMore = '<a onClick="joms.friends.loadMoreFriend(\'' . $callback . '\',\'' . $cid . '\',\'' . $limitstart . '\',\'' . $limit . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }

        $objResponse->addAssign('community-invitation-loadmore', 'innerHTML', $loadMore);
//		$objResponse->addScriptCall('joms.friends.updateFriendList',$html,JText::_('COM_COMMUNITY_INVITE_NO_FRIENDS'));
        $objResponse->addScriptCall('joms.friends.updateFriendList', $html, JText::_('COM_COMMUNITY_INVITE_NO_FRIENDS_FOUND'));


        return $objResponse->sendResponse();
    }

    public function ajaxSubmitInvitation($callback, $cid, $values) {
        //CFactory::load( 'helpers' , 'validate' );
        $filter = JFilterInput::getInstance();
        $callback = $filter->clean($callback, 'string');
        $cid = $filter->clean($cid, 'int');
        $values = $filter->clean($values, 'array');
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();
        $methods = explode(',', $callback);
        $emails = array();
        $recipients = array();
        $users = '';
        $message = $values['message'];
        $values['friends'] = isset($values['friends']) ? $values['friends'] : array();

        if (!is_array($values['friends'])) {
            $values['friends'] = array($values['friends']);
        }

        // This is where we process external email addresses
        if (!empty($values['emails'])) {
            $emails = explode(',', $values['emails']);
            foreach ($emails as $email) {
                if (!CValidateHelper::email($email)) {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_INVALID', $email));
                    return $objResponse->sendResponse();
                }
                $recipients[] = $email;
            }
        }

        // This is where we process site members that are being invited
        if (!empty($values['friends'])) {
            $users = implode(',', $values['friends']);

            foreach ($values['friends'] as $id) {
                $recipients[] = $id;
            }
        }

        if (!empty($recipients)) {
            $arguments = array($cid, $values['friends'], $emails, $message);

            if (is_array($methods) && $methods[0] != 'plugins') {
                $controller = JString::strtolower(basename($methods[0]));
                $function = $methods[1];
                require_once( JPATH_ROOT . '/components/com_community/controllers/controller.php' );
                $file = JPATH_ROOT . '/components/com_community/controllers' . '/' . $controller . '.php';


                if (JFile::exists($file)) {
                    require_once( $file );

                    $controller = JString::ucfirst($controller);
                    $controller = 'Community' . $controller . 'Controller';
                    $controller = new $controller();

                    if (method_exists($controller, $function)) {
                        $inviteMail = call_user_func_array(array($controller, $function), $arguments);
                    } else {
                        $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                        return $objResponse->sendResponse();
                    }
                } else {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                    return $objResponse->sendResponse();
                }
            } else if (is_array($methods) && $methods[0] == 'plugins') {
                // Load 3rd party applications
                $element = JString::strtolower(basename($methods[1]));
                $function = $methods[2];
                $file = CPluginHelper::getPluginPath('community', $element) . '/' . $element . '.php';

                if (JFile::exists($file)) {
                    require_once( $file );
                    $className = 'plgCommunity' . JString::ucfirst($element);


                    if (method_exists($controller, $function)) {
                        $inviteMail = call_user_func_array(array($className, $function), $arguments);
                    } else {
                        $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                        return $objResponse->sendResponse();
                    }
                } else {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                    return $objResponse->sendResponse();
                }
            }

            //CFactory::load( 'libraries' , 'invitation' );
            // If the responsible method returns a false value, we should know that they want to stop the invitation process.

            if ($inviteMail instanceof CInvitationMail) {
                if ($inviteMail->hasError()) {
                    $objResponse->addAssign('invitation-error', 'innerHTML', $inviteMail->getError());

                    return $objResponse->sendResponse();
                } else {
                    // Once stored, we need to store selected user so they wont be invited again
                    $invitation = JTable::getInstance('Invitation', 'CTable');
                    $invitation->load($callback, $cid);

                    if (!empty($values['friends'])) {
                        if (!$invitation->id) {
                            // If the record doesn't exists, we need add them into the
                            $invitation->cid = $cid;
                            $invitation->callback = $callback;
                        }
                        $invitation->users = empty($invitation->users) ? implode(',', $values['friends']) : $invitation->users . ',' . implode(',', $values['friends']);
                        $invitation->store();
                    }

                    // Add notification
                    //CFactory::load( 'libraries' , 'notification' );
                    CNotificationLibrary::add($inviteMail->getCommand(), $my->id, $recipients, $inviteMail->getTitle(), $inviteMail->getContent(), '', $inviteMail->getParams());
                }
            } else {
                $objResponse->addScriptCall(JText::_('COM_COMMUNITY_INVITE_INVALID_RETURN_TYPE'));
                return $objResponse->sendResponse();
            }
        } else {
            $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_NO_SELECTION'));
            return $objResponse->sendResponse();
        }

        $actions = '<input type="button" class="btn" onclick="cWindowHide();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';
        $html = JText::_('COM_COMMUNITY_INVITE_SENT');

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxReport($reportFunc, $pageLink) {
        $filter = JFilterInput::getInstance();
        $pageLink = $filter->clean($pageLink, 'string');
        $reportFunc = $filter->clean($reportFunc, 'string');

        $objResponse = new JAXResponse();
        $config = CFactory::getConfig();
        $reports = JString::trim($config->get('predefinedreports'));
        $reports = empty($reports) ? false : explode("\n", $reports);

        $html = '';

        $argsCount = func_num_args();

        $argsData = '';

        if ($argsCount > 1) {

            for ($i = 2; $i < $argsCount; $i++) {
                $argsData .= "\'" . func_get_arg($i) . "\'";
                $argsData .= ( $i != ( $argsCount - 1) ) ? ',' : '';
            }
        }

        $tmpl = new CTemplate();
        $tmpl->set('reports', $reports);
        $tmpl->set('reportFunc', $reportFunc);

        $html = $tmpl->fetch('ajax.reporting');
        ob_start();
        ?>
        <button class="btn" onclick="javascript:cWindowHide();" name="cancel">
            <?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>
        </button>
        <button class="btn btn-primary pull-right" onclick="joms.report.submit('<?php echo $reportFunc; ?>', '<?php echo $pageLink; ?>', '<?php echo $argsData; ?>');" name="submit">
            <?php echo JText::_('COM_COMMUNITY_SEND_BUTTON'); ?>
        </button>
        <?php
        $actions = ob_get_contents();
        ob_end_clean();

        // Change cWindow title
        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_REPORT_THIS'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxSendReport() {
        $reportFunc = func_get_arg(0);
        $pageLink = func_get_arg(1);
        $message = func_get_arg(2);

        $argsCount = func_num_args();
        $method = explode(',', $reportFunc);

        $args = array();
        $args[] = $pageLink;
        $args[] = $message;

        for ($i = 3; $i < $argsCount; $i++) {
            $args[] = func_get_arg($i);
        }

        // Reporting should be session sensitive
        // Construct $output
        if ($reportFunc == 'activities,reportActivities' && strpos($pageLink, 'actid') === false) {
            $pageLink = $pageLink . '&actid=' . func_get_arg(3);
        }

        $uniqueString = md5($reportFunc . $pageLink);
        $session = JFactory::getSession();


        if ($session->has('action-report-' . $uniqueString)) {
            $output = JText::_('COM_COMMUNITY_REPORT_ALREADY_SENT');
        } else {
            if (is_array($method) && $method[0] != 'plugins') {
                $controller = JString::strtolower(basename($method[0]));

                require_once( JPATH_ROOT . '/components/com_community/controllers/controller.php' );
                require_once( JPATH_ROOT . '/components/com_community/controllers' . '/' . $controller . '.php' );

                $controller = JString::ucfirst($controller);
                $controller = 'Community' . $controller . 'Controller';
                $controller = new $controller();


                $output = call_user_func_array(array(&$controller, $method[1]), $args);
            } else if (is_array($method) && $method[0] == 'plugins') {
                // Application method calls
                $element = JString::strtolower($method[1]);
                require_once( CPluginHelper::getPluginPath('community', $element) . '/' . $element . '.php' );
                $className = 'plgCommunity' . JString::ucfirst($element);
                $output = call_user_func_array(array($className, $method[2]), $args);
            }
        }
        $session->set('action-report-' . $uniqueString, true);

        // Construct the action buttons $action
        ob_start();
        ?>
        <button class="btn" onclick="javascript:cWindowHide();" name="cancel">
            <?php echo JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON'); ?>
        </button>
        <?php
        $action = ob_get_contents();
        ob_end_clean();

        // Construct the ajax response
        $objResponse = new JAXResponse();

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_REPORT_SENT'));
        $objResponse->addScriptCall('cWindowAddContent', $output, $action);

        return $objResponse->sendResponse();
    }

    public function ajaxEditWall($wallId, $editableFunc) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');
        $editableFunc = $filter->clean($editableFunc, 'string');

        $objResponse = new JAXResponse();
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        //CFactory::load( 'libraries' , 'wall' );
        $isEditable = CWall::isEditable($editableFunc, $wall->id);

        if (!$isEditable) {
            $objResponse->addAlert(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT'));
            return $objResponse->sendResponse();
        }

        //CFactory::load( 'libraries' , 'comment' );
        $tmpl = new CTemplate();
        $message = CComment::stripCommentData($wall->comment);
        $tmpl->set('message', $message);
        $tmpl->set('editableFunc', $editableFunc);
        $tmpl->set('id', $wall->id);

        $content = $tmpl->fetch('wall.edit');

        $objResponse->addScriptCall('joms.jQuery("#wall_' . $wallId . ' div.loading").hide();');
        $objResponse->addAssign('wall-edit-container-' . $wallId, 'innerHTML', $content);

        return $objResponse->sendResponse();
    }

    public function ajaxUpdateWall($wallId, $message, $editableFunc) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');
        $editableFunc = $filter->clean($editableFunc, 'string');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);
        $objResponse = new JAXresponse();

        if (empty($message)) {
            $objResponse->addScriptCall('alert', JText::_('COM_COMMUNITY_EMPTY_MESSAGE'));
            return $objResponse->sendResponse();
        }

        $isEditable = CWall::isEditable($editableFunc, $wall->id);

        if (!$isEditable) {
            $response->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT'));
            return $objResponse->sendResponse();
        }

        // We don't want to touch the comments data.
        $comments = CComment::getRawCommentsData($wall->comment);
        $wall->comment = $message;
        $wall->comment .= $comments;
        $my = CFactory::getUser();
        $data = CWallLibrary::saveWall($wall->contentid, $wall->comment, $wall->type, $my, false, $editableFunc, 'wall.content', $wall->id);

        $objResponse = new JAXResponse();

        $objResponse->addScriptCall('joms.walls.update', $wall->id, $data->content);

        return $objResponse->sendResponse();
    }

    public function ajaxGetOlderWalls($groupId, $discussionId, $limitStart) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $discussionId = $filter->clean($discussionId, 'int');
        $limitStart = $filter->clean($limitStart, 'int');

        $limitStart = max(0, $limitStart);
        $response = new JAXResponse();

        $app = JFactory::getApplication();
        $my = CFactory::getUser();
        //$jconfig	= JFactory::getConfig();

        $groupModel = CFactory::getModel('groups');
        $isGroupAdmin = $groupModel->isAdmin($my->id, $groupId);

        $html = CWall::getWallContents('discussions', $discussionId, $isGroupAdmin, $app->getCfg('list_limit'), $limitStart, 'wall.content', 'groups,discussion', $groupId);

        // parse the user avatar
        $html = CStringHelper::replaceThumbnails($html);
        $html = CString::str_ireplace(array('{error}', '{warning}', '{info}'), '', $html);


        $config = CFactory::getConfig();
        $order = $config->get('group_discuss_order');

        if ($order == 'ASC') {
            // Append new data at Top.
            $response->addScriptCall('joms.walls.prepend', $html);
        } else {
            // Append new data at bottom.
            $response->addScriptCall('joms.walls.append', $html);
        }

        return $response->sendResponse();
    }

    /**
     * Like an item. Update ajax count
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId	    Unique id to identify object item
     *
     */
    public function ajaxLike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $element = $filter->clean($element, 'string');
        $itemId = $filter->clean($itemId, 'int');

        $table = array('photo' => 'photo',
            'videos' => 'video');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $like = new CLike();

        if (!$like->enabled($element)) {
            // @todo: return proper ajax error
            return;
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();


        $like->addLike($element, $itemId);
        $html = $like->getHTML($element, $itemId, $my->id);

        $act = new stdClass();
        $act->cmd = $element . '.like';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = '';
        $act->content = '';
        $act->app = $element . '.like';
        $act->cid = $itemId;

        if (isset($table[$element])) {
            $table = JTable::getInstance($table[$element], 'CTable');
            $table->load($itemId);

            if (isset($table->permissions)) {
                $act->access = $table->permissions;
            }
        }

        $params = new CParameter('');

        switch ($element) {

            case 'groups':
                $act->groupid = $itemId;
                break;
            case 'events':
                $act->eventid = $itemId;
                break;
        }

        $params->set('action', $element . '.like');

        // Add logging
        CActivityStream::addActor($act, $params->toString());

        $objResponse->addScriptCall('__callback', $html);

        return $objResponse->sendResponse();
    }

    /**
     * Dislike an item
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId	    Unique id to identify object item
     *
     */
    public function ajaxDislike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $dislike = new CLike();

        if (!$dislike->enabled($element)) {
            // @todo: return proper ajax error
            return;
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();


        $dislike->addDislike($element, $itemId);
        $html = $dislike->getHTML($element, $itemId, $my->id);

        $objResponse->addScriptCall('__callback', $html);

        return $objResponse->sendResponse();
    }

    /**
     * Unlike an item
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId	    Unique id to identify object item
     *
     */
    public function ajaxUnlike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        // Load libraries
        $unlike = new CLike();

        if (!$unlike->enabled($element)) {

        } else {
            $unlike->unlike($element, $itemId);
            $html = $unlike->getHTML($element, $itemId, $my->id);

            $objResponse->addScriptCall('__callback', $html);
        }

        $act = new stdClass();
        $act->cmd = $element . '.like';
        $act->actor = $my->id;
        $act->target = 0;
        $act->title = '';
        $act->content = '';
        $act->app = $element . '.like';
        $act->cid = $itemId;

        $params = new CParameter('');

        switch ($element) {

            case 'groups':
                $act->groupid = $itemId;
                break;
            case 'events':
                $act->eventid = $itemId;
                break;
        }

        $params->set('action', $element . '.like');

        // Remove logging
        CActivityStream::removeActor($act, $params->toString());

        return $objResponse->sendResponse();
    }

    /**
     * Called by status box to add new stream data
     *
     * @param type $message
     * @param type $attachment
     * @return type
     */
    public function ajaxStreamAdd($message, $attachment) {

        $streamHTML = '';
        // $attachment pending filter

        $cache = CFactory::getFastCache();
        $cache->clean(array('activities'));

        $my = CFactory::getUser();
        $userparams = $my->getParams();

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        //@rule: In case someone bypasses the status in the html, we enforce the character limit.
        $config = CFactory::getConfig();
        if (JString::strlen($message) > $config->get('statusmaxchar')) {
            $message = JHTML::_('string.truncate', $message, $config->get('statusmaxchar'));
        }

        $message = JString::trim($message);
        $objResponse = new JAXResponse();
        $rawMessage = $message;

        // @rule: Autolink hyperlinks
        // @rule: Autolink to users profile when message contains @username
        // $message		= CLinkGeneratorHelper::replaceAliasURL($message); // the processing is done on display side
        $emailMessage = CLinkGeneratorHelper::replaceAliasURL($rawMessage, true);

        // @rule: Spam checks
        if ($config->get('antispam_akismet_status')) {
            $filter = CSpamFilter::getFilter();
            $filter->setAuthor($my->getDisplayName());
            $filter->setMessage($message);
            $filter->setEmail($my->email);
            $filter->setURL(CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $filter->setType('message');
            $filter->setIP($_SERVER['REMOTE_ADDR']);

            if ($filter->isSpam()) {
                $objResponse->addAlert(JText::_('COM_COMMUNITY_STATUS_MARKED_SPAM'));
                return $objResponse->sendResponse();
            }
        }

        $attachment = json_decode($attachment, true);

        switch ($attachment['type']) {
            case 'message':
                //if (!empty($message)) {
                    switch ($attachment['element']) {

                        case 'profile':
                            //only update user status if share messgage is on his profile
                            if (COwnerHelper::isMine($my->id, $attachment['target'])) {

                                //save the message
                                $status = $this->getModel('status');
                                /* If no privacy in attachment than we apply default: Public */
                                if (!isset($attachment['privacy']))
                                    $attachment['privacy'] = COMMUNITY_STATUS_PRIVACY_PUBLIC;
                                $status->update($my->id, $rawMessage, $attachment['privacy']);

                                //set user status for current session.
                                $today = JFactory::getDate();
                                $message2 = (empty($message)) ? ' ' : $message;
                                $my->set('_status', $rawMessage);
                                $my->set('_posted_on', $today->toSql());

                                // Order of replacement
                                $order = array("\r\n", "\n", "\r");
                                $replace = '<br />';

                                // Processes \r\n's first so they aren't converted twice.
                                $messageDisplay = str_replace($order, $replace, $message);
                                $messageDisplay = CKses::kses($messageDisplay, CKses::allowed());

                                //update user status
                                $objResponse->addScriptCall("joms.jQuery('#profile-status span#profile-status-message').html('" . addslashes($messageDisplay) . "');");
                            }

                            //if actor posted something to target, the privacy should be under target's profile privacy settings
                            if (!COwnerHelper::isMine($my->id, $attachment['target']) && $attachment['target'] != '') {
                                $attachment['privacy'] = CFactory::getUser($attachment['target'])->getParams()->get('privacyProfileView');
                            }

                            //push to activity stream
                            $act = new stdClass();
                            $act->cmd = 'profile.status.update';
                            $act->actor = $my->id;
                            $act->target = $attachment['target'];
                            $act->title = $message;
                            $act->content = '';
                            $act->app = $attachment['element'];
                            $act->cid = $my->id;
                            $act->access = $attachment['privacy'];
                            $act->comment_id = CActivities::COMMENT_SELF;
                            $act->comment_type = 'profile.status';
                            $act->like_id = CActivities::LIKE_SELF;
                            $act->like_type = 'profile.status';

                            $activityParams = new CParameter('');

                            /* Save cords if exists */
                            if (isset($attachment['location'])) {
                                /* Save geo name */
                                $act->location = $attachment['location'][0];
                                $act->latitude = $attachment['location'][1];
                                $act->longitude = $attachment['location'][2];
                            };

                            $headMeta = new CParameter('');

                            if (isset($attachment['fetch'])) {
                                $headMeta->set('title', $attachment['fetch'][2]);
                                $headMeta->set('description', $attachment['fetch'][3]);
                                $headMeta->set('image', $attachment['fetch'][1]);
                                $headMeta->set('link', $attachment['fetch'][0]);

                                //do checking if this is a video link
                                $video = JTable::getInstance('Video', 'CTable');
                                $isValidVideo = @$video->init($attachment['fetch'][0]);
                                if ($isValidVideo) {
                                    $headMeta->set('type', 'video');
                                    $headMeta->set('video_provider', $video->type);
                                    $headMeta->set('video_id', $video->getVideoId());
                                    $headMeta->set('height', $video->getHeight());
                                    $headMeta->set('width', $video->getWidth());
                                }

                                $activityParams->set('headMetas', $headMeta->toString());
                            }
                            //Store mood in paramm
                            if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                                $activityParams->set('mood', $attachment['mood']);
                            }
                            $act->params = $activityParams->toString();

                            //CActivityStream::add($act);
                            /* Let use our new CApiStream */
                            $activityData = CApiActivities::add($act);

                            CTags::add($activityData);
                            CUserPoints::assignPoint('profile.status.update');

                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::sprintf('COM_COMMUNITY_FRIEND_WALL_POST', $my->getDisplayName()), '', 'wall.post', $params);

                            //email and add notification if user are tagged
                            CUserHelper::parseTaggedUserNotification($message, $my, $activityData);

                            break;
                        // Message posted from Group page
                        case 'groups':
                            //
                            $groupLib = new CGroups();
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($attachment['target']);

                            // Permission check, only site admin and those who has
                            // mark their attendance can post message
                            if (!COwnerHelper::isCommunityAdmin() && !$group->isMember($my->id) && $config->get('lockgroupwalls')) {
                                $objResponse->addScriptCall("alert('permission denied');");
                                return $objResponse->sendResponse();
                            }

                            $act = new stdClass();
                            $act->cmd = 'groups.wall';
                            $act->actor = $my->id;
                            $act->target = 0;

                            $act->title = $message;
                            $act->content = '';
                            $act->app = 'groups.wall';
                            $act->cid = $attachment['target'];
                            $act->groupid = $group->id;
                            $act->group_access = $group->approvals;
                            $act->eventid = 0;
                            $act->access = 0;
                            $act->comment_id = CActivities::COMMENT_SELF;
                            $act->comment_type = 'groups.wall';
                            $act->like_id = CActivities::LIKE_SELF;
                            $act->like_type = 'groups.wall';

                            $activityParams = new CParameter('');

                            /* Save cords if exists */
                            if (isset($attachment['location'])) {
                                /* Save geo name */
                                $act->location = $attachment['location'][0];
                                $act->latitude = $attachment['location'][1];
                                $act->longitude = $attachment['location'][2];
                            };

                            //Store mood in paramm
                            if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                                $activityParams->set('mood', $attachment['mood']);
                            }

                            $act->params = $activityParams->toString();

                            $activityData = CApiActivities::add($act);

                            CTags::add($activityData);
                            CUserPoints::assignPoint('group.wall.create');

                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('message', $emailMessage);
                            $params->set('group', $group->name);
                            $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                            $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));

                            //Get group member emails
                            $model = CFactory::getModel('Groups');
                            $members = $model->getMembers($attachment['target'], null, true, false, true);

                            $membersArray = array();
                            if (!is_null($members)) {
                                foreach ($members as $row) {
                                    if ($my->id != $row->id) {
                                        $membersArray[] = $row->id;
                                    }
                                }
                            }

                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);

                            // Add custom stream
                            // Reload the stream with new stream data
                            $streamHTML = $groupLib->getStreamHTML($group);

                            break;

                        // Message posted from Event page
                        case 'events' :

                            $eventLib = new CEvents();
                            $event = JTable::getInstance('Event', 'CTable');
                            $event->load($attachment['target']);

                            // Permission check, only site admin and those who has
                            // mark their attendance can post message
                            if ((!COwnerHelper::isCommunityAdmin() && !$event->isMember($my->id) && $config->get('lockeventwalls'))) {
                                $objResponse->addScriptCall("alert('permission denied');");
                                return $objResponse->sendResponse();
                            }

                            // If this is a group event, set the group object
                            $groupid = ($event->type == 'group') ? $event->contentid : 0;
                            //
                            $groupLib = new CGroups();
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($groupid);

                            $act = new stdClass();
                            $act->cmd = 'events.wall';
                            $act->actor = $my->id;
                            $act->target = 0;
                            $act->title = $message;
                            $act->content = '';
                            $act->app = 'events.wall';
                            $act->cid = $attachment['target'];
                            $act->groupid = ($event->type == 'group') ? $event->contentid : 0;
                            $act->group_access = $group->approvals;
                            $act->eventid = $event->id;
                            $act->event_access = $event->permission;
                            $act->access = 0;
                            $act->comment_id = CActivities::COMMENT_SELF;
                            $act->comment_type = 'events.wall';
                            $act->like_id = CActivities::LIKE_SELF;
                            $act->like_type = 'events.wall';

                            $activityParams = new CParameter('');

                            /* Save cords if exists */
                            if (isset($attachment['location'])) {
                                /* Save geo name */
                                $act->location = $attachment['location'][0];
                                $act->latitude = $attachment['location'][1];
                                $act->longitude = $attachment['location'][2];
                            };

                            //Store mood in paramm
                            if (isset($attachment['mood']) && $attachment['mood'] != 'Mood') {
                                $activityParams->set('mood', $attachment['mood']);
                            }

                            $act->params = $activityParams->toString();

                            $activityData = CApiActivities::add($act);
                            CTags::add($activityData);

                            // add points
                            CUserPoints::assignPoint('event.wall.create');

                            // Reload the stream with new stream data
                            $streamHTML = $eventLib->getStreamHTML($event);
                            break;
                    }

                    $objResponse->addScriptCall('__callback', '');
                // /}

                break;

            case 'photo':
                switch ($attachment['element']) {

                    case 'profile':
                        $photoIds = $attachment['id'];
                        //use User Preference for Privacy
                        //$privacy = $userparams->get('privacyPhotoView'); //$privacy = $attachment['privacy'];

                        $photo = JTable::getInstance('Photo', 'CTable');


                        if (!isset($photoIds[0]) || $photoIds[0] <= 0) {
                            //$objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
                            exit;
                        }

                        //always get album id from the photo itself, do not let it assign by params from user post data
                        $photoModel = CFactory::getModel('photos');
                        $photo = $photoModel->getPhoto($photoIds[0]);
                        /* OK ! If album_id is not provided than we use album id from photo ( it should be default album id ) */
                        $albumid = (isset($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        $privacy = $album->permissions;

                        //limit checking
//                        $photoModel = CFactory::getModel( 'photos' );
//                        $config		= CFactory::getConfig();
//                        $total		= $photoModel->getTotalToday( $my->id );
//                        $max		= $config->getInt( 'limit_photo_perday' );
//                        $remainingUploadCount = $max - $total;

                        foreach ($photoIds as $key => $photoId) {
                            if (CLimitsLibrary::exceedDaily('photos')) {
                                unset($photoIds[$key]);
                                continue;
                            }
                            $photo->load($photoId);

                            $photo->caption = (!empty($message)) ? $message : $photo->caption;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->albumid = $albumid; /* We must update this photo into correct album id */
                            $photo->store();
                        }
                        if($config->get('autoalbumcover') && !$album->photoid){
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }
                        // Trigger onPhotoCreate
                        //
						$apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $photo;
                        $apps->triggerEvent('onPhotoCreate', $params);

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy; //$attachment['privacy'];
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message;
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $albumid;
                        $act->location = $album->location;

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1');
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE);
                        $params->set('photosId', implode(',', $photoIds));

                        if (count($photoIds > 1)) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }

                        // Add activity logging
                        // CActivityStream::remove($act->app, $act->cid);
                        CActivityStream::add($act, $params->toString());

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
                        break;

                    case 'groups':
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        $photoIds = $attachment['id'];
                        $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;

                        $photo = JTable::getInstance('Photo', 'CTable');
                        foreach ($photoIds as $photoId) {
                            $photo->load($photoId);

                            $photo->caption = $message;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->store();
                        }
                        // Trigger onPhotoCreate
                        //
						$apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $photo;
                        $apps->triggerEvent('onPhotoCreate', $params);

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($photo->albumid);

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $album->id;
                        $act->location = $album->location;

                        $act->groupid = $group->id;
                        $act->group_access = $group->approvals;
                        $act->eventid = 0;
                        //$act->access		= $attachment['privacy'];

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1'); // this photo uploaded from status stream
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        $params->set('photosId', implode(',', $photoIds));
                        // Add activity logging
                        if (count($photoIds > 1)) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }
                        // CActivityStream::remove($act->app, $act->cid);
                        CActivityStream::add($act, $params->toString());

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group);

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));

                        break;
                        dafault:
                        return;
                }

                break;

            case 'video':
                switch ($attachment['element']) {
                    case 'profile':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        $privacy = $attachment['privacy'];

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('creator_type', VIDEO_USER_TYPE);
                        $video->set('status', 'ready');
                        $video->set('permissions', $privacy);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);
                        $video->store();

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.upload';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (isset($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //
						CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        //
						CUserPoints::assignPoint('video.add', $video->creator);

                        // Trigger for onVideoCreate
                        //
						$apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        break;

                    case 'groups':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        $privacy = 0; //$attachment['privacy'];

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('status', 'ready');
                        $video->set('groupid', $attachment['target']);
                        $video->set('permissions', $privacy);
                        $video->set('creator_type', VIDEO_GROUP_TYPE);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);
                        $video->store();

                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.upload';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->groupid = $video->groupid;
                        $act->group_access = $group->approvals;
                        $act->location = $video->location;

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style

                        CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        CUserPoints::assignPoint('video.add', $video->creator);

                        // Trigger for onVideoCreate
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group);

                        break;

                    default:
                        return;
                }

                break;

            case 'event':
                switch ($attachment['element']) {

                    case 'profile':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        $objResponse->addScriptCall('joms.status.Creator[\'event\'].success', JText::sprintf('COM_COMMUNITY_EVENTS_CREATED_NOTICE', $event->title));

                        if ( CFactory::getConfig()->get('event_moderation') ) {
                            $objResponse->addAlert( JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title) );
                        }

                        break;

                    case 'groups':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        $objResponse->addScriptCall('__callback', '');

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group);

                        if ( CFactory::getConfig()->get('event_moderation') ) {
                            $objResponse->addAlert( JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title) );
                        }

                        break;
                }

                break;

            case 'link':
                break;
        }

        if (!isset($attachment['filter'])) {
            $attachment['filter'] = '';
        }

        if (empty($streamHTML)) {
            if (!isset($attachment['target']))
                $attachment['target'] = '';
            if (!isset($attachment['element']))
                $attachment['element'] = '';
            $streamHTML = CActivities::getActivitiesByFilter($attachment['filter'], $attachment['target'], $attachment['element']);
        }

        $objResponse->addAssign('activity-stream-container', 'innerHTML', $streamHTML);

        // Log user engagement
        CEngagement::log($attachment['type'] . '.share', $my->id);

        return $objResponse->sendResponse();
    }

    /**
     * Add comment to the stream
     *
     * @param int	$actid acitivity id
     * @param string $comment
     * @return obj
     */
    public function ajaxStreamAddComment($actid, $comment) {
        $filter      = JFilterInput::getInstance();
        $actid       = $filter->clean($actid, 'int');
        $my          = CFactory::getUser();
        $config      = CFactory::getConfig();
        $objResponse = new JAXResponse();
        $wallModel   = CFactory::getModel('wall');
        $rawComment  = $comment;


        // Pull the activity record and find out the actor
        // only allow comment if the actor is a friend of current user
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        //who can add comment
        $obj = $act;

        if ($act->groupid > 0) {
            $obj = JTable::getInstance('Group', 'CTable');
            $obj->load($act->groupid);
        } else if ($act->eventid > 0) {
            $obj = JTable::getInstance('Event', 'CTable');
            $obj->load($act->eventid);
        }

        $params = new CParameter($act->params);

        $batchcount = $params->get('batchcount', 0);
        $wallParam = new CParameter('');
        if ($act->app == 'photos' && $batchcount > 1) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($params->get('photoid'));

            $act->comment_type = 'albums';
            $act->comment_id = $photo->albumid;

            $wallParam->set('activityId',$act->id);
        }

        // Allow comment for system post
        $allowComment = false;
        if ($act->app == 'system') {
            $allowComment = !empty($my->id);
        }

        if ($my->authorise('community.add', 'activities.comment.' . $act->actor, $obj) || $allowComment) {

            $table            = JTable::getInstance('Wall', 'CTable');
            $table->type      = $act->comment_type;
            $table->contentid = $act->comment_id;
            $table->post_by   = $my->id;
            $table->comment   = $comment;
            $table->params    = $wallParam->toString();

            if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $comment))) {

                $urlsParser = new CParserUrls ();
                $urlsParser->init(array('content' => $comment));
                $urls = $urlsParser->extract();

                if (count($urls) > 0) {
                    $url = array_shift($urls);
                    $crawl = CCrawler::getCrawler();
                    $data = $crawl->crawl('GET', $url);
                    $graphObject = $data->parse();
                    $table->params = $graphObject->toString();
                }
            }

            $table->store();

            $cache = CFactory::getFastCache();
            $cache->clean(array('activities'));

            if ($act->app == 'photos') {
                $table->contentid = $act->id;
            }
            $table->params = new CParameter($table->get('params'));
            $comment = CWall::formatComment($table);

            $objResponse->addScriptCall('joms.miniwall.insert', $actid, $comment);

            //notification for activity comment
            //case 1: user's activity
            //case 2 : group's activity
            //case 3 : event's activity
            if ($act->groupid == 0 && $act->eventid == 0) {
                // //CFactory::load( 'libraries' , 'notification' );
                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=profile&userid=' . $act->actor . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    $command = 'profile_activity_add_comment';
                    CNotificationLibrary::add('profile_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'), '', 'profile.activitycomment', $params);
                    /* Notifications to all poster in this activity except myself */
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $my->id);
                    if (!empty($users)) {
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    }
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('profile_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_REPLY_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    }
                }
            } elseif ($act->groupid != 0) { /* Group activity */

                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $act->groupid . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    /**
                     * Adds notification data into the mailq table
                     * @uses Make sure your provide body parameter or email content will be empty
                     * @param type $command
                     * @param null $actorId
                     * @param type $recipients
                     * @param type $subject
                     * @param type $body
                     * @param type $templateFile
                     * @param type $mailParams
                     * @param type $sendEmail
                     * @param type $favicon
                     * @param type $altSubject
                     * @return type
                     */
                    CNotificationLibrary::add('groups_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_GROUP_EMAIL_SUBJECT'), $table->comment, 'group.activitycomment', $params);
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('groups_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_REPLY_EMAIL_SUBJECT'), $table->comment, 'group.activityreply', $params);
                    }
                }
            } elseif ($act->eventid != 0) {
                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $act->eventid . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    CNotificationLibrary::add('events_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EVENT_EMAIL_SUBJECT'), '', 'event.activitycomment', $params);
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('events_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_REPLY_EMAIL_SUBJECT'), '', 'event.activityreply', $params);
                    }
                }
            }

            //notifications
            CUserHelper::parseTaggedUserNotification($rawComment, $my, $act);

            //Add tag
            CTags::add($table);
            // Log user engagement
            CEngagement::log($act->app . '.comment', $my->id);
        } else {
            // Cannot comment on non-friend stream.
            $objResponse->addAlert('Permission denied');
        }

        return $objResponse->sendResponse();
    }

    /**
     * Remove a wall comment
     *
     * @param int $actid
     * @param int $wallid
     */
    public function ajaxStreamRemoveComment($wallid) {
        $filter = JFilterInput::getInstance();
        $wallid = $filter->clean($wallid, 'int');

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        //
        //@todo: check permission. Find the activity id that
        // has this wall's data. Make sure actor is friend with
        // current user

        $table = JTable::getInstance('Wall', 'CTable');
        $table->load($wallid);
        $table->delete();

        //$objResponse->addScriptCall('joms.miniwall.delete', $wallid);
        $objResponse->addScriptCall('joms.jQuery("div.stream-comment[data-commentid=' . $table->id . ']").remove', "");

        return $objResponse->sendResponse();
    }

    /**
     * Fill up the 'all comment fields with.. all comments
     *
     */
    public function ajaxStreamShowComments($actid) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');

        // Pull the activity record and find out the actor
        // only allow comment if the actor is a friend of current user
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);
        $params = new CParameter($act->params);

        if ($act->comment_type == 'photos' && $params->get('batchcount',0) > 1) {
             $act->comment_type ='albums';
             $act->comment_id 	= $act->cid;
        }

        $comments = $wallModel->getAllPost($act->comment_type, $act->comment_id);

        $commentsHTML = '';

        foreach ($comments as $row) {
            $row->params = new CParameter($row->get('params', '{}'));
            if($row->type == 'albums' && $row->params->get('activityId',NULL) != $actid){
                continue;
            }
            $commentsHTML .= CWall::formatComment($row);
        }

        $objResponse->addScriptCall('joms.miniwall.loadall', $actid, $commentsHTML);

        return $objResponse->sendResponse();
    }

    /**
     *
     */
    public function ajaxStreamAddLike($actid, $type = null) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');
        $like = new CLike();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        if ($type == 'comment') {
            $act = JTable::getInstance('Wall', 'CTable');
            $act->load($actid);
            $act->like_type = 'comment';
            $act->like_id = $act->id;
        }

        $params  = new CParameter($act->params);

        if(isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) > 1){
            $act->like_type = 'album';
            $act->like_id = $act->cid;
        }
        // Count before the add
        $oldLikeCount = $like->getLikeCount($act->like_type, $act->like_id);

        $like->addLike($act->like_type, $act->like_id);

        $likeCount = $like->getLikeCount($act->like_type, $act->like_id);

        // If the like count is 1, then, the like bar most likely not there before
        // but, people might just click twice, hence the need to compare it before
        // the actual like

        if ($likeCount == 1 && $oldLikeCount != $likeCount) {
            // Clear old like status
            $objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid} .cStream-Likes').remove", '');
            $objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid}').prepend", '<div class="cStream-Likes"></div>');
        }
        if ($type == 'comment') {
            $this->_commentShowLikes($objResponse, $act->id);
            $dataStreamType = 'data-stream-type="comment"';
        } else {
            $this->_streamShowLikes($objResponse, $act->id, $act->like_type, $act->like_id);
            $dataStreamType = '';
        }

        $objResponse->addScriptCall("joms.jQuery('a.joms-icon-thumbs-up[data-stream-id=" . $act->id . "]').replaceWith", '<a data-action="unlike" ' . $dataStreamType . 'data-stream-id=' . $act->id . ' href="#" class="joms-icon-thumbs-down">' . JText::_('COM_COMMUNITY_UNLIKE') . '</a>');

        return $objResponse->sendResponse();
    }

    /**
     *
     */
    public function ajaxStreamUnlike($actid, $type = null) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $objResponse = new JAXResponse();
        $like = new CLike();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        if ($type == 'comment') {
            $act = JTable::getInstance('Wall', 'CTable');
            $act->load($actid);
            $act->like_type = 'comment';
            $act->like_id = $act->id;
        }

        $params  = new CParameter($act->params);

        if(isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) > 1){
            $act->like_type = 'album';
            $act->like_id = $act->cid;
        }

        $like->unlike($act->like_type, $act->like_id);

        if ($type == 'comment') {
            $this->_commentShowLikes($objResponse, $act->id);
            $dataStreamType = 'data-stream-type="comment"';
        } else {
            $this->_streamShowLikes($objResponse, $act->id, $act->like_type, $act->like_id);
            $dataStreamType = '';
        }

        $objResponse->addScriptCall("joms.jQuery('a.joms-icon-thumbs-down[data-stream-id=" . $act->id . "]').replaceWith", '<a data-action="like" ' . $dataStreamType . 'data-stream-id=' . $act->id . ' href="#" class="joms-icon-thumbs-up">' . JText::_('COM_COMMUNITY_LIKE') . '</a>');


        return $objResponse->sendResponse();
    }

    /**
     * List down all people who like it
     *
     */
    public function ajaxStreamShowLikes($actid) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');

        // Pull the activity record
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        $this->_streamShowLikes($objResponse, $actid, $act->like_type, $act->like_id);

        return $objResponse->sendResponse();
    }

    public function ajaxDeleteTempImage() {
        $jinput = JFactory::getApplication()->input;
        $photo_ids = $jinput->get('arg2', 'default_value', 'array');
        //$photo_ids = (!isset($photo_ids)) ? '' : explode(',', $photo_ids);

        $my = CFactory::getUser();

        if (isset($photo_ids) && count($photo_ids) > 0) {
            foreach ($photo_ids as $photoid) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($photoid);

                //we must make sure that the creator is the current user
                if ($photo->creator == $my->id && $photo->status == 'temp') {
                    $photo->delete();
                }
            }
        }

        exit;
    }

    /**
     * Display the full list of people who likes this stream item
     *
     * @param <type> $objResponse
     * @param <type> $actid
     * @param <type> $like_type
     * @param <type> $like_id
     */
    private function _streamShowLikes($objResponse, $actid, $like_type, $like_id) {
        $my = CFactory::getUser();
        $like = new CLike();

        $likes = $like->getWhoLikes($like_type, $like_id);

        $canUnlike = false;
        $likeHTML = '<i class="stream-icon joms-icon-thumbs-up"></i>';
        $likeUsers = array();

        foreach ($likes as $user) {
            $likeUsers[] = '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
            if ($my->id == $user->id)
                $canUnlike = true;
        }

        if (count($likeUsers) == 0) {
            $likeHTML = JText::_('COM_COMMUNITY_NO_ONE_LIKE_THIS');
        } else {
            $likeHTML .= implode(", ", $likeUsers);
            $likeHTML = CStringHelper::isPlural(count($likeUsers)) ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY_LIST', $likeHTML) : JText::sprintf('COM_COMMUNITY_LIKE_THIS_LIST', $likeHTML);
        }

        // When we show all, we hide the count, the "3 people like this"
        $objResponse->addScriptCall("joms.jQuery('*[data-streamid={$actid}] .cStream-Likes').html", "$likeHTML");
    }

    private function _commentShowLikes($obj, $actid) {
        $my = CFactory::getUser();
        $like = new CLike();

        $likeCount = $like->getLikeCount('comment', $actid);

        if ($likeCount > 0) {
            $likeHTML = '<a href="#" data-stream-id="' . $actid . '" data-action="showlike"><i class="joms-icon-thumbs-up"></i><span>' . $likeCount . '</span></a>';
        } else {
            $likeHTML = '';
        }
        $obj->addScriptCall("joms.jQuery('.cStream-Meta').find('a[data-stream-id={$actid}][data-action=\'showlike\']').remove");
        $obj->addScriptCall("joms.jQuery('*[data-commentid={$actid}] .cStream-Meta a[data-stream-id={$actid}]').after", $likeHTML);
    }

    public function ajaxeditComment($id, $value, $parentId) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $actModel = CFactory::getModel('activities');
        $objResponse = new JAXResponse();

        if ($my->id == 0) {
            $this->blockUnregister();
        }

        $wall = JTable::getInstance('wall', 'CTable');
        $wall->load($id);

        $cid = isset($wall->contentid) ? $wall->contentid : null;
        $activity = $actModel->getActivity($cid);
        $ownPost = ($my->id == $wall->post_by);
        $targetPost = ($activity->target == $my->id);
        $allowEdit = COwnerHelper::isCommunityAdmin() || ( ( $ownPost || $targetPost ) && !empty($my->id) );
        $value = trim($value);

        if (empty($value)) {
            $objResponse->addAlert(JText::_('COM_COMMUNITY_CANNOT_EDIT_COMMENT_ERROR'));
        } else if ($config->get('wallediting') && $allowEdit) {
            $wall->comment = $value;
            $wall->store();

            $CComment = new CComment();
            $value = $CComment->stripCommentData($value);

            // Need to perform basic formatting here
            // 1. support nl to br,
            // 2. auto-link text
            $CTemplate = new CTemplate();
            $value = $origValue = $CTemplate->escape($value);
            $value = CStringHelper::autoLink($value);
            $value = nl2br($value);
            $value = CLinkGeneratorHelper::replaceAliasURL($value);
            $value = CStringHelper::getEmoticon($value);

            $objResponse->addScriptCall("joms.jQuery('div[data-commentid=" . $id . "] .cStream-Content span.comment').html", $value);
            $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").val', $origValue);
            $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").removeData', 'initialized');

        } else {
            $objResponse->addAlert(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT'));
        }

        return $objResponse->sendResponse();
    }

    /**
     *
     * @param type $text
     * @return type
     */
    public function ajaxGetFetchUrl($text) {
        $objResponse = new JAXResponse();
        $urlsParser = new CParserUrls ();
        $urlsParser->init(array('content' => $text));
        $urls = $urlsParser->extract();


        /**
         * Crawle data
         * We only work with first url
         */
        if (count($urls) > 0) {
            $url = array_shift($urls);
            $crawl = CCrawler::getCrawler();
            $data = $crawl->crawl('GET', $url);
            $graphObject = $data->parse();
            /* Do reset all fetched object */
            $objResponse->addScriptCall('joms.sharebox.shareStatus.reset();');
            $objResponse->addScriptCall('joms.sharebox.shareStatus.updateFetchedData', "" . $graphObject);
        }
        /* No matter any error or not we must do sendResponse */
        return $objResponse->sendResponse();
    }

}
