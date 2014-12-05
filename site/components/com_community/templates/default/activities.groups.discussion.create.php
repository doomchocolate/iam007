<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

$user = CFactory::getUser($this->act->actor);
$config = CFactory::getConfig();

// get created date time
$date = JFactory::getDate($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}


// Setup group table
$group = $this->group;

// Setup Discussion Table
$discussion = JTable::getInstance('Discussion' , 'CTable' );
$discussion->load($act->cid);
$discussionLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id );

if($user->block) {
	$discussion->title = $discussion->message = JText::_('COM_COMMUNITY_CENSORED');
}

// Load params
$action = $act->params->get('action');
$actors = $act->params->get('actors');
$this->set('actors', $actors);

$stream = new stdClass();
$stream->actor = $user;
$stream->target = null;
$stream->datetime = $createdTime;
$stream->headline = '<a class="joms-stream-author" href="' .CUrlHelper::userLink($user->id).'">'.$user->getDisplayName().'</a> ' . JTEXT::_('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION') . '<p class="joms-share-meta date">' . $stream->datetime . '</p>';
$stream->message = "";
$stream->group = $group;
$stream->groupid = $group->id;
$stream->attachments = array();
$stream->link = $discussionLink;
$stream->title = $discussion->title;
$stream->access = $act->access;

$attachment = new stdClass();
$attachment->type = 'create_discussion';
$attachment->message = $this->escape(JHTML::_('string.truncate', $discussion->message, $config->getInt('streamcontentlength'), true, false ));
$stream->attachments[] = $attachment;

$this->set('stream', $stream);
$this->load('activities.stream');