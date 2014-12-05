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

/**
 * Class exists checking
 */
if (!class_exists('CommunityModelStream')) {

    /**
     * Stream model
     * @since 3.2
     */
    class CommunityModelStream extends JCCModel {

        /**
         * Current logged / unlogged user
         * @var CUser 
         */
        private $_me;

        /**
         *
         * @var array 
         */
        private $_query = array();

        /**
         * Construct
         * @param type $config
         */
        public function __construct($config = array()) {
            parent::__construct($config);
            $this->_me = CFactory::getUser();
        }

        /**
         * Build SELECT query
         * @param type $filters
         * @return type
         */
        protected function _buildSelect($filters = array()) {
            $this->_query['SELECT'][] = ' `a`.* ';
            return $this;
        }

        /**
         * Build FROM query
         * @param type $filters
         * @return type
         */
        protected function _buildFrom($filters = array()) {
            $db = JFactory::getDbo();
            $this->_query['FROM'][] = $db->quoteName('#__community_activities') . ' AS ' . $db->quoteName('a');
            return $this;
        }

        /**
         * Build WHERE query
         * @param type $filters
         */
        protected function _buildWhere($filters = array()) {
            $todayDate = new JDate();
            $isCommunityAdmin = COwnerHelper::isCommunityAdmin($this->_me->id);

            $db = $this->getDBO();

            
            $onActor = '';

            /* Filter by apps */
            if ($filters['app'] != null) {
                /* Search app in request */
                if (is_string($filters['app'])) {
                    $filters['app'] = array($filters['app']);
                }
                $app = "'" . implode("','", $app) . "'";
                $this->_query['WHERE']['AND'] = ' a.' . $db->quoteName('app') . ' IN (' . $app . ')';

                if ($filters['cid'] != null) {
                    $this->_query['WHERE']['AND'] = ' AND a.' . $db->quoteName('cid') . ' = ' . $db->Quote($filters['cid']);
                }
            }
            /* Since 2.6 by default we display all activities including the archived one */
            if ($filters['displayArchived'] != null) {
                $this->_query['WHERE']['AND'] = $db->quoteName('archived') . '=' . (int) $filters['displayArchived'];
            }
            /* Filter by with actors */
            if ($filters['actor'] != null) {
                /* Activity' actor */
                $whereOR[] = '( ' . $db->quoteName('a') . '.' . $db->quoteName('actor') . ' = ' . $db->Quote($filters['actor']) . ' ) ';
                /* @since 2.6, show friends activities even its not related to the current user(me-and-friends fpage) */
                if ($filters['actor'] != $this->_me->id) {
                    $onActor .= ' AND ( '
                            . '( ' . $db->quoteName('a') . '.' . $db->quoteName('actor') . ' = ' . $db->Quote($filters['actor']) . ' ) '
                            . ' OR '
                            . ' ( ' . $db->quoteName('a') . '.' . $db->quoteName('target') . '=' . $db->Quote($filters['actor']) . ')'
                            . ')';
                }
                /* @since 2.8 also search within actors column */
                $whereOR[] = ' ( '
                        . ' ( ' . $db->quoteName('a') . '.' . $db->quoteName('actor') . ' = ' . $db->Quote(0) . ' ) '
                        . ' AND '
                        . ' ( ' . $db->quoteName('a') . '.' . $db->quoteName('actors') . ' LIKE \'%{"id":"' . $filters['actor'] . '"}%\' ) '
                        . ' )';
                /* Actor is target */
                $orWhere[] = '( a.' . $db->quoteName('target') . ' = ' . $db->Quote($filters['actor']) . ' ) ';
            }
            /* Query friends activities */
            if (implode(',', $filters['friends']) != '') {
                $whereOR[] = '( a .' . $db->quoteName('actor') . ' IN ( ' . implode(',', $filters['friends']) . '))';
                $whereOR[] = '( a.' . $db->quoteName('target') . ' IN (' . implode(',', $filters['friends']) . '))';
                /* actor are friends, clear the on Actor condition */
                $onActor .= '';
            }
            /* Filter activities created after request time until current */
            if ($filters['afterDate'] != null)
                $this->_query['WHERE']['AND'] = '( a.' . $db->quoteName('created') . ' BETWEEN ' . $db->Quote($filters['afterDate']) . ' AND ' . $db->Quote($todayDate->toSql()) . ' ) ';
            /* Filter activities in range of request ID */
            if (($filters['actidRange'] != null)) {
                /* Make sure it is an integer (singed and unsigned) */
                $filters['actidRange'] = intval($filters['actidRange']);
                /* If idrange is positive, return items older than the given id */
                if ($filters['actidRange'] > 0) {
                    $exclusionQuery = ' a.id < ' . $filters['actidRange'] . ' ';
                    $this->_query['WHERE']['AND'] = $exclusionQuery;
                } else if ($filters['actidRange'] < 0) { /* If idrange is negative, return items older than the given id */
                    $exclusionQuery = ' a.id > ' . abs($filters['actidRange']) . ' ';
                    $this->_query['WHERE']['AND'] = $exclusionQuery;
                }
            }
            /* Filter exactly activity ID */
            if ($filters['actid'] > 0) {
                $this->_query['WHERE']['AND'] = ' ( a.id = ' . (int) $filters['actid'] . ' ) ';
            }
            /* Limit to a particular group */
            if ($filters['groupid'] > 0) {
                $this->_query['WHERE']['AND'] = ' ( a.groupid = ' . (int) $filters['groupid'] . ' ) ';
            }
            /* Limit to a particular event */
            if ($filters['eventid'] > 0) {
                $this->_query['WHERE']['AND'] = ' ( a.eventid = ' . (int) $filters['eventid'] . ' ) ';
            }

            /**
             * Filter blocked user
             */
            if (count($filters['blockedUsers']) > 0) {
                $this->_query['WHERE']['AND'] = ' ( a.actor NOT IN (' . implode(',', $filters['blockedUserIds']) . ') )';
                /**
                 * @todo Improve this query
                 */
                foreach ($filters['blockedUserIds'] as $blockedUserId) {
                    $this->_query['WHERE']['AND'] = ' ( a.actors NOT LIKE ' . $db->quote('%' . $blockedUserId . '%') . ')';
                }
            }

            /**
             * Filter by Group & Event permission
             * Admin can see everything
             */
            if (!$isCommunityAdmin) {
                /* Group */
                $groupids = empty($this->_me->_groups) ? "" : $this->_me->_groups;
                if (!empty($groupids)) {
                    $this->_query['WHERE']['AND'] = '( ( a.' . $db->quoteName('group_access') . '=' . $db->Quote(0) . ' ) '
                            . '  OR '
                            . '  ( a.' . $db->quoteName('groupid') . ' IN (' . $groupids . ' ) ) '
                            . ' OR ( a.' . $db->quoteName('groupid') . '=' . $db->Quote(0) . ' ) ) ';
                } else {
                    /* Only show public groups */
                    $this->_query['WHERE']['AND'] = ' ( a.' . $db->quoteName('group_access') . '=' . $db->Quote(0) . ' )';
                }
                /* Event */
                $eventids = empty($this->_me->_events) ? "" : $this->_me->_events;
                if (!empty($eventids)) {
                    $this->_query['WHERE']['AND'] = '( ( a.' . $db->quoteName('event_access') . '=' . $db->Quote(0) . ')' /* public event */
                            . '  OR '
                            . '  ( a.' . $db->quoteName('eventid') . ' IN (' . $eventids . ' ) ) '
                            . ' OR (a.' . $db->quoteName('eventid') . '=' . $db->Quote(0) . ') )';
                }
                /* If eventid provided than we need to chek current user id member of this event */
                $event = JTable::getInstance('Event', 'CTable');
                $event->load((int) $filters['eventid']);
                if ($event->isMember($this->_me->id)) {
                    $this->_query['WHERE']['AND'] = ' ( ( a.' . $db->quoteName('event_access') . '=' . $db->Quote(1) . ') '
                            . ' OR ( a.' . $db->quoteName('event_access') . '=' . $db->Quote(0) . ') )';
                } else {
                    // Only show public events
                    $this->_query['WHERE']['AND'] = ' ( a.' . $db->quoteName('event_access') . '=' . $db->Quote(0) . ')';
                }
            }

            if ($filters['respectPrivacy']) {
                /**
                 * Add friends limits, but admin should be able to see all
                 * @todo: should use global admin code check instead
                 */
                if ($this->_me->id == 0) {
                    /* for guest, it is enough to just test access <= 0 */
                    $this->_query['WHERE']['AND'] = ' (a.' . $db->quoteName('access') . ' <= 10)';
                } elseif (!$isCommunityAdmin) {
                    /**
                     * @todo clean up these code later
                     */
                    $orWherePrivacy = array();
                    $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = 0) ' . $onActor . ')';
                    $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = 10) ' . $onActor . ')';
                    $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = 20) AND ( ' . $db->Quote($this->_me->id) . ' != 0) ' . $onActor . ')';
                    if ($this->_me->id != 0) {
                        $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = ' . $db->Quote(40) . ') AND (a.' . $db->quoteName('actor') . ' = ' . $db->Quote($this->_me->id) . ') ' . $onActor . ')';
                        $orWherePrivacy[] = '((a.' . $db->quoteName('access') . ' = ' . $db->Quote(30) . ') AND ((a.' . $db->quoteName('actor') . 'IN (SELECT c.' . $db->quoteName('connect_to')
                                . ' FROM ' . $db->quoteName('#__community_connection') . ' as c'
                                . ' WHERE c.' . $db->quoteName('connect_from') . ' = ' . $db->Quote($this->_me->id)
                                . ' AND c.' . $db->quoteName('status') . ' = ' . $db->Quote(1) . ' ) ) OR (a.' . $db->quoteName('actor') . ' = ' . $db->Quote($this->_me->id) . ') )' . $onActor . ' )';
                    }
                    $OrPrivacy = implode(' OR ', $orWherePrivacy);
                    // If groupid is specified, no need to check the privacy
                    // really
                    $this->_query['WHERE']['AND'] = "(a." . $db->quoteName('groupid') . " OR (" . $OrPrivacy . "))";
                }
            }
            /**
             * Sub queries
             * @todo Should we move this into another function
             */
            $subQuery = 'SELECT GROUP_CONCAT(DISTINCT b.' . $db->quoteName('activity_id')
                    . ') as activity_id FROM ' . $db->quoteName('#__community_activities_hide') . ' as b WHERE b.' . $db->quoteName('user_id') . ' = ' . $db->Quote($this->_me->id);

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
                $this->_query['WHERE']['AND'] = 'a.' . $db->quoteName('id') . ' NOT IN (' . $subString . ')';

            $whereOR = trim(implode(' OR ', $whereOR));
            $whereAND = trim(implode(' AND ', $whereAND));

            $where = ' WHERE ';
            if ($whereOR != '')
                $where .= ' ( ' . $whereOR . ' ) ';
            if ($whereAND != '')
                $where .= ' AND ( ' . $whereAND . ' ) ';
            return $where;
        }

        protected function _buildLimit($filters = array()) {
            $db = $this->getDBO();
            $maxEntries = '';
            if ($filters['maxEntries'] != null) {
                $maxEntries = ' LIMIT ' . (int) $filters['maxEntries']; /* Do never use $limit without (int) */
            }
            return $maxEntries;
        }

        protected function _buildGroup($filters = array()) {
            $db = $this->getDBO();
            return ' GROUP BY ' . $db->quoteName('a') . '.' . $db->quoteName('id');
        }

        protected function _buildOrderBy($filters = array()) {
            $db = $this->getDBO();
            return ' ORDER BY ' . $db->quoteName('a') . '.' . $db->quoteName('created') . ' DESC ,' . $db->quoteName('a') . '.' . $db->quoteName('id') . ' DESC ';
        }

        protected function _buildQuery($filters = array()) {
            $db = $this->getDBO();
            $activities = array();
            $user = CFactory::getUser((isset($options['userid']) ? $options['userid'] : null));
            $blockLists = $user->getBlockedUsers();
            $blockedUserIds = array();
            foreach ($blockLists as $blocklist) {
                $blockedUserIds[] = $blocklist->blocked_userid;
            }
            $defFilter = array(
                'actor' => $user->id,
                'friends' => $user->getFriendIds(),
                'blockedUsers' => $blockedUserIds,
                'afterDate' => $user->registerDate,
                'maxEntries' => 20,
                'respectPrivacy' => true,
                'actidRange' => null,
                'displayArchived' => null,
                'actid' => null,
                'groupid' => null,
                'eventid' => null,
                /* Filter by apps */
                'app' => null
            );
            $filters = array_merge($defFilter, $filters);
            $query = $this->_buildSelect($filters) . $this->_buildFrom($filters) . $this->_buildWhere($filters) . $this->_buildG($filters) . $this->_buildORDERBY($filters);
            return CString::str_ireplace('WHERE (  ) AND', ' WHERE ', $query);
        }

        /**
         * Main function use to get stream' activities
         * @param type $filters
         * @return array
         */
        public function getActivities($filters = array()) {
            $db = $this->getDBO();
            $query = $this->_buildQuery($filters);
            /**
             * @todo Should we create function to do validation query before execute it ?
             */
            $db->setQuery($query);
            $activities = array();
            $activities = $db->loadObjectList();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            return $this->_getActivitiesLikeComment($activities);
        }

        /**
         * Given rows of activities, return activities with the likes and comment data
         * @param array $result
         *
         */
        protected function _getActivitiesLikeComment($activities) {
            $db = JFactory::getDbo();

            $comments = array();
            $likes = array();
            foreach ($activities as $index => $activity) {

                /**
                 * @todo These prepare should be under CActivity object NOT HERE !!!
                 */
                if (CFactory::getUser($activity->actor)->block) {
                    $activities[$index]->content = $activities[$index]->title = JText::_('COM_COMMUNITY_CENSORED');
                }
                /* Convert params into JRegistry */
                if (($activity->params != '') && (!is_object($activity->params))) {
                    $params = new JRegistry;
                    $params->loadString($activity->params);
                    $activities[$index]->params = $params;
                }

                /* Comment */
                if (!empty($activity->comment_type)) {
                    if ($activity->comment_type == 'photos') {
                        $comments['photos'][] = $activity->comment_id;
                    } else {
                        $comments[$activity->comment_type][] = $activity->comment_id;
                    }
                }
                /* Like */
                if (!empty($activity->like_type))
                    $likes[$activity->like_type][] = $activity->like_id;
            }

            /* Get comments */
            $commentsResult = array();
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
                    $key = $comment->type . '-' . $comment->contentid;

                    if (!isset($commentsResult[$key])) {
                        $commentsResult[$key] = $comment;
                        $commentsResult[$key]->_comment_count = 0;
                    }

                    $commentsResult[$key]->_comment_count++;
                }
            }


            /* Get likes */
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


            $list = array();
            foreach ($activities as $activity) {
                // Merge Like data
                if (array_key_exists($activity->like_type . '-' . $activity->like_id, $likesResult)) {
                    $activity->_likes = $likesResult[$activity->like_type . '-' . $activity->like_id];
                } else {
                    $activity->_likes = '';
                }

                /**
                 * @todo Need check to clean this code
                 */
                if ($activity->comment_type == 'photos') {
                    // $activity->comment_id = $activity->cid;
                    // $activity->comment_type = 'albums';
                }

                // Merge comment data
                if (array_key_exists($activity->comment_type . '-' . $activity->comment_id, $commentsResult)) {
                    $data = $commentsResult[$activity->comment_type . '-' . $activity->comment_id];
                    $activity->_comment_last_id = $data->id;
                    $activity->_comment_last_by = $data->post_by;
                    $activity->_comment_date = $data->date;
                    $activity->_comment_count = $data->_comment_count;
                    $activity->_comment_last = isset($data->comment) ? $data->comment : null;
                    if (isset($data->params)) {
                        $activity->_comment_params = $data->params;
                    } else {
                        $activity->_comment_params = null;
                    }
                } else {
                    $activity->_comment_last_id = '';
                    $activity->_comment_last_by = '';
                    $activity->_comment_date = '';
                    $activity->_comment_count = 0;
                    $activity->_comment_last = '';
                }

                /**
                 * I don't use JTable here. By logic everything must go via CActivity object even with database update ( wrap methods )
                 */
                $list[] = $activity;
            }


            return $list;
        }

    }

}
