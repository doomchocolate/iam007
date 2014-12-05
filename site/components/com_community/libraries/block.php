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

if (!class_exists('blockUser')) {

    /**
     * This's quick class to provide method to block / unblock user
     * @todo class rename to follow correct JomSocial classname pattern
     */
    class blockUser {

        /**
         * 
         * @param type $userId
         * @param type $type
         * @param type $redirect
         */
        public function block($userId, $type, $redirect = false) {
            $my = CFactory::getUser();
            $result = false;
            /* both of ids must not empty */
            if (empty($my->id) || empty($userId)) {
                $message = JText::_('COM_COMMUNITY_ERROR_BLOCK_USER');
            } else {
                /* You can't block yourself */
                if ($my->id != $userId) {
                    /* Get model */
                    $model = CFactory::getModel('Block');                                        
                    /* Do block */
                    if ($model->blockUser($my->id, $userId, $type)) {                        
                        $result = true;
                        /* check if current user is friend with requested user */
                        $isFriend = CFriendsHelper::isConnected($userId, $my->id);
                        /* Remove user as friend if user is a friend */
                        if ($isFriend)
                            $this->removeFriend($userId);
                        $message = JText::_('COM_COMMUNITY_USER_BLOCKED');
                    }else {                        
                        $message = JText::_('COM_COMMUNITY_ERROR_BLOCK_USER');
                    }
                } else {
                    $message = JText::_('COM_COMMUNITY_ERROR_BLOCK_USER');
                }
            }            
            if ($redirect) {
                $mainframe = JFactory::getApplication();
                $jinput = $mainframe->input;

                /* generate redirect url */
                $viewName = $jinput->get->get('view', '');
                $urlUserId = ($viewName == 'friends') ? '' : "&userid=" . $userId;
                $url = CRoute::_("index.php?option=com_community&view=" . $viewName . $urlUserId, false);
                $mainframe->redirect($url, $message);
            }
            return $result;
        }

        /**
         * unblock user(removeBan)
         */
        public function unBlock($userId, $layout = null) {
            $my = CFactory::getUser();
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;

            $viewName = $jinput->get->get('view', ''); //JRequest::getVar('view','','GET');
            $task = !empty($layout) && $layout != 'null' ? '&task=' . $layout : null;
            $urlUserId = $viewName == 'friends' ? '' : "&userid=" . $userId;
            $url = CRoute::_("index.php?option=com_community&view=" . $viewName . $task . $urlUserId, false);

            $message = empty($my->id) || empty($userId) ? JText::_('COM_COMMUNITY_ERROR_BLOCK_USER') : '';

            if (!empty($my->id) && !empty($userId)) {
                $model = CFactory::getModel('block');
                $message = $model->removeBannedUser($my->id, $userId) ? JText::_('COM_COMMUNITY_USER_UNBLOCKED') : JText::_('COM_COMMUNITY_ERROR_BLOCK_USER');
            }

            $mainframe->redirect($url, $message);
        }

        /**
         * remove friend
         */
        public function removeFriend($friendid) {
            $mainframe = JFactory::getApplication();
            $jinput = $mainframe->input;
            $model = CFactory::getModel('friends');
            $my = CFactory::getUser();

            $viewName = $jinput->get->get('view', ''); //JRequest::getVar('view','','GET');
            if ( $viewName )
                $view = CFactory::getView($viewName);

            if ($model->deleteFriend($my->id, $friendid)) {
                // Substract the friend count
                $model->updateFriendCount($my->id);
                $model->updateFriendCount($friendid);

                //add user points
                // we deduct poinst to both parties
                //CFactory::load( 'libraries' , 'userpoints' );
                CUserPoints::assignPoint('friends.remove');
                CUserPoints::assignPoint('friends.remove', $friendid);

                $friend = CFactory::getUser($friendid);
                if ( isset($view) )
                    $view->addInfo(JText::sprintf('COM_COMMUNITY_FRIENDS_REMOVED', $friend->getDisplayName()));
                //@todo notify friend after remove them from our friend list
                //trigger for onFriendRemove
                $eventObject = new stdClass();
                $eventObject->profileOwnerId = $my->id;
                $eventObject->friendId = $friendid;
                $this->_triggerFriendEvents('onFriendRemove', $eventObject);
                unset($eventObject);
            } else {
                if ( isset($view) )
                    $view->addinfo(JText::_('COM_COMMUNITY_FRIENDS_REMOVING_FRIEND_ERROR'));
            }
        }

        /*
         * friends event name
         * object
         */

        public function _triggerFriendEvents($eventName, &$args, $target = null) {
            CError::assert($args, 'object', 'istype', __FILE__, __LINE__);

            require_once( COMMUNITY_COM_PATH . '/libraries/apps.php' );
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $params = array();
            $params[] = $args;

            if (!is_null($target))
                $params[] = $target;

            $appsLib->triggerEvent($eventName, $params);
            return true;
        }

        /**
         * restrict blocked user to access owner details
         */
        public function ajaxBlockMessage() {
            $objResponse = new JAXResponse();
            $uri = CFactory::getLastURI();
            $uri = base64_encode($uri);
            $config = CFactory::getConfig();

            $tmpl = new CTemplate();
            $html = $tmpl->set('uri', $uri)
                    ->set('config', $config)
                    ->fetch('block.denied');

            $objResponse->addScriptCall('cWindowAddContent', $html);
            return $objResponse->sendResponse();
        }

        /**
         * restrict blocked user to access owner details
         */
        public function ajaxBlockWarn() {
            $objResponse = new JAXResponse();
            $config = CFactory::getConfig();
            $html = JText::_('COM_COMMUNITY_YOU_HAD_BLOCKED_THIS_USER');

            $actions = '<form method="post" action="" style="float:right;">';
            $actions .= '<input type="button" class="input button" onclick="cWindowHide();return false;" name="cancel" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '" />';
            $actions .= '</form>';

            $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

            $objResponse->addScriptCall('joms.jQuery("#cwin_logo").html("' . $config->get('sitename') . '");');
            return $objResponse->sendResponse();
        }

        /**
         * restrict blocked user to access owner details
         */
        public function isUserBlocked($userId, $viewName) {
            $my = CFactory::getUser();
            $view = array('photos', 'videos', 'friends', 'profile', 'inbox');

            if (in_array($viewName, $view) && !empty($userId) && $userId != $my->id) {

                $block = CFactory::getModel('block');

                if ($block->getBlockStatus($my->id, $userId)) {
                    return true;
                }

                if ($block->getBlockStatus($userId, $my->id)) {
                    return true;
                }
            }

            return false;
        }

    }

}