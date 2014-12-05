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

/**
 * Class exists checking
 */
if (!class_exists('plgSystemjomsocialredirect')) {

    /**
     * Plugin entrypoint
     * @link http://docs.joomla.org/Plugin/Events/System
     */
    class plgSystemjomsocialredirect extends JPlugin {

        /**
         * Construct method
         * @param type $subject
         * @param type $config
         */
        function plgSystemjomsocialredirect(& $subject, $config) {
            parent::__construct($subject, $config);
            $this->loadLanguage();
            if (JFactory::getApplication()->isSite()) {
                include_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
            }
        }

        /**
         * This event is triggered after the framework has loaded and the application initialise method has been called.
         */
        public function onAfterRoute() {
            if (JFactory::getApplication()->isSite()) {
                $option = JRequest::getVar('option');
                if ($option == 'com_user' || $option == 'com_users') {
                    $this->overrideComUserRegistration();
                    $this->overrideRedirectLoginLogout();
                }
            }
        }

        /**
         * New event to be triged when FB user login
         * @return string
         */
        public function onAfterFBUserLogin() {
            $link = $this->getMenuLink($this->params->get('redirect_login', 1));
            return $link;
        }

        /**
         * Method to override Login / Logout redirect
         */
        private function overrideRedirectLoginLogout() {
            $mainframe = JFactory::getApplication();
            /* override redirect after login / logout */
            $task = JRequest::getVar('task');

            if ($this->params->get('redirect_previous') == 1) {
                /* in com_user we need to save reference url for these tasks to know previous page */
                if ($task === 'user.login' || $task === 'login' || $task === 'user.logout' || $task === 'logout') {
                    $session = JFactory::getSession();
                    $session->set('redirect_previous_url', $_SERVER['HTTP_REFERER']);
                }
            }
            switch ($task) {
                case 'user.login' : //Joomla 1.6 and later
                case 'login' : /* on logging */
                    if ($this->login()) { /* we do login by self */
                        /* redirect if login success */
                        $link = $this->getMenuLink($this->params->get('redirect_login', 1));
                        if ($this->params->get('redirect_previous') == 1) {
                            $session = JFactory::getSession();
                            $link = $session->get('redirect_previous_url', $link);
                        }
                        $mainframe->redirect($link, JText::_($this->params->get('redirect_login_msg', 'LOGIN_SUCCESSFUL')), 'message');
                    } else {
                        /* redirect if login failed */
                        $link = $this->getMenuLink($this->params->get('redirect_login_failed', 1));
                        if ($this->params->get('redirect_previous') == 1) {
                            $session = JFactory::getSession();
                            $link = $session->get('redirect_previous_url', $link);
                        }
                        $mainframe->redirect($link, JText::_($this->params->get('redirect_login_failed_msg', 'LOGIN_FAILED')), 'notice');
                    }
                    break;
                case 'user.logout' : //Joomla 1.6 and later
                case 'logout' :
                    $link = $this->getMenuLink($this->params->get('redirect_logout', 1));
                    if ($this->params->get('redirect_previous') == 1) {
                        $session = JFactory::getSession();
                        $link = $session->get('redirect_previous_url', $link);
                    }
                    JFactory::getApplication()->logout();
                    $mainframe->redirect($link, JText::_($this->params->get('redirect_logout_msg', 'YOU_HAVE_LOGGED_OUT')), 'message');
                    break;

                default :

                    break;
            }
        }

        /**
         * Method to redirect com_user registration to JomSocial registration
         */
        private function overrideComUserRegistration() {
            $mainframe = JFactory::getApplication();
            $view = JRequest::getVar('view');
            /* override Joomla com_user registration by JomSocial registration */
            if ($view == 'register' || $view == 'registration') {
                /* index.php?option=com_user&view=register */
                if ($this->params->get('override_com_user_registration', 1) == 1) {
                    $jsRegistrationLink = 'index.php?option=com_community&view=register';
                    $jsRegistrationLink = CRoute::_($jsRegistrationLink);
                    //JFactory::getApplication ()->enqueueMessage ( $jsRegistrationLink );
                    $mainframe->redirect($jsRegistrationLink, JText::_($this->params->get('redirect_registration_msg', 'REDIRECTED_TO_COMMUNITY_REGISTRATION')), 'message');
                }
            }
        }

        /**
         * Method to login
         * This method will call Application login and force to return true / false
         * We will show message override authentication event
         * @return boolean
         */
        private function login() {
            /**
             * @param   array  $credentials  Array('username' => string, 'password' => string)
             * @param   array  $options      Array('remember' => boolean)
             */
            $credentials ['username'] = JRequest::getVar('username');
            $credentials ['password'] = JRequest::getVar('passwd');
            if ($credentials ['password'] == '') {
                //try to detect for joomla 1.6, 17
                $credentials ['password'] = JRequest::getVar('password');
            }
            $options ['silent'] = true; /* force turn true / false of login function instead error msg */
            $options ['remember'] = JRequest::getVar('remember');
            return JFactory::getApplication()->login($credentials, $options);
        }

        /**
         * Private method to get JomSocial itemId
         * If have no itemId will return itemId of current page
         * @return int
         */
        private function getItemId() {
            $jsFrontpageLink = 'index.php?option=com_community&view=frontpage';

            $db = JFactory::getDBO();
            $query = ' SELECT id FROM #__menu WHERE link = ' . $db->quote($jsFrontpageLink) . ' AND published = 1 ';
            $db->setQuery($query);
            $itemId = $db->loadResult();
            if (!$itemId) {
                $itemId = JRequest::getVar('Itemid');
            }
            return $itemId;
        }

        /**
         * 
         * @param int $Itemid
         * @return string
         */
        private function getMenuLink($Itemid) {
            $db = JFactory::getDBO();
            $query = ' SELECT menutype,link FROM #__menu WHERE id = ' . $db->quote($Itemid) . ' AND published = 1 ';
            $db->setQuery($query);
            $menu = $db->loadObject();
            if (!empty($menu)) {
                if ($menu->menutype == 'jomsocial') {
                    //for toolbar menu item
                    $url = CRoute::_($menu->link, false);
                } else {
                    $url = JRoute::_($menu->link . "&Itemid=" . $Itemid, false); // use JRoute to make link from object			
                }
            } else {
                $url = JRoute::_(JURI::root(), false);
            }
            return $url;
        }

    }

}