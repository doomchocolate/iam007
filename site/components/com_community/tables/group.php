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

class CTableGroup extends JTable {

    var $id = null;
    var $published = null;
    var $ownerid = null;
    var $categoryid = null;
    var $name = null;
    var $description = null;
    var $email = null;
    var $website = null;
    var $approvals = null;
    var $created = null;
    var $avatar = null;
    var $thumb = null;
    var $discusscount = null;
    var $wallcount = null;
    var $membercount = null;
    var $params = null;
    var $_pagination = null;
    var $storage = null;
    var $cover = null;
    var $hits = 0;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_groups', 'id', $db);
    }

    public function getPagination() {
        return $this->_pagination;
    }

    public function updateMembers() {
        $db = JFactory::getDBO();
        $query = 'SELECT m.* FROM '
                . $db->quoteName('#__community_groups_members') . ' AS m'
                . ' LEFT JOIN '
                . $db->quoteName('#__users') . ' AS u ON u.id = m.memberid'
                . ' WHERE ' . $db->quoteName('u.block') . ' = ' . $db->quote(0)
                . ' AND ' . $db->quoteName('m.groupid') . ' = ' . $db->quote($this->id)
                . ' AND ' . $db->quoteName('m.approved') . ' = ' . $db->quote(1);
        $db->setQuery();
        $row = $db->loadResult();
    }

    /**
     * Update all internal count without saving them
     */
    public function updateStats() {
        if ($this->id != 0) {
            $db = JFactory::getDBO();

            // @rule: Update the members count each time stored is executed.
            $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_groups_members') . ' AS a '
                    . 'JOIN ' . $db->quoteName('#__users') . ' AS b ON a.' . $db->quoteName('memberid') . '=b.' . $db->quoteName('id')
                    . 'AND b.' . $db->quoteName('block') . '=0 '
                    . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($this->id) . ' '
                    . 'AND ' . $db->quoteName('approved') . '=' . $db->Quote('1') . ' '
                    . 'AND permissions!=' . $db->Quote(COMMUNITY_GROUP_BANNED);

            $db->setQuery($query);
            $this->membercount = $db->loadResult();

            // @rule: Update the discussion count each time stored is executed.
            $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_groups_discuss') . ' '
                    . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($this->id);

            $db->setQuery($query);
            $this->discusscount = $db->loadResult();

            // @rule: Update the wall count each time stored is executed.
            $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_activities') . ' '
                    . 'WHERE ' . $db->quoteName('cid') . '=' . $db->Quote($this->id) . ' '
                    . 'AND ' . $db->quoteName('app') . '=' . $db->Quote('groups.wall');

            $db->setQuery($query);
            $this->wallcount = $db->loadResult();
        }
    }

    public function check() {
        // Santinise data
        $safeHtmlFilter = CFactory::getInputFilter();
        $this->name = $safeHtmlFilter->clean($this->name);
        $this->email = $safeHtmlFilter->clean($this->email);
        $this->website = $safeHtmlFilter->clean($this->website);

        // Allow html tags
        $config = CFactory::getConfig();
        $safeHtmlFilter = CFactory::getInputFilter($config->get('allowhtml'));
        $this->description = $safeHtmlFilter->clean($this->description);

        return true;
    }

    /**
     * Binds an array into this object's property
     *
     * @access	public
     * @param	$data	mixed	An associative array or object
     * */
    public function store($updateNulls = false) {
        if (!$this->check()) {
            return false;
        }

        // Update activities as necessary
        $activityModel = CFactory::getModel('activities');
        $activityModel->update(array('groupid' => $this->id), array('group_access' => $this->approvals));

        return parent::store();
    }

    /**
     * Return the category name for the current group
     *
     * @return string	The category name
     * */
    public function getCategoryName() {
        $category = JTable::getInstance('GroupCategory', 'CTable');
        $category->load($this->categoryid);

        return $category->name;
    }

    /**
     * Get large avatar use for cropping
     * @return string
     */
    public function getLargeAvatar() {
        $config = CFactory::getConfig();
        $largeAvatar = $config->getString('imagefolder') . '/avatar/group/group-' . JFile::getName($this->avatar);
        if (JFile::exists($largeAvatar)) {
            return CUrlHelper::avatarURI($largeAvatar) . '?' . md5(time()); /* adding random param to prevent browser caching */
        } else {
            return $this->getAvatar();
        }
    }

    /**
     * Return the full URL path for the specific image
     *
     * @param	string	$type	The type of avatar to look for 'thumb' or 'avatar'. Deprecated since 1.8
     * @return string	The avatar's URI
     * */
    public function getAvatar() {

        // Get the avatar path. Some maintance/cleaning work: We no longer store
        // the default avatar in db. If the default avatar is found, we reset it
        // to empty. In next release, we'll rewrite this portion accordingly.
        // We allow the default avatar to be template specific.
        if ($this->avatar == 'components/com_community/assets/group.jpg') {
            $this->avatar = '';
            $this->store();
        }

        // For group avatars that are stored in a remote location, we should return the proper path.
        if ($this->storage != 'file' && !empty($this->avatar)) {
            $storage = CStorage::getStorage($this->storage);
            return $storage->getURI($this->avatar);
        }


        $avatar = CUrlHelper::avatarURI($this->avatar, 'group.png');

        return $avatar;
    }

    public function getThumbAvatar() {
        if ($this->thumb == 'components/com_community/assets/group_thumb.jpg') {
            $this->thumb = '';
            $this->store();
        }

        // For group avatars that are stored in a remote location, we should return the proper path.
        if ($this->storage != 'file' && !empty($this->thumb)) {
            $storage = CStorage::getStorage($this->storage);
            return $storage->getURI($this->thumb);
        }


        $thumb = CUrlHelper::avatarURI($this->thumb, 'group_thumb.png');

        return $thumb;
    }

    /**
     * Return the owner's name for the current group
     *
     * @return string	The owner's name
     * */
    public function getOwnerName() {
        $user = CFactory::getUser($this->ownerid);
        return $user->getDisplayName();
    }

    public function getParams() {
        $params = new CParameter($this->params);

        return $params;
    }

    /**
     * Method to determine whether the specific user is a member of a group
     *
     * @param	string	User's id
     * @return boolean True if user is registered and false otherwise
     * */
    public function isMember($userid) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(*) FROM '
                . $db->quoteName('#__community_groups_members') . ' '
                . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($this->id) . ' '
                . 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
                . 'AND ' . $db->quoteName('approved') . '=' . $db->Quote('1');
        $db->setQuery($query);

        $status = ( $db->loadResult() > 0 ) ? true : false;

        return $status;
    }

    public function isBanned($userid) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(*) FROM '
                . $db->quoteName('#__community_groups_members') . ' '
                . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($this->id) . ' '
                . 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
                . 'AND ' . $db->quoteName('permissions') . '=' . $db->Quote(COMMUNITY_GROUP_BANNED);

        $db->setQuery($query);

        $status = ( $db->loadResult() > 0 ) ? true : false;

        return $status;
    }

    public function isAdmin($userid) {
        if ($this->id == 0)
            return false;

        if ($userid == 0)
            return false;

        // the creator is also the admin
        if ($userid == $this->ownerid)
            return true;

        $db = $this->getDBO();

        $query = 'SELECT COUNT(*) FROM '
                . $db->quoteName('#__community_groups_members') . ' '
                . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($this->id) . ' '
                . 'AND ' . $db->quoteName('memberid') . '=' . $db->Quote($userid)
                . 'AND ' . $db->quoteName('approved') . '=' . $db->Quote('1')
                . 'AND ' . $db->quoteName('permissions') . '=' . $db->Quote(COMMUNITY_GROUP_ADMIN);
        $db->setQuery($query);

        $status = ( $db->loadResult() > 0 ) ? true : false;

        return $status;
    }

    public function getLink($xhtml = false) {
        $link = CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->id, $xhtml);
        return $link;
    }

    public function getMembersCount() {
        return $this->membercount;
    }

    /**
     * Determines if the current group is a private group.
     * */
    public function isPrivate() {
        return $this->approvals == COMMUNITY_PRIVATE_GROUP;
    }

    /**
     * Determines if the current group is a public group.
     * */
    public function isPublic() {
        return $this->approvals == COMMUNITY_PUBLIC_GROUP;
    }

    /**
     * Return true if the user is allow to modify the tag
     */
    public function tagAllow($userid) {
        return $this->isAdmin($userid);
    }

    /**
     * Return the title of the object
     */
    public function tagGetTitle() {
        return $this->title;
    }

    /**
     * Allows caller to bind parameters from the request
     * @param	array 	$params		An array of values which keys should match with the parameter.
     */
    public function bindRequestParams() {
        // Default to current params
        $params = new CParameter($this->params);
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $discussordering = $jinput->request->get('discussordering', DISCUSSION_ORDER_BYLASTACTIVITY, 'NONE'); //JRequest::getVar( 'discussordering' , DISCUSSION_ORDER_BYLASTACTIVITY , 'REQUEST' );
        $params->set('discussordering', $discussordering);

        $photopermission = $jinput->request->get('photopermission', GROUP_PHOTO_PERMISSION_ADMINS, 'NONE'); //JRequest::getVar( 'photopermission' , GROUP_PHOTO_PERMISSION_ADMINS , 'REQUEST' );
        $params->set('photopermission', $photopermission);

        $videopermission = $jinput->request->get('videopermission', GROUP_VIDEO_PERMISSION_ADMINS, 'NONE'); //JRequest::getVar( 'videopermission' , GROUP_PHOTO_PERMISSION_ADMINS , 'REQUEST' );
        $params->set('videopermission', $videopermission);

        $eventpermission = $jinput->request->get('eventpermission', GROUP_EVENT_PERMISSION_ADMINS, 'NONE'); //JRequest::getVar( 'eventpermission' , GROUP_EVENT_PERMISSION_ADMINS , 'REQUEST' );
        $params->set('eventpermission', $eventpermission);

        $grouprecentphotos = JRequest::getInt('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT, 'REQUEST');
        $params->set('grouprecentphotos', $grouprecentphotos);

        $grouprecentvideos = JRequest::getInt('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT, 'REQUEST');
        $params->set('grouprecentvideos', $grouprecentvideos);

        $grouprecentevent = JRequest::getInt('grouprecentevents', GROUP_EVENT_RECENT_LIMIT, 'REQUEST');
        $params->set('grouprecentevents', $grouprecentevent);

        $newmembernotification = JRequest::getInt('newmembernotification', '1', 'REQUEST');
        $params->set('newmembernotification', $newmembernotification);

        $joinrequestnotification = JRequest::getInt('joinrequestnotification', '1', 'REQUEST');
        $params->set('joinrequestnotification', $joinrequestnotification);

        $wallnotification = JRequest::getInt('wallnotification', '1', 'REQUEST');
        $params->set('wallnotification', $wallnotification);

        $groupannouncementfilesharing = JRequest::getInt('groupannouncementfilesharing', '0', 'REQUEST');
        $params->set('groupannouncementfilesharing', $groupannouncementfilesharing);
        
        $this->params = $params->toString();

        return true;
    }

    /**
     * Allows caller to update the owner name
     */
    public function updateOwner($oldOwner, $newOwner) {
        if ($oldOwner == $newOwner) {
            return true;
        }

        // Add member if member does not exist.
        if (!$this->isMember($newOwner, $this->id)) {
            $data = new stdClass();
            $data->groupid = $this->id;
            $data->memberid = $newOwner;
            $data->approved = 1;
            $data->permissions = 1;

            // Add user to group members table
            $this->addMember($data);

            // Add the count.
            $this->updateStats($group->id);
        } else {
            // If member already exists, update their permission

            $member = JTable::getInstance('GroupMembers', 'CTable');
            $member->load($group->id, $newOwner);
            $member->permissions = '1';

            $member->store();
        }
    }

    /**
     *
     */
    public function addMember($data) {
        $db = $this->getDBO();

        // Test if user if already exists
        if (!$this->isMember($data->memberid, $data->groupid)) {
            $db->insertObject('#__community_groups_members', $data);
            $this->updateStats();
        }

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $data;
    }

    public function deleteMember($gid, $memberid) {
        $db = JFactory::getDBO();

        $sql = "DELETE FROM " . $db->quoteName("#__community_groups_members") . "
		    WHERE " . $db->quoteName("groupid") . "=" . $db->quote($gid) . "
		    AND " . $db->quoteName("memberid") . "=" . $db->quote($memberid);

        $db->setQuery($sql);
        $db->Query();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return true;
    }

    public function getAdmins($limit = 0, $randomize = false) {
        $mainframe = JFactory::getApplication();
        $limit = ($limit != 0) ? $limit : $mainframe->getCfg('list_limit');
        $jinput = $mainframe->input;
        $limitstart = $jinput->get('limitstart', 0, 'INT'); //JRequest::getInt( 'limitstart' , 0 );

        $query = 'SELECT ' . $this->_db->quoteName('memberid') . ' AS id, ' . $this->_db->quoteName('approved') . ' AS statusCode FROM '
                . $this->_db->quoteName('#__community_groups_members')
                . ' WHERE ' . $this->_db->quoteName('groupid') . ' = ' . $this->_db->Quote($this->id)
                . ' AND ' . $this->_db->quoteName('permissions') . ' IN (1,2)';

        if ($randomize) {
            $query .= ' ORDER BY RAND() ';
        }

        if (!is_null($limit)) {
            $query .= ' LIMIT ' . $limit;
        }
        $this->_db->setQuery($query);
        $result = $this->_db->loadObjectList();

        if ($this->_db->getErrorNum()) {
            JError::raiseError(500, $this->_db->stderr());
        }

        $query = 'SELECT COUNT(1) FROM '
                . $this->_db->quoteName('#__community_groups_members')
                . ' WHERE ' . $this->_db->quoteName('groupid') . ' = ' . $this->_db->Quote($this->id)
                . ' AND ' . $this->_db->quoteName('permissions') . ' IN (1,2)';
        $this->_db->setQuery($query);
        $total = $this->_db->loadResult();

        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');

            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        return $result;
    }

    public function removeAvatar() {
        if (JFile::exists($this->avatar)) {
            JFile::delete($this->avatar);
        }

        if (JFile::exists($this->thumb)) {
            JFile::delete($this->thumb);
        }

        $this->avatar = '';
        $this->thumb = '';
        $this->store();
    }

    public function setImage($path, $type = 'thumb') {
        CError::assert($path, '', '!empty', __FILE__, __LINE__);

        $db = $this->getDBO();

        // Fix the back quotes
        $path = CString::str_ireplace('\\', '/', $path);
        $type = JString::strtolower($type);

        // Test if the record exists.
        $oldFile = $this->$type;

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        if ($oldFile) {
            // File exists, try to remove old files first.
            $oldFile = CString::str_ireplace('/', '/', $oldFile);

            // If old file is default_thumb or default, we should not remove it.
            //
			// Need proper way to test it
            if (!JString::stristr($oldFile, 'group.jpg') && !JString::stristr($oldFile, 'group_thumb.jpg') && !JString::stristr($oldFile, 'default.jpg') && !JString::stristr($oldFile, 'default_thumb.jpg')) {
                jimport('joomla.filesystem.file');
                JFile::delete($oldFile);
            }
        }
        $this->$type = $path;
        $this->store();
    }

    /**
     * In 2.4, wall is removed and converted to stream data
     * On first load, import old wall to stream data
     */
    public function upgradeWallToStream() {
        $params = new CParameter($this->params);
        if ($params->get('stream') != 1) {
            $this->groupActivitiesMigrate();
            /*
              UPDATE `jos_community_activities` as a
              SET
              a.`title` = a.`content` ,
              a.`content` = '',
              a.`groupid`= a.`cid`,
              a.`comment_type` = 'groups.wall',
              a.`comment_id` = a.`id`,
              a.`like_type` = 'groups.wall',
              a.`like_id` = a.`id`,
              a.`params` = ''

              WHERE a.`app` = 'groups.wall' AND a.`groupid` IS NULL


              $query	= 'UPDATE '. $this->_db->quoteName('#__community_activities') . ' as a '
              .' SET '
              .' a.'. $this->_db->quoteName('title'). ' = '. $this->_db->quoteName('content'). ' , '
              .' a.'. $this->_db->quoteName('content'). ' = '. $this->_db->Quote(''). ', '
              .' a.'. $this->_db->quoteName('groupid'). '= a.'. $this->_db->quoteName('cid'). ', '
              .' a.'. $this->_db->quoteName('comment_type'). ' = '. $this->_db->Quote('groups.wall'). ', '
              .' a.'. $this->_db->quoteName('comment_id'). ' = a.'. $this->_db->quoteName('id'). ', '
              .' a.'. $this->_db->quoteName('like_type'). ' = '. $this->_db->Quote('groups.wall'). ', '
              .' a.'. $this->_db->quoteName('like_id'). ' = a.'. $this->_db->quoteName('id'). ', '
              .' a.'. $this->_db->quoteName('params'). ' = '. $this->_db->Quote('')
              .' WHERE '
              .' a.'. $this->_db->quoteName('app'). ' = '. $this->_db->Quote('groups.wall')
              .' AND a.'. $this->_db->quoteName('groupid'). ' IS NULL ';

              $this->_db->setQuery($query);
              $this->_db->Query();
             */
            // Mark this group as upgraded
            $params = new CParameter($this->params);
            $params->set('stream', 1);
            $this->params = $params->toString();

            // Store will upgrade save the params AND update stream group_access data
            $this->store();
        }
    }

    private function groupActivitiesMigrate() {
        $db = JFactory::getDBO();

        /* To check what is the version of jomsocial for current db */
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_activities')
                . ' WHERE ' . $db->quoteName('params') . ' LIKE ' . $db->Quote('%group.wall.create%') . ' AND ' . $db->quoteName('params') . ' LIKE ' . $db->Quote('%groupid=' . $this->id . '%');

        $db->setQuery($query);
        $results = $db->loadobjectList();

        if (!empty($results)) {
            // format : wall_activity_match['activity id'] = wall id
            $wall_activity_match = array(); // this is used to match id from activities with wall id

            foreach ($results as &$result) {
                // update the info
                $result = (array) $result;
                //change content to title
                $result['title'] = $result['content'];
                $result['content'] = ''; //empty content after assigned to title

                $wall_activity_match[$result['id']] = $result['comment_id'];

                //getting the group id out from the param
                $decoded_params = (array) json_decode($result['params']); //explode('=',$result['params']);
                if (isset($decoded_params['group_url'])) {
                    $group_url = $decoded_params['group_url'];
                    $group_url_arr = explode('=', $group_url);
                } else {
                    $group_url_arr = explode('=', $result['params']);
                }
                $group_id = $group_url_arr[count($group_url_arr) - 1];
                $result['groupid'] = $group_id; // set group id
                $result['target'] = $group_id; // set target as group id
                $result['cid'] = $group_id; // set cid as group id
                $result['params'] = ''; //empty params
                $result['like_id'] = $result['id']; // set like_id as id
                $result['comment_id'] = $result['id']; // set comment id to the current row id
                $result['eventid'] = 0;
                $result['like_type'] = 'groups.wall';
                $result['comment_type'] = 'groups.wall';
            }

            //echo '> Start to convert 2.2.x activities table<br/>';
            //echo '2.2.x has '.count($results).' activities to be converted.<br/>';
            // Lets update the converted row into the 2.4 format!
            foreach ($results as $res) {
                $tmp_res = $res;
                unset($tmp_res['created']); //created no need to update
                //echo 'Converting activity #'.$res['id'].' -- ';
                $tmp_result = (object) $tmp_res;
                $db->updateObject('#__community_activities', $tmp_result, 'id');
                if ($db->getErrorNum()) {
                    //echo 'Failed <br/>';
                } else {
                    //echo 'Succeed <br/>';
                }
            }
            //echo '> 2.2.x activities table conversion ends<br/><br/><br/>';

            /* lets update the wall content */
            if (!empty($wall_activity_match)) {
                //echo '> Start to convert 2.2.x wall table<br/> ';
                // narrow down the search with array
                $in = implode(',', $wall_activity_match);

                $query = 'SELECT * FROM ' . $db->quoteName('#__community_wall')
                        . ' WHERE ' . $db->quoteName('id') . ' IN (' . $in . ' ) ';

                $db->setQuery($query);

                $results = $db->loadobjectList();

                foreach ($results as $result) {
                    //extract the comments if there is any
                    $pos = strpos($result->comment, '<comment>');

                    if (!$pos) {
                        continue;
                    }

                    list($str, $comments) = explode('<comment>', $result->comment);
                    $comments_arr = json_decode(strip_tags(trim($comments), '</comment>'));

                    //delete this record... optional

                    $activity_id = array_search($result->id, $wall_activity_match);

                    foreach ($comments_arr as $comment) {
                        $dateObject = CTimeHelper::getDate($comment->date);
                        $date = $dateObject->Format('Y-m-d H:i:s');

                        //echo 'Inserting new wall base on wall #'.$result->id.' -- date: '.$date.' == ';

                        $data = array(
                            'contentid' => $activity_id,
                            'post_by' => $comment->creator,
                            'ip' => '', //leave empty because the ip is not stored in 2.2.x
                            'comment' => $comment->text,
                            'date' => $date,
                            'published' => 1,
                            'type' => 'groups.wall'
                        );

                        $tmp_data = (object) $data;
                        $db->insertObject('#__community_wall', $tmp_data);
                        if ($db->getErrorNum()) {
                            //echo 'Failed <br/>';
                        } else {
                            //echo 'Succeed <br/>';
                        }
                    }
                }
                //echo '> 2.2.x wall table conversion ends<br/><br/><br/>';
            }
        }
    }

    /**
     * [Store Cover path in db]
     * @param [string] $path [description]
     */
    public function setCover($path) {
        $this->cover = $path;
        return $this->store();
    }

    /**
     * Add hit count for group
     * @return [type] [description]
     */
    public function hit($pk = null) {
        $session = JFactory::getSession();
        if ($session->get('view-group-' . $this->id, false) == false) {
            parent::hit();
        }

        $session->set('view-group-' . $this->id, true);
    }

    /**
     * Get current Group cover
     * @return [string] [url]
     */
    public function getCover() {

        if (empty($this->cover)) {
            $this->cover = '';            
        } else { /* if not local than get remote storage */
            if ($this->storage != 'file') {
                $storage = CStorage::getStorage($this->storage);
                return $storage->getURI($this->cover);
            }
        }

        return CUrlHelper::coverURI($this->cover, 'cover-group-default.png');
    }

}
