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

require_once(JPATH_ROOT . '/components/com_community/libraries/tooltip.php');

/**
 *
 */
class CommunityFrontpageController extends CommunityBaseController {

    /**
     * Display the front-end of our community component
     *
     * @todo: 	what to show first should be configurable via the component
     * 			parameters
     */
    var $_icon = 'front';

    public function ajaxIphoneFrontpage() {
        $objResponse = new JAXResponse();
        $document = JFactory::getDocument();

        $viewType = $document->getType();
        $view = $this->getView('frontpage', '', $viewType);

        $html = '';

        ob_start();
        $this->display();
        $content = ob_get_contents();
        ob_end_clean();

        $objResponse->addAssign('social-content', 'innerHTML', $content);
        return $objResponse->sendResponse();
    }

    public function display($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();

        $view = $this->getView('frontpage', '', $viewType);
        echo $view->get('display');
    }

    public function ajaxGetFeaturedMember($limit) {
        $filter = JFilterInput::getInstance();
        $limit = $filter->clean($limit, 'int');

        $limit = max(0, $limit);

        $featured = new CFeatured(FEATURED_USERS);
        $featuredUsers = $featured->getItemIds();

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView('frontpage', '', $viewType);

        if (!empty($featuredUsers)) {
            shuffle($featuredUsers);
            $featuredUsersObj = array();
            foreach ($featuredUsers as $featured) {
                $obj = CFactory::getUser($featured);
                if ($obj->block == 0)
                    $featuredUsersObj[] = $obj; //ignore blocked/disabled user
            }

            $data['members'] = $featuredUsersObj;
            $data['limit'] = ( count($featuredUsers) > $limit ) ? $limit : count($featuredUsers);
            $html = $view->get('getMembersHTML', $data);
        } else {
            $html = JText::_('COM_COMMUNITY_NO_FEATURED_MEMBERS_YET');
        }


        $objResponse = new JAXResponse();
        $objResponse->addAssign('latest-members-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    /**
     *
     * @param type $limit
     * @return type
     */
    public function ajaxGetNewestMember($limit) {
        $filter = JFilterInput::getInstance();
        $limit = $filter->clean($limit, 'int');

        $limit = max(0, $limit);

        $model = CFactory::getModel('user');
        $latestMembers = $model->getLatestMember($limit);

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView('frontpage', '', $viewType);

        if (!empty($latestMembers)) {
            shuffle($latestMembers);

            $data['members'] = $latestMembers;
            $data['limit'] = ( count($latestMembers) > $limit ) ? $limit : count($latestMembers);
            $html = $view->get('getMembersHTML', $data);
        } else {
            $html = JText::_('COM_COMMUNITY_NO_NEWEST_MEMBER');
        }

        $objResponse = new JAXResponse();
        $objResponse->addAssign('latest-members-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    public function ajaxGetActiveMember($limit) {
        $filter = JFilterInput::getInstance();
        $limit = $filter->clean($limit, 'int');

        $limit = max(0, $limit);
        $model = CFactory::getModel('user');
        $activeMembers = $model->getActiveMember($limit);

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView('frontpage', '', $viewType);

        if (!empty($activeMembers)) {
            $data['members'] = $activeMembers;
            $data['limit'] = ( count($activeMembers) > $limit ) ? $limit : count($activeMembers);

            $html = $view->get('getMembersHTML', $data);
        } else {
            $html = JText::_('COM_COMMUNITY_NO_ACTIVE_MEMBERS_YET');
        }

        $objResponse = new JAXResponse();
        $objResponse->addAssign('latest-members-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    public function ajaxGetPopularMember($limit) {
        $filter = JFilterInput::getInstance();
        $limit = $filter->clean($limit, 'int');

        $limit = max(0, $limit);
        $objResponse = new JAXResponse();
        $html = '';

        $model = CFactory::getModel('user');
        $popularMembers = $model->getPopularMember($limit);

        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $view = $this->getView('frontpage', '', $viewType);

        if (!empty($popularMembers)) {
            $data['members'] = $popularMembers;
            $data['limit'] = ( count($popularMembers) > $limit ) ? $limit : count($popularMembers);
            $html = $view->get('getMembersHTML', $data);
        }

        $objResponse->addAssign('latest-members-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    public function prepareVideosData($videos, $limit, &$objResponse) {
        //CFactory::load( 'helpers', 'videos' );
        //CFactory::load( 'helpers', 'string' );
        //CFactory::load( 'libraries', 'videos' );

        $data = array();
        for ($i = 0; $i < $limit; $i++) {
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($videos[$i]->id);

// 			$video->title			= htmlspecialchars( $video->title , ENT_QUOTES , 'UTF-8' );
// 			$video->description		= htmlspecialchars( $video->description , ENT_QUOTES , 'UTF-8' );

            $data[] = $video;
        }

        $tmpl = new CTemplate();
        $tmpl->set('data', $data);
        $tmpl->set('thumbWidth', CVideoLibrary::thumbSize('width'));
        $tmpl->set('thumbHeight', CVideoLibrary::thumbSize('height'));
        return $tmpl->fetch('frontpage.latestvideos');
    }

    public function ajaxGetActivities($filter, $user_id = 0, $view = '') {

        $objResponse = new JAXResponse();
        $input_filter = JFilterInput::getInstance();
        $filter = $input_filter->clean($filter, 'string');
        $user_id = $input_filter->clean($user_id, 'int');
        $view = $input_filter->clean($view, 'string');

        $html = CActivities::getActivitiesByFilter($filter, $user_id);
        $html = '<div class="cActivity-LoadLatest joms-latest-activities-container"><a id="activity-update-click" class="btn btn-block" href="javascript:void(0);"></a></div>' . $html;

        $objResponse->addAssign('activity-stream-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        // Set main UL data attributes 'filter'
        $objResponse->addScriptCall("joms.jQuery('#activity-stream-container ul.cStreamList').data('filter', '" . $filter . "');");



        return $objResponse->sendResponse();
    }

    public function ajaxGetFeaturedVideos($limit) {
        $filter = JFilterInput::getInstance();
        $limit = $filter->clean($limit, 'int');

        $limit = max(0, $limit);

        $my = CFactory::getUser();
        $permissions = ($my->id == 0) ? 0 : 20;

        //CFactory::load( 'libraries', 'featured' );

        $featured = new CFeatured(FEATURED_VIDEOS);
        $featuredVideos = $featured->getItemIds();

        if (!empty($featuredVideos)) {
            $videoId = array();
            foreach ($featuredVideos as $featuredVideo) {
                $videoId[] = $featuredVideo;
            }

            $objResponse = new JAXResponse();
            $oversampledTotal = $limit * COMMUNITY_OVERSAMPLING_FACTOR;

            $model = CFactory::getModel('videos');
            $filter = array(
                'id' => $videoId,
                'status' => 'ready',
                'permissions' => $permissions,
                'sorting' => 'latest',
                'limit' => $oversampledTotal
            );

            $featuredVideos = $model->getVideos($filter, true);

            if (!empty($featuredVideos)) {
                shuffle($featuredVideos);
                $maxLatestCount = ( count($featuredVideos) > $limit ) ? $limit : count($featuredVideos);
                $html = $this->prepareVideosData($featuredVideos, $maxLatestCount, $objResponse);
            } else {
                $html = JText::_('COM_COMMUNITY_VIDEOS_NO_FEATURED_VIDEOS_YET');
            }
        } else {
            $html = JText::_('COM_COMMUNITY_VIDEOS_NO_FEATURED_VIDEOS_YET');
        }

        $objResponse = new JAXResponse();
        $objResponse->addAssign('latest-videos-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    public function ajaxGetNewestVideos($limit) {
        $limit = max(0, $limit);
        $my = CFactory::getUser();
        $permissions = ($my->id == 0) ? 0 : 20;


        $html = '';
        $oversampledTotal = $limit * COMMUNITY_OVERSAMPLING_FACTOR;

        $model = CFactory::getModel('videos');
        $filter = array(
            'status' => 'ready',
            'permissions' => $permissions,
            'or_group_privacy' => 0,
            'sorting' => 'latest',
            'limit' => $oversampledTotal
        );

        $latestVideos = $model->getVideos($filter, true);

        if (!empty($latestVideos)) {
            shuffle($latestVideos);
            $maxLatestCount = ( count($latestVideos) > $limit ) ? $limit : count($latestVideos);
            $html = $this->prepareVideosData($latestVideos, $maxLatestCount, $objResponse);
        } else {
            $html = JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO');
        }


        $objResponse = new JAXResponse();
        $objResponse->addAssign('latest-videos-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    public function ajaxGetPopularVideos($limit) {
        $limit = max(0, $limit);
        $model = CFactory::getModel('videos');
        $my = CFactory::getUser();
        $oversampledTotal = $limit * COMMUNITY_OVERSAMPLING_FACTOR;
        $html = '';

        $filter = array(
            'status' => 'ready',
            'permissions' => ($my->id == 0) ? 0 : 20,
            'or_group_privacy' => 0,
            'sorting' => 'mostwalls',
            'limit' => $oversampledTotal
        );
        $popularVideos = $model->getVideos($filter, true);

        if (!empty($popularVideos)) {
            shuffle($popularVideos);
            $maxLatestCount = ( count($popularVideos) > $limit ) ? $limit : count($popularVideos);
            $html = $this->prepareVideosData($popularVideos, $maxLatestCount, $objResponse);
        } else {
            $html = JText::_('COM_COMMUNITY_VIDEOS_NO_POPULAR_VIDEOS_YET');
        }

        $objResponse = new JAXResponse();
        $objResponse->addAssign('latest-videos-container', 'innerHTML', $html);
        $objResponse->addScriptCall("joms.filters.hideLoading();");

        return $objResponse->sendResponse();
    }

    public function fluidgrid() {
        $tmpl = new CTemplate();
        echo $tmpl->fetch('fluidgrid');
    }

}
