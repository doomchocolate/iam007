<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<div class="empty-message"><?php echo JText::_('COM_COMMUNITY_EVENTS_CREATED_DESCRIPTION');?></div>

<ul class="linklist">
	<li class="upload_avatar">
		<a href="<?php echo $linkUpload; ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_UPLOAD_AVATAR');?></a>
	</li>
	<li class="event_invite">
		<a href="<?php echo $linkInvite; ?>"><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS');?></a>
	</li>
	<li class="event_edit">
		<a href="<?php echo $linkEdit;?>">
			<?php echo JText::_('COM_COMMUNITY_EVENTS_EDIT_DETAILS');?>
		</a>
	</li>
	<li class="event_view">
		<a href="<?php echo $link; ?>">
			<?php echo JText::_('COM_COMMUNITY_EVENTS_VIEW');?>
		</a>
	</li>
</ul>