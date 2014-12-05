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

class CMailq {

    /**
     * Do a batch send
     */
    public function send($total = 100) {
        $app = JFactory::getApplication();
        $mailqModel = CFactory::getModel('mailq');
        $userModel = CFactory::getModel('user');
        $mails = $mailqModel->get($total, true);
        //$jconfig	= JFactory::getConfig();
        $mailer = JFactory::getMailer();
        $config = CFactory::getConfig();

        //$senderEmail  = $jconfig->getValue('mailfrom');
        //$senderName   = $jconfig->getValue('fromname');

        $senderEmail = $app->getCfg('mailfrom');
        $senderName = $app->getCfg('fromname');

        //$conf = JFactory::getConfig();
        //$sitename = $conf->getValue('config.sitename');

        $sitename = $app->getCfg('config.sitename');


        if (empty($mails)) {
            return;
        }

        //CFactory::load( 'helpers' , 'string' );

        foreach ($mails as $row) {
			if ( $row->email_type === 'etype_friends_invite_users' ) {
				/* for invite email */
				$raw = isset($row->params) ? $row->params : '';
				$rowParams = new CParameter ($row->params);
				$userid = JUri::getInstance($rowParams->get('actor_url'))->getVar('userid');
			}else {
				// @rule: only send emails that is valid.
				// @rule: make sure recipient is not blocked!
				$userid = $userModel->getUserFromEmail($row->recipient);
			}

            $user = CFactory::getUser($userid);

            //verify user email list settting

            $user_params = $user->getParams();
            $validate = true;
            if (!empty($row->email_type)) {
                $validate = ($user_params->get($row->email_type, $config->get($row->email_type)) == 1 ) ? true : false;
            }

            if (!$user->isBlocked() && !JString::stristr($row->recipient, 'foo.bar') && $validate) {

                $mailer->setSender(array($senderEmail, $senderName));
                $mailer->addRecipient($row->recipient);

                // Replace any occurences of custom variables within the braces scoe { }
                $row->subject = CContentHelper::injectTags($row->subject, $row->params, false);

                $mailer->setSubject($row->subject);

                $tmpl = new CTemplate();
                $raw = isset($row->params) ? $row->params : '';
                $params = new CParameter($row->params);
                $base = $config->get('htmlemail') ? 'email.html' : 'email.text';

                if ($config->get('htmlemail')) {
                    $row->body = CString::str_ireplace(array("\r\n", "\r", "\n"), '<br />', $row->body);
                    $mailer->IsHTML(true);
                } else {
                    //@rule: Some content might contain 'html' tags. Strip them out since this mail should never contain html tags.
                    $row->body = CStringHelper::escape(strip_tags($row->body));
                }
                $copyrightemail = JString::trim($config->get('copyrightemail'));

                $tmpl->set('email_type', $row->email_type);
                $tmpl->set('avatar', $user->getAvatar());
                $tmpl->set('thumbAvatar', $user->getThumbAvatar());
                $tmpl->set('name', $user->getDisplayName());
                $tmpl->set('email', $user->email);
                $tmpl->set('sitename', $sitename);
                $tmpl->set('unsubscribeLink', CRoute::getExternalURL('index.php?option=com_community&view=profile&task=email'), false);
                $tmpl->set('userid', $userid);
                $tmpl->set('copyrightemail', $copyrightemail);
                $tmpl->set('recepientemail', $row->recipient);
                $tmpl->set('content', $row->body);
                $tmpl->set('template', rtrim(JURI::root(), '/') . '/components/com_community/templates/' . $config->get('template'));
                $tmpl->set('sitename', $config->get('sitename'));

                $row->body = $tmpl->fetch($base);

                // Replace any occurences of custom variables within the braces scoe { }
                if (!empty($row->body)) {
                    $row->body = CContentHelper::injectTags($row->body, $row->params, false);
                }
                unset($tmpl);
                $mailer->setBody($row->body);
                $mailer->send();
            }

            if (!$validate) { //email is blocked by user settings
                $mailqModel->markEmailStatus($row->id, 2);
            } else {
                $mailqModel->markSent($row->id);
            }
            $mailer->ClearAllRecipients();
        }
    }

}

/**
 * Maintain classname compatibility with JomSocial 1.6 below
 */
class CMailqLibrary extends CMailq {

}
