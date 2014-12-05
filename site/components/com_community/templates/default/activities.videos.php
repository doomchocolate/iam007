<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
$user	= CFactory::getUser($this->act->actor);
if ( !is_object($act->params))
    $act->params = new JRegistry ($act->params);
$param	= $act->params;
$video	= JTable::getInstance( 'Video' , 'CTable' );
$video->load( $act->cid );
$this->set('video', $video);

// Attach to $act since it is used by the video library
$act->video = $video;

// Load saperate template for featured videos
if( $act->app == 'videos.featured'){
	$this->load('activities.videos.featured');
	return;
}

// Load saperate template for comment on videos
// param is legacy code, its kept to make sure we are able to distinguish this as a comment
if ($act->app == 'videos.comment' || $param->get('action') == 'wall') {
	$this->load('activities.videos.comment');
	return;
}

$stream = new stdClass();

if($act->groupid){
	$group	= JTable::getInstance( 'Group' , 'CTable' );
	$group->load( $act->groupid );
	$stream->group = $group;
	$act->appTitle = $group->name;
}

// get created date time
$date = JFactory::getDate($this->act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

$stream->actor = $user;
$stream->target = null;
$stream->headline = CVideos::getActivityTitleHTML($act);
$stream->message = "";
$stream->groupid = $act->groupid;
$stream->eventid = $act->eventid;
$stream->attachments = array();
$stream->access = $video->permissions;
$stream->createdtime = $createdTime;


$attachment = new stdClass();
$attachment->type = 'video';
$attachment->id = $act->cid;
$attachment->title = $video->title;
$attachment->thumbnail = $video->getThumbnail();
$attachment->description = $video->description;
$attachment->duration = CVideosHelper::toNiceHMS(CVideosHelper::formatDuration($video->getDuration()));
$attachment->access = $act->access;
$attachment->video_type = $video->type;
$attachment->link = $video->path;
$stream->attachments[] = $attachment;

$quoteContent = CActivities::format($act->title);
if(!empty($quoteContent) && $param->get('style') == COMMUNITY_STREAM_STYLE){
	$attachment = new stdClass();
	$attachment->type = 'text';
	$attachment->message = $quoteContent;
	$stream->attachments[] = $attachment;
}

$this->set('stream', $stream);
$this->load('activities.stream');
