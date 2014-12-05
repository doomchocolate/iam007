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

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');

if (!class_exists('plgUserJomSocialUser')) {

    class plgUserJomSocialUser extends JPlugin {

        public function __construct(& $subject, $config) {
            parent::__construct($subject, $config);
            include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
            require_once JPATH_ROOT . '/components/com_community/libraries/featured.php';
            require_once JPATH_ROOT . '/components/com_community/libraries/videos.php';
            require_once JPATH_ROOT . '/components/com_community/events/router.php';
        }

        /**
         * This method should handle any login logic and report back to the subject
         *
         * @access	public
         * @param 	array 	holds the user data
         * @param 	array    extra options
         * @return	boolean	True on success
         * @since	1.5
         */
        public function onLoginUser($user, $options) {
            $id = CUserHelper::getUserId($user['username']);

            CFactory::setActiveProfile($id);

            return true;
        }

        /**
         * This method should handle any login logic and report back to the subject
         * For Joomla 1.6, onLoginUser is now onUserLogin
         *
         * @access	public
         * @param 	array 	holds the user data
         * @param 	array    extra options
         * @return	boolean	True on success
         * @since	1.6
         */
        public function onUserLogin($user, $options) {
            return $this->onLoginUser($user, $options);
        }

        /**
         * This method should handle any logout logic and report back to the subject
         *
         * @access public
         * @param array holds the user data
         * @return boolean True on success
         * @since 1.5
         */
        public function onLogoutUser($user) {
            CFactory::unsetActiveProfile();

            return true;
        }

        /**
         * This method should handle any logout logic and report back to the subject
         * For Joomla 1.6, onLogoutUser is now onUserLogout
         *
         * @access	public
         * @param 	array 	holds the user data
         * @param 	array    extra options
         * @return	boolean	True on success
         * @since	1.6
         */
        public function onUserLogout($user) {
            return $this->onLogoutUser($user);
        }

        /**
         * Clean up user profile when user be deleted
         * @param type $user
         */
        public function onBeforeDeleteUser($user) {
            $mainframe = JFactory::getApplication();
            $this->_cleanupProfile($user);
            $this->deleteFromCommunityEvents($user);
            $this->deleteFromCommunityUser($user);
            $this->deleteFromCommunityWall($user);
            $groups = $this->deleteFromCommunityGroup($user);
            $this->deleteFromCommunityDiscussion($user, $groups);
            $this->deleteFromCommunityPhoto($user);
            $this->deleteFromCommunityMsg($user);
            $this->_deleteFromCommunityProfile($user);
            $this->deleteFromCommunityConnection($user);
            $this->deleteFromCommunityApps($user);
            $this->deleteFromCommunityActivities($user);
            $this->deleteFromCommunityVideos($user);
            $this->deleteFromCommunityConnectUsers($user);
            $this->deleteFromCommunityFeatured($user, $groups, $albums, $videos);
            $this->deleteFromCommunityLiked($user);
            if ($this->params->get('delete_jommla_contact', 0)) {
                $this->deleteFromJoomlaContactDetails($user);
            }
        }

        /**
         * To handle onBeforeDeleteUser event
         * For Joomla 1.6, onBeforeDeleteUser is now onUserBeforeDelete
         *
         * @access	public
         * @return	boolean	True on success
         * @since	1.6
         */
        function onUserBeforeDelete($user) {
            $this->onBeforeDeleteUser($user);
        }

        /**
         * Remove likes by user
         * @param type $user
         * @since 3.0
         */
        function deleteFromCommunityLiked($user) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query
                    ->select('*')
                    ->from($db->quoteName('#__community_likes'))
                    ->where($db->quoteName('like') . ' LIKE ' . $db->quote('%' . $user['id'] . '%'));
            $db->setQuery($query);
            $likes = $db->loadObjectList();
            foreach ($likes as $like) {
                /* parse likes to array */
                $query = $db->getQuery(true);
                $liked = explode(',', $like->like);
                /* find index of userid in array */
                $key = array_search($user['id'], $liked);
                /* remove this user */
                unset($liked[$key]);
                /* now save back to likes table */
                $query
                        ->update($db->quoteName('#__community_likes'))
                        ->set($db->quoteName('like') . '=' . $db->quote(implode(',', $liked)))
                        ->where($db->quoteName('id') . '=' . $db->quote($like->id));
                $db->setQuery($query)->query();
                if ($db->getErrorNum()) {
                    JError::raiseError(500, $db->stderr());
                }
            }
        }

        /**
         * Remove association when a user is removed
         * */
        function deleteFromCommunityConnectUsers($user) {
            $db = JFactory::getDBO();

            $query = 'DELETE FROM ' . $db->quoteName('#__community_connect_users') . ' '
                    . 'WHERE ' . $db->quoteName('userid') . '=' . $db->Quote($user['id']);
            $db->setQuery($query);
            $db->query();

            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityUser($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_users") . "
				WHERE
						" . $db->quoteName("userid") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityWall($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_wall") . "
				WHERE
						(" . $db->quoteName("contentid") . " = " . $db->quote($user['id']) . " OR
						" . $db->quoteName("post_by") . " = " . $db->quote($user['id']) . ") AND
						" . $db->quoteName("type") . " = " . $db->quote('user');
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityDiscussion($user, $gids) {
            $db = JFactory::getDBO();

            if (!empty($gids)) {
                $sql = "SELECT
							" . $db->quoteName("id") . "
					FROM
							" . $db->quoteName("#__community_groups_discuss") . "
					WHERE
							" . $db->quoteName("groupid") . " IN (" . $gids . ")";
                $db->setQuery($sql);
                $row = $db->loadobjectList();
                if ($db->getErrorNum()) {
                    JError::raiseError(500, $db->stderr());
                }

                if (!empty($row)) {
                    $count = 0;
                    $scount = sizeof($row) - 1;
                    $ids = "";
                    foreach ($row as $data) {
                        $ids .= $data->id;
                        if ($count < $scount) {
                            $ids .= ",";
                        }
                        $count++;
                    }
                }
                $condition = $db->quoteName("creator") . " = " . $db->quote($user['id']) . " OR
						" . $db->quoteName("groupid") . " IN (" . $gids . ")";
            } else {
                $condition = $db->quoteName("creator") . " = " . $db->quote($user['id']);
            }

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_groups_discuss") . "
				WHERE
						" . $condition;
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            if (!empty($ids)) {
                $condition = "(" . $db->quoteName("post_by") . " = " . $db->quote($user['id']) . " OR
						   " . $db->quoteName("contentid") . " IN (" . $ids . "))";
            } else {
                $condition = $db->quoteName("post_by") . " = " . $db->quote($user['id']);
            }

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_wall") . "
				WHERE
						" . $condition . " AND
						" . $db->quoteName("type") . " = " . $db->quote('discussions');
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityPhoto($user) {
            $db = JFactory::getDBO();
            //mark photos for deletion
            $sql = 'UPDATE ' . $db->quoteName('#__community_photos')
                    . ' SET ' . $db->quoteName('albumid') . '=' . $db->Quote(0)
                    . ' WHERE ' . $db->quoteName("creator") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
            //remove user's albums
            $sql = "SELECT
						" . $db->quoteName("id") . "
				FROM
						" . $db->quoteName("#__community_photos_albums") . "
				WHERE
						" . $db->quoteName("creator") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $albums = $db->loadobjectList();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
            $album = JTable::getInstance('Album', 'CTable');
            //CFactory::load( 'libraries' , 'featured' );

            if (!empty($albums)) {
                foreach ($albums as $data) {
                    $album->load($data->id);
                    $album->delete();
                    // @rule: remove from featured item if item is featured
                    $featured = new CFeatured(FEATURED_ALBUMS);
                    $featured->delete($album->id);
                }
            }

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_photos_tokens") . "
				WHERE
						" . $db->quoteName("userid") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            return $albums;
        }

        function deleteFromCommunityMsg($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_msg") . "
				WHERE
						" . $db->quoteName("from") . " = " . $db->quote($user['id']);
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_msg_recepient") . "
				WHERE
						" . $db->quoteName("msg_from") . " = " . $db->quote($user['id']) . " OR
						" . $db->quoteName("to") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        /**
         * Remove all events related to the user that is being removed.
         *
         * 	@param	Array	An array of user's information
         * 	@return	null
         * */
        public function deleteFromCommunityEvents($user) {
            $db = JFactory::getDBO();
            $query = 'SELECT `id` FROM ' . $db->quoteName('#__community_events') . ' '
                    . 'WHERE ' . $db->quoteName('creator') . '=' . $db->Quote($user['id']);
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            $event = JTable::getInstance('Event', 'CTable');
            $eventMembers = JTable::getInstance('EventMembers', 'CTable');

            // @rule: Delete all events created by this user.
            if ($rows) {
                foreach ($rows as $row) {
                    $event->load($row->id);
                    $event->delete();
                }
            }
            unset($rows);

            // @rule: Delete all events participated by this user.
            $query = 'SELECT * FROM ' . $db->quoteName('#__community_events_members') . ' '
                    . 'WHERE ' . $db->quoteName('memberid') . '=' . $db->Quote($user['id']);
            $db->setQuery($query);
            $rows = $db->loadObjectList();

            if ($rows) {
                foreach ($rows as $row) {
                    $event->load($row->eventid);
                    $eventMembers->load($user['id'], $row->eventid);

                    $eventMembers->delete();
                    $event->updateGuestStats();
                }
            }
        }

        function deleteFromCommunityGroup($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_groups_bulletins") . "
				WHERE
						" . $db->quoteName("created_by") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            $sql = "SELECT
						" . $db->quoteName("id") . "
				FROM
						" . $db->quoteName("#__community_groups") . "
				WHERE
						" . $db->quoteName("ownerid") . " = " . $db->quote($user['id']);
            $db->setQuery($sql);
            $row = $db->loadobjectList();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            if (!empty($row)) {
                $count = 0;
                $scount = sizeof($row) - 1;
                $ids = "";
                foreach ($row as $data) {
                    $ids .= $data->id;
                    if ($count < $scount) {
                        $ids .= ",";
                    }
                    $count++;
                }

                $sql = "DELETE

					FROM
							" . $db->quoteName("#__community_groups_members") . "
					WHERE
							" . $db->quoteName("groupid") . " IN (" . $ids . ") OR
							" . $db->quoteName("memberid") . " = " . $db->Quote($user['id']);
                $db->setQuery($sql);
                $db->Query();
                if ($db->getErrorNum()) {
                    JError::raiseError(500, $db->stderr());
                }
            }

            $sql = "UPDATE " . $db->quoteName("#__community_groups") .
                    " SET " . $db->quoteName('published') . " = " . $db->Quote('0') .
                    " WHERE " . $db->quoteName("ownerid") . " = " . $db->quote($user['id']);

            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_wall") . "
				WHERE
						" . $db->quoteName("post_by") . " = " . $db->quote($user['id']) . " AND
						" . $db->quoteName("type") . " = " . $db->quote('groups');
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            $ids = empty($ids) ? "" : $ids;

            return $ids;
        }

        /**
         * Cleanup user profile' files
         * @param type $user
         */
        private function _cleanupProfile($user) {
            $cUser = CFactory::getUser($user['id']);
            /* Profile avatar cleanup */
            $config = CFactory::getConfig();
            $fileInfo = pathinfo($cUser->_avatar);
            $dirPath = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar';

            $avatarFile = $dirPath . '/' . $fileInfo['basename'];
            $thumb = $dirPath . '/' . 'thumb_' . $fileInfo['basename'];
            $profile = $dirPath . '/' . 'profile-' . $fileInfo['basename'];
            $stream = $dirPath . '/' . $fileInfo['filename'] . '_stream_.' . $fileInfo['extension'];

            $dirs[] = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/' . $cUser->id;
            $dirs[] = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/cover/profile/' . $cUser->id;
            $dirs[] = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/originalphotos/' . $cUser->id;
            $dirs[] = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/originalvideos/' . $cUser->id;
            $dirs[] = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/videos/' . $cUser->id;

            if (JFile::exists($avatarFile))
                JFile::delete($avatarFile);
            if (JFile::exists($thumb))
                JFile::delete($thumb);
            if (JFile::exists($profile))
                JFile::delete($profile);
            if (JFile::exists($stream))
                JFile::delete($stream);
            foreach ($dirs as $dir) {
                if (JFolder::exists($dir))
                    JFolder::delete($dir);
            }
        }

        /**
         * Clean up user profile
         * @param type $user
         */
        private function _deleteFromCommunityProfile($user) {

            $db = JFactory::getDBO();
            $sql = "DELETE FROM " . $db->quoteName("#__community_fields_values") . "
                WHERE " . $db->quoteName("user_id") . " = " . $db->quote($user['id']);
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityConnection($user) {
            $db = JFactory::getDBO();

            $sql = "SELECT
						a." . $db->quoteName("connect_from") . "
				FROM
						" . $db->quoteName("#__community_connection") . " a
			INNER JOIN
						" . $db->quoteName("#__community_connection") . " b ON a." . $db->quoteName("connect_from") . "=b." . $db->quoteName("connect_to") . "
				WHERE
						a." . $db->quoteName("connect_to") . " = " . $db->quote($user['id']) . " AND
						b." . $db->quoteName("connect_from") . " = " . $db->quote($user['id']);
            $db->setQuery($sql);
            $row = $db->loadobjectList();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            if (!empty($row)) {
                $count = 0;
                $scount = sizeof($row) - 1;
                $ids = "";
                foreach ($row as $data) {
                    $ids .= $data->connect_from;
                    if ($count < $scount) {
                        $ids .= ", ";
                    }
                    $count++;
                }

                $sql = "UPDATE
							" . $db->quoteName("#__community_users") . "
					SET
							" . $db->quoteName("friendcount") . " = " . $db->quoteName("friendcount") . " - 1
					WHERE
							" . $db->quoteName("userid") . " IN (" . $ids . ")";
                $db->setQuery($sql);
                $db->Query();
                if ($db->getErrorNum()) {
                    JError::raiseError(500, $db->stderr());
                }
            }

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_connection") . "
				WHERE
						" . $db->quoteName("connect_from") . " = " . $db->quote($user['id']) . " OR
						" . $db->quoteName("connect_to") . " = " . $db->quote($user['id']);
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityApps($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_apps") . "
				WHERE
						" . $db->quoteName("userid") . " = " . $db->quote($user['id']);
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityActivities($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__community_activities") . "
				WHERE
						(" . $db->quoteName("actor") . " = " . $db->quote($user['id']) . " OR
						" . $db->quoteName("target") . " = " . $db->quote($user['id']) . ") AND
						" . $db->quoteName("archived") . " = " . $db->quote(0);
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

        function deleteFromCommunityVideos($user) {
            $db = JFactory::getDBO();

            $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__community_videos')
                    . ' WHERE ' . $db->quoteName('creator') . ' = ' . $db->quote($user['id']);
            $db->setQuery($query);
            $videos = $db->loadResultArray();

            $query = 'DELETE FROM ' . $db->quoteName('#__community_videos')
                    . ' WHERE ' . $db->quoteName('creator') . ' = ' . $db->quote($user['id']);
            $db->setQuery($query);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }

            $videoLib = new CVideoLibrary();

            // Converted Videos Folder
            $videoFolder = $videoLib->videoRootHome . '/' . $user['id'];
            if (JFolder::exists($videoFolder)) {
                JFolder::delete($videoFolder);
            }
            // Original Videos Folder
            $videoFolder = $videoLib->videoRootOrig . '/' . $user['id'];
            if (JFolder::exists($videoFolder)) {
                JFolder::delete($videoFolder);
            }

            return $videos;
        }

        function deleteFromCommunityFeatured($user, $groups, $albums, $videos) {
            //delete featured user
            $featured = new CFeatured(FEATURED_USERS);
            if (!empty($user)) {
                $featured->delete($user['id']);
            }

            //delete featured groups
            $featured = new CFeatured(FEATURED_GROUPS);
            if (!empty($groups)) {
                $groupIds = explode(",", $groups);
                foreach ($groupIds as $groupId) {
                    $featured->delete($groupId);
                }
            }

            //delete featured albums
            $featured = new CFeatured(FEATURED_ALBUMS);
            if (!empty($albums)) {
                foreach ($albums as $albumId) {
                    $featured->delete($albumId);
                }
            }

            //delete featured albums
            $featured = new CFeatured(FEATURED_VIDEOS);
            if (!empty($videos)) {
                foreach ($videos as $videoId) {
                    $featured->delete($videoId);
                }
            }
        }

        function deleteFromJoomlaContactDetails($user) {
            $db = JFactory::getDBO();

            $sql = "DELETE

				FROM
						" . $db->quoteName("#__contact_details") . "
				WHERE
						" . $db->quoteName("user_id") . " = " . ($user['id']);
            $db->setQuery($sql);
            $db->Query();
            if ($db->getErrorNum()) {
                JError::raiseError(500, $db->stderr());
            }
        }

    }

}
