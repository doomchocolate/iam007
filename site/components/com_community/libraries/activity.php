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
if (!class_exists('CActivity')) {

    /**
     * Activity object class
     */
    class CActivity extends JObject {

        /**
         * Activity id
         * @var int
         */
        public $id = null;

        /**
         * Actor id
         * @var int 
         */
        public $actor = null;

        /**
         * Target id
         * @var int
         */
        public $target = null;

        /**
         * Activity' title
         * @var string
         */
        public $title = null;

        /**
         * Activity' content
         * @var string
         */
        public $content = null;

        /**
         * Which app created this activity
         * @var string
         */
        public $app = null;

        /**
         * String
         * @link http://activitystrea.ms/registry/verbs/
         * @var type 
         */
        public $verb = null;

        /**
         * Content id
         * @var  
         */
        public $cid = null;
        public $groupid = null;
        public $eventid = null;
        public $group_access = null;
        public $event_access = null;
        public $created = null;

        /**
         * Access permission of activity
         * @var int 
         */
        public $access = null;
        public $params = null;
        public $points = null;
        public $archived = null;
        public $location = null;
        public $latitude = null;
        public $longitude = null;
        public $comment_id = null;
        public $comment_type = null;
        public $like_id = null;
        public $like_type = null;

        /**
         * Array of actors in JSON format
         * @var string 
         */
        public $actors = null;

        /**
         * Activity parameters object
         * @var JRegistry
         */
        protected $_params = null;

        /**
         * Construct function
         */
        public function __construct($properties = null) {
            if (is_object($properties)) {
                $properties = get_object_vars($properties);
            }
            /**
             * @todo Put our default properties init here
             */
            $this->def('actor', CFactory::getUser()->id);
            $this->def('points', 1);
            $this->def('verb', 'post');
            $this->def('comment_id', CActivities::COMMENT_SELF);
            $this->def('like_id', CActivities::LIKE_SELF);
            /**
             * @todo
             * Look like logic !isset is not valid
             */
            // Update access for activity based on the user's profile privacy
            // since 2.4.2 if access is provided, dont use the default
            if (!empty($this->actor) && $this->actor != 0 && !isset($this->access)) {
                $user = CFactory::getUser($this->actor);
                $userParams = $user->getParams();
                $profileAccess = $userParams->get('privacyProfileView');

                // Only overwrite access if the user global profile privacy is higher
                // BUT, if access is defined as PRIVACY_FORCE_PUBLIC, do not modify it
                if (($this->get('access', 0) != PRIVACY_FORCE_PUBLIC) && ($profileAccess > $this->get('access', 0))) {
                    $this->set('access', $profileAccess);
                }
            }

            /* And now bind with input properties */
            parent::__construct($properties);
            /* Build internal JRegistry to work with params */
            $this->_params = new JRegistry();
            $this->_params->loadString($this->get('params', '{}'));
        }

        /**
         *
         * @param type $property
         * @param type $value
         * @return type
         */
        public function setParams($property, $value) {
            return $this->_params->set($property, $value);
        }

        /**
         *
         * @param type $property
         * @param type $default
         * @return type
         */
        public function getParams($property, $default = null) {
            return $this->_params->get($property, $default);
        }

        /**
         *
         * @param type $newPrivacy
         * @return int
         */
        public function setPrivacy($newPrivacy) {
            return $this->set('access', $newPrivacy);
        }

        /**
         *
         * @return string
         */
        public function getTitle() {
            return CActivities::format($this->get('title'));
        }

        /**
         * Return location data
         * @link https://developers.google.com/maps/documentation/geocoding/#JSON Google GeoCoding JSON
         * @param string $type
         * @return boolean
         */
        public function getLocation($type = 'formatted_address') {
            if ($this->get('location')) {
                return $this->get('location');
            } else {
                if ($this->get('latitude') != '255.000000' & $this->get('longitude') != '255.000000') {
                    $googleService = new CServiceGoogle();
                    $address = $googleService->getAddressFromCoords($this->get('latitude'), $this->get('longitude'));

                    return $address->$type;
                }
            }

            return false;
        }

        /**
         * Return CUser singleton instance of this actor
         * @return type
         */
        public function getActor() {
            return CFactory::getUser($this->get('actor'));
        }

        /**
         * Return CUser singleton instance of this target
         * @return type
         */
        public function getTarget() {
            return CFactory::getUser($this->get('target'));
        }

        /**
         *
         * @return type
         */
        public function getCreateTimeFormatted() {
            $config = CFactory::getConfig();
            $date = JFactory::getDate($this->get('created'));
            if ($config->get('activitydateformat') == "lapse") {
                $createdTime = CTimeHelper::timeLapse($date);
            } else {
                $createdTime = $date->format($config->get('profileDateFormat'));
            }
            return $createdTime;
        }

        /**
         * 
         * @param int $userId
         * @return int
         */
        public function getPermission($userId = null) {
            $me = CFactory::getUser($userId);
            return $me->authorise('community.permission', 'activities.stream.' . $this->get('actor'), $this);
        }

        /**
         * Build and return HTML of activity to render
         * @return string
         */
        public function getHtml() {
            $html = '';
            return $html;
        }

        /**
         * Save this activity into database
         * @return \CActivity
         */
        public function save() {
            /* Convert internal params from JRegistry into string to store into database */
            $this->set('params', $this->_params->toString());
            $table = JTable::getInstance('Activity', 'CTable');
            $table->bind($this->getProperties());
            if ($table->store()) {

                /* Update comment id, if we comment on the stream itself */
                if ($this->comment_id == CActivities::COMMENT_SELF) {
                    $table->comment_id = $table->id;
                }
                /* Update comment id, if we like on the stream itself */
                if ($this->like_id == CActivities::LIKE_SELF) {
                    $table->like_id = $table->id;
                }
                if ($this->comment_id == CActivities::COMMENT_SELF || $this->comment_id == CActivities::LIKE_SELF) {
                    $table->store($table);
                }
                /* Set back properties */
                $this->setProperties($table->getProperties());
                return true;
            }
            return false;
        }

    }

}
