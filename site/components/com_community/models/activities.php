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

require_once ( JPATH_ROOT . '/components/com_community/models/models.php');
//CFactory::load( 'tables' , 'activity' );

/**
 *
 */
class CommunityModelActivities extends JCCModel {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Return an object with a single activity item
     */
    public function getActivity($activityId) {
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($activityId);
        return $act;
    }

    /**
     * Retrieves the activity content for specific activity
     * @deprecated since 2.2
     * @return string
     * */
    public function getActivityContent($activityId) {
        $act = $this->getActivity($activityId);
        return $act->content;
    }

    /**
     * Retrieves the activity stream for specific activity
     * @deprecated since 2.2
     * */
    public function getActivityStream($activityId) {
        return $this->getActivity($activityId);
    }

    /**
     * Add new data to the stream
     * @deprecated since 2.2
     */
    public function add($actor, $target, $title, $content, $appname = '', $cid = 0, $params = '', $points = 1, $access = 0) {
        jimport('joomla.utilities.date');

        $table = JTable::getInstance('Activity', 'CTable');
        $table->actor = $actor;
        $table->target = $target;
        $table->title = $title;
        $table->content = $content;
        $table->app = $appname;
        $table->cid = $cid;
        $table->points = $points;
        $table->access = $access;
        $table->location = '';
        $table->params = $params;

        return $table->store();
    }

    /**
     * For photo upload, we should delete all aggregated photo upload activity,
     * instead of just 1 photo uplaod activity
     */
    public function hide($userId, $activityId) {
        $db = $this->getDBO();

        // 1st we compare if the activity stream author match the userId. If yes,
        // archive the record. if not, insert into hide table.
        $activity = $this->getActivityStream($activityId);

        if (!empty($activity)) {
            $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_activities');
            $query .= ' WHERE ' . $db->quoteName('app') . ' = ' . $db->Quote($activity->app);
            $query .= ' AND ' . $db->quoteName('cid') . ' = ' . $db->Quote($activity->cid);
            $query .= ' AND ' . $db->quoteName('title') . ' = ' . $db->Quote($activity->title);
            $query .= ' AND DATEDIFF( created, ' . $db->Quote($activity->created) . ' )=0';

            $db->setQuery($query);
            $db->query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            $rows = $db->loadColumn();

            if (!empty($rows)) {
                foreach ($rows as $key => $value) {
                    $obj = new stdClass();
                    $obj->user_id = $userId;
                    $obj->activity_id = $value;
                    $db->insertObject('#__community_activities_hide', $obj);
                    if ($db->getErrorNum()) {
                        JError::raiseError(500, $db->stderr());
                    }
                }
            }
        }

        return true;
    }

    public function countActivities($userid = '', $friends = '', $afterDate = null, $maxEntries = 0, $respectPrivacy = true, $actidRange = null, $displayArchived = true, $actid = null, $groupid = null, $eventid = null, $filters = array()) {
        $db = $this->getDBO();

        $filters = array_merge(array(
            'userid' => $userid,
            'friends' => $friends,
            'afterDate' => $afterDate,
            'maxEntries' => 1, // avoid returning too many data
            'respectPrivacy' => $respectPrivacy,
            'actidRange' => $actidRange,
            'displayArchived' => $displayArchived,
            'actid' => $actid,
            'groupid' => $groupid,
            'eventid' => $eventid,
            /* Specific query format */
            'returnCount' => true), $filters);

        $sql = $this->_buildQuery($filters);
        $sql = CString::str_ireplace('a.*', ' SQL_CALC_FOUND_ROWS a.* ', $sql);

        $db->setQuery($sql);

        $db->Query();
        //echo $db->getQuery(); exit;
        $db->setQuery("SELECT FOUND_ROWS()");
        $result = $db->loadResult();

        return $result;
    }

    /**
     *
     * @param type $userid
     * @param type $friends
     * @param type $afterDate
     * @param type $maxEntries
     * @param type $respectPrivacy
     * @param type $actidRange
     * @param type $displayArchived
     * @param type $actid
     * @param type $groupid
     * @param type $eventid
     * @return array
     */
    public function getActivities($userid = '', $friends = '', $afterDate = null, $maxEntries = 20, $respectPrivacy = true, $actidRange = null, $displayArchived = true, $actid = null, $groupid = null, $eventid = null, $filters = array()) {

        /**
         * Get blocked user id
         * We do filter blocked user activities in root cause of query
         */
        $me = CFactory::getUser();
        $blockLists = $me->getBlockedUsers();
        $blockedUserIds = array();
        foreach ($blockLists as $blocklist) {
            $blockedUserIds[] = $blocklist->blocked_userid;
        }

        $db = $this->getDBO();

        /**
         * Session method be removed !
         */
        $queryOptions = array(
            'userid' => $userid,
            'friends' => $friends,
            'afterDate' => $afterDate,
            'maxEntries' => $maxEntries,
            'respectPrivacy' => $respectPrivacy,
            'actidRange' => $actidRange,
            'displayArchived' => $displayArchived,
            'actid' => $actid,
            'groupid' => $groupid,
            'eventid' => $eventid,
            'blockedUserIds' => $blockedUserIds,
            'apps' => null
        );
        /**
         * @since 3.2 A2
         * Allow input more than one filter by use array
         */
        $queryOptions = array_merge($queryOptions, $filters);

        /* Do query */
        $sql = $this->_buildQuery($queryOptions);

        $db->setQuery($sql);

        //echo $maxEntries;
        // echo $db->getQuery();
        $result = $db->loadObjectList();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        if (!empty($result)) {
            foreach ($result as $row) {
                if (CFactory::getUser($row->actor)->block) {
                    $row->content = $row->title = JText::_('COM_COMMUNITY_CENSORED');
                }
                if (($row->params != '') && (!is_object($row->params))) {
                    $params = new JRegistry;
                    $params->loadString($row->params);
                    $row->params = $params;
                }
            }
        }

        $activities = $this->_getActivitiesLikeComment($result);

        // This is probably not necessary
        unset($likesResult);
        unset($commentsResult);

        //$cache->store($activities, $cacheid,array('activities'));
        $this->_getGroups();
        return $activities;
    }

    /**
     * Build master query
     * @param  array $filters condition
     * @return string          part of query
     */
    private function _buildQuery($filters) {
        $db = $this->getDBO();
        $my = CFactory::getUser();


        $todayDate = new JDate();

        $orWhere = array();
        $andWhere = array(' 1 ');
        $onActor = '';
        //default the 1st condition here so that if the date is null, it wont give sql error.

        /* Disabled on 2.6 to take all activities including the archived one.
          if( !$displayArchived )
          {
          $andWhere[] = $db->quoteName('archived')."=0";
          }
         */
        if (!empty($filters['userid'])) {
            $orWhere[] = '(a.' . $db->quoteName('actor') . '=' . $db->Quote($filters['userid']) . ')';

            //@since 2.6, show friends activities even its not related to the current user(me-and-friends fpage)
            if ($filters['userid'] != $my->id) {
                $onActor .= ' AND ((a.' . $db->quoteName('actor') . '=' . $db->Quote($filters['userid']) . ') OR (a.' . $db->quoteName('target') . '=' . $db->Quote($filters['userid']) . '))';
            }

            //@since 2.8 also search within actors column
            $orWhere[] = '(
				(a.' . $db->quoteName('actor') . '=' . $db->Quote(0) . ') AND
				(a.' . $db->quoteName('actors') . ' LIKE \'%{"id":"' . $filters['userid'] . '"}%\')
				)';
        }

        //
        if (!empty($filters['friends']) && implode(',', $filters['friends']) != '') {
            $orWhere[] = '(a.' . $db->quoteName('actor') . ' IN (' . implode(',', $filters['friends']) . '))';
            $orWhere[] = '(a.' . $db->quoteName('target') . ' IN (' . implode(',', $filters['friends']) . '))';
            //actor are friends, clear the on Actor condition
            $onActor .= '';
        }

        if (!empty($filters['userid']))
            $orWhere[] = '(a.' . $db->quoteName('target') . '=' . $db->Quote($filters['userid']) . ')';

        if (!empty($afterDate))
            $andWhere[] = '(a.' . $db->quoteName('created') . ' between ' . $db->Quote($afterDate->toSql()) . ' and ' . $db->Quote($todayDate->toSql()) . ')';

        // Make sure it is an integer (singed and unsigned)
        $filters['actidRange'] = intval($filters['actidRange']);

        // If idrange is positive, return items older than the given id
        if (!is_null($filters['actidRange']) && $filters['actidRange'] > 0) {
            $exclusionQuery = ' a.id < ' . $filters['actidRange'] . ' ';
            $andWhere[] = $exclusionQuery;
        }

        // // If idrange is negative, return items older than the given id
        if (!is_null($filters['actidRange']) && $filters['actidRange'] < 0) {
            //$exclusionQuery	= ' a.id = '. abs($filters['actidRange']).' ';
            $exclusionQuery = ' a.id > ' . abs($filters['actidRange']) . ' ';
            $andWhere[] = $exclusionQuery;
        }

        if (!is_null($filters['actid']) && $filters['actid'] > 0) {
            $andWhere[] = ' ( a.id = ' . (int) $filters['actid'] . ' ) ';
        }

        //if this is own profile, do not display whatever post i ever posted to another user
        if (!empty($filters['userid']) && empty($filters['actid'])) {
            $andWhere[] = ' (( a.' . $db->quoteName('actor') . ' <> ' . $db->Quote($filters['userid']) . ' AND a.target <> a.actor) OR a.target = ' . $filters['userid'] . ' OR a.target = 0 )';
        }

        // Limit to a particular group
        if (!is_null($filters['groupid']) && $filters['groupid'] > 0) {
            $andWhere[] = ' ( a.groupid = ' . (int) $filters['groupid'] . ' AND a.comment_type != "groups.create") ';
        }

        // Limit to a particular event
        if (!is_null($filters['eventid']) && $filters['eventid'] > 0) {
            $andWhere[] = ' ( a.eventid = ' . (int) $filters['eventid'] . ' AND a.comment_type != "events") ';
        }

        /**
         * Filter blocked user
         */
        if (isset($filters['blockedUserIds'])) {
            if (count($filters['blockedUserIds']) > 0) {
                $andWhere[] = ' ( a.actor NOT IN (' . implode(',', $filters['blockedUserIds']) . ') )';
                /**
                 * @todo Improve this query
                 */
                foreach ($filters['blockedUserIds'] as $blockedUserId) {
                    $andWhere[] = ' ( a.actors NOT LIKE ' . $db->quote('%' . $blockedUserId . '%') . ')';
                }
            }
        }

        // Filter by group permission
        // Admin can see all groups
        if (!COwnerHelper::isCommunityAdmin($my->id)) {
            $groupIds = empty($my->_groups) ? "''" : $my->_groups;
            if (!empty($groupIds)) {
                $andWhere[] = '( (a.' . $db->quoteName('group_access') . '=' . $db->Quote(0) . ')'
                        . '  OR '
                        . '  (a.' . $db->quoteName('groupid') . ' IN (' . $groupIds . ' ) )'
                        . ' OR (a.' . $db->quoteName('groupid') . '=' . $db->Quote(0) . '))';
            } else {
                // Only show public groups
                $andWhere[] = ' (a.' . $db->quoteName('group_access') . '=' . $db->Quote(0) . ')';
            }
        }

        // Filter by event permission
        // Admin can see everything
        if (!COwnerHelper::isCommunityAdmin($my->id)) {
            $eventIds = empty($my->_events) ? "" : $my->_events;
            if (!empty($eventIds)) {
                $andWhere[] = '( (a.' . $db->quoteName('event_access') . '=' . $db->Quote(0) . ')'
                        . '  OR '
                        . '  (a.' . $db->quoteName('eventid') . ' IN (' . $eventIds . ' ) ) '
                        . ' OR (a.' . $db->quoteName('eventid') . '=' . $db->Quote(0) . ') )';
            }

            $event = JTable::getInstance('Event', 'CTable');
            $event->load((int) $filters['eventid']);

            if ($event->isMember($my->id)) {
                $andWhere[] = ' ( (a.' . $db->quoteName('event_access') . '=' . $db->Quote(1) . ') '
                        . ' OR (a.' . $db->quoteName('event_access') . '=' . $db->Quote(0) . ') )';
            } else {
                // Only show public events
                $andWhere[] = ' (a.' . $db->quoteName('event_access') . '=' . $db->Quote(0) . ')';
            }
        }

        if ($filters['respectPrivacy']) {
            // Add friends limits, but admin should be able to see all
            // @todo: should use global admin code check instead
            if ($my->id == 0) {
                // for guest, it is enough to just test access <= 0
                //$andWhere[] = "(a.`access` <= 10)";
                $andWhere[] = "(a." . $db->quoteName('access') . " <= 10)";
            } elseif (!COwnerHelper::isCommunityAdmin($my->id)) {
                $orWherePrivacy = array();
                $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = 0) ' . $onActor . ')';
                $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = 10) ' . $onActor . ')';
                $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = 20) AND ( ' . $db->Quote($my->id) . ' != 0) ' . $onActor . ')';
                if ($my->id != 0) {
                    $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = ' . $db->Quote(40) . ') AND (a.' . $db->quoteName('actor') . ' = ' . $db->Quote($my->id) . ') ' . $onActor . ')';
                    $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = ' . $db->Quote(30) . ') AND ((a.' . $db->quoteName('actor') . 'IN (SELECT c.' . $db->quoteName('connect_to')
                            . ' FROM ' . $db->quoteName('#__community_connection') . ' as c'
                            . ' WHERE c.' . $db->quoteName('connect_from') . ' = ' . $db->Quote($my->id)
                            . ' AND c.' . $db->quoteName('status') . ' = ' . $db->Quote(1) . ' ) ) OR (a.' . $db->quoteName('actor') . ' = ' . $db->Quote($my->id) . ') )' . $onActor . ' )';
                }
                $OrPrivacy = implode(' OR ', $orWherePrivacy);
                // If groupid is specified, no need to check the privacy
                // really
                $andWhere[] = "(a." . $db->quoteName('groupid') . " OR (" . $OrPrivacy . "))";
            }
        }

        /**
         * @link https://trello.com/c/cL314YcO/72-jom-2282-private-group-events-visibility-1
         * We get list of public groups & joined groups than make sure activity groupid in this list
         */
        $groups = $this->_getGroups();
        if ($groups && is_array($groups) && count($groups) > 0 && !COwnerHelper::isCommunityAdmin($my->id)) {
            $andWhere[] = ' ( a.' . $db->quoteName('groupid') . ' IN ( ' . implode(',', $groups) . ' )' /* in valid group */
                    . ' OR ( a.' . $db->quoteName('groupid') . ' IS NULL )'
                    . ' OR ( a.' . $db->quoteName('groupid') . ' = 0 ) )'; /* or not in any group */
        }

        /**
         * Filter unpublished video activities
         */
        $unpublishedVideo = $this->_getUnpublishedVideos();
        if ($unpublishedVideo && is_array($unpublishedVideo) && count($unpublishedVideo) > 0 && !COwnerHelper::isCommunityAdmin($my->id)) {
            $andWhere[] = ' ( a.' . $db->quoteName('app') . ' <> ' . $db->quote('videos')
                    . ' OR ( ' . ' ( a.' . $db->quoteName('app') . ' = ' . $db->quote('videos') . ' AND a.' . $db->quoteName('cid') . ' NOT IN ( '. implode(',', $unpublishedVideo) . ' ) ) ) )';
        }

        if (isset($filters['apps']) && is_array($filters['apps'])) {
            /**
             * @link http://stackoverflow.com/questions/4172195/mysql-like-multiple-values
             */
            $andWhere[] = ' ( a.' . $db->quoteName('app') . ' REGEXP \'' . implode("'|", $filters['apps']) . '\' ) ';
        }
        //if (!empty($filters['userid'])) {
        //get the list of acitivity id in archieve table 1st.
        //Use GROUP_CONCAT ============
        $subQuery = 'SELECT GROUP_CONCAT(DISTINCT b.' . $db->quoteName('activity_id')
                . ') as activity_id FROM ' . $db->quoteName('#__community_activities_hide') . ' as b WHERE b.' . $db->quoteName('user_id') . ' = ' . $db->Quote($my->id);

        $db->setQuery($subQuery);
        $subResult = $db->loadColumn();

        $subString = (empty($subResult)) ? array() : explode(',', $subResult[0]);
        $idlist = array();

        //cleanup empty values
        while (!empty($subString)) {
            $str = array_shift($subString);
            if (!empty($str))
                $idlist[] = $str;
            unset($str);
        }
        $subString = implode(',', $idlist);
        //==========================

        if (!empty($subString))
            $andWhere[] = 'a.' . $db->quoteName('id') . ' NOT IN (' . $subString . ')';
        //   }
        // If current user is blocked by a user he should not see the activity of the user
        // who block him. (of course, if the user data is public, he can see it anyway!)
        /*
          if($my->id != 0){
          $andWhere[] = "a.`actor` NOT IN (SELECT `userid` FROM #__community_blocklist WHERE `blocked_userid`='{$my->id}')";
          }
         */

        $whereOr = implode(' OR ', $orWhere);
        $whereAnd = implode(' AND ', $andWhere);


        // Actors can also be your friends
        // We load 100 activities to cater for aggregated content
        $date = CTimeHelper::getDate(); //we need to compare where both date with offset so that the day diff correctly.
        // Have limit?
        $maxEntries = '';
        if (!empty($filters['maxEntries'])) {
            $maxEntries = ' LIMIT ' . (int) $filters['maxEntries']; /* Do never use $limit without (int) */
        }
        // Azrul Code start
        // 1. Get all the ids of the activities
        $sql = 'SELECT a.* '
                /* .' TO_DAYS('.$db->Quote($date->toSql(true)).') -  TO_DAYS( DATE_ADD(a.' . $db->quoteName('created').', INTERVAL '.$date->getOffset(true).' HOUR ) ) as _daydiff' */
                . ' FROM ' . $db->quoteName('#__community_activities') . ' as a '
                . ' WHERE '
                . ' ( ' . $whereOr . ' ) AND '
                . $whereAnd
                . ' GROUP BY a.' . $db->quoteName('id')
                . ' ORDER BY a.' . $db->quoteName('created') . ' DESC, a. ' . $db->quoteName('id') . ' DESC' . $maxEntries;

        // Remove the bracket if it is not needed
        $sql = CString::str_ireplace('WHERE  (  ) AND', ' WHERE ', $sql);

        return $sql;
    }

    /**
     * Get public & joined group
     * @return type
     */
    private function _getGroups() {
        $db = JFactory::getDbo();
        /* Get public groups */
        $query = ' SELECT ' . $db->quoteName('id');
        $query .= ' FROM ' . $db->quoteName('#__community_groups');
        $query .= ' WHERE ' . $db->quoteName('approvals') . ' = 0';
        $db->setQuery($query);
        $publicGroups = $db->loadColumn();
        $query = ' SELECT ' . $db->quoteName('groupid');
        $query .= ' FROM ' . $db->quoteName('#__community_groups_members');
        $query .= ' WHERE ' . $db->quoteName('approved') . ' = 1';
        $query .= ' AND ' . $db->quoteName('memberid') . ' = ' . CFactory::getUser()->id;
        $query .= ' GROUP BY ' . $db->quoteName('groupid');
        $db->setQuery($query);
        $joinedGroups = $db->loadColumn();
        $groups = array_merge($publicGroups, $joinedGroups);

        return (array_unique($groups));
    }

    /**
     * Get array unpublished video ids
     * @return type
     */
    private function _getUnpublishedVideos() {
        $db = JFactory::getDbo();
        /* Get public groups */
        $query = ' SELECT ' . $db->quoteName('id');
        $query .= ' FROM ' . $db->quoteName('#__community_videos');
        $query .= ' WHERE ' . $db->quoteName('published') . ' = 0';
        $db->setQuery($query);
        return $db->loadColumn();
    }

    /**
     * Given rows of activities, return activities with the likes and comment data
     * @param array $result
     *
     */
    public function _getActivitiesLikeComment($result) {
        $db = $this->getDBO();

        // 2. Get the ids of the comments and likes we will query
        $comments = array();
        $likes = array();

        if (!empty($result)) {
            foreach ($result as $key=>$row) {
                if (!empty($row->comment_type)) {
                    if ($row->comment_type == 'photos') {
                        if ($row->params->get('batchcount', 0) > 1) {
                            $comments['albums'][] = $row->cid;
                        } else {
                            $comments['photos'][] = $row->comment_id;
                        }
                    } else {
                        $comments[$row->comment_type][] = $row->comment_id;
                    }
                }

                if (!empty($row->like_type)){
                    if($row->like_type == 'photo' && $row->params->get('batchcount',0) > 1){
                         $likes['album'][] = $row->cid;
                         $result[$key]->like_type = 'album';
                         $result[$key]->like_id = $row->cid;
                    } else {
                        $likes[$row->like_type][] = $row->like_id;
                    }
                }
            }
        }

        // 3. Query the comments
        $commentsResult = array();

        if (!empty($result)) {
            $cond = array();
            foreach ($comments as $lk => $lv) {
                // Make every uid unique
                $lv = array_unique($lv);
                if (!empty($lv)) {
                    $cond[] = ' ( '
                            . ' a.' . $db->quoteName('type') . '=' . $db->Quote($lk)
                            . ' AND '
                            . ' a.' . $db->quoteName('contentid') . ' IN (' . implode(',', $lv) . ') '
                            . ' ) ';
                }
            }

            if (!empty($cond)) {

                $sql = 'SELECT a.* '
                        . ' FROM ' . $db->quoteName('#__community_wall') . ' as a '
                        . ' WHERE '
                        . implode(' OR ', $cond)
                        . ' ORDER BY ' . $db->quoteName('id') . ' DESC ';

                $db->setQuery($sql);
                $resultComments = $db->loadObjectList();

                if ($db->getErrorNum()) {
                    JError::raiseError(500, $db->stderr());
                }

                foreach ($resultComments as $comment) {
                    $params = new CParameter($comment->params);
                    $actId = $params->get('activityId', NULL);
                    if ($actId) {
                        $key = $comment->type . '-' . $comment->contentid . '-' . $actId;
                    } else {
                        $key = $comment->type . '-' . $comment->contentid;
                    }
                    if (!isset($commentsResult[$key])) {
                        $commentsResult[$key] = $comment;
                        $commentsResult[$key]->_comment_count = 0;
                        $commentsResult[$key]->actId = $actId;
                    }

                    $commentsResult[$key]->_comment_count++;
                }
            }
        }

        // 4. Query the likes
        $likesResult = array();
        if (!empty($result)) {
            $cond = array();
            foreach ($likes as $lk => $lv) {
                // Make every uid unique
                $lv = array_unique($lv);

                if (!empty($lv)) {
                    $cond[] = ' ( '
                            . ' a.' . $db->quoteName('element') . '=' . $db->Quote($lk)
                            . ' AND '
                            . ' a.' . $db->quoteName('uid') . ' IN (' . implode(',', $lv) . ') '
                            . ' ) ';
                }
            }
            if (!empty($cond)) {

                $sql = 'SELECT a.* '
                        . ' FROM ' . $db->quoteName('#__community_likes') . ' as a '
                        . ' WHERE '
                        . implode(' OR ', $cond);

                $db->setQuery($sql);
                $resultLikes = $db->loadObjectList();

                if ($db->getErrorNum()) {
                    JError::raiseError(500, $db->stderr());
                }
                foreach ($resultLikes as $like) {

                    $likesResult[$like->element . '-' . $like->uid] = $like->like;
                }
            }
        }


        // 4. Merge data
        $activities = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                // Merge Like data
                if (array_key_exists($row->like_type . '-' . $row->like_id, $likesResult)) {
                    $row->_likes = $likesResult[$row->like_type . '-' . $row->like_id];
                } else {
                    $row->_likes = '';
                }

                if ($row->comment_type == 'photos' && $row->params->get('batchcount', 0) > 1) {
                    $row->comment_id = $row->cid;
                    $row->comment_type = 'albums';
                }

                // Merge comment data
                if (array_key_exists($row->comment_type . '-' . $row->comment_id, $commentsResult) || array_key_exists($row->comment_type . '-' . $row->comment_id . '-' . $row->id, $commentsResult)) {
                    if (isset($commentsResult[$row->comment_type . '-' . $row->comment_id . '-' . $row->id])) {
                        $data = $commentsResult[$row->comment_type . '-' . $row->comment_id . '-' . $row->id];
                    } else {
                        $data = $commentsResult[$row->comment_type . '-' . $row->comment_id];
                    }

                    $row->_comment_last_id = $data->id;
                    $row->_comment_last_by = $data->post_by;
                    $row->_comment_date = $data->date;
                    $row->_comment_count = $data->_comment_count;
                    $row->_comment_last = isset($data->comment) ? $data->comment : null;
                    if (isset($data->params)) {
                        $row->_comment_params = $data->params;
                    } else {
                        $row->_comment_params = null;
                    }
                } else {
                    $row->_comment_last_id = '';
                    $row->_comment_last_by = '';
                    $row->_comment_date = '';
                    $row->_comment_count = 0;
                    $row->_comment_last = '';
                }

                // Create table object
                $act = JTable::getInstance('Activity', 'CTable');
                $act->bind($row);
                $activities[] = $act;
            }
        }

        return $activities;
    }

    /**
     * Return all activities by the given apps
     *
     * @param mixed $appname string or array of string
     */
    public function getAppActivities($options) {
        $me = CFactory::getUser();
        $blockLists = $me->getBlockedUsers();
        $blockedUserIds = array();
        foreach ($blockLists as $blocklist) {
            $blockedUserIds[] = $blocklist->blocked_userid;
        }
        $queryOptions = array(
            'userid' => null,
            'friends' => '',
            'afterDate' => null,
            /* Back work with old code */
            'createdAfter' => null,
            'exclusions' => null,
            'maxEntries' => 100,
            'respectPrivacy' => true,
            'actidRange' => null,
            'displayArchived' => true,
            'actid' => null,
            'groupid' => null,
            'eventid' => null,
            'blockedUserIds' => $blockedUserIds
        );
        $options = array_merge($queryOptions, $options);

        if (count($options['app']) > 1) {
            $options['apps'] = "'" . implode("','", $options['app']) . "'";
        } else {
            $options['apps'] = "'" . $options['app'] . "'";
        }

        $sql = $this->_buildQuery($options);
        $db = JFactory::getDbo();
        $db->setQuery($sql);

        $result = $db->loadObjectList();

        foreach ($result as $key => $_result) {
            $result[$key]->params = new CParameter($_result->params);
        }

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        $activities = $this->_getActivitiesLikeComment($result);

        return $activities;
    }

    /**
     * Remove any recently changed activities
     */
    public function removeRecent($actor, $title, $app, $timeDiff) {
        
    }

    /*
     * Remove One Photo Activity
     * As it's tricky to remove the activity since there's no photo id in the
     * activity data. Here we get all the activities of 5 seconds within the
     * activity creation time, then we try to match the photo id in the activity
     * params, and also the thumbnail in the activity content field. When all
     * fails, we fallback to removeOneActivity()
     */

    public function removeOnePhotoActivity($app, $uniqueId, $datetime, $photoId, $thumbnail) {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote($app) . ' '
                . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($uniqueId) . ' '
                . 'AND ( ' . $db->quoteName('created') . ' BETWEEN ' . $db->Quote($datetime) . ' '
                . 'AND ( ADDTIME(' . $db->Quote($datetime) . ', ' . $db->Quote('00:00:05') . ' ) ) ) '
        ;
        $db->setQuery($query);
        $result = $db->loadObjectList();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        $activityId = null;
        $handler = new CParameter(null);

        // the activity data contains photoid and the photo thumbnail
        // which can be useful for us to find the correct activity id
        foreach ($result as $activity) {
            $handler->loadINI($activity->params);
            if ($handler->getValue('photoid') == $photoId) {
                $activityId = $activity->id;
                break;
            }
            if (JString::strpos($activity->content, $thumbnail) !== false) {
                $activityId = $activity->id;
                break;
            }
        }

        if (is_null($activityId)) {
            return $this->removeOneActivity($app, $uniqueId);
        }

        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote($activityId) . ' '
                . 'LIMIT 1 ';
        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    public function removeOneActivity($app, $uniqueId) {
        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote($app) . ' '
                . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($uniqueId) . ' '
                . 'LIMIT 1 ';

        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    //Remove Discussion via params
    function removeDiscussion($app, $uniqueId, $paramName, $paramValue) {

        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote($app) . ' '
                . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($uniqueId) . ' '
                . 'AND ' . $db->quoteName('params') . ' LIKE ' . $db->Quote('%' . $paramName . '=' . $paramValue . '%');
        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    public function removeAlbumActivity($uniqueId) {
        $db = $this->getDBO();
        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote('photos') . ' '
                . 'AND ' . $db->quoteName('comment_type') . '=' . $db->Quote('albums') . ' '
                . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($uniqueId);

        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    public function removeActivity($app, $uniqueId) {
        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('app') . '=' . $db->Quote($app) . ' '
                . 'AND ' . $db->quoteName('cid') . '=' . $db->Quote($uniqueId);

        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    /**
     * Remove an activity by ID
     * @param type $activityId
     * @return boolean
     */
    public function removeActivityById($activityId) {
        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('id') . '=' . $db->quote($activityId) . ' ';

        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    public function removeGroupActivity($ids) {
        $db = $this->getDBO();
        $app = '"groups","groups.bulletin","groups.discussion","groups.wall"';
        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' '
                . 'WHERE ' . $db->quoteName('app') . 'IN (' . $app . ') '
                . 'AND ' . $db->quoteName('cid') . 'IN (' . $ids . ')';

        $db->setQuery($query);
        $status = $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $status;
    }

    /**
     * Return the actor id by a given activity id
     */
    public function getActivityOwner($uniqueId) {
        $db = $this->getDBO();


        $sql = 'SELECT ' . $db->quoteName('actor')
                . ' FROM ' . $db->quoteName('#__community_activities')
                . ' WHERE ' . $db->quoteName('id') . '=' . $db->Quote($uniqueId);

        $db->setQuery($sql);
        $result = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // @todo: write a plugin that return the html part of the whole system
        return $result;
    }

    /**
     * Return the number of total activity by a given user
     */
    public function getActivityCount($userid) {
        $db = $this->getDBO();


        $sql = 'SELECT SUM(' . $db->quoteName('points')
                . ') FROM ' . $db->quoteName('#__community_activities')
                . ' WHERE ' . $db->quoteName('actor') . '=' . $db->Quote($userid);

        $db->setQuery($sql);
        $result = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // @todo: write a plugin that return the html part of the whole system
        return $result;
    }

    /**
     * Retrieves total number of activities throughout the site.
     *
     * @return	int	$total	Total number of activities.
     * */
    public function getTotalActivities($filter = array()) {
        $db = JFactory::getDBO();

        $andWhere[] = ' 1 ';

        if (isset($filter['apps'])) {
            /**
             * @link http://stackoverflow.com/questions/4172195/mysql-like-multiple-values
             */
            $andWhere[] = ' ( ' . $db->quoteName('app') . ' REGEXP \'' . implode("'|", $filter['apps']) . '\' ) ';
        }

        $whereAnd = implode(' AND ', $andWhere);
        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_activities') . ' WHERE ' . $whereAnd;

        $db->setQuery($query);
        $total = $db->loadResult();

        return $total;
    }

    /**
     * Update acitivy stream access
     *
     * @param <type> $access
     * @param <type> $previousAccess
     * @param <type> $actorId
     * @param <type> $app
     * @param <type> $cid
     * @return <type>
     *
     */
    public function updatePermission($access, $previousAccess, $actorId, $app = '', $cid = '') {
        $db = $this->getDBO();

        $query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET ' . $db->quoteName('access') . ' = ' . $db->Quote($access);
        $query .= ' WHERE ' . $db->quoteName('actor') . ' = ' . $db->Quote($actorId);

        if ($previousAccess != null && $previousAccess > $access) {
            $query .= ' AND ' . $db->quoteName('access') . ' <' . $db->Quote($access);
        }

        if (!empty($app)) {
            $query .= ' AND ' . $db->quoteName('app') . ' = ' . $db->Quote($app);
        }

        if (!empty($cid)) {
            $query .= ' AND ' . $db->quoteName('cid') . ' = ' . $db->Quote($cid);
        }

        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $this;
    }

    public function updatePermissionByCid($access, $previousAccess = null, $cid, $app) {
        // if (is_array($cid)) {}

        $db = $this->getDBO();

        $query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET ' . $db->quoteName('access') . ' = ' . $db->Quote($access);
        $query .= ' WHERE ' . $db->quoteName('cid') . ' IN (' . $db->Quote($cid) . ')';
        $query .= ' AND ' . $db->quoteName('app') . ' = ' . $db->Quote($app);

        if ($previousAccess != null && $previousAccess > $access) {
            $query .= ' AND ' . $db->quoteName('access') . ' <' . $db->Quote($access);
        }

        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $this;
    }

    /**
     * Generic activity update code
     *
     * @param array  $condition
     * @param array $update
     * @return CommunityModelActivities
     */
    public function update($condition, $update) {
        $db = $this->getDBO();

        $where = array();
        foreach ($condition as $key => $val) {
            $where[] = $db->quoteName($key) . '=' . $db->Quote($val);
        }
        $where = implode(' AND ', $where);

        $set = array();
        foreach ($update as $key => $val) {
            $set[] = ' ' . $db->quoteName($key) . '=' . $db->Quote($val);
        }
        $set = implode(', ', $set);

        $query = 'UPDATE ' . $db->quoteName('#__community_activities') . ' SET ' . $set . ' WHERE ' . $where;

        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $this;
    }

    /**
     * Get activities with inputed location within 30 days.
     * We'll use that for smart location auto complete
     * @since 3.2
     * @param type $userId
     * @return type
     */
    public function getUserVisitedLocation($userId) {
        $db = $this->getDbo();
        $query = ' SELECT '
                . $db->quoteName('latitude') . ' , '
                . $db->quoteName('longitude') . ' , '
                . $db->quoteName('location') . ' , '
                . $db->quoteName('params');
        $query .= ' FROM ' . $db->quoteName('#__community_activities');
        $query .= ' WHERE ' . $db->quoteName('actor') . ' = ' . $db->quote($userId);
        /* Must have location */
        $query .= ' AND ' . $db->quoteName('latitude') . ' <> ' . $db->quote('255.000000');
        $query .= ' AND ' . $db->quoteName('longitude') . ' <> ' . $db->quote('255.000000');
        $query .= ' AND ' . $db->quoteName('created') . ' BETWEEN NOW() - INTERVAL 30 DAY AND NOW() ';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

}
