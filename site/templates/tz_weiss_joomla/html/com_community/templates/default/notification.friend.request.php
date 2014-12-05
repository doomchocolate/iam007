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
<?php if (count ($rows) > 0 ) { ?>
<div class="cWindowNotification forGeneral">
	<p class="cStreamSubject">
		<b><?php echo JText::_('COM_COMMUNITY_NOTI_NEW_FRIEND_REQUEST'); ?></b>
	</p>

	<ul class="cWindowStream forGeneral cResetList">
		<?php foreach ( $rows as $row ) : ?>
		<li id="noti-request-group-<?php echo $row->id; ?>">
			<a href="<?php echo $row->user->profileLink; ?>" class="cStream-Avatar cFloat-L">
				<img src="<?php echo $row->user->getThumbAvatar(); ?>" class="cAvatar" alt="<?php echo $row->user->getDisplayName();?>"/>
			</a>
			<div class="joms-stream-content">
				<div class="cStream-Headline" id="msg-pending-<?php echo $row->connection_id; ?>">
					<?php echo JText::sprintf('COM_COMMUNITY_NOTI_ADD_YOU_AS_FRIEND' , $row->user->getDisplayName() ,  CRoute::_('index.php?option=com_community&view=friends&task=pending', false)); ?>
				</div>
				<div class="cStream-Actions" id="noti-answer-friend-<?php echo $row->connection_id; ?>">
					<a href="javascript:void(0);" onclick="joms.jQuery('#noti-answer-friend-<?php echo $row->connection_id; ?>').remove(); jax.call('community' , 'notification,ajaxApproveRequest' , '<?php echo $row->connection_id; ?>');">
						<?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?>
					</a>
					<a href="javascript:void(0);" onclick="joms.jQuery('#noti-answer-friend-<?php echo $row->connection_id; ?>').remove(); jax.call('community','notification,ajaxRejectRequest','<?php echo $row->connection_id; ?>');">
						<?php echo JText::_('COM_COMMUNITY_REMOVE'); ?>
					</a>
				</div>
				<div id="error-pending-<?php echo $row->connection_id; ?>"></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php } ?>