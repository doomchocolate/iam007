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
<div class="cWindowNotification forGroup-Invitations">
	<p class="cWindowSubject">
		<b><?php echo JText::_('COM_COMMUNITY_NOTI_NEW_GROUP_INVITATION'); ?></b>
	</p>

	<ul class="cWindowStream forGroups-Invitations cResetList">
	<?php foreach ( $gRows as $row ) : ?>
	<li id="noti-pending-group-<?php echo $row->groupid; ?>">
		<a href="<?php echo $row->url; ?>" class="cStream-Avatar cFloat-L">
			<img src="<?php echo $row->groupAvatar; ?>" class="cAvatar" alt="<?php echo $this->escape($row->name); ?>"/>
		</a>
		<div class="joms-stream-content">
			<div class="cStream-Headline" id="msg-pending-<?php echo $row->groupid; ?>" >
				<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_INVITED_NOTIFICATION' , $row->invitor->getDisplayName() ,  '<a href="'.$row->url.'">'.$row->name.'</a>'); ?>
			</div>
			<div class="cStream-Actions" id="noti-answer-group-<?php echo $row->groupid; ?>" class="notiAction" >
				<a class="action" href="javascript:void(0);" onclick="joms.jQuery('#noti-answer-group-<?php echo $row->groupid; ?>').remove(); jax.call('community' , 'notification,ajaxGroupJoinInvitation' , '<?php echo $row->groupid ?>');">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_ACCEPT'); ?>
				</a>
				<b>&middot;</b>
				<a class="action" href="javascript:void(0);" onclick="joms.jQuery('#noti-answer-group-<?php echo $row->groupid; ?>').remove(); jax.call('community','notification,ajaxGroupRejectInvitation', '<?php echo $row->groupid ?>');">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
				</a>
			</div>
			<div class="cStream-Error" id="error-pending-<?php echo $row->groupid; ?>"></div>
		</div>
	</li>
	<?php endforeach; ?>
	</ul>
</div>