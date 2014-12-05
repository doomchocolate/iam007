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
<div class="cWindowNotification forEvent-Invitations">
	<p class="cWindowSubject">
		<b><?php echo JText::_('COM_COMMUNITY_NOTI_NEW_EVENT_INVITATION'); ?></b>
	</p>

	<ul class="cWindowStream forGroups-Invitations cResetList">
	<?php foreach ( $rows as $row ) : ?>
	<li id="noti-pending-group-<?php echo $row->eventid; ?>">
		<a href="<?php echo $row->url; ?>" class="cStream-Avatar cFloat-L">
			<img src="<?php echo $row->eventAvatar; ?>" class="cAvatar" alt="<?php echo $this->escape($row->title); ?>"/>
		</a>
		<div class="joms-stream-content">
			<div class="cStream-Headline" id="msg-pending-<?php echo $row->eventid; ?>" >
				<?php echo JText::sprintf('COM_COMMUNITY_EVENTS_INVITED_NOTIFICATION' , $row->invitor->getDisplayName() ,  '<a href="'.$row->url.'">'.$row->title.'</a>'); ?>
			</div>
			<div id="noti-answer-event-<?php echo $row->id; ?>" class="cStream-Actions">
				<a class="action" style="text-indent:0;margin-right:5px;" href="javascript:void(0);" onclick="joms.jQuery('#noti-answer-event-<?php echo $row->id; ?>').remove(); jax.call('community' , 'notification,ajaxJoinInvitation' , '<?php echo $row->id; ?>', '<?php echo $row->eventid ?>');">
					    <?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?>
				</a>
				<a class="action" style="text-indent: 0;" href="javascript:void(0);" onclick="joms.jQuery('#noti-answer-event-<?php echo $row->id; ?>').remove(); jax.call('community','notification,ajaxRejectInvitation','<?php echo $row->id; ?>', '<?php echo $row->eventid ?>');">
					    <?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
				</a>
			</div>
			<div id="error-pending-<?php echo $row->id; ?>"></div>
		</div>
	</li>
	<?php endforeach; ?>
	</ul>
</div>