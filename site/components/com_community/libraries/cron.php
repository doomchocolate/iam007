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

class CCron {

    private $_message = array();

    /**
     *
     */
    public function run() {
        jimport('joomla.filesystem.file');
        set_time_limit(120);

        // The cron job caller has the option to specify specific cron target
        $target = JRequest::getWord('target', '');
        if (!empty($target)) {
            $target = '_' . $target;
            if (method_exists($this, $target)) {
                // We're about to run a targeted con job
                // Close the connection so that the caller terminate the call
                while (ob_get_level())
                    ob_end_clean();
                header('Connection: close');
                ignore_user_abort();
                ob_start();
                echo('Closed');
                $size = ob_get_length();
                header("Content-Length: $size");
                ob_end_flush();
                flush();

                // The caller will get connection closed. Now run the target
                $this->$target();
            }
        } else {
            /* complete process all tasks */
            $this->_sendEmails();
            $this->_convertVideos();
            $this->_archiveActivities();
            $this->_cleanRSZFiles();
            $this->_removeTempPhotos();
            $this->_removeTempVideos();
            $this->_processPhotoStorage();
            $this->_updatePhotoFileSize();
            $this->_updateVideoFileSize();
            $this->_removeDeletedPhotos();
            $this->_processVideoStorage();
            $this->_processAvatarCoverStorage(COMMUNITY_PROCESS_STORAGE_LIMIT, 'users');
            $this->_processAvatarCoverStorage(COMMUNITY_PROCESS_STORAGE_LIMIT, 'groups');
            $this->_removePendingInvitation();
            $this->_processFileStorage();
            $this->_createIndexFile(JPATH_ROOT . '/images');
            $this->_removeDeletedUserFolder(JPATH_ROOT . '/images');
            $this->_unFeaturedEvents();
        }

        // Include CAppPlugins library
        require_once( JPATH_COMPONENT . '/libraries/apps.php');
        // Trigger system event onCronRun
        $appsLib = CAppPlugins::getInstance();
        $appsLib->loadApplications();

        $args = array();
        $appsLib->triggerEvent('onCronRun', $args);

        // Display cron messages if neessary
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8" ?' . '>'; // saperated to assist syntax highliter
        echo '<messages>';
        foreach ($this->_message as $msg) {
            echo '<message>';
            echo $msg;
            echo '</message>';
        }
        echo '</messages>';
        exit;
    }

    /**
     *
     */
    public function sendEmailsOnPageLoad() {

        $mailq = new CMailq();
        $mailq->send();
    }

    /**
     * Avatar / Cover storage transfer
     * @param type $updateNum
     * @param type $element
     * @return type
     */
    private function _processAvatarCoverStorage($updateNum = COMMUNITY_PROCESS_STORAGE_LIMIT, $element = 'users') {
        $config = CFactory::getConfig();
        //$jconfig  = JFactory::getConfig();
        $app = JFactory::getApplication();

        // Because the configuration of users remote storage is stored as user_avatar_storage, we need to get the correct name for it.
        $configElement = $element == 'users' ? 'user' : $element;
        $configElement .= '_avatar_storage';

        /* get storage type */
        $storageMethod = $config->getString($configElement);
        $storage = CStorage::getStorage($storageMethod);

        $totalMoved = 0;
        $totalCover = 0;
        $db = JFactory::getDBO();

        /**
         * @todo should we use model to get user with avatar instead of query here ?
         */
        /* query user with avatar */
        $query = 'SELECT * FROM ' . $db->quoteName('#__community_' . $element) . ' '
                . 'WHERE ' . $db->quoteName('storage') . ' != ' . $db->quote($storageMethod) . ' '
                . ' AND ( '
                . '( ' . $db->quoteName('thumb') . ' != ' . $db->quote('') . ' AND ' . $db->quoteName('avatar') . ' != ' . $db->Quote('') . ' ) '
                . ' OR ' . $db->quoteName('cover') . ' != ' . $db->quote('')
                . ' ) '
                . 'LIMIT ' . $updateNum;
        $db->setQuery($query);

        $rows = $db->loadObjectList();

        if (!$rows) {
            $this->_message[] = JText::_('No avatars or cover of ' . $element . ' needed to be transferred');
            return;
        }

        foreach ($rows as $row) {
            $current = CStorage::getStorage($row->storage);

            /* do cover transfer is exists */
            if ($current->exists($row->cover)) {
                $tmpCoverFileName = $app->getCfg('tmp_path') . '/' . md5($row->cover);
                $current->get($row->cover, $tmpCoverFileName);
                if (JFile::exists($tmpCoverFileName)) {
                    if ($storage->put($row->cover, $tmpCoverFileName)) {
                        switch ($element) {
                            case 'users':
                                // User's avatar and thumbnail is successfully uploaded to the remote location.
                                // We need to update it now.
                                $user = CFactory::getUser($row->userid);
                                $user->_storage = $storageMethod;
                                $user->save();

                                $cover = $user->_cover;
                                break;
                            case 'groups':
                                $group = JTable::getInstance('Group', 'CTable');
                                $group->load($row->id);
                                $group->storage = $storageMethod;
                                $group->store();

                                $cover = $group->cover;
                                break;
                        }
                        // Delete existing storage's avatar and thumbnail.
                        $current->delete($cover);

                        // Remove temporary generated avatar and thumbnail.
                        JFile::delete($tmpCoverFileName);
                        $totalCover++;
                    }
                }
            }

            /* If it exist on current storage, we can transfer it to preferred storage */
            if ($current->exists($row->thumb) && $current->exists($row->avatar)) {
                /**
                 * @todo Need to check if local is newer than remote storage
                 */
                // Move locally if file exists on remote storage.
                //$tmpThumbFileName = $jconfig->getValue( 'tmp_path' ) .'/'. md5( $row->thumb );
                $tmpThumbFileName = $app->getCfg('tmp_path') . '/' . md5($row->thumb);
                $current->get($row->thumb, $tmpThumbFileName);

                //$tmpAvatarFileName    = $jconfig->getValue( 'tmp_path' ) .'/'. md5( $row->avatar );
                $tmpAvatarFileName = $app->getCfg('tmp_path') . '/' . md5($row->avatar);
                $current->get($row->avatar, $tmpAvatarFileName);


                /**
                 * Check again if prepare transfer files exists
                 */
                if (JFile::exists($tmpThumbFileName) && JFile::exists($tmpAvatarFileName)) {
                    /* Do transfer into preferred storage */
                    if ($storage->put($row->avatar, $tmpAvatarFileName) && $storage->put($row->thumb, $tmpThumbFileName)) {
                        switch ($element) {
                            case 'users':
                                // User's avatar and thumbnail is successfully uploaded to the remote location.
                                // We need to update it now.
                                $user = CFactory::getUser(
                                                $row->userid);
                                $user->_storage = $storageMethod;
                                $user->save();

                                $avatar = $user->_avatar;
                                $thumb = $user->_thumb;

                                break;
                            case 'groups':
                                $group = JTable::getInstance('Group', 'CTable');
                                $group->load($row->id);
                                $group->storage = $storageMethod;
                                $group->store();

                                $avatar = $group->avatar;
                                $thumb = $group->thumb;
                                break;
                        }
                        // Delete existing storage's avatar and thumbnail.
                        $current->delete($avatar);
                        $current->delete($thumb);

                        // Remove temporary generated avatar and thumbnail.
                        JFile::delete($tmpAvatarFileName);
                        JFile::delete($tmpThumbFileName);
                        $totalMoved++;
                    }
                }
            }
        }
        $this->_message[] = JText::sprintf('%1$s avatar file(s) transferred.', $totalMoved);
        $this->_message[] = JText::sprintf('%1$s cover file(s) transferred.', $totalCover);
    }

    /**
     * For photos that does not have proper filesize info, update it.
     * Due to IO issues, run only 20 photos at a time
     */
    private function _updatePhotoFileSize($updateNum = 20) {

        $db = JFactory::getDBO();

        $sql = 'SELECT ' . $db->quoteName('id')
                . ' FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('filesize') . '=' . $db->Quote(0)
                . ' ORDER BY rand() LIMIT ' . $updateNum;
        $db->setQuery($sql);
        $photos = $db->loadObjectList();

        if (!empty($photos)) {
            $photo = JTable::getInstance('Photo', 'CTable');

            foreach ($photos as $data) {
                $photo->load($data->id);
                $originalPath = JPATH_ROOT . '/' . $photo->original;
                if (JFile::exists($originalPath)) {
                    $photo->filesize = sprintf("%u", filesize($originalPath));
                    $photo->store();
                }
            }
        }
    }

    /**
     * For videos that does not have proper filesize info, update it.
     * Due to IO issues, run only 20 photos at a time
     */
    private function _updateVideoFileSize($updateNum = 20) {

        $db = JFactory::getDBO();
        $sql = 'SELECT ' . $db->quoteName('id') . ', ' . $db->quoteName('creator')
                . ' FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('type') . '=' . $db->quote('file')
                . ' AND ' . $db->quoteName('status') . '=' . $db->quote('ready')
                . ' AND ' . $db->quoteName('filesize') . '=' . $db->Quote(0)
                . ' ORDER BY rand() LIMIT ' . $updateNum;
        $db->setQuery($sql);
        $videos = $db->loadObjectList();

        if (!empty($videos)) {
            $video = JTable::getInstance('Video', 'CTable');

            foreach ($videos as $data) {
                $video->load($data->id);
                $videoPath = JPATH::clean(JPATH_ROOT
                                . '/' . $video->path);
                if (JFile::exists($videoPath)) {
                    $video->filesize = sprintf("%u", filesize($videoPath));
                    $video->store();
                }
            }
        }
    }

    /**
     * Remove all photos that are orphaned, whose parent album has been deleted
     */
    private function _removeDeletedPhotos($updateNum = 100) {
        $db = JFactory::getDBO();

        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('albumid') . '=' . $db->Quote(0)
                . ' ORDER BY id limit ' . $updateNum;
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        if ($result) {
            foreach ($result as $row) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($row->id);
                $photo->delete();

                // Remove all related activities
                $query = 'DELETE FROM ' . $db->quoteName('#__community_activities')
                        . ' WHERE ' . $db->quoteName('app') . ' LIKE ' . $db->Quote('photos')
                        . ' AND ' . $db->quoteName('cid') . ' =' . $db->Quote($row->id)
                        . ' AND ' . $db->quoteName('params') . ' LIKE ' . $db->Quote('%photoid=' . $row->id . '%');
                $db->setQuery($query);
                $db->query();
            }
        }


        /**
         * @since 3.2
         * Get photos in deleting queue and do delete physical files ( would be on S3 )
         */
        $query = ' SELECT * FROM ' . $db->quoteName('#__community_photos');
        $query .= ' WHERE '
                . $db->quoteName('published') . ' = ' . (int) (0)
                . ' AND '
                . $db->quoteName('status') . ' = ' . $db->quote('delete');
        $db->setQuery($query);
        $deletedPhotos = $db->loadObjectList();
        if ($deletedPhotos) {
            foreach ($deletedPhotos as $deletedPhoto) {
                $currentStorage = CStorage::getStorage($deletedPhoto->storage);
                $currentStorage->delete($deletedPhoto->image);
                $currentStorage->delete($deletedPhoto->thumbnail);
                $currentStorage->delete($deletedPhoto->original);
            }
        }
        /* And now we do delete in database */
        $query = ' DELETE FROM ' . $db->quoteName('#__community_photos');
        $query .= ' WHERE '
                . $db->quoteName('published') . ' = ' . (int) (0)
                . ' AND '
                . $db->quoteName('status') . ' = ' . $db->quote('delete');
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Remove old dynamically resized image files
     */
    private function _cleanRSZFiles($updateNum = 5) {
        $db = JFactory::getDBO();

        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_photos')
                . ' ORDER BY rand() limit ' . $updateNum;
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        if (!$result) {
            return;
        }

        foreach ($result as $row) {
            // delete all rsz_ files which are no longer used
            $rszFiles = JFolder::files(dirname(JPATH_ROOT . '/' . $row->image), '.', false, true);
            if ($rszFiles)
                foreach ($rszFiles as $rszRow) {
                    if (substr(basename($rszRow), 0, 3) == 'rsz') {
                        JFile::delete($rszRow);
                    }
                }
        }
    }

    /**
     * If remote storage is used, transfer some files to the remote storage
     * - fetch file from current storage to a temp location
     * - put file from temp to new storage
     * - delete file from old storage
     */
    private function _processPhotoStorage($updateNum = 5) {
        $config = CFactory::getConfig();
        //$jconfig = JFactory::getConfig();
        $app = JFactory::getApplication();
        $photoStorage = $config->getString('photostorage');

        $fileTranferCount = 0;
        $storage = CStorage::getStorage($photoStorage);

        $db = JFactory::getDBO();

        // @todo, we nee to find a way to make sure that we transfer most of
        // our photos remotely
        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_photos')
                . ' WHERE ' . $db->quoteName('storage') . '!=' . $db->Quote($photoStorage)
                . ' AND ' . $db->quoteName('albumid') . '!=' . $db->Quote(0)
                . ' ORDER BY rand() limit ' . $updateNum;
        $db->setQuery($sql);
        $result = $db->loadObjectList();

        if (!$result) {
            $this->_message[] = JText::_('No files to transfer.');
            return;
        }

        foreach ($result as $row) {
            $currentStorage = CStorage::getStorage($row->storage);

            // If current storage is file based, create the image since we might not have them yet
            if ($row->storage == 'file' && !JFile::exists(JPATH_ROOT . '/' . $row->image)) {
                // resize the original image to a smaller viewable version
                $this->_message[] = 'Image file missing. Creating image file.';

                // make sure original file exist
                if (JFile::exists(JPATH_ROOT . '/' . $row->original)) {
                    $displyWidth = $config->getInt('photodisplaysize');
                    $info = getimagesize(JPATH_ROOT . '/' . $row->original);
                    $imgType = image_type_to_mime_type($info[2]);
                    $width = ($info[0] < $displyWidth) ? $info[0] : $displyWidth;
                    CImageHelper ::resizeProportional(JPATH_ROOT . '/' . $row->original, JPATH_ROOT . '/' . $row->image, $imgType, $width);
                } else {
                    $this->_message[] = 'Original file is missing!!';
                }
            }

            // If it exist on current storage, we can transfer it to preferred storage
            if ($currentStorage->exists($row->image) && $currentStorage->exists($row->thumbnail)) {
                // File exist on remote storage, move it locally first
                //$tempFilename = $jconfig->getValue('tmp_path').'/'. md5($row->image);
                $tempFilename = $app->getCfg('tmp_path') . '/' . md5($row->image);
                $currentStorage->get($row->image, $tempFilename);

                //$thumbsTemp       = $jconfig->getValue('tmp_path').'/thumb_' . md5($row->thumbnail);
                $thumbsTemp = $app->getCfg('tmp_path') . '/thumb_' . md5($row->thumbnail);
                $currentStorage->get($row->thumbnail, $thumbsTemp);

                if (JFile::exists($tempFilename) && JFile::exists($thumbsTemp)) {
                    // we assume thumbnails is always there
                    // put both image and thumbnails remotely

                    if ($storage->put($row->image, $tempFilename) && $storage->put($row->thumbnail, $thumbsTemp)) {
                        // if the put is successful, update storage type
                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($row->id);
                        $photo->storage = $photoStorage;
                        $photo->store();

                        //UPDATE ALBUM THUMBNAIL ======
                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($photo->albumid);
                        if ($row->id == $album->thumbnail_id) {
                            $album->setParam('thumbnail', $storage->getURI($row->thumbnail));
                            $album->store();
                        }
                        unset($album);
                        //============================

                        $currentStorage->delete($row->image);
                        $currentStorage->delete($row->thumbnail);

                        // remove temporary file
                        JFile::delete($tempFilename);
                        JFile::delete($thumbsTemp);
                        $fileTranferCount++;
                    }
                }
            }
        }

        $this->_message[] = $fileTranferCount . ' files transferred.';
    }

    private function _processVideoStorage($updateNum = 5) {
        $config = CFactory::getConfig();
        //$jconfig      = JFactory::getConfig();

        $app = JFactory::getApplication();
        $videoStorage = $config->getString('videostorage');





        $db = JFactory::getDBO();
        $query = ' SELECT * FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('storage') . ' != ' . $db->quote($videoStorage)
                //. ' AND ' . $db->quoteName('type') . ' = ' . $db->quote('file')
                . ' AND ' . $db->quoteName('status') . ' = ' . $db->quote('ready') . ' ORDER BY rand() limit ' . $updateNum;

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if (!$result) {
            $this->_message[] = JText::_('No Videos to transfer.');
            return;
        }

        $storage = CStorage::getStorage($videoStorage);
        //$tempFolder   = $jconfig->getValue('tmp_path');
        $tempFolder = $app->getCfg('tmp_path');
        $fileTransferCount = 0;

        foreach ($result as $videoEntry) {
            $currentStorage = CStorage::getStorage($videoEntry->storage);

            if ($videoEntry->type == 'file') {
                // If it exist on current storage, we can transfer it to preferred storage
                if ($currentStorage->exists($videoEntry->path)) {
                    // File exist on remote storage, move it locally first
                    $tempFilename = JPATH::clean($tempFolder . '/' . md5($videoEntry->path));
                    $tempThumbname = JPATH::clean($tempFolder . '/' . md5($videoEntry->thumb));
                    $currentStorage->get($videoEntry->path, $tempFilename);
                    $currentStorage->get($videoEntry->thumb, $tempThumbname);

                    if (JFile::exists($tempFilename) && JFile::exists($tempThumbname)) {
                        // we assume thumbnails is always there
                        // put both video and thumbnails remotely
                        if ($storage->put($videoEntry->path, $tempFilename) &&
                                $storage->put($videoEntry->thumb, $tempThumbname)) {
                            // if the put is successful, update storage type
                            $video = JTable::getInstance('Video', 'CTable');
                            $video->load($videoEntry->id);
                            $video->storage = $videoStorage;
                            $video->store();

                            // remove files on storage and temporary files

                            $currentStorage->delete($videoEntry->path);
                            $currentStorage->delete($videoEntry->thumb);
                            JFile::delete($tempFilename);
                            JFile::delete($tempThumbname);

                            $fileTransferCount++;
                        }
                    }
                }
            } else {
                // This is for non-upload video file type e.g. YouTube etc
                // We'll just process the video thumbnail only

                if ($currentStorage->exists($videoEntry->thumb)) {
                    $tempThumbname = JPATH::clean($tempFolder . '/' . md5($videoEntry->thumb));
                    $currentStorage->get($videoEntry->thumb, $tempThumbname);

                    if (JFile::exists($tempThumbname)) {
                        if ($storage->put($videoEntry->thumb, $tempThumbname)) {
                            $video = JTable::getInstance('Video', 'CTable');
                            $video->load($videoEntry->id);
                            $video->storage = $videoStorage;
                            $video->store();

                            $currentStorage->delete($videoEntry->thumb);
                            JFile::delete($tempThumbname);
                            $fileTransferCount++;
                        }
                    }
                }
            }
        }
        $this->_message [] = $fileTransferCount . ' video file(s) transferred';
    }

    private function _convertVideos() {

        $videos = new CVideos ();
        $videos->runConvert();
        if (trim($videos->errorMsg[0]) == 'No videos pending for conversion.') {
            $this->_message[] = "No videos pending for conversion.";
        } else if (strpos($videos->errorMsg [0], 'videos converted successfully')) {
            $this->_message [] = $videos->errorMsg[0];
        } else {
            $this->_message [] = 'Could not convert video';
        }
    }

    private function _sendEmails() {

        $mailq = new CMailq();

        $config = CFactory::getConfig();
        $mailq->send($config->get('totalemailpercron'));
    }

    /**
     * Archive older activities for performance reason
     */
    private function _archiveActivities() {
        $config = CFactory::getConfig();

        $db = JFactory::

                getDBO();

        $date = JFactory::getDate();
        $currentTime = $date->toSql();

        // Get the id of the most recent 500th (or whatever archive_activity_limit is)
        $sql = 'SELECT id FROM ' . $db->quoteName('#__community_activities')
                . ' WHERE '
                . $db->quoteName('archived') . '=' . $db->Quote(0)
                . ' AND DATEDIFF(\'' . $currentTime . '\',' . $db->quoteName('created') . ')<=' . $config->get('archive_activity_max_day')
                . ' ORDER BY ' . $db->quoteName('id') . ' DESC'
                . ' LIMIT ' . $config->get('archive_activity_limit') . ' , 1 ';

        $db->setQuery($sql);
        $id = $db->loadResult();

        if ($id) {

            // Now that we have the id, since id is auto-increment, we can assume
            // any value lower than it is an earlier stream data
            $sql = 'UPDATE ' . $db->quoteName('#__community_activities') . ' act'
                    . ' SET act.' . $db->quoteName('archived') . ' = ' . $db->Quote(1)
                    . ' WHERE '
                    /* Only archive those not archived yet */
                    . $db->quoteName('archived') . '=' .
                    $db->Quote(0)
                    . ' AND '
                    . $db->quoteName('id') . '<' . $db->Quote($id);

            $db->setQuery($sql);
            $db->query();
        }
    }

    private function _removeTempPhotos() {
        $db = JFactory::getDBO();
        $sql = 'UPDATE ' . $db->quoteName('#__community_photos')
                . ' SET ' . $db->quoteName('albumid') . '=' . $db->Quote(0)
                . ' WHERE ' . $db->quoteName('status') . '=' . $db->Quote('temp')
                . '  AND ' . $db->quoteName('created') . ' < DATE_SUB( UTC_TIMESTAMP()  , INTERVAL 30 MINUTE )';

        $db->setQuery($sql);
        $db->query();
    }

    private function _removeTempVideos() {
        $db = JFactory::getDBO();


        $sql = ' SELECT ' . $db->quoteName('thumb') . ' FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('status') . '=' . $db->quote('temp');

        $db->setQuery($sql);

        $result = $db->loadObjectList();

        if (!$result) {
            $this->_message[] = JText::_('No temporary videos to delete.');
            //return;
        } foreach ($result as $video) {
            $thumb = JPATH_ROOT . '/' . $video->thumb;
            JFile::delete($thumb);
        }

        $sql = 'DELETE FROM ' . $db->quoteName('#__community_videos')
                . ' WHERE ' . $db->quoteName('status') . '=' . $db->quote('temp');

        $db->setQuery($sql);

        $db->query();

        /* Delete original video files if asked */
        $config = CFactory::getConfig();
        $videoFolder = $config->get('videofolder');
        $deleteOriginal = $config->get('deleteoriginalvideos');
        if ($deleteOriginal) {
            /* These videos already converted */
            $query = ' SELECT * FROM #__community_videos WHERE ' . $db->quoteName('status') . ' = ' . $db->quote('ready') . ' AND ' . $db->quoteName('type') . ' = ' . $db->quote('file');
            $db->setQuery($query);
            $videos = $db->loadObjectList();
            if ($videos) {
                foreach ($videos as $video) {
                    $path = JPATH_ROOT . '/' . $videoFolder . '/originalvideos/' . $video->creator . '/' . JFile::getName($video->path);
                    if (JFile::exists($path))
                        JFile::delete($path);
                }
            }
        }
    }

    private function _removePendingInvitation() {
        $eventTable = JTable::getInstance('Event', 'CTable');
        $eventTable->deletePendingMember();

        $this->_message[] = 'Removed Pending Invitation for Past Event';
    }

    private function _processFileStorage($updateNum = 5) {
        $config = CFactory::getConfig();
        //$jconfig = JFactory::getConfig();

        $app = JFactory ::getApplication();
        $fileStorage = $config->getString('file_storage');

        if ($fileStorage != 'file') {


            $fileTranferCount = 0;
            $storage = CStorage::getStorage($fileStorage);

            $db = JFactory::getDBO();

            $sql = 'SELECT * FROM ' . $db->quoteName('#__community_files')
                    . ' WHERE ' . $db->quoteName('storage') . '!=' . $db->Quote($fileStorage)
                    . ' ORDER BY rand() limit ' . $updateNum;

            $db->setQuery($sql);
            $result = $db->loadObjectList();

            if (
                    !$result) {
                $this->_message[] = JText::_('No files to transfer.');
                return;
            }

            foreach ($result as $row) {
                $currentStorage = CStorage::getStorage($row->storage);

                if ($currentStorage->exists($row->filepath)) {
                    // File exist on remote storage, move it locally first
                    //$tempFilename = $jconfig->getValue('tmp_path').'/'. md5($row->filepath);
                    $tempFilename = $app->getCfg('tmp_path') . '/' . md5($row->filepath);
                    $currentStorage->get($row->filepath, $tempFilename);

                    if (JFile::exists($tempFilename)) {
                        if ($storage->put($row->filepath, $tempFilename)) {
                            // if the put is successful, update storage type
                            $file = JTable::getInstance('File', 'CTable');
                            $file->load($row->id);
                            $file->storage = $fileStorage;
                            $file->store();

                            $currentStorage->delete($row->filepath);

                            // remove temporary file
                            JFile::delete($tempFilename);
                            $fileTranferCount++;
                        }
                    }
                }
            }
            $this->_message[] = $fileTranferCount . ' files transferred. to s3';
        }
    }

    private function _createIndexFile($path, $level = 0) {

        $ignore = array('cgi-bin', '.', '..');
        // Directories to ignore when listing output. Many hosts
        // will deny PHP access to the cgi-bin.

        $dh = @opendir($path);
        // Open the directory to the handle $dh
        $flag = true;
        while (false !== ( $file = readdir($dh) )) {
            // Loop through the directory

            if (!in_array($file, $ignore)) {
                // Check that this file is not to be ignored
                //  $spaces = str_repeat('&nbsp;', ( $level * 4));
                // Just to add spacing to the list, to better
                // show the directory tree.

                if (is_dir("$path/$file")) {
                    // Its a directory, so we need to keep reading down...
                    //echo "<strong>$spaces $file</strong><br />";
                    $this->_createIndexFile("$path/$file", ($level + 1));
                    // Re-call this same function but on a new directory.
                    // this is what makes function recursive.
                } else {
                    if ($file == 'index.html') {
                        //echo "$path/$file" . '<br />';
                        $flag = false;
                    } else {
                        //echo "$path/$file" . '<br />';
                    }

                    // Just print out the filename
                }
            }
        }

        if ($flag) {
            $buffer = '';
            JFile::write($path . '/index.html', $buffer);
        }
        closedir($dh);
        // Close the directory handle
    }

    private function _removeDeletedUserFolder($path) {

        $folders = JFolder::folders($path);
        $allowFolders = array('photos', 'groupphotos', 'cover', 'videos');

        foreach ($folders as $folder) {
            if (in_array($folder, $allowFolders)) {
                $sub_folders = JFolder::folders($path . '/' . $folder);

                if ($folder == 'photos' || $folder == 'videos') {
                    foreach ($sub_folders as $sub) {
                        $user = CFactory::getUser($sub);

                        if (is_null($user->username)) {
                            JFolder::delete($path . '/' . $folder . '/' . $sub);
                        }
                    }
                } elseif ($folder == 'cover') {
                    $pCover = JFolder::folders($path . '/' . $folder . '/profile');
                    foreach ($pCover as $_pCoverId) {
                        $pUser = CFactory::getUser($_pCoverId);

                        if (is_null($pUser->username)) {
                            JFolder::delete($path . '/' . $folder . '/profile/' . $_pCoverId);
                        }
                    }
                }
            }
        }
    }

    /**
     * 
     * @return type
     */
    protected function _unFeaturedEvents() {
        $db = JFactory::getDbo();
        $query = ' DELETE `featured`.* FROM ' . $db->quoteName('#__community_featured') . ' AS ' . $db->quoteName('featured');
        $query .= ' INNER JOIN ' . $db->quoteName('#__community_events') . ' AS ' . $db->quoteName('events') . ' ON ' . $db->quoteName('events') . '.' . $db->quoteName('id') . ' = ' . $db->quoteName('featured') . '.' . $db->quoteName('cid');
        $query .= ' WHERE ' . $db->quoteName('featured') . '.' . $db->quoteName('type') . ' = ' . $db->quote('events');
        $query .= ' AND ' . $db->quoteName('events') . '.' . $db->quoteName('enddate') . ' < CURDATE()';
        $db->setQuery($query);
        return $db->execute();
    }
}
