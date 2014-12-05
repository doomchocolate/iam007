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
if (!class_exists('CApiActivities')) {

    /**
     * Activities' APIs
     */
    class CApiActivities {

        public static function get($activityId) {
            $table = JTable::getInstance('Activity', 'CTable');
            if ($table->load($activityId)) {
                return new CActivity($table->getProperties());
            }
            return false;
        }

        public static function removeUserTag($id , $userstring, $type, $replaceWith = ""){
            if($type == 'post'){
                $activity = CApiActivities::get($id);
                $userstring = $activity->title = str_replace($userstring, $replaceWith, $activity->title);
                $activity->save();
            }else{
                $wall = JTable::getInstance('Wall', 'CTable');
                $wall->load($id);
                $userstring = $wall->comment = str_replace($userstring, $replaceWith, $wall->comment);
                $wall->store();
            }

            return $userstring;
        }

        /**
         * Add new activity into stream
         * @param array|object $data
         * @return CActivity|boolean
         */
        public static function add($data) {
            $activity = new CActivity($data);

            /**
             * @todo We'll move all event trigger into this class not in table class or anywhere
             * @todo Anything else we want to include when posting please put here
             */
            /* Event trigger */
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $params = array();
            $params[] = &$activity;

            /* We do raise event here that will allow user change $activity properties before save it */
            $appsLib->triggerEvent('onBeforeActivitySave', $params);

            if ($activity->get('cmd')) {
                /* Userpoints */
                $userPointModel = CFactory::getModel('Userpoints');
                $point = $userPointModel->getPointData($activity->get('cmd'));
                if ($point) {
                    /**
                     * @todo Need clearly about this !
                     */
                    if (!$point->published) {
                        return;
                    }
                }
            }

            if ($activity->save()) {
                $params = array();
                $params[] = &$activity;
                $appsLib->triggerEvent('onAfterActivitySave', $params);
                return $activity;
            }
            return false;
        }

        /**
         * Remove an activity by ID
         * @param type $activityId
         * @return type
         */
        public static function remove($activityId) {
            $model = CFactory::getModel('activities');
            return $model->removeActivityById($activityId);
        }

        /**
         *
         * @param int $activityId
         * @param int $newPrivacy
         * @return boolean
         */
        public static function updatePrivacy($activityId, $newPrivacy) {
            /**
             * @todo Check privacy update permission before process
             * @todo Raise events
             */
            $activity = self::get($activityId);
            if ($activity) {
                $activity->setPrivacy($newPrivacy);
                return $activity->save();
            }
            return false;
        }

        /**
         * Function to share status
         * @param [Int] $activityId [description]
         * @param [JSON] $attachment [description]
         */
        public static function addShare($activityId, $attachment) {

            $my = CFactory::getUser();
            $params = new CParameter('');
            $attachment = json_decode($attachment);

            $act = new stdClass();
            $act->cmd = 'profile.status.share';
            $act->actor = $my->id;
            $act->target = $my->id;
            $act->title = $attachment->msg;
            $act->content = '';
            $act->app = 'profile.status.share';
            $act->cid = $my->id;
            $act->comment_id = CActivities::COMMENT_SELF;
            $act->comment_type = 'profile.status.share';
            $act->like_id = CActivities::LIKE_SELF;
            $act->like_type = 'profile.status.share';
            $act->access = $attachment->privacy;

            $params->set('activityId', $activityId);

            $act->params = $params->toString();

            return self::add($act);
        }

    }

}