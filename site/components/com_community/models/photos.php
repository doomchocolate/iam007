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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

// Deprecated since 1.8.x to support older modules / plugins
class CommunityModelPhotos extends JCCModel implements CLimitsInterface {

    var $_pagination;
    var $total;
    var $test;

    public function CommunityModelPhotos() {
        parent::JCCModel();
        $config = CFactory::getConfig();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        // Get the pagination request variables
        $limit = $config->getString('listlimit');
        $limitstart = $jinput->request->get('limitstart', 0, 'INT'); //JRequest::getVar('limitstart', 0, 'REQUEST');

        if (empty($limitstart)) {
            $limitstart = $jinput->get('start', 0, 'INT');
        }

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Retrieves total number of photos from the site.
     * @param   none
     *
     * @return  int $total  Total number of photos.
     * */
    public function getTotalSitePhotos() {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('published') . '=' . $db->Quote(1);

        $db->setQuery($query);
        $total = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $total;
    }

    public function cleanUpTokens() {
        $date = JFactory::getDate();
        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_photos_tokens') . ' '
                . 'WHERE ' . $db->quoteName('datetime') . '<= DATE_SUB(' . $db->Quote($date->toSql()) . ', INTERVAL 1 HOUR)';

        $db->setQuery($query);
        $db->query();
    }

    public function getUserUploadToken($userId) {
        $db = JFactory::getDBO();

        $query = 'SELECT * FROM '
                . $db->quoteName('#__community_photos_tokens') . ' '
                . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($userId);

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    public function addUserUploadSession($token) {
        $db = JFactory::getDBO();

        $db->insertObject('#__community_photos_tokens', $token);

        return $this;
    }

    public function update($data, $type = 'photo') {
        // Set creation date
        if (!isset($data->created)) {
            $today = JFactory::getDate();
            $data->created = $today->toSql();
        }

        if (isset($data->id) && $data->id != 0)
            $func = '_update' . JString::ucfirst($type);
        else
            $func = '_create' . JString::ucfirst($type);

        return $this->$func($data);
    }

    // A user updated his view permission, change the permission level for
    // all album and photos
    public function updatePermission($userid, $permission) {
        $db = $this->getDBO();
        $query = 'UPDATE #__community_photos_albums SET `permissions`='
                . $db->Quote($permission)
                . ' WHERE `creator`='
                . $db->Quote($userid);
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        $query = 'UPDATE #__community_photos SET `permissions`='
                . $db->Quote($permission)
                . ' WHERE `creator`='
                . $db->Quote($userid);
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // update permissions in activity streams as well
        $activityModel = CFactory::getModel('activities');
        $activityModel->updatePermission($permission, null, $userid, 'photos');

        return $this;
    }

    public function updatePermissionByGroup($groupid, $permission) {
        $db = $this->getDBO();
        $query = 'UPDATE ' . $db->quoteName('#__community_photos_albums')
                . ' SET ' . $db->quoteName('permissions') . ' = '
                . $db->Quote($permission)
                . ' WHERE ' . $db->quoteName('groupid') . ' = '
                . $db->Quote($groupid);
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        $query = 'SELECT ' . $db->quoteName('id')
                . ' FROM ' . $db->quoteName('#__community_photos_albums')
                . ' WHERE ' . $db->quoteName('groupid') . ' = '
                . $db->Quote($groupid);
        $db->setQuery($query);
        $albumIDs = $db->loadColumn();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        $albumIDs = implode(', ', $albumIDs);
        if ($albumIDs) {
            $query = 'UPDATE ' . $db->quoteName('#__community_photos')
                    . ' SET ' . $db->quoteName('permissions') . ' = '
                    . $db->Quote($permission)
                    . ' WHERE ' . $db->quoteName('albumid')
                    . ' IN (' . $albumIDs . ') ';
            $db->setQuery($query);
            $db->query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        // update permissions in activity streams as well
        $activityModel = CFactory::getModel('activities');
        $activityModel->updatePermissionByCid($permission, null, $albumIDs, 'photos');

        return $this;
    }

    public function updatePermissionByAlbum($albumid, $permissions) {
        $db = $this->getDBO();
        $query = 'UPDATE #__community_photos SET `permissions`=' . $db->Quote($permissions) . 'WHERE `albumid`=' . $db->Quote($albumid);
        $db->setQuery($query);
        $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $this;
    }

    private function _createPhoto($data) {
        $db = $this->getDBO();

        // Fix the directory separators.
        $data->image = CString::str_ireplace('\\', '/', $data->image);
        $data->thumbnail = CString::str_ireplace('\\', '/', $data->thumbnail);

        $db->insertObject('#__community_photos', $data);

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        $data->id = $db->insertid();

        return $data;
    }

    private function _createAlbum($data) {
        $db = $this->getDBO();

        // New record, insert it.
        $db->insertObject('#__community_photos_albums', $data);

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        $data->id = $db->insertid();

        return $data;
    }

    /**
     * Removes a photo from the database and the file.
     *
     * @access	public
     * @param	string 	User's id.
     * @returns boolean true upon success.
     */
    public function removePhoto($id, $type = PHOTOS_USER_TYPE) {
        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($id);
        $photo->delete();
    }

    public function get($id, $type = 'photos') {
        $func = '_get' . JString::ucfirst($type);
        return $this->$func($id);
    }

    public function getPagination() {
        return $this->_pagination;
    }

    public function getFirstPhoto($albumId = null) {
        $db = $this->getDBO();

        $query = 'SELECT a.* FROM ' . $db->quoteName('#__community_photos') . ' AS a WHERE albumid=' . $db->Quote($albumId) . ' ' .
                ' ORDER BY a.`ordering` LIMIT 1 ';

        $db->setQuery($query);
        $result = $db->loadObject();

        $album = JTable::getInstance('Photo', 'CTable');
        $album->bind($result);

        return $album;
    }

    /**
     * Return a list of photos from specific album
     *
     * @param	int	$id	The album id that we want to retrieve photos from
     */
    public function getAllPhotos($albumId = null, $photoType = PHOTOS_USER_TYPE, $limit = null, $permission = null, $orderType = 'DESC', $primaryOrdering = 'ordering') {
        $db = $this->getDBO();

        $where = ' WHERE b.`type` = ' . $db->Quote($photoType);

        if (!is_null($albumId)) {
            $where .= ' AND b.`id`'
                    . '=' . $db->Quote($albumId)
                    . ' AND a.`albumid`'
                    . '=' . $db->Quote($albumId);
        }

        // Only apply the permission if explicitly specified
        if (!is_null($permission)) {
            $where .= ' AND a.`permissions`'
                    . '=' . $db->Quote($permission);
        }

        $where .= ' AND a.`published`=' . $db->Quote(1);
        $limitWhere = '';

        if (!is_null($limit)) {
            $limit = ($limit < 0) ? 0 : $limit;
            $limitWhere .= ' LIMIT ' . $limit;
        }

        $query = 'SELECT a.* FROM ' . $db->quoteName('#__community_photos') . ' AS a';
        $query .= ' INNER JOIN ' . $db->quoteName('#__community_photos_albums') . ' AS b';
        $query .= ' ON a.`albumid` = b.`id`';
        $query .= $where;

        $query .= ' ORDER BY ';
        $query .= ( $primaryOrdering == 'ordering' ) ? 'a.`ordering`, a.`created` ' : 'a.`created` ';
        $query .= $orderType;

        $query .= $limitWhere;

        $db->setQuery($query);

        $result = $db->loadObjectList();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }


        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Photo', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /**
     * Return a list of photos from specific album
     *
     * @param	int	$id	The album id that we want to retrieve photos from
     * @param boolean $includeUnpublished will include the photos which is unpublished
     */
    public function getPhotos($id, $limit = null, $limitstart = null, $includeUnpublished = false) {
        $db = $this->getDBO();

        // Get limit
        $limit = ( is_null($limit) ) ? $this->getState('limit') : $limit;
        $limitstart = ( is_null($limitstart) ) ? $this->getState('limitstart') : $limitstart;

        // Get total photos from specific album
        $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($id);

        $db->setQuery($query);
        $total = $db->loadResult();
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // Appy pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        //include unpublished photo or not
        if (!$includeUnpublished) {
            $includeUnpublished = ' AND `published`=' . $db->Quote(1);
        } else {
            $includeUnpublished = ''; //include
        }

        //var_dump($limitstart);
        // Get all photos from specific albumid
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($id) . ' '
                . $includeUnpublished
                . ' ORDER BY `ordering` ASC '
                . 'LIMIT ' . $limitstart . ',' . $limit;

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        $data = array();
        foreach ($result as $row) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->bind($row);
            $data[] = $photo;
        }

        return $data;
    }

    /**
     * @param	integer albumid Unique if of the album
     */
    public function getAlbum($albumId) {
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($albumId);

        return $album;
    }

    /**
     * Return the
     * @param type $typeId is userid for user type and group id for group type
     * @param type $type
     * @return type
     */
    public function getDefaultAlbum($typeId, $type = PHOTOS_USER_TYPE) {
        $db = $this->getDBO();
        $query = '';
        switch ($type) {
            case PHOTOS_GROUP_TYPE:
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                        . 'WHERE ' . $db->quoteName('groupid') . '=' . $db->Quote($typeId) . ' '
                        . 'AND ' . $db->quoteName('default') . '=' . '1';
                break;
            default:
                $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums') . ' '
                        . 'WHERE '
                        . $db->quoteName('creator') . '=' . $db->Quote($typeId) . ' '
                        . 'AND ' . $db->quoteName('groupid') . '=' . '0' . ' '
                        . 'AND ' . $db->quoteName('default') . '=' . '1';
        }


        $db->setQuery($query);

        $result = $db->loadObject();

        // if default album exist, return as album type
        if ($result) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($result);
            $result = $album;
        }

        return $result;
    }

    /**
     * Return total photos in a given album id.
     *
     * @param	int	$id	The album id.
     */
    public function getTotalPhotos($albumId) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($albumId);

        $db->setQuery($query);
        $total = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return $total;
    }

    public function getAllAlbums($userId = 0, $limit = 0) {
        $db = $this->getDBO();

        $isAdmin = (int) COwnerHelper::isCommunityAdmin();

        // Get limit
        $limit = $limit == 0 ? $this->getState('limit') : $limit;
        $limitstart = $this->getState('limitstart');
        $extraSQL = '';

        $permissions = ($userId == 0) ? 0 : 20;
        $permissions = COwnerHelper::isCommunityAdmin() ? 40 : $permissions;

        $extraSQL .= ' WHERE ( permissions <=' . $db->Quote($permissions) . ' OR (creator=' . $db->Quote($userId) . ' AND permissions <=' . $db->Quote(40) . ') )';
        //need to grab friends' album that has "Friends only" permission as well
        $friendmodel = CFactory::getModel('friends');
        $friends = $friendmodel->getFriendIds($userId);

        if (!empty($friends)) {
            $extraSQL .= ' OR (creator IN(' . implode(',', $friends) . ') AND permissions = ' . $db->Quote(30) . ') ';
        }

        /* if not administrator than we'll filter profile cover album */
        if (!$isAdmin) {
            $extraSQL .= ' AND ( ';
            /* get own profile' cover album */
            $extraSQL .= ' ( ' . $db->quoteName('type') . ' = ' . $db->quote('profile.cover') . ' AND ' . $db->quoteName('creator') . ' = ' . $userId . ' ) ';
            /* or get none profile' cover album */
            $extraSQL .= ' OR ( ' . $db->quoteName('type') . ' != ' . $db->quote('profile.cover') . ' ) ';
            $extraSQL .= ' ) ';
        }

        $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos_albums');
        $query .= $extraSQL;
        $query .= " ORDER BY `created` DESC ";

        $db->setQuery($query);
        $result = $db->loadObjectList();
        $total = count($result);
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // Appy pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $db->setQuery($query . 'LIMIT ' . $limitstart . ',' . $limit);
        $result = $db->loadObjectList();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // Update their correct Thumbnails and check album permissions
        //$this->_updateThumbnail($result);

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $album->thumbnail = $album->getCoverThumbPath();

            $lastupdate = new DateTime($album->lastupdated);

            if (array_key_exists($lastupdate->format('U'), $data)) {
                $data[$lastupdate->format('U') + 1] = $album;
            } else {
                $data[$lastupdate->format('U')] = $album;
            }
        }

        krsort($data);
        $tmpData = array();

        foreach ($data as $_data) {
            $tmpData[] = $_data;
        }

        return $tmpData;
    }

    public function checkAlbumsPermissions($row, $myId) {
        switch ($row->permissions) {
            case 0:
                $result = true;
                break;
            case 20:
                $result = !empty($myId) ? true : false;
                break;
            case 30:
                $result = CFriendsHelper::isConnected($row->creator, $myId) ? true : false;
                break;
            case 40:
                $result = $row->creator == $myId ? true : false;
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * Get site wide albums
     *
     * */
    public function getSiteAlbums($type = PHOTOS_USER_TYPE) {
        $db = $this->getDBO();
        $searchType = '';

        if ($type == PHOTOS_GROUP_TYPE) {
            $searchType = PHOTOS_GROUP_TYPE;
        } else {
            $searchType = PHOTOS_USER_TYPE;
        }

        // Get limit
        $limit = $this->getState('limit');
        $limitstart = $this->getState('limitstart');

        // Get total albums
        $query = 'SELECT COUNT(DISTINCT(a.id)) '
                . 'FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos') . ' AS b '
                . 'ON a.id=b.albumid '
                . 'WHERE a.type=' . $db->Quote($searchType);

        $db->setQuery($query);
        $total = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // Appy pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $where = ' WHERE a.type=' . $db->Quote($searchType);
        $result = $this->getAlbumPhotoCount($where, $limit, $limitstart);

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    public function getAlbumCount($where = '') {

        $db = $this->getDBO();
        // Get total albums
        $query = 'SELECT COUNT(*) '
                . 'FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . $where;

        $db->setQuery($query);
        $total = $db->loadResult();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $total;
    }

    public function getAlbumPhotoCount($where = '', $limit = NULL, $limitstart = NULL) {

        $db = $this->getDBO();

        $query = 'SELECT a.*, '
                . 'COUNT( DISTINCT(b.id) ) AS count, '
                . 'MAX(b.created) AS lastupdated, '
                . 'c.thumbnail as thumbnail, '
                . 'c.storage AS storage, '
                . 'c.id as photoid '
                . 'FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . 'LEFT JOIN ' . $db->quoteName('#__community_photos') . ' AS b '
                . 'ON a.id=b.albumid '
                . 'LEFT JOIN ' . $db->quoteName('#__community_photos') . ' AS c '
                . 'ON a.photoid=c.id '
                . $where
                . 'GROUP BY a.id '
                . ' ORDER BY a.`created` DESC';

        if (!is_null($limit) && !is_null($limitstart)) {
            $query .= ' LIMIT ' . $limitstart . ',' . $limit;
        }

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }
        return $result;
    }

    public function getGroupAlbums($groupId = '', $pagination = false, $doubleLimit = false, $limit = "", $isAdmin = false, $creator = '') {
        $creatorfilter = (!$isAdmin && !empty($creator)) ? $creator : '';

        $result = $this->_getAlbums($groupId, PHOTOS_GROUP_TYPE, $pagination, $doubleLimit, $limit, $creatorfilter);

        return $result;
    }

    /**
     * Get the albums for specific user or site wide
     *
     * @param	userId	string	The specific user id
     *
     * */
    public function getAlbums($userId = '', $pagination = false, $doubleLimit = false) {
        return $this->_getAlbums($userId, PHOTOS_USER_TYPE, $pagination, $doubleLimit);
    }

    /**
     * This function is used to return profile album, excluding group and event album
     * @param string $userId
     * @param bool $pagination
     * @param bool $doubleLimit
     * @return array
     */
    public function getProfileAlbums($userId = '', $pagination = false, $doubleLimit = false){
        return $this->_getAlbums($userId, PHOTOS_PROFILE_TYPE, $pagination, $doubleLimit, '' );
    }

    public function _getAlbums($id, $type, $pagination = false, $doubleLimit = false, $limit = "", $creator = '') {
        $db = $this->getDBO();
        $extraSQL = ' WHERE a.type != ' . $db->Quote('');

        if (!empty($id) && $type == PHOTOS_GROUP_TYPE) {
            $extraSQL .= ' AND a.groupid=' . $db->Quote($id) . ' ';
            if (!empty($creator)) {
                $extraSQL .= ' AND a.creator=' . $db->Quote($creator) . ' ';
            }
        } else if (!empty($id) && $type == PHOTOS_USER_TYPE) {
            $extraSQL .= ' AND a.creator=' . $db->Quote($id) . ' ';
            // privacy

            $permission = CPrivacy::getAccessLevel(null, $id);
            $extraSQL .= ' AND a.permissions <=' . $db->Quote($permission) . ' ';
        }else if( !empty($id) && $type == PHOTOS_PROFILE_TYPE ){
            $extraSQL .= ' AND a.creator=' . $db->Quote($id)
                      .' AND a.groupid=' . $db->Quote(0) . ' AND a.eventid= '. $db->Quote(0)
                      .' AND a.type <> '.$db->quote('profile.cover');
        }

        // Get limit
        $limit = (!empty($limit)) ? $limit : $this->getState('limit');
        $limit = ( $doubleLimit ) ? $this->getState('limit') : $limit;
        $limitstart = $this->getState('limitstart');

        // Get total albums
        $total = $this->getAlbumCount($extraSQL);
        $this->total = $total;

        // Appy pagination
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }

        $result = ($pagination) ? $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart) : $this->getAlbumPhotoCount($extraSQL);

        /* filter results, album that has photos + all unpublished = not to be displayed
         * 				   album that has no photos = display
         */

        foreach ($result as $key => $res) {
            $temp = $this->getPhotos($res->id, null, null, true);
            $hasPhoto = true; //assume all photo is temp

            if (count($temp) > 0) {
                foreach ($temp as $tempPhoto) {
                    if ($tempPhoto->published == 1) {
                        $hasPhoto = false; // this album has photos, show this album
                        break;
                    }
                }
            } else {
                $hasPhoto = false;
            }

            if ($hasPhoto) {
                unset($result[$key]);
            }
        }

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $album->thumbnail = $album->getCoverThumbPath();

            $lastupdated = new DateTime($album->lastupdated);

            if (array_key_exists($lastupdated->format('U'), $data)) {
                $data[$lastupdated->format('U') + 1] = $album;
            } else {
                $data[$lastupdated->format('U')] = $album;
            }
        }
        krsort($data);
        $tmpData = array();

        foreach ($data as $_data) {
            $tmpData[] = $_data;
        }

        return $tmpData;
    }

    /*
     * Since 2.4
     * Currently used for Single Photo Album view's other albums only
     */

    public function _getOnlyAlbums($id, $type, $limitstart = "", $limit = "") {
        $db = $this->getDBO();
        $extraSQL = ' WHERE a.type = ' . $db->Quote($type);


        $extraSQL .= ' AND a.creator=' . $db->Quote($id) . ' ';
        // privacy

        $permission = CPrivacy::getAccessLevel(null, $id);
        $extraSQL .= ' AND a.permissions <=' . $db->Quote($permission) . ' ';



        // Get limit
        $limit = ($limit !== '') ? $limit : '';
        $limitstart = ($limitstart !== '') ? $limitstart : '';

        // Get total albums
        $total = $this->getAlbumCount($extraSQL);
        $this->total = $total;

        $extraSQL .= ' AND b.published =' . $db->Quote(1) . ' ';

        $result = ($limit === '' || $limitstart === '') ? $this->getAlbumPhotoCount($extraSQL) : $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart);

        /* filter results, album that has photos + all unpublished = not to be displayed
         * 				   album that has no photos = display
         */
        foreach ($result as $key => $res) {
            if ($res->count <= 0) {
                unset($result[$key]);
            }
        }

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);
        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /*
     * Since 2.6
     * Currently used for Single Photo Album view's other group albums only
     */

    public function _getOnlyGroupAlbums($id, $groupid, $type, $limitstart = "", $limit = "") {
        $db = $this->getDBO();

        $extraSQL = ' WHERE a.groupid=' . $db->Quote($groupid) . ' ';
        // privacy

        $permission = CPrivacy::getAccessLevel(null, $id);
        $extraSQL .= ' AND a.permissions <=' . $db->Quote($permission) . ' ';



        // Get limit
        $limit = ($limit !== '') ? $limit : '';
        $limitstart = ($limitstart !== '') ? $limitstart : '';

        // Get total albums
        $total = $this->getAlbumCount($extraSQL);
        $this->total = $total;

        $extraSQL .= ' AND b.published =' . $db->Quote(1) . ' ';
        $result = ($limit === '' || $limitstart === '') ? $this->getAlbumPhotoCount($extraSQL) : $this->getAlbumPhotoCount($extraSQL, $limit, $limitstart);

        /* filter results, album that has photos + all unpublished = not to be displayed
         * 				   album that has no photos = display
         */
        foreach ($result as $key => $res) {
            if ($res->count <= 0) {
                unset($result[$key]);
            }
        }

        // Update their correct Thumbnails
        $this->_updateThumbnail($result);

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /*
     * Return true is the user is the owner/creator of the photo
     */

    public function isCreator($photoId, $userId) {
        // Guest has no album
        if ($userId == 0)
            return false;

        $db = $this->getDBO();

        $strSQL = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote($photoId) . ' '
                . 'AND creator=' . $db->Quote($userId);

        $db->setQuery($strSQL);
        $result = $db->loadResult();

        return $result;
    }

    /**
     * Return CPhoto object
     */
    public function getPhoto($id) {
        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($id);

        return $photo;
    }

    /**
     * Get the count of the photos from specific user or groups.
     * @param id user or group id
     * */
    public function getPhotosCount($id, $photoType = PHOTOS_USER_TYPE) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM '
                . $db->quoteName('#__community_photos') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos_albums') . ' AS b '
                . 'ON a.albumid=b.id '
                . 'AND b.type=' . $db->Quote($photoType);

        if ($photoType == PHOTOS_GROUP_TYPE) {
            $query .= ' WHERE b.groupid=' . $db->Quote($id);
        } else {
            $query .= ' WHERE a.creator=' . $db->Quote($id);
        }
        $query .= ' AND `albumid`!=0';


        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public function getDefaultImage($albumId) {
        $db = $this->getDBO();

        $strSQL = 'SELECT b.* FROM ' . $db->quoteName('#__community_photos_albums') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos') . 'AS b '
                . 'WHERE a.id=' . $db->Quote($albumId) . ' '
                . 'AND a.photoid=b.id';

        //echo $strSQL;
        $db->setQuery($strSQL);
        $result = $db->loadObject();

        return $result;
    }

    /**
     *
     * Set the $photoId as the album cover of the album
     *
     * @param type $albumId
     * @param type $photoId
     * @return CommunityModelPhotos
     */
    public function setDefaultImage($albumId, $photoId) {
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($albumId);

        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($photoId);

        $thumbUri = str_replace(JURI::root(),'',$photo->getThumbURI());
        $album->setParam('thumbnail', $thumbUri);
        $album->setParam('thumbnail_id', $photoId);
        $album->photoid = $photoId;

        $album->store();

        return $this;
    }

    public function setOrdering($photos, $albumId) {
        $db = $this->getDBO();

        foreach ($photos as $id => $order) {
            $query = 'UPDATE ' . $db->quoteName('#__community_photos') . ' '
                    . 'SET ' . $db->quoteName('ordering') . '=' . $db->Quote($order) . ' '
                    . 'WHERE ' . $db->quoteName('id') . '=' . $db->Quote($id) . ' '
                    . 'AND ' . $db->quoteName('albumid') . '=' . $db->Quote($albumId);

            $db->setQuery($query);
            $db->query();

            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        return $this;
    }

    /**
     * Return true if the given id is a group photo
     * @param type $photoId
     * @return type
     */
    public function isGroupPhoto($photoId) {
        $db = $this->getDBO();

        $query = 'SELECT b.`type` FROM `#__community_photos` AS a';
        $query .= ' INNER JOIN `#__community_photos_albums` AS b';
        $query .= ' ON a.`albumid` = b.`id`';
        $query .= ' WHERE a.`id` = ' . $db->Quote($photoId);

        $db->setQuery($query);
        $type = $db->loadResult();

        return ($type == PHOTOS_GROUP_TYPE);
    }

    public function getPhotoGroupId($photoId) {
        $db = $this->getDBO();

        $query = 'SELECT b.`groupid` FROM `#__community_photos` AS a';
        $query .= ' INNER JOIN `#__community_photos_albums` AS b';
        $query .= ' ON a.`albumid` = b.`id`';
        $query .= ' WHERE a.`id` = ' . $db->Quote($photoId);
        $query .= ' AND b.`type` = ' . $db->Quote(PHOTOS_GROUP_TYPE);

        $db->setQuery($query);
        $type = $db->loadResult();

        return $type;
    }

    /**
     * Retrieve popular photos from the site.
     *
     * @param
     * @return
     * */
    public function getPopularPhotos($limit = 20, $permission = null) {
        $db = $this->getDBO();

        $query = 'SELECT * FROM #__community_photos '
                . 'WHERE ' . $db->quoteName('published') . '=' . $db->Quote(1);

        // Only apply the permission if explicitly specified
        if (!is_null($permission)) {
            $query .= ' AND' . $db->quoteName('permissions') . '=' . $db->Quote($permission);
        }

        $query .= ' ORDER BY ' . $db->quoteName('hits') . ' DESC '
                . 'LIMIT 0,' . $limit;

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $result = array();

        foreach ($rows as $row) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->bind($row);
            $result[] = $photo;
        }

        return $result;
    }

    public function getInviteListByName($namePrefix, $userid, $cid, $limitstart = 0, $limit = 8) {
        $db = $this->getDBO();
        $my = CFactory::getUser();
        $andName = '';
        $config = CFactory::getConfig();
        $nameField = $config->getString('displayname');
        if (!empty($namePrefix)) {
            $andName = ' AND b.' . $db->quoteName($nameField) . ' LIKE ' . $db->Quote('%' . $namePrefix . '%');
        }

        //we will treat differently for member's photo and group's photo
        $photo = JTable::getInstance('Photo', 'CTable');
        $photo->load($cid);
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($photo->albumid);
        if ($album->groupid) {
            $countQuery = 'SELECT COUNT(DISTINCT(a.' . $db->quoteName('memberid') . '))  FROM ' . $db->quoteName('#__community_groups_members') . ' AS a ';
            $listQuery = 'SELECT DISTINCT(a.' . $db->quoteName('memberid') . ') AS id  FROM ' . $db->quoteName('#__community_groups_members') . ' AS a ';
            $joinQuery = ' INNER JOIN ' . $db->quoteName('#__users') . ' AS b '
                    . ' ON a.' . $db->quoteName('memberid') . '=b.' . $db->quoteName('id')
                    . ' AND a.' . $db->quoteName('approved') . '=' . $db->Quote(1)
                    . ' AND a.' . $db->quoteName('memberid') . '!=' . $db->Quote($my->id)
                    . ' AND a.' . $db->quoteName('groupid') . '=' . $db->Quote($album->groupid)
                    . ' WHERE NOT EXISTS (SELECT e.' . $db->quoteName('userid') . ' as id'
                    . ' FROM ' . $db->quoteName('#__community_photos_tag') . ' AS e  '
                    . ' WHERE e.' . $db->quoteName('photoid') . ' = ' . $db->Quote($cid)
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
                    . ' FROM ' . $db->quoteName('#__community_photos_tag') . ' AS e  '
                    . ' WHERE e.' . $db->quoteName('photoid') . ' = ' . $db->Quote($cid)
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

    /**
     * Return total photos for the day for the specific user.
     *
     * @param	string	$userId	The specific userid.
     * */
    function getTotalToday($userId) {
        $db = JFactory::getDBO();
        $date = JFactory::getDate();

        $query = 'SELECT COUNT(*) FROM #__community_photos AS a WHERE '
                . $db->quoteName('creator') . '=' . $db->Quote($userId) . ' '
                . 'AND TO_DAYS(' . $db->Quote($date->toSql(true)) . ') - TO_DAYS( DATE_ADD( a.`created` , INTERVAL ' . $date->getOffset() . ' HOUR ) ) = 0 '
                . ' AND a.status <> '.$db->Quote('temp');

        $db->setQuery($query);
        return $db->loadResult();
    }

    private function _updateThumbnail(&$photos) {
        if (!empty($photos)) {
            foreach ($photos as &$row) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->bind($row);
                $photo->id = $row->photoid; // the id was photo_album id, need to fix it
                $row->thumbnail = $photo->getThumbURI();
            }
        }
    }

    function getPhotoList($data) {

        $db = JFactory::getDBO();

        switch ($data['type']) {
            case 1:
                $extraSQL = 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($data['id']) . ' LIMIT ' . $data['start'] . ' ,' . $data['no'];
                break;
            case 2:
                $extraSQL = 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($data['id']) . ' LIMIT ' . $data['start'] . ' ,' . $data['no'];
                break;
            case 3:
                $extraSQL = 'LIMIT 0,' . $data['no'];
                break;
        }
        $query = 'SELECT * FROM #__community_photos ' . $extraSQL;

        $db->setQuery($query);
        $result = $db->loadObjectList();
        $data = array();

        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $data[] = $album;
        }
        return $data;
    }

    /**
     * Method to get Album Photo Count
     * @param $albumId -  Album id
     * @return photo count for specific album
     * */
    public function getAlbumPhotosCount($albumId) {
        $db = $this->getDBO();

        $query = 'SELECT COUNT(1) FROM '
                . $db->quoteName('#__community_photos') . ' AS a '
                . 'INNER JOIN ' . $db->quoteName('#__community_photos_albums') . ' AS b '
                . 'ON a.albumid=b.id '
                . 'WHERE b.id=' . $db->Quote($albumId)
                . 'AND a.status != "temp"';

        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public function getUserAllAlbums($userId) {
        $db = $this->getDBO();

        $query = 'SELECT * FROM '
                . $db->quoteName('#__community_photos_albums')
                . 'WHERE ' . $db->quoteName('creator') . ' = ' . $db->Quote($userId);

        $db->setQuery($query);
        $result = $db->loadObjectList();

        $data = array();
        foreach ($result as $row) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->bind($row);
            $album->thumbnail = $album->getCoverThumbPath();
            $data[] = $album;
        }

        return $data;
    }
}