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


// Core file is required since we need to use CFactory
require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

// check if FB library already available or not
if (!class_exists('Facebook')) {
    // Need to include Facebook's PHP API library so we can utilize them.
    require_once( JPATH_ROOT . '/components/com_community/libraries/facebook/facebook.php' );
}

/**
 * Wrapper class for Facebook's API.
 * */
class CFacebook {

    public $facebook = null;

    /**
     * 	Fields to map from Facebook and the values are the default field codes in Jomsocial.
     * */
    private $_fields = array(
        'sex' => 'FIELD_GENDER',
        'birthday_date' => 'FIELD_BIRTHDATE',
        'hometown_location' => array('state' => 'FIELD_STATE', 'city' => 'FIELD_CITY', 'country' => 'FIELD_COUNTRY'),
        'about_me' => 'FIELD_ABOUTME',
        'education' => 'FIELD_COLLEGE',
        'website' => 'FIELD_WEBSITE'
    );

    /**
     * Deprecated since 1.8.x
     * */
    public $lib = null;
    public $userId = null;

    /**
     * 	Initial method
     * */
    public function __construct($requireLogin = false) {
        $config = CFactory::getConfig();
        $key = $config->get('fbconnectkey');
        $secret = $config->get('fbconnectsecret');

        $this->facebook = new Facebook(array(
            'appId' => $config->get('fbconnectkey', ''),
            'secret' => $config->get('fbconnectsecret')
        ));
    }

    /**
     * 	Return user's data that is fetched from Facebook
     *
     * 	@params $fields	Array of fields available.
     * */
    public function getUserInfo($fields, $uid) {
        $fql = 'select ' . implode(',', $fields) . ' from user where uid=' . $uid;

        $param = array('method' => 'fql.query',
            'query' => $fql,
            'callback' => ''
        );

        $result = $this->facebook->api($param);

        if (isset($result['pic_square']) && empty($result['pic_square'])) {
            $result['pic_square'] = rtrim(JURI::root(), '/') . '/' . DEFAULT_USER_THUMB;
        }
        $result = isset($result[0]) ? $result[0] : false;

        return $result;
    }

    /**
     * get User Id
     * */
    public function getUserId() {
        $user = $this->getUser();
        return $user['id'];
    }

    public function mapAvatar($avatarUrl = '', $joomlaUserId, $addWaterMark) {
        $image = '';

        if (!empty($avatarUrl)) {
            // Make sure user is properly added into the database table first
            $user = CFactory::getUser($joomlaUserId);
            $fbUser = $this->getUser();

            // Store image on a temporary folder.
            $tmpPath = JPATH_ROOT . '/images/originalphotos/facebook_connect_' . $fbUser;

            // Need to extract the non-https version since it will cause
            // certificate issue
            $avatarUrl = str_replace('https://', 'http://', $avatarUrl);

            $source = CRemoteHelper::getContent($avatarUrl, true);
            list( $headers, $source ) = explode("\r\n\r\n", $source, 2);
            JFile::write($tmpPath, $source);

            // @todo: configurable width?
            $imageMaxWidth = 160;

            // Get a hash for the file name.
            $fileName = JApplication::getHash($fbUser . time());
            $hashFileName = JString::substr($fileName, 0, 24);

            $extension = JString::substr($avatarUrl, JString::strrpos($avatarUrl, '.'));

            $type = 'image/jpg';

            if ($extension == '.png') {
                $type = 'image/png';
            }

            if ($extension == '.gif') {
                $type = 'image/gif';
            }

            //@todo: configurable path for avatar storage?
            $config = CFactory::getConfig();
            $storage = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar';
            $storageImage = $storage . '/' . $hashFileName . $extension;
            $storageThumbnail = $storage . '/thumb_' . $hashFileName . $extension;
            $image = $config->getString('imagefolder') . '/avatar/' . $hashFileName . $extension;
            $thumbnail = $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . $extension;

            $userModel = CFactory::getModel('user');

            // Only resize when the width exceeds the max.
            CImageHelper::resizeProportional($tmpPath, $storageImage, $type, $imageMaxWidth);
            CImageHelper::createThumb($tmpPath, $storageThumbnail, $type);

            if ($addWaterMark) {
                // Get the width and height so we can calculate where to place the watermark.
                list( $watermarkWidth, $watermarkHeight ) = getimagesize(FACEBOOK_FAVICON);
                list( $imageWidth, $imageHeight ) = getimagesize($storageImage);
                list( $thumbWidth, $thumbHeight ) = getimagesize($storageThumbnail);

                CImageHelper::addWatermark($storageImage, $storageImage, $type, FACEBOOK_FAVICON, ( $imageWidth - $watermarkWidth), ( $imageHeight - $watermarkHeight));
                CImageHelper::addWatermark($storageThumbnail, $storageThumbnail, $type, FACEBOOK_FAVICON, ( $thumbWidth - $watermarkWidth), ( $thumbHeight - $watermarkHeight));
            }

            // Update the CUser object with the correct avatar.
            $user->set('_thumb', $thumbnail);
            $user->set('_avatar', $image);

            // @rule: once user changes their profile picture, storage method should always be file.
            $user->set('_storage', 'file');

            $userModel->setImage($joomlaUserId, $image, 'avatar');
            $userModel->setImage($joomlaUserId, $thumbnail, 'thumb');

            $user->save();
        }
    }

    /**
     * Maps a user profile with JomSocial's default custom values
     *
     * 	@param	Array	User values
     * */
    public function mapProfile($values, $userId) {
        $profileModel = CFactory::getModel('Profile');

        foreach ($this->_fields as $field => $fieldCodes) {
            // Test if value really exists and it isn't empty.
            if (isset($values[$field]) && !empty($values[$field])) {
                switch ($field) {
                    case 'birthday_date':
                        $date = JFactory::getDate($values[$field]);

                        $profileModel->updateUserData($fieldCodes, $userId, $date->toSql());

                        break;
                    case 'sex':
                        $gender = ucfirst($values[$field]);

                        if (!empty($gender)) {
                            $profileModel->updateUserData($fieldCodes, $userId, $gender);
                        }
                        break;
                    case 'education':

                        $education = end($values['education']);

                        if ($education['type'] == 'College') {
                            if (isset($education['school']))
                                $name = $education['school']['name'];
                            if (isset($education['year']['name']))
                                $year = $education['year']['name'];

                            if (!empty($name)) {
                                $profileModel->updateUserData($fieldCodes, $userId, $name);
                            }
                            if (!empty($year)) {
                                $profileModel->updateUserData('FIELD_GRADUATION', $userId, $year);
                            }
                        }

                        break;
                    default:
                        if (is_array($fieldCodes)) {
                            // Facebook library returns an array of values for certain fields so we need to manipulate them differently.
                            foreach ($fieldCodes as $fieldData => $fieldCode) {
                                if (isset($values[$field][$fieldData])) {
                                    $profileModel->updateUserData($fieldCode, $userId, $values[$field][$fieldData]);
                                }
                            }
                        } else {
                            if (!empty($values[$field])) {
                                $profileModel->updateUserData($fieldCodes, $userId, $values[$field]);
                            }
                        }
                        break;
                }
            }
        }
        return false;
    }

    /**
     * Posts a status into user's facebook stream
     *
     * 	@param	$status	String	Message to be posted to Facebook
     * */
    public function postStatus($message) {
        $user = $this->facebook->getUser();

        try {
            $statusUpdate = $this->facebook->api('/me/feed', 'post', array('message' => $message, 'cb' => ''));

            if (!empty($statusUpdate)) {
                return true;
            }
            return false;
        } catch (FacebookApiException $e) {
            return false;
        }
    }

    /**
     * Maps a user status with JomSocial's user status
     *
     * 	@param	Array	User values
     * */
    public function mapStatus($userId) {

        $result = $this->facebook->api('/me/statuses');
        $status = isset($result['data'][0]) ? $result['data'][0] : '';

        if (empty($status)) {
            return;
        }

        $connectModel = CFactory::getModel('Connect');
        $status = $status['message'];
        $rawStatus = $status;

        // @rule: Do not strip html tags but escape them.
        $status = CStringHelper::escape($status);

        // @rule: Autolink hyperlinks
        $status = CLinkGeneratorHelper::replaceURL($status);

        // @rule: Autolink to users profile when message contains @username
        $status = CLinkGeneratorHelper::replaceAliasURL($status);

        // Reload $my from CUser so we can use some of the methods there.
        $my = CFactory::getUser($userId);
        $params = $my->getParams();

        // @rule: For existing statuses, do not set them.
        if ($connectModel->statusExists($status, $userId)) {
            return;
        }


        $act = new stdClass();
        $act->cmd = 'profile.status.update';
        $act->actor = $userId;
        $act->target = $userId;
        $act->title = $status;
        $act->content = '';
        $act->app = 'profile';
        $act->cid = $userId;
        $act->access = $params->get('privacyProfileView');

        $act->comment_id = CActivities::COMMENT_SELF;
        $act->comment_type = 'profile.status';
        $act->like_id = CActivities::LIKE_SELF;
        $act->like_type = 'profile.status';

        CActivityStream::add($act);

        //add user points
        CUserPoints::assignPoint('profile.status.update');

        // Update status from facebook.
        $my->setStatus($rawStatus);
    }

    public function getUser() {
        if (Facebook::VERSION == '2.0.3') {
            $session = $this->facebook->getSession();

            if (!$session) {
                return false;
            }
        }
        try {
            $user = $this->facebook->getUser();
        } catch (FacebookApiException $exception) {
            return false;
        }

        return $user;
    }

    /**
     * Gets the html content of the Facebook login
     *
     * @return String the html data
     */
    public function getLoginHTML() {
        JFactory::getLanguage()->load('com_community');

        $config = CFactory::getConfig();

        $tmpl = new CTemplate();
        $tmpl->set('config', $config);

        return $tmpl->fetch('facebook.button');
    }

}