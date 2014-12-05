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

jimport('joomla.application.component.view');
jimport('joomla.utilities.arrayhelper');

if (!class_exists('CommunityViewFrontpage')) {

    class CommunityViewFrontpage extends CommunityView {

        public function display($tpl = null) {

            $config = CFactory::getConfig();
            $document = JFactory::getDocument();

            $usersConfig = JComponentHelper::getParams('com_users');

            /**
             * Opengraph
             */
            CHeadHelper::setType('website', JText::sprintf('COM_COMMUNITY_FRONTPAGE_TITLE', $config->get('sitename')));

            $my = CFactory::getUser();
            $model = CFactory::getModel('user');

            $frontpageUsers = intval($config->get('frontpageusers'));
            $document->addScriptDeclaration("var frontpageUsers	= " . $frontpageUsers . ";");

            /* This config already declared in module !!! */
            //$frontpageVideos = intval($config->get('frontpagevideos'));
            //$document->addScriptDeclaration("var frontpageVideos	= " . $frontpageVideos . ";");

            $feedLink = CRoute::_('index.php?option=com_community&view=frontpage&format=feed');
            $feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('COM_COMMUNITY_SUBSCRIBE_RECENT_ACTIVITIES_FEED') . '" href="' . $feedLink . '"/>';
            $document->addCustomTag($feed);

            // Process headers HTML output
            $headerHTML = '';
            $tmpl = new CTemplate();
            $alreadyLogin = 0;

            if ($my->id != 0) {
                $headerHTML = $tmpl->fetch('frontpage.members');
                $alreadyLogin = 1;
            } else {
                $uri = CRoute::_('index.php?option=com_community&view=' . $config->get('redirect_login'), false);
                $uri = base64_encode($uri);

                $fbHtml = '';

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

                $headerHTML = $tmpl->set('allowUserRegister', $usersConfig->get('allowUserRegistration'))
                        ->set('fbHtml', $fbHtml)
                        ->set('useractivation', $usersConfig->get('useractivation'))
                        ->set('return', $uri)
                        ->fetch('frontpage.guests');
            }

            $my = CFactory::getUser();
            $totalMembers = $model->getMembersCount();

            unset($tmpl);

            $latestActivitiesData = $this->showLatestActivities();
            $latestActivitiesHTML = $latestActivitiesData['HTML'];

            $tmpl = new CTemplate();
            $tmpl->set('totalMembers', $totalMembers)
                    ->set('my', $my)
                    ->set('alreadyLogin', $alreadyLogin)
                    ->set('header', $headerHTML)
                    ->set('userActivities', $latestActivitiesHTML)
                    ->set('config', $config)
                    ->set('customActivityHTML', $this->getCustomActivityHTML());

            $status = new CUserStatus();

            if ($my->authorise('community.view', 'frontpage.statusbox')) {
                // Add default status box

                CUserHelper::addDefaultStatusCreator($status);

                if (COwnerHelper::isCommunityAdmin() && $config->get('custom_activity')) {
                    $template = new CTemplate();
                    $template->set('customActivities', CActivityStream::getCustomActivities());

                    $creator = new CUserStatusCreator('custom');
                    $creator->title = JText::_('COM_COMMUNITY_CUSTOM');
                    $creator->html = $template->fetch('status.custom');

                    $status->addCreator($creator);
                }
            }

            echo $tmpl->set('userstatus', $status)
                    ->fetch('frontpage.index');
        }

        public function getCustomActivityHTML() {
            $tmpl = new CTemplate();
            return $tmpl->set('isCommunityAdmin', COwnerHelper::isCommunityAdmin())
                            ->set('customActivities', CActivityStream::getCustomActivities())
                            ->fetch('custom.activity');
        }

        /**
         * Get latest activities with HTML to render
         * @return array
         */
        public function showLatestActivities() {
            $config = CFactory::getConfig();
            $my = CFactory::getUser();            
            $jinput = JFactory::getApplication()->input;

            /* We do store filters into session than we can reuse it under ajax */
            $filter = $jinput->get('filter', $config->get('frontpageactivitydefault'));
            $value = $jinput->get('value');
            
            if (strpos($filter, ':') !== false && $my->id != 0) {
                $filter = explode(':', $filter);
                JFactory::getApplication()->redirect(CRoute::_('index.php?option=com_community&view=frontpage&filter=' . $filter[0] . '&value=' . $filter[1],false));
            }
            $userActivities = '';

            /* Filtering */
            switch ($filter) {
                /* Filter by privacy */
                case 'privacy':
                    /* Filter by me and my friends */
                    if ($value == 'me-and-friends' && $my->id != 0) {
                        /**
                         *
                         * @param type $filter
                         * @param type $userId
                         * @param type $view
                         * @param type $showMore
                         */
                        $userActivities = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true);
                    } else {
                        /* No filter. Get all */
                        $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true);
                    }
                    break;
                /* Filter by type */
                case 'apps':
                    /* By default we use all */
                    $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array('apps' => array($value)));
                    break;
                /* By default we do filter by privacy and follow backend configured */
                default:
                    $defaultFilter = $config->get('frontpageactivitydefault');
                    /* Filter by me and my friends and of course not for guess */
                    if ($defaultFilter == 'friends' && $my->id != 0) {
                        $userActivities = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true);
                    } else {
                        $userActivities = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true);
                    }
                    break;
            }

            $activities = array();
            $activities['HTML'] = $userActivities;

            return $activities;
        }

        public function showFeaturedEvents($total = 5) {
            $session = JFactory::getSession();
            $html = ''; //$session->get('frontpage_events');
            if (!$html) {


                $tmpl = new CTemplate();
                $frontpage_latest_events = intval($tmpl->params->get('frontpage_latest_events'));
                $html = '';
                $data = array();

                if ($frontpage_latest_events != 0) {
                    $model = CFactory::getModel('Events');
                    $result = $model->getEvents(null, null, null, null, true, false, null, null, CEventHelper::ALL_TYPES, 0, $total);

                    $events = array();
                    $eventView = CFactory::getView('events');
                    $events = $eventView->_getEventsFeaturedList();

                    $tmpl = new CTemplate();
                    $tmpl->set('events', $events);

                    $html = $tmpl->fetch('frontpage.latestevents');
                }
            }
            $session->set('frontpage_events', $html);
            $data['HTML'] = $html;
            return $data;
        }

        public function showFeaturedGroups($total = 5) {
            $tmpl = new CTemplate();
            $config = CFactory::getConfig();
            $showlatestgroups = intval($tmpl->params->get('showlatestgroups'));
            $html = '';
            $data = array();

            if ($showlatestgroups != 0) {
                $groupModel = CFactory::getModel('groups');
                $tmpGroups = $groupModel->getAllGroups(null, null, null, $total);
                $groups = array();

                $data = array();
                $groupView = CFactory::getView('groups');
                $groups = $groupView->getGroupsFeaturedList();

                $tmpl = new CTemplate();
                $html = $tmpl->setRef('groups', $groups)
                        ->fetch('frontpage.latestgroup');
            }

            $data['HTML'] = $html;

            return $data;
        }

        public function getMembersHTML($data) {
            if (empty($data))
                return '';

            $members = array_slice($data['members'],0,$data['limit']);
            //$limit = $data['limit'];

            $tmpl = new CTemplate();
            echo $tmpl->set('members', $members)
                    ->fetch('frontpage.latestmember.list');
        }

    }

}