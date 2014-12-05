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

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_community/tables');

class CTablePhoto extends JTable {

    var $id = null;
    var $albumid = null;
    var $caption = null;
    var $permissions = null;
    var $created = null;
    var $thumbnail = null;
    var $image = null;
    var $creator = null;
    var $published = null;
    var $original = null;
    var $filesize = null;
    var $storage = 'file';
    var $hits = 0;
    var $ordering = null;
    var $status = null;
    var $params = null;
    private $_params = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_photos', 'id', $db);
        $this->_params = new JRegistry($this->params);
    }

    /**
     * 	Allows us to test if the user has access to the album
     * */
    public function hasAccess($userId, $permissionType) {
        //CFactory::load( 'helpers' , 'owner' );
        // @rule: For super admin, regardless of what permission, they should be able to access
        if (COwnerHelper::isCommunityAdmin())
            return true;

        $album = JTable::getInstance('Album', 'CTable');
        $album->load($this->albumid);

        switch ($album->type) {
            case PHOTOS_USER_TYPE:

                if ($permissionType == 'delete') {
                    return $this->creator == $userId;
                }

                break;
            case PHOTOS_GROUP_TYPE:

                //CFactory::load( 'models' , 'groups' );
                $group = JTable::getInstance('Group', 'CTable');
                $group->load($album->groupid);

                if ($permissionType == 'delete') {
                    return $album->creator == $userId || $group->isAdmin($userId);
                }

                return false;
                break;
        }
    }

    public function getNextPhoto() {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_photos') . ' '
                . 'WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote($this->albumid) . ' '
                . 'ORDER BY ' . $db->quoteName('ordering') . ' ASC '
                . 'LIMIT 1';

        $db->setQuery($query);
        $result = $db->loadObject();

        $photo = JTable::getInstance('Photo', 'CTable');
        if (!is_null($result))
            $photo->bind($result);

        return $photo;
    }

    public function check() {
        //CFactory::load( 'helpers', 'string');
        // Santinise data
        $safeHtmlFilter = CFactory::getInputFilter();
        $this->caption = CStringHelper::nl2br($safeHtmlFilter->clean($this->caption));

        return true;
    }

    /**
     * Overrides parent store function as we need to clean up some variables
     * */
    public function store($updateNulls = false) {
        if (!$this->check()) {
            return false;
        }

        $this->image = CString::str_ireplace('\\', '/', $this->image);
        $this->thumbnail = CString::str_ireplace('\\', '/', $this->thumbnail);
        $this->original = CString::str_ireplace('\\', '/', $this->original);

        // Store params
        $this->params = $this->_params->toString();

        $result = parent::store();

        if ($this->status != 'temp' && $result) {
            // Changes in photos will affect the album. Do a store on album
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($this->albumid);
            $album->store();
        }

        return $result;
    }

    /**
     * Delete the photo, the original and the actual resized photos and thumbnails
     */
    public function delete($pk = null) {

        $storage = CStorage::getStorage($this->storage);
        $storage->delete($this->image);
        $storage->delete($this->thumbnail);

        /* We did store original than now time to remove it */
        if (!empty($this->original)) {
            /* Original do not sync with 3rd storage than we don't need to use $storage->delete; even it would be same */
            if (JFile::exists(JPATH_ROOT . '/' . $this->original)) {
                JFile::delete(JPATH_ROOT . '/' . $this->original);
            }
            $originalDir = dirname(JPATH_ROOT . '/' . $this->original);
            $files = JFolder::files($originalDir);
            /* If the original path is empty, we can delete it too */
            if (empty($files)) {
                JFolder::delete($originalDir);
            }
        }

        // if the photo is the album cover, set the album cover as default 0
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($this->albumid);
        $album->set('photoid', 0);
        $album->store();

        // delete the tags
        CFactory::load('libraries', 'phototagging');
        $phototagging = new CPhotoTagging();
        $phototagging->removeTagByPhoto($this->id);

        // delete the activities
        CActivities::remove('photos', $this->id);
        //Remove photo from activity stream
        CActivityStream::remove('photo.like', $this->id);

        // delete the comments
        $wall = CFactory::getModel('wall');
        $wall->deleteAllChildPosts($this->id, 'photos');

        /* And now finally we do delete this photo in database */
        /* return parent::delete(); */

        /**
         * @since 3.2
         * We do not delete database record for now. Leave it for cron !
         * @todo Considering local files should be deleted this time or not ? 
         */
        $this->published = 0;
        $this->status = 'delete';
        $this->store();
    }

    /**
     * Return the exact URL
     */
    public function getThumbURI() {
        // CDN or local
        $config = CFactory::getConfig();
        $file = $this->thumbnail;

        // Remote storage
        if ($this->storage != 'file') {

            $storage = CStorage::getStorage($this->storage);
            $uri = $storage->getURI($file);
            return $uri;
        }

        // Default avatar
        if (empty($file) || !JFile::exists(JPATH_ROOT . '/' . $file)) {

            $template = new CTemplateHelper();
            $asset = $template->getTemplateAsset('photo_thumb.png', 'images');
            $uri = $asset->url;
            return $uri;
        }

        // Strip cdn path if exists.
        // Note: At one point, cdn path was stored along with the avatar path
        //       in the db which is the mistake we are trying to rectify here.
        $file = str_ireplace($config->get('photocdnpath'), '', $file);

        // CDN or local
        $baseUrl = $config->get('photobaseurl') or
                $baseUrl = JURI::root();
        $uri = str_replace('\\', '/', rtrim($baseUrl, '/') . '/' . ltrim($file, '/'));
        return $uri;
    }

    /**
     * Return unencoded version of thr uri
     */
    public function getRawPhotoURI() {
        $album = JTable::getInstance('Album', 'CTable');
        $album->load($this->albumid);


        $url = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id;
        $url .= $album->groupid ? '&groupid=' . $album->groupid : '&userid=' . $album->creator;
        $url .= '&photoid=' . $this->id;
        return $url;
    }

    public function getPhotoURI($external = false, $xhtml = true) {
        return $this->getPhotoLink($external, $xhtml);
    }

    public function getPhotoLink($external = false, $xhtml = true) {
        $url = $this->getRawPhotoURI();

        if ($external) {
            return CRoute::getExternalURL($url);
        }

        return CRoute::_($url, $xhtml);
    }

    /**
     * Return the exist URL to be displayed.
     * @param size string, (normal, origianl, small)
     */
    public function getImageURI($size = 'normal') {

        $uri = '';
        if ($this->storage != 'file') {
            $storage = CStorage::getStorage($this->storage);
            $uri = $storage->getURI($this->image);
        } else {
            $config = CFactory::getConfig();
            $baseUrl = $config->get('photobaseurl');

            if (!empty($baseUrl)) {
                if (JFile::exists(JPATH_ROOT . '/' . $this->image)) {
                    //$uri	= rtrim( $baseUrl , '/' ) . '/' . $this->image;
                    // Strip cdn path if necessary
                    $path = $this->image;

                    $cdnpath = $config->get('photocdnpath');
                    if ($cdnpath) {
                        $path = str_replace($cdnpath, '', $path);
                    }
                    $path = ltrim($path, '/');
                    $uri = rtrim($baseUrl, '/') . '/' . $path;
                } else {
                    $uri = JURI::root() . 'index.php?option=com_community&view=photos&task=showimage&tmpl=component&imgid=' . $this->image;
                }
            } else {
                // If the resized photos exist, use it instead
                if (JFile::exists(JPATH_ROOT . '/' . $this->image)) {
                    $uri = JURI::root() . $this->image;
                } else {
                    $uri = JURI::root() . 'index.php?option=com_community&view=photos&task=showimage&tmpl=component&imgid=' . $this->image;
                }
            }
        }
        return $uri;
    }

    /**
     * Return the URI of the original photo
     */
    public function getOriginalURI() {
        if (JFile::exists(JPATH_ROOT . '/' . $this->original)) {
            return rtrim(JURI::root(), '/') . '/' . $this->original;
        }

        // If original url was deleted, we use the resized image as the original photo
        return rtrim(JURI::root(), '/') . '/' . $this->image;
    }

    // Load the Photo object given the image path
    public function loadFromImgPath($path) {
        $db = JFactory::getDBO();

        $strSQL = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_photos')
                . 'WHERE ' . $db->quoteName('image') . '=' . $db->Quote($path);

        //echo $strSQL;
        $db->setQuery($strSQL);
        $result = $db->loadObject();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        // Backward compatibility because anything prior to this version uses id
        // @since 1.6
        if (!$result) {
            $id = $path;
            $this->load($id);
            return $id;
        }

        $this->load($result->id);
        return $result;
    }

    /**
     * Override parent's hit method as we don't really want to
     * carefully increment it every time a photo is viewed.
     * */
    public function hit($pk = null) {
        $session = JFactory::getSession();
        if ($session->get('photo-view-' . $this->id, false) == false) {
            parent::hit();
            $session->set('photo-view-' . $this->id, true);

            //@rule: We need to test if the album for this photo also has the hits.
            // otherwise it would not tally when photo has large hits but not the album.
            // The album hit() will take care of the album hits session correctly.
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($this->albumid);
            $album->hit();
        }
    }

    /**
     * Set the ordering of the current photo based on the maximum
     * ordering in the photos table for the current album.
     * */
    public function setOrdering() {
        $db = JFactory::getDBO();

        $this->ordering = $this->getNextOrder($db->quoteName('albumid') . '=' . $db->Quote($this->albumid));
    }

    public function lastId() {
        $db = JFactory::getDBO();

        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_photos') . ' ORDER BY ' . $db->quoteName('id') . ' DESC';

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result->id;
    }

    public function getInfo() {
        $path = JPATH_ROOT . '/' . $this->image;
        if ( JFile::exists($path)) {
             $info = getimagesize ( $path);
             if ( $info[0] > $info[1])  {
                 $info['size'] = 'landscape';
             } else {
                 if ( $info[0] == $info[1]) {
                     $info['size'] = 'square';
                 } else {
                     if ( $info[0] < $info[1]) {
                         $info['size'] = 'portrait';
                     }
                 }
             }
             return $info;
        }
       return false;
    }
}
