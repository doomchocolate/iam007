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

require_once ( JPATH_ROOT . '/components/com_community/models/models.php');

class CommunityModelVideos extends JCCModel implements CLimitsInterface {

    var $_pagination = '';
    var $total = '';

    public function CommunityModelVideos() {
        parent::JCCModel();

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $id = $jinput->get('videoid', 0, 'int');
        $this->setId((int) $id);

        $config = CFactory::getConfig();
        // Get the pagination request variables
        $limit = $config->getString('listlimit');
        $limitstart = $jinput->request->get('limitstart', 0, 'INT');

        if (empty($limitstart)) {
            $limitstart = $jinput->get('start', 0, 'INT');
        }

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    public function setId($id) {
        // Set new video ID and wipe data
        $this->_id = $id;
        return $this;
    }

    /**
     * 	Checks whether specific user or group has pending videos
     *
     * 	@params	$id	int	The unique id of the creator or groupid
     * 	@params	$type	string	The video type whether user or group
     * */
    public function hasPendingVideos($id, $type = VIDEO_USER_TYPE) {
        if ($type == VIDEO_USER_TYPE && $id == 0) {
            return 0;
        }

        $db = $this->getDBO();

        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_videos') . ' '
                . 'WHERE ' . $db->quoteName('creator_type') . '=' . $db->Quote($type) . ' ';

        if ($type == VIDEO_USER_TYPE) {
            $query .= 'AND ' . $db->quoteName('creator') . '=' . $db->Quote($id);
        } else {
            $query .= 'AND ' . $db->quoteName('groupid') . '=' . $db->Quote($id);
        }

        $query .= ' AND ' . $db->quoteName('status') . '=' . $db->Quote('pending');
        $query .= ' AND ' . $db->quoteName('published') . '=' . $db->Quote(1);

        $db->setQuery($query);
        $result = $db->loadResult() >= 1 ? true : false;

        return $result;
    }

    /**
     * Loads the videos
     *
     * @public
     * @param	array	$filters	The video's filter
     * @return	array	An array of videos object
     * @since	1.2
     */
    public function getVideos($filters = array(), $tableBind = false, $limitSetting = true, $useCache = true) {
        /* use caching */
        if ($useCache) {
            $cache = CFactory::getFastCache();
            $cacheid = serialize(func_get_args()) . serialize(JRequest::get());
            if ($data = $cache->get($cacheid)) {
                jimport('joomla.html.pagination');
                $this->_pagination = new JPagination($data['total'], $data['limitstart'], $data['limit']);
                return $data['entries'];
            }
        }

        $db = $this->getDBO();

        $where = array();
        $limiter = '';
        $defaultFilters = array(
            'published' => 1
        );
        $filters = array_merge($defaultFilters,$filters);
        foreach ($filters as $field => $value) {
            if ($value || $value === 0) {
                
                switch (strtolower($field)) {
                    case 'id':
                        if (is_array($value)) {
                            JArrayHelper::toInteger($value);
                            $value = implode(',', $value);
                        }
                        $where[] = 'v.' . $db->quoteName('id') . ' IN (' . $value . ')';
                        break;
                    case 'title':
                        $where[] = 'v.' . $db->quoteName('title') . '  LIKE ' . $db->quote('%' . $value . '%');
                        break;
                    case 'type':
                        $where[] = 'v.' . $db->quoteName('type') . ' = ' . $db->quote($value);
                        break;
                    case 'description':
                        $where[] = 'v.' . $db->quoteName('description') . ' LIKE ' . $db->quote('%' . $value . '%');
                        break;
                    case 'creator':
                        $where[] = 'v.' . $db->quoteName('creator') . ' = ' . $db->quote((int) $value);
                        break;
                    case 'creator_type':
                        $where[] = 'v.' . $db->quoteName('creator_type') . ' = ' . $db->quote($value);
                        break;
                    case 'created':
                        $value = JFactory::getDate($value)->toSql();
                        $where[] = 'v.' . $db->quoteName('created') . ' BETWEEN ' . $db->quote('1970-01-01 00:00:01') . ' AND ' . $db->quote($value);
                        break;
                    case 'permissions':
                        if (isset($filters['friendsvideos']) && $filters['friendsvideos']) {
                            $my = CFactory::getUser();
                            $extrasql = '';
                            if ($my->id != 0) {
                                if (empty($filters['groupid'])) {
                                    $friendmodel = CFactory::getModel('friends');
                                    $friends = $friendmodel->getFriendIds($my->id);
                                    if (!empty($friends)) {
                                        $extrasql .= ' OR (creator IN(' . implode(',', $friends) . ') AND permissions = ' . $db->Quote(30) . ') ';
                                    }
                                }
                            }
                            $where[] = '( v.' . $db->quoteName('permissions') . ' <= ' . $db->quote((int) $value) . ' ' . $extrasql . '  OR ( creator=' . $db->Quote($my->id) . ' AND permissions <=' . $db->Quote(40) . ' ) )';
                        } else {
                            $where[] = 'v.' . $db->quoteName('permissions') . ' <= ' . $db->quote((int) $value);
                        }

                        break;
                    case 'category_id':
                        if (is_array($value)) {
                            JArrayHelper::toInteger($value);
                            $value = implode(',', $value);
                        }
                        $where[] = 'v.' . $db->quoteName('category_id') . ' IN (' . $value . ')';
                        break;
                    case 'hits':
                        $where[] = 'v.' . $db->quoteName('hits') . ' >= ' . $db->quote((int) $value);
                        break;
                    case 'published':
                        $where[] = 'v.' . $db->quoteName('published') . ' = ' . $db->quote((bool) $value);
                        break;
                    case 'featured':
                        $where[] = 'v.' . $db->quoteName('featured') . ' = ' . $db->quote((bool) $value);
                        break;
                    case 'duration':
                        $where[] = 'v.' . $db->quoteName('duration') . ' >= ' . $db->quote((int) $value);
                        break;
                    case 'status':
                        $where[] = 'v.' . $db->quoteName('status') . ' = ' . $db->quote($value);
                        break;
                    case 'groupid':
                        $where[] = 'v.' . $db->quoteName('groupid') . ' = ' . $db->quote($value);
                        break;
                    case 'limitstart':
                        $limitstart = (int) $value;
                        break;
                    case 'limit':
                        $limit = (int) $value;
                        break;
                }
            }
        }

        $where = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';

        // Joint with group table
        $join = '';
        if (isset($filters['or_group_privacy'])) {
            $approvals = (int) $filters['or_group_privacy'];
            $join = ' LEFT JOIN ' . $db->quoteName('#__community_groups') . ' AS g';
            $join .= ' ON g.' . $db->quoteName('id') . ' = v.' . $db->quoteName('groupid');
            $where .= ' AND (g.' . $db->quoteName('approvals') . ' = ' . $db->Quote('0') . ' OR g.' . $db->quoteName('approvals') . ' IS NULL)';
        }

        $limit = (isset($limit)) ? $limit : $this->getState('limit');
        $limit = ($limit < 0) ? 0 : $limit;
        $limitstart = (isset($limitstart)) ? $limitstart : $this->getState('limitstart');

        if ($limitSetting == true) {
            /* Temporary fix for ajaxGetFeaturedVideos LIMIT 0,0 */
            if ($limit != 0)
                $limiter = ' LIMIT ' . $limitstart . ', ' . $limit;
        } else {
            $limiter = '';
        }

        $order = '';
        $sorting = isset($filters['sorting']) ? $filters['sorting'] : 'latest';

        switch ($sorting) {
            case 'mostwalls':

                // do a query to get the most commented items ids
                $wallquery = ' SELECT contentid'
                        . ' FROM ' . $db->quoteName('#__community_wall')
                        . ' WHERE ' . $db->quoteName('type') . ' = ' . $db->quote('videos')
                        . ' AND ' . $db->quoteName('published') . ' = ' . $db->quote(1)
                        . ' group by contentid '
                        . ' order by count(*) desc ' . $limiter;
                $db->setQuery($wallquery);
                $wallResult = $db->loadColumn();

                $where .=!empty($wallResult) ? 'AND v.' . $db->quoteName('id') . ' IN (' . implode(',', $wallResult) . ')' : 'AND v.' . $db->quoteName('id');
                break;
            case 'mostviews':
                $order = ' ORDER BY v.' . $db->quoteName('hits') . ' DESC';
                break;
            case 'title':
                $order = ' ORDER BY v.' . $db->quoteName('title') . ' ASC';
                break;
            case 'latest':
            default :
                $order = ' ORDER BY v.' . $db->quoteName('created') . ' DESC';
                break;
        }

        $query = ' SELECT v.*, v.' . $db->quoteName('created') . ' AS lastupdated'
                . ' FROM ' . $db->quoteName('#__community_videos') . ' AS v'
                . $join
                . $where
                . $order
                . $limiter;
        $db->setQuery($query);
        
        $result = $db->loadObjectList();

        //Fix worng order issue
        if ($sorting === 'mostwalls' && isset($wallResult) && !empty($wallResult)) {
            $temporaryStack = array();
            foreach ($wallResult as $videoid) {
                foreach ($result as $videosRet) {
                    if (isset($videosRet->id)) {
                        if ($videoid == $videosRet->id) {
                            $temporaryStack[] = $videosRet;
                            break;
                        }
                    }
                }
            }
            $result = $temporaryStack;
        }

        // bind to JTable
        $data = array();
        foreach ($result AS $row) {
            $video = JTable::getInstance('Video', 'CTable');
            $video->bind($row);
            $video->lastupdated = $video->created;
            $data[] = $video;
        }

        if ($db->getErrorNum())
            JError::raiseError(500, $db->stderr());

        // Get total of records to be used in the pagination
        $query = ' SELECT COUNT(*)'
                . ' FROM ' . $db->quoteName('#__community_videos') . ' AS v'
                . $join
                . $where
        ;
        $db->setQuery($query);
        $total = $db->loadResult();
        $this->total = $total;

        if ($db->getErrorNum())
            JError::raiseError(500, $db->stderr());

        // Apply pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }


        // Add the wallcount property for sorting purpose
        // foreach ($data as $video) {
        // 	// Wall post count
        // 	$query	= ' SELECT COUNT(*)'
        // 			. ' FROM ' . $db->quoteName('#__community_wall')
        // 			. ' WHERE ' . $db->quoteName('type') . ' = ' . $db->quote('videos')
        // 			. ' AND ' . $db->quoteName('published') . ' = ' . $db->quote(1)
        // 			. ' AND ' . $db->quoteName('contentid') . ' = ' . $db->quote($video->id)
        // 			;
        // 	$db->setQuery($query);
        // 	$video->wallcount	= $db->loadResult();
        // }
        // // Sort videos according to wall post count
        // if ($sorting == 'mostwalls')
        // 	JArrayHelper::sortObjects( $data, 'wallcount', -1);

        $resultentriesdata = array('entries' => $data, 'total' => $total, 'limitstart' => $limitstart, 'limit' => $limit);
        if ($useCache) {
            $cache->store($resultentriesdata, $cacheid, array(COMMUNITY_CACHE_TAG_VIDEOS));
        }

        return $data;
    }

    /**
     * Loads the categories
     *
     * @access	public
     * @return	array	An array of categories object
     * @since	1.2
     */
    public function getCategories($categoryId = null) {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $cache = CFactory::getFastCache();
        $cacheid = serialize(func_get_args()) . serialize(JRequest::get());
        if ($data = $cache->get($cacheid)) {
            return $data;
        }

        $my = CFactory::getUser();
        $permissions = ($my->id == 0) ? 0 : 20;
        $groupId = $jinput->get->get('groupid', '', 'INT'); //JRequest::getVar('groupid' , '' , 'GET');
        $conditions = '';
        $db = $this->getDBO();

        if (!empty($groupId)) {
            $conditions = ' AND v.' . $db->quoteName('creator_type') . ' = ' . $db->quote(VIDEO_GROUP_TYPE);
            //$conditions	.= ' AND b.groupid = ' . $groupId;
            $conditions .= ' AND g.' . $db->quoteName('id') . ' = ' . $db->Quote($groupId);
        } else {
            $conditions .= ' AND (g.' . $db->quoteName('approvals') . ' = ' . $db->Quote('0') . ' OR g.' . $db->quoteName('approvals') . ' IS NULL)';
        }

        $allcats = $this->getAllCategories($categoryId);
        $allCatsNoFilter = $this->getAllCategories();

        $result = array();
        foreach ($allcats as $cat) {
            $categoryIds = CCategoryHelper::getCategoryChilds($allCatsNoFilter, $cat->id);
            if ((int) $cat->id > 0) {
                $categoryIds[] = (int) $cat->id;
            }

            if (is_array($categoryIds)) {
                $categoryIds = implode($categoryIds, ',');
                $categoryCondition = $db->quoteName('category_id') . ' IN (' . $categoryIds . ') ';
            } else {
                $categoryCondition = $db->quoteName('category_id') . ' = ' . $db->Quote($cat->id);
            }
            $query = ' SELECT COUNT(v.' . $db->quoteName('id') . ') AS count'
                    . ' FROM ' . $db->quoteName('#__community_videos') . ' AS v'
                    . ' LEFT JOIN ' . $db->quoteName('#__community_groups') . ' AS g ON g.' . $db->quoteName('id') . ' = v.' . $db->quoteName('groupid')
                    . ' WHERE v.' . $categoryCondition
                    . ' AND v.' . $db->quoteName('status') . ' = ' . $db->Quote('ready')
                    . ' AND v.' . $db->quoteName('published') . ' = ' . $db->Quote(1)
                    . ' AND v.' . $db->quoteName('permissions') . ' <= ' . $db->Quote($permissions)
                    . $conditions;
            $db->setQuery($query);

            $cat->count = $db->loadResult();
            $result[] = $cat;
        }

        if ($db->getErrorNum())
            JError::raiseError(500, $db->stderr());

        $cache->store($result, $cacheid, array(COMMUNITY_CACHE_TAG_VIDEOS_CAT));
        return $result;
    }

    public function getAllCategories($catId = COMMUNITY_ALL_CATEGORIES) {
        $db = $this->getDBO();

        $where = '';
        if ($catId !== COMMUNITY_ALL_CATEGORIES) {
            if ($catId === COMMUNITY_NO_PARENT) {
                $where = 'WHERE ' . $db->quoteName('parent') . '=' . $db->Quote(COMMUNITY_NO_PARENT) . ' ';
            } else {
                $where = 'WHERE ' . $db->quoteName('parent') . '=' . $db->Quote($catId) . ' ';
            }
        }

        $query = ' SELECT * '
                . ' FROM ' . $db->quoteName('#__community_videos_category')
                . ' ' . $where . ' ORDER BY ' . $db->quoteName('name') . ' ASC';
        $db->setQuery($query);
        $result = $db->loadObjectList();

        // bind to table
        $data = array();
        foreach ($result as $row) {
            $video = JTable::getInstance('VideosCategory', 'CTable');
            $video->bind($row);
            $data[] = $video;
        }

        return $data;
    }

    public function getPagination() {
        return $this->_pagination;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getUserTotalVideos($userId) {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $model = CFactory::getModel('videos');

        $filters = array
            (
            'creator' => $userId,
            'status' => 'ready',
            'sorting' => $jinput->get('sort', 'latest', 'STRING') //JRequest::getVar('sort', 'latest')
        );
        $videos = $model->getVideos($filters, false, false);
        return $videos;
    }

    public function deleteVideoWalls($id) {
        if (!$id)
            return;
        $db = $this->getDBO();
        $query = 'DELETE FROM ' . $db->quoteName('#__community_wall')
                . ' WHERE ' . $db->quoteName('contentid') . ' = ' . $db->quote($id)
                . ' AND ' . $db->quoteName('type') . ' = ' . $db->quote('videos');
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return true;
    }

    public function deleteVideoActivities($id = 0) {
        if (!$id)
            return;
        $db = $this->getDBO();
        $query = 'DELETE FROM ' . $db->quoteName('#__community_activities')
                . ' WHERE ' . $db->quoteName('app') . ' = ' . $db->quote('videos')
                . ' AND ' . $db->quoteName('cid') . ' = ' . $db->quote($id);
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return true;
    }

    /**
     * Returns Group's videos
     *
     * @access public
     * @param integer the id of the group
     */
    public function getGroupVideos($groupid, $categoryid = "", $limit = "") {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $filter = array(
            'groupid' => $groupid,
            'published' => 1,
            'status' => 'ready',
            'category_id' => $categoryid,
            'creator_type' => VIDEO_GROUP_TYPE,
            'sorting' => $jinput->get('sort', 'latest', 'STRING'), //JRequest::getVar('sort', 'latest'),
            'limit' => $limit
        );

        $videos = $this->getVideos($filter);

        return $videos;
    }

    /**
     *
     * @return type
     */
    public function getPendingVideos() {
        $filter = array('status' => 'pending');
        return $this->getVideos($filter, false, true, false);
    }

    /**
     * Get the count of the videos from specific user
     * */
    public function getVideosCount($userId = 0, $videoType = VIDEO_USER_TYPE) {
        if ($userId == 0)
            return 0;

        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM '
                . $db->quoteName('#__community_videos') . ' AS a '
                . 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($userId) . ' '
                . 'AND ' . $db->quoteName('creator_type') . '=' . $db->Quote($videoType);

        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    /**
     * Retrieve a list of popular videos from the site.
     *
     * @param   int $limit  The total number of records to return.
     * */
    public function getPopularVideos($limit = 0) {
        $filter = array(
            'published' => 1,
            'status' => 'ready',
            'sorting' => 'mostviews',
            'limit' => $limit
        );

        $result = $this->getVideos($filter);
        $videos = array();
        foreach ($result as $row) {
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($row->id);
            $videos[] = $video;
        }
        return $videos;
    }

    // A user updated his view permission, change the permission level for
    // all videos
    public function updatePermission($userid, $permission) {
        $db = $this->getDBO();
        $query = 'UPDATE ' . $db->quoteName('#__community_videos')
                . ' SET ' . $db->quoteName('permissions') . ' = ' . $db->Quote($permission)
                . ' WHERE ' . $db->quoteName('creator') . ' = ' . $db->Quote($userid)
                . ' AND ' . $db->quoteName('groupid') . ' = ' . $db->quote(0);

        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $this;
    }

    /**
     * Return total videos for the day for the specific user.
     *
     * @param	string	$userId	The specific userid.
     * */
    public function getTotalToday($userId) {
        $db = JFactory::getDBO();
        $date = JFactory::getDate();

        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_videos') . ' AS a WHERE '
                . $db->quoteName('creator') . '=' . $db->Quote($userId) . ' '
                . 'AND TO_DAYS(' . $db->Quote($date->toSql(true)) . ') - TO_DAYS( DATE_ADD( a.' . $db->quoteName('created') . ' , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = 0 ';

        $db->setQuery($query);
        return $db->loadResult();
    }

    /*
      since 2.6
     */

    public function getInviteListByName($namePrefix, $userid, $cid, $limitstart = 0, $limit = 8) {
        $db = $this->getDBO();
        $my = CFactory::getUser();

        $andName = '';
        $config = CFactory::getConfig();
        $nameField = $config->getString('displayname');
        if (!empty($namePrefix)) {
            $andName = ' AND b.' . $db->quoteName($nameField) . ' LIKE ' . $db->Quote('%' . $namePrefix . '%');
        }

        //we will treat differently for member's video and group's video
        $video = JTable::getInstance('Video', 'CTable');
        $video->load($cid);
        if ($video->groupid) {
            $countQuery = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('memberid') . '))  FROM ' . $db->quoteName('#__community_groups_members') . ' AS a ';
            $listQuery = 'SELECT DISTINCT(a.' . $db->quoteName('memberid') . ') AS id  FROM ' . $db->quoteName('#__community_groups_members') . ' AS a ';
            $joinQuery = ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                    . ' ON a.' . $db->quoteName('memberid') . '=b.' . $db->quoteName('id')
                    . ' AND a.' . $db->quoteName('approved') . '=' . $db->Quote(1)
                    . ' AND a.' . $db->quoteName('memberid') . '!=' . $db->Quote($my->id)
                    . ' AND a.' . $db->quoteName('groupid') . '=' . $db->Quote($video->groupid)
                    . ' WHERE NOT EXISTS (SELECT e.' . $db->quoteName('userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_videos_tag') . ' AS e  '
                    . ' WHERE e.' . $db->quoteName('videoid') . ' = ' . $db->Quote($cid)
                    . ' AND e.' . $db->quoteName('userid') . ' = a.' . $db->quoteName('memberid')
                    . ')';
        } else {
            $countQuery = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('connect_to') . ')) FROM ' . $db->quoteName('#__community_connection') . ' AS a ';
            $listQuery = 'SELECT DISTINCT(a.' . $db->quoteName('connect_to') . ') AS id  FROM ' . $db->quoteName('#__community_connection') . ' AS a ';
            $joinQuery = ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                    . ' ON a.' . $db->quoteName('connect_from') . '=' . $db->Quote($userid)
                    . ' AND a.' . $db->quoteName('connect_to') . '=b.' . $db->quoteName('id')
                    . ' AND a.' . $db->quoteName('status') . '=' . $db->Quote('1')
                    . ' AND b.' . $db->quoteName('block') . '=' . $db->Quote('0')
                    . ' WHERE NOT EXISTS ( SELECT d.' . $db->quoteName('blocked_userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_blocklist') . ' AS d  '
                    . ' WHERE d.' . $db->quoteName('userid') . ' = ' . $db->Quote($userid)
                    . ' AND d.' . $db->quoteName('blocked_userid') . ' = a.' . $db->quoteName('connect_to') . ')'
                    . ' AND NOT EXISTS (SELECT e.' . $db->quoteName('userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_videos_tag') . ' AS e  '
                    . ' WHERE e.' . $db->quoteName('videoid') . ' = ' . $db->Quote($cid)
                    . ' AND e.' . $db->quoteName('userid') . ' = a.' . $db->quoteName('connect_to')
                    . ')';
        }
        $query = $listQuery . $joinQuery . $andName
                . ' ORDER BY b.' . $db->quoteName($nameField)
                . ' LIMIT ' . $limitstart . ',' . $limit;
        $db->setQuery($query);
        $friends = $db->loadColumn();

        //calculate total
        $query = $countQuery . $joinQuery . $andName;
        $db->setQuery($query);
        $this->total = $db->loadResult();
        //friend yourself
        if ($my->id) {
            if ($namePrefix === '') {
                $found = false;
            } else {
                $found = JString::strpos($my->getDisplayName(), $namePrefix);
            }
            if ($namePrefix == '' || $found || $found === 0) {
                array_unshift($friends, $my->id);
                $this->total = $this->total + 1;
            }
        }
        return $friends;
    }

    public function getTotalSiteVideos() {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_videos') . ' '
                . 'WHERE ' . $db->quoteName('published') . '=' . $db->Quote(1);

        $db->setQuery($query);
        $total = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $total;
    }

}

abstract class CVideoProvider extends JObject {

    abstract function getThumbnail();

    abstract function getTitle();

    abstract function getDuration();

    abstract function getType();

    abstract function getViewHTML($videoId, $videoWidth, $videoHeight);

    public function __construct($db = null) {
        parent::__construct();
    }

    /**
     * Initialize the provider with video url resource
     */
    public function init($url) {
        $this->url = $url;
        $this->videoId = $this->getId();
    }

    /**
     * Return embedded code
     *
     * @param type $videoId
     * @param type $videoWidth
     * @param type $videoHeight
     * @return type
     *
     */
    public function getEmbedCode($videoId, $videoWidth, $videoHeight) {
        return $this->getViewHTML($videoId, $videoWidth, $videoHeight);
    }

    /**
     * Return true if the video is valid.
     * This function uses a typical video privider method where they normally provide
     * a XML feed file to extract all the video info
     * @return type Boolean
     */
    public function isValid() {
        // Connect and get the remote video
        // Simple check, make sure video id exist
        if (empty($this->videoId)) {
            $this->setError(JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_ID_ERROR'));
            return false;
        }
        // Youtube might return 'Video not found' in the content file
        $this->xmlContent = CRemoteHelper::getContent($this->getFeedUrl());
        if ($this->xmlContent == false) {
            $this->setError(JText::_('COM_COMMUNITY_VIDEOS_FETCHING_VIDEO_ERROR'));
            return false;
        }


        return true;
    }

}
