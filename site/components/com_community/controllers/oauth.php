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

class CommunityOauthController extends CommunityBaseController {

    public function callback() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $denied = $jinput->get('denied', '', 'NONE'); //JRequest::getVar( 'denied' , '' );
        $app = $jinput->get('app', '', 'STRING'); //JRequest::getVar( 'app' , '' );
        $oauth_verifier = $jinput->get('oauth_verifier', '', 'STRING'); //JRequest::getVar( 'oauth_verifier' , '' );
        $verify = $jinput->get('verify', '', 'NONE');  //JRequest::getVar( 'verify' , '' );
        $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
        $consumer = plgCommunityTwitter::getConsumer();

        if ($oauth_verifier && empty($verify)) {
            $consumer->config['user_token'] = $_SESSION['oauth']['oauth_token'];
            $consumer->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

            $code = $consumer->request(
                    'POST', $consumer->url('oauth/access_token', ''), array(
                'oauth_verifier' => $_REQUEST['oauth_verifier']
                    )
            );

            if ($code == 200) {
                $_SESSION['access_token'] = $consumer->extract_params($consumer->response['response']);
                unset($_SESSION['oauth']);
                $instance = JURI::getInstance();
                $url = JURI::getInstance()->toString();
                $mainframe->redirect($url . '&verify=true');
            } else {
                echo JText::_('COM_COMMUNITY_INVALID_APPLICATION');
                return;
            }
        }

        if (empty($app)) {
            echo JText::_('COM_COMMUNITY_INVALID_APPLICATION');
            return;
        }

        if ($my->id == 0) {
            echo JText::_('COM_COMMUNITY_INVALID_ACCESS');
            return;
        }

        if (!empty($denied)) {
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_OAUTH_APPLICATION_ACCESS_DENIED_WARNING'));
        }

        $oauth = JTable::getInstance('Oauth', 'CTable');
        if ($oauth->load($my->id, $app) && $verify) {

            $consumer->config['user_token'] = $_SESSION['access_token']['oauth_token'];
            $consumer->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

            /* The Twitter REST API v1 is no longer active. Please migrate to API v1.1. https://dev.twitter.com/docs/api/1.1/overview. */
            $code = $consumer->request(
                    'GET', $consumer->url('1.1/account/verify_credentials')
            );

            if ($code == 200) {
                $resp = json_decode($consumer->response['response']);
                $consumer->config['screen_name'] = $resp->screen_name;
            } else {
                /**
                 * @todo Should we display response / error of message
                 */
                echo JText::_('COM_COMMUNITY_INVALID_ACCESS');
                return;
            }
            $oauth->userid = $my->id;
            $oauth->app = $app;
            $getData = JRequest::get('get');

            try {
                $oauth->accesstoken = serialize($consumer->config);
            } catch (Exception $error) {
                $mainframe->redirect($url, $error->getMessage());
            }

            if (!empty($oauth->accesstoken)) {
                $oauth->store();
            }
            $msg = JText::_('COM_COMMUNITY_OAUTH_AUTHENTICATION_SUCCESS');
            $mainframe->redirect($url, $msg);
        }
    }

    public function remove() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $my = CFactory::getUser();
        $app = $jinput->get('app', '', 'NONE'); // JRequest::getVar( 'app' , '' );

        if (empty($app)) {
            echo JText::_('COM_COMMUNITY_INVALID_APPLICATION');
            return;
        }

        if ($my->id == 0) {
            echo JText::_('COM_COMMUNITY_INVALID_ACCESS');
            return;
        }
        $oauth = JTable::getInstance('Oauth', 'CTable');
        if (!$oauth->load($my->id, $app)) {
            $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
            $mainframe->redirect($url, JText::_('COM_COMMUNITY_OAUTH_LOAD_APPLICATION_ERROR'));
        }

        $oauth->delete();
        $url = CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id, false);
        $mainframe->redirect($url, JText::_('COM_COMMUNITY_OAUTH_DEAUTHORIZED_APPLICATION_SUCCESS'));
    }

}
