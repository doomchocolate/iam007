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

//CFactory::load( 'libraries' , 'template' );

class CNotification {

    /**
     * Adds notification data into the mailq table
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
    static public function add($command, $actorId, $recipients, $subject, $body, $templateFile = '', $mailParams = '', $sendEmail = true, $favicon = '', $altSubject = '') {

        // Need to make sure actor is NULL, so default user will be returned
        // from getUser
        if (empty($actorId)) {
            $actorId = null;
        }

        $mailq = CFactory::getModel('Mailq');
        $actor = CFactory::getUser($actorId);
        $config = CFactory::getConfig();

        if (!is_array($recipients)) {
            $recipientsArray = array();
            $recipientsArray[] = $recipients;
        } else {
            $recipientsArray = $recipients;
        }
        $contents = '';

        // If template file is given, we shall extract the email from the template file.

        if (!empty($templateFile)) {
            $tmpl = new CTemplate();
            preg_match('/email/i', $templateFile, $matches);

            if (empty($matches)) {
                $templateFile = 'email.' . $templateFile;
                $templateFile .= $config->get('htmlemail') ? '.html' : '.text';
            }

            if (is_object($mailParams)) {
                $dataArray = $mailParams->toArray();

                foreach ($dataArray as $key => $value) {
                    $tmpl->set($key, $value);
                }
            } elseif (is_array($mailParams)) {
                foreach ($mailParams as $key => $val)
                    $tmpl->set($key, $val);
            }
            $contents = $tmpl->fetch($templateFile);
        } else {
            $contents = $body;
        }

        /* In case contents be empty ( template file missed ) than we use body content */
        if (trim($contents == ''))
            $contents = $body;

        $cmdData = explode('_', $command);

        //check and add some default tags to params
        if (is_object($mailParams)) {
            if (is_null($mailParams->get('actor', null))) {
                $mailParams->set('actor', $actor->getDisplayName());
            }
            if (is_null($mailParams->get('actor_url', null))) {
                $mailParams->set('actor_url', 'index.php?option=com_community&view=profile&userid=' . $actor->id);
            }
        }

        $notificationTypes = new CNotificationTypes();
        if (empty($recipientsArray)) {
            return;
        }
        //prevent sending duplicate notification to the same users
        $recipientsArray = array_unique($recipientsArray);
        // check for privacy setting for each user
        foreach ($recipientsArray as $recipient) {
            //we process the receipient emails address differently from the receipient id.
            $recipientEmail = '';
            $recipientName = '';
            $sendIt = false;

            if (CValidateHelper::email($recipient)) {
                // Check if the recipient email same with actor email
                $self = self::filterActor($actorId, $recipient);

                // If same, skip to next email
                if ($self) {
                    continue;
                }

                $recipientName = '';
                $sendIt = true;
                $recipientEmail = $recipient;
            } else {
                $userTo = CFactory::getUser($recipient);

                // Check if the recipient email same with actor email
                $self = self::filterActor($actorId, $userTo->email);

                // If same, skip to next email
                if ($self) {
                    continue;
                }

                $params = $userTo->getParams();
                $recipientName = $userTo->getDisplayName();
                $recipientEmail = $userTo->email;
                $sendIt = false;

                if (isset($cmdData[1])) {
                    switch ($cmdData[0]) {
                        case 'inbox':
                        case 'photos':
                        case 'groups':
                            $sendIt = $params->get('etype_groups_invite');
                            break;
                        case 'events':
                        case 'friends':
                        case 'profile':
//							$sendIt	= $params->get('notifyEmailSystem');
//							break;
                        case 'system':
                            $sendIt = $params->get('etype_system_messaging');
                            break;
                        default:
                            $sendIt = true;
                            break;
                    }
                }
                //add global notification
                $notifType = $notificationTypes->getType('', $command);

                if (!is_object($notifType)) {
                    $type = 0;
                } else {
                    $type = $notifType->requiredAction ? '1' : '0';
                }

                $model = CFactory::getModel('Notification');

                if($altSubject != ''){
                    $model->add($actorId, $recipient, $altSubject, CNotificationTypesHelper::convertNotifId($command), $type, $mailParams);
                }else{
                    $model->add($actorId, $recipient, $subject, CNotificationTypesHelper::convertNotifId($command), $type, $mailParams);
                }
            }

            if ($sendIt) {
                // Porcess the message and title
                $search = array('{actor}', '{target}');
                $replace = array($actor->getDisplayName(), $recipientName);

                $emailSubject = CString::str_ireplace($search, $replace, $subject);
                $body = CString::str_ireplace($search, $replace, $contents);

                //inject params value to subject

                $params = ( is_object($mailParams) && method_exists($mailParams, 'toString') ) ? $mailParams->toString() : '';

                //inject params if this is not user tagging
                if($command != 'users_tagged'){
                    $emailSubject = CContentHelper::injectTags($emailSubject, $params, false);
                }

                $mailq->add($recipientEmail, $emailSubject, $body, $templateFile, $mailParams, 0, CNotificationTypesHelper::convertEmailId($command));
            }
        }
    }

    /**
     * 	Adds notification data into the mailq table
     * */
    static public function addMultiple($command, $actorId, $recipients, $subject, $body, $templateFile = '', $mailParams = '', $sendEmail = true, $favicon = '') {
        //CFactory::load( 'helpers' , 'validate' );
        // Need to make sure actor is NULL, so default user will be returned
        // from getUser
        if (empty($actorId)) {
            $actorId = null;
        }

        $mailq = CFactory::getModel('Mailq');
        $actor = CFactory::getUser($actorId);
        $config = CFactory::getConfig();

        if (!is_array($recipients)) {
            $recipientsArray = array();
            $recipientsArray[] = $recipients;
        } else {
            $recipientsArray = $recipients;
        }
        $contents = '';

        // If template file is given, we shall extract the email from the template file.
        if (!empty($templateFile)) {
            $tmpl = new CTemplate();
            preg_match('/email/i', $templateFile, $matches);

            if (empty($matches)) {
                $templateFile = 'email.' . $templateFile;
                $templateFile .= $config->get('htmlemail') ? '.html' : '.text';
            }

            if (is_object($mailParams)) {
                $dataArray = $mailParams->toArray();

                foreach ($dataArray as $key => $value) {
                    $tmpl->set($key, $value);
                }
            } elseif (is_array($mailParams)) {
                foreach ($mailParams as $key => $val)
                    $tmpl->set($key, $val);
            }
            $contents = $tmpl->fetch($templateFile);
        } else {
            $contents = $body;
        }

        $cmdData = explode('_', $command);

        //check and add some default tags to params
        if (is_object($mailParams)) {
            if (is_null($mailParams->get('actor', null))) {
                $mailParams->set('actor', $actor->getDisplayName());
            }
            if (is_null($mailParams->get('actor_url', null))) {
                $mailParams->set('actor_url', 'index.php?option=com_community&view=profile&userid=' . $actor->id);
            }
        }



        $notificationTypes = new CNotificationTypes();
        if (empty($recipientsArray)) {
            return;
        }
        //prevent sending duplicate notification to the same users
        $recipientsArray = array_unique($recipientsArray);
        // check for privacy setting for each user
        foreach ($recipientsArray as $recipient) {
            //we process the receipient emails address differently from the receipient id.
            $recipientEmail = '';
            $recipientName = '';
            $sendIt = false;

            if (CValidateHelper::email($recipient)) {
                // Check if the recipient email same with actor email
                $self = self::filterActor($actorId, $recipient);

                // If same, skip to next email
                if ($self) {
                    continue;
                }

                $recipientName = '';
                $sendIt = true;
                $recipientEmail = $recipient;
            } else {
                $userTo = CFactory::getUser($recipient);

                // Check if the recipient email same with actor email
                $self = self::filterActor($actorId, $userTo->email);

                // If same, skip to next email
                if ($self) {
                    continue;
                }

                $params = $userTo->getParams();
                $recipientName = $userTo->getDisplayName();
                $recipientEmail = $userTo->email;
                $sendIt = false;

                if (isset($cmdData[1])) {
                    switch ($cmdData[0]) {
                        case 'inbox':
                        case 'photos':
                        case 'groups':
                        case 'events':
                        case 'friends':
                        case 'profile':
//							$sendIt	= $params->get('notifyEmailSystem');
//							break;
                        case 'system':
                        default:
                            $sendIt = true;
                            break;
                    }
                }
                //add global notification
                $notifType = $notificationTypes->getType('', $command);
                $type = $notifType->requiredAction ? '1' : '0';
                $model = CFactory::getModel('Notification');
                $model->add($actorId, $recipient, $subject, CNotificationTypesHelper::convertNotifId($command), $type, $mailParams);
            }

            if ($sendIt) {
                // Porcess the message and title
                $search = array('{actor}', '{target}');
                $replace = array($actor->getDisplayName(), $recipientName);

                $emailSubject = CString::str_ireplace($search, $replace, $subject);
                $body = CString::str_ireplace($search, $replace, $contents);

                //inject params value to subject

                $params = ( is_object($mailParams) && method_exists($mailParams, 'toString') ) ? $mailParams->toString() : '';
                $emailSubject = CContentHelper::injectTags($emailSubject, $params, false);

                $mailq->addMultiple($recipientEmail, $emailSubject, $body, $templateFile, $mailParams, 0, CNotificationTypesHelper::convertEmailId($command));
            }
        }
        /* have done adding multiple than now do send */
        $mailq->send();
    }

    /**
     * Return notification send to the given user
     */
    public function get($id) {
        $mailqModel = CFactory::getModel('mailq');
        $mailers = $mailqModel->get();
    }

    /**
     * Filter actor from send notification email to self
     * If the actor email and the recipient email is same return TRUE
     */
    static public function filterActor($actorId, $recipientEmail) {
        $actor = CFactory::getUser($actorId);
        return ( $actor->email == $recipientEmail ) ? true : false;
    }

}

/**
 * Maintain classname compatibility with JomSocial 1.6 below
 */
class CNotificationLibrary extends CNotification {

}
