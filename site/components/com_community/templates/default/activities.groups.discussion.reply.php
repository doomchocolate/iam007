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

// Setup event table
$group = $this->group;

// Load params
$param = new JRegistry($this->act->params);
$action = $param->get('action');
$actors = $param->get('actors');
$this->set('actors', $actors);

$discussion = JTable::getInstance('Discussion' , 'CTable' );
$discussion->load($act->cid);

if($user->block) {
	$discussion->title = JText::_('COM_COMMUNITY_CENSORED');
}

// get created date time
$date = JFactory::getDate($this->act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
  $createdTime = CTimeHelper::timeLapse($date);
} else {
  $createdTime = $date->format($config->get('profileDateFormat'));
}

$stream = new stdClass();
$stream->actor = $user;
$stream->target = null;
$stream->headline = '<a class="cStream-Author" href="' .CUrlHelper::userLink($user->id).'">'.$user->getDisplayName().'</a>'
		. JText::sprintf('COM_COMMUNITY_GROUPS_REPLY_DISCUSSION' , CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$discussion->groupid.'&topicid='.$discussion->id), $discussion->title );
$stream->message = "";
$stream->group = $group;
$stream->groupid = $discussion->groupid;
$stream->attachments = array();

$attachment = new stdClass();
$attachment->type = 'discussion_reply';
$attachment->message = $this->escape(JHTML::_('string.truncate', $this->act->content, $config->getInt('streamcontentlength'), true, false ));
$stream->attachments[] = $attachment;
$stream->access=$act->access;
$stream->createdtime = $createdTime;

$this->set('stream', $stream);
$this->load('activities.stream');