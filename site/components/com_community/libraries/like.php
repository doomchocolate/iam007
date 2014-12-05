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

class CLike {
    public function addLike($element, $itemId) {
        $my = CFactory::getUser();

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $like->element = $element;
        $like->uid = $itemId;

        // Check if user already like
        $likesInArray = explode(',', trim($like->like, ','));
        /* Like once time */
        if(in_array($my->id, $likesInArray))
                return;
        array_push($likesInArray, $my->id);
        $likesInArray = array_unique($likesInArray);
        $like->like = ltrim(implode(',', $likesInArray), ',');

        // Check if the user already dislike
        $dislikesInArray = explode(',', trim($like->dislike, ','));
        if (in_array($my->id, $dislikesInArray)) {
            // Remove user dislike from array
            $key = array_search($my->id, $dislikesInArray);
            unset($dislikesInArray [$key]);

            $like->dislike = implode(',', $dislikesInArray);
        }

        //trigger notification
        //CFactory::load( 'libraries' , 'notification' );
        switch ($element) {
            case 'comment':
                //get the instance of the wall
                $wall = JTable::getInstance('Wall', 'CTable');
                $wall->load($itemId);

                if(!$wall->id){
                    break;
                };

                //load the stream id from activity stream
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load($wall->contentid);

                if ($stream->id) {
                    $profile = CFactory::getUser($stream->actor);
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id;
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('comment', JText::_('COM_COMMUNITY_SINGULAR_COMMENT'));
                    $params->set('comment_url', $url);

                    //add to notifications
                    CNotificationLibrary::add('comments_like', $my->id, $wall->post_by, JText::sprintf('COM_COMMUNITY_PROFILE_WALL_LIKE_EMAIL_SUBJECT'), '', 'comments.like', $params);
                }
                break;
            case 'photo':
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($itemId);
                if ($photo->id) {
                    $url = $photo->getRawPhotoURI();
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('photo', JText::_('COM_COMMUNITY_SINGULAR_PHOTO'));
                    $params->set('photo_url', $url);

                    CNotificationLibrary::add('photos_like', $my->id, $photo->creator, JText::sprintf('COM_COMMUNITY_PHOTO_LIKE_EMAIL_SUBJECT'), '', 'photos.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('photos.like');
                }
                break;
            case 'album':
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($itemId);
                if ($album->id) {
                    if ($album->groupid) {
                        $url = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&groupid=' . $album->groupid;
                    } else {
                        $url = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id;
                    }

                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('album', $album->name);
                    $params->set('album_url', $url);

                    CNotificationLibrary::add('photos_like', $my->id, $album->creator, JText::sprintf('COM_COMMUNITY_ALBUM_LIKE_EMAIL_SUBJECT'), '', 'album.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('album.like');
                }
                break;
            case 'videos':
                $video = JTable::getInstance('Video', 'CTable');
                $video->load($itemId);
                if ($video->id) {
                    if ($video->groupid) {
                        $url = 'index.php?option=com_community&view=videos&task=video&groupid=' . $video->groupid . '&videoid=' . $video->id;
                    } else {
                        $url = 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id;
                    }
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('video', $video->title);
                    $params->set('video_url', $url);

                    CNotificationLibrary::add('videos_like', $my->id, $video->creator, JText::sprintf('COM_COMMUNITY_VIDEO_LIKE_EMAIL_SUBJECT'), '', 'videos.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('videos.like');
                }
                break;
            case 'profile':
                $profile = CFactory::getUser($itemId);
                if ($profile->id) {
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id;
                    $params = new CParameter('');
                    $params->set('url', $url);

                    CNotificationLibrary::add('profile_like', $my->id, $profile->id, JText::sprintf('COM_COMMUNITY_PROFILE_LIKE_EMAIL_SUBJECT'), '', 'profile.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('profile.like');
                }
                break;
            case 'profile.status':
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load($itemId);

                if ($stream->id) {
                    $profile = CFactory::getUser($stream->actor);
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id;
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                    $params->set('stream_url', $url);

                    CNotificationLibrary::add('profile_stream_like', $my->id, $profile->id, JText::sprintf('COM_COMMUNITY_PROFILE_STREAM_LIKE_EMAIL_SUBJECT'), '', 'profile.stream.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('profile.stream.like');
                }
                break;
            case 'cover.upload':
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load(CPhotosHelper::getPhotoOfStream($itemId));

                if ($photo->id) {
                    $url = $photo->getRawPhotoURI();
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('photo', JText::_('COM_COMMUNITY_SINGULAR_PHOTO'));
                    $params->set('photo_url', $url);

                    CNotificationLibrary::add('photos_like', $my->id, $photo->creator, JText::sprintf('COM_COMMUNITY_COVER_LIKE_EMAIL_SUBJECT'), '', 'photos.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('photos.like');
                }
                break;
            case 'profile.avatar.upload':
                $stream = JTable::getInstance('Activity', 'CTable');
                $stream->load($itemId);

                if ($stream->id) {
                    $profile = CFactory::getUser($stream->actor);
                    $url = 'index.php?option=com_community&view=profile&userid=' . $profile->id . '&actid=' . $stream->id;
                    $params = new CParameter('');
                    $params->set('url', $url);
                    $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                    $params->set('stream_url', $url);

                    CNotificationLibrary::add('profile_stream_like', $my->id, $profile->id, JText::sprintf('COM_COMMUNITY_PROFILE_AVATAR_LIKE_EMAIL_SUBJECT'), '', 'profile.stream.like', $params);
                    /* Adding user points */
                    CUserPoints::assignPoint('profile.stream.like');
                }
                break;
            default:
                CUserPoints::assignPoint($element . '.like');
        }

		// Log user engagement
		CEngagement::log($element . '.like', $my->id);

        $like->store();
    }

    public function addDislike($element, $itemId) {
        $my = CFactory::getUser();

        $dislike = JTable::getInstance('Like', 'CTable');
        $dislike->loadInfo($element, $itemId);

        $dislike->element = $element;
        $dislike->uid = $itemId;

        $dislikesInArray = explode(',', $dislike->dislike);
        array_push($dislikesInArray, $my->id);
        $dislikesInArray = array_unique($dislikesInArray);
        $dislike->dislike = ltrim(implode(',', $dislikesInArray), ',');

        // Check if the user already like
        $likesInArray = explode(',', $dislike->like);
        if (in_array($my->id, $likesInArray)) {
            // Remove user like from array
            $key = array_search($my->id, $likesInArray);
            unset($likesInArray[$key]);

            $dislike->like = implode(',', $likesInArray);
        }

        $dislike->store();
    }

    public function unlike($element, $itemId) {
        $my = CFactory::getUser();

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        // Check if the user already like
        $likesInArray = explode(',', $like->like);
        if (in_array($my->id, $likesInArray)) {
            // Remove user like from array
            $key = array_search($my->id, $likesInArray);
            unset($likesInArray[$key]);

            $like->like = implode(',', $likesInArray);
        }

        // Check if the user already dislike
        $dislikesInArray = explode(',', $like->dislike);
        if (in_array($my->id, $dislikesInArray)) {
            // Remove user dislike from array
            $key = array_search($my->id, $dislikesInArray);
            unset($dislikesInArray[$key]);

            $like->dislike = implode(',', $dislikesInArray);
        }

        $like->store();
    }

    // Check if the user like this
    // Returns:
    // -1	- Unlike
    // 1	- Like
    // 0	- Dislike
    public function userLiked($element, $itemId, $userId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        // Check if user already like
        $likesInArray = explode(',', trim($like->like, ','));

        if (in_array($userId, $likesInArray)) {
            // Return 1, the user is liked
            return COMMUNITY_LIKE;
        }

        // Check if user already dislike
        $dislikesInArray = explode(',', trim($like->dislike, ','));

        if (in_array($userId, $dislikesInArray)) {
            // Return 0, the user is disliked
            return COMMUNITY_DISLIKE;
        }

        // Return -1 as neutral
        return COMMUNITY_UNLIKE;
    }

    /**
     * Can current $my user 'like' an item ?
     * - rule: friend can like friend's item (photos/vidoes/event)
     * @return bool
     */
    public function canLike() {
        $my = CFactory::getInstance();

        return ( $my->id != 0 );
    }

    /**
     * Return number of likes
     */
    public function getLikeCount($element, $itemId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);
        $count = 0;

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
            $count = count($likesInArray);
        }

        return $count;
    }

    /**
     * Return an array of user who likes the element
     * @return CUser objects
     */
    public function getWhoLikes($element, $itemId) {
        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $users = array();
        $likesInArray = array();

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
        }

        foreach ($likesInArray as $row) {
            $user = CFactory::getUser($row);
            $users[] = $user;
        }

        return $users;
    }

    /**
     *
     * @return bool True if element can be liked
     */
    public function enabled($element) {
        $config = CFactory::getConfig();

        // Element can also contain sub-element. eg:// photos.album
        // for enable/disable configuration, we only check the first component
        $elements = explode('.', $element);
        return ( $config->get('likes_' . $elements[0]) );
    }

    /**
     *
     * @return string
     */
    public function getHTML($element, $itemId, $userId) {
        if ($userId == 0) {
            return false;
        }
        // @rule: Only display likes html codes when likes is allowed.
        $config = CFactory::getConfig();

        if (!$this->enabled($element)) {
            return;
        }

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $userLiked = COMMUNITY_UNLIKE;
        $likesInArray = array();
        $dislikesInArray = array();
        $likes = 0;
        $dislikes = 0;

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
            $likes = count($likesInArray);
        }

        if (!empty($like->dislike)) {
            $dislikesInArray = explode(',', trim($like->dislike, ','));
            $dislikes = count($dislikesInArray);
        }

        $userLiked = $this->userLiked($element, $itemId, $userId);

        $tmpl = new CTemplate();

        // For rendering, we need to replace . with _ since it is not
        // a valid id
        $element = str_replace('.', '_', $element);
        $tmpl->set('likeId', 'like' . '-' . $element . '-' . $itemId);
        $tmpl->set('likes', $likes);
        $tmpl->set('dislikes', $dislikes);
        $tmpl->set('userLiked', $userLiked);

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->getHtmlPublic($element, $itemId);
        } else {
            return $tmpl->fetch('like.html');
        }
    }

    /**
     * Display like/dislike for public
     * @return string
     */
    public function getHtmlPublic($element, $itemId) {
        $config = CFactory::getConfig();

        if (!$config->get('likes_' . $element)) {
            return;
        }

        $like = JTable::getInstance('Like', 'CTable');
        $like->loadInfo($element, $itemId);

        $likesInArray = array();
        $dislikesInArray = array();
        $likes = 0;
        $dislikes = 0;

        if (!empty($like->like)) {
            $likesInArray = explode(',', trim($like->like, ','));
            $likes = count($likesInArray);
        }

        if (!empty($like->dislike)) {
            $dislikesInArray = explode(',', trim($like->dislike, ','));
            $dislikes = count($dislikesInArray);
        }

        $tmpl = new CTemplate();
        $tmpl->set('likes', $likes);
        $tmpl->set('dislikes', $dislikes);

        if ($config->get('show_like_public')) {
            return $tmpl->fetch('like.public');
        }
    }

}