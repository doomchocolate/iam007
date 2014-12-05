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
<div class="cLayout cFriends-Pending">
	<div id="request-notice"></div>
	<?php
	if ( $rows ) {
	?>
	<ul class="cIndexList forFriendsPending cResetList">
	<?php
		foreach( $rows as $row )
		{
	?>
	<li>
		<div id="pending-<?php echo $row->connection_id; ?>" class="cIndex-Box clearfix">

			<a href="<?php echo $row->user->profileLink; ?>" class="cIndex-Avatar cFloat-L">
				<img src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?>" class="cAvatar" />
				<?php if($row->user->isOnline()): ?>
				<b class="cStatus-Online"><?php echo JText::_('COM_COMMUNITY_ONLINE'); ?></b>
				<?php endif; ?>
			</a>

			<div class="cIndex-Content">
				<div class="pull-right">
					<a href="javascript:void(0);" class="btn" onclick="jax.call('community','friends,ajaxRejectRequest','<?php echo $row->connection_id; ?>');">
						<?php echo JText::_('COM_COMMUNITY_REMOVE'); ?>
					</a>
					<a type="submit" class="btn btn-primary" onclick="jax.call('community' , 'friends,ajaxApproveRequest' , '<?php echo $row->connection_id; ?>');">
						<?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?>
					</a>
				</div>

				<h3 class="cIndex-Name cResetH">
					<a href="<?php echo $row->user->profileLink; ?>"><strong><?php echo $row->user->getDisplayName(); ?></strong></a>
				</h3>
				<?php if(!empty($row->msg)) { ?>
				<div class="cIndex-Status">
					<?php echo $row->msg; ?>
				</div>
				<?php } ?>
				<div class="cIndex-Actions">
					<?php if ($my->authorise('community.view', 'friends.pm.' . $row->user->id)): ?>
					<div>
						<i class="com-icon-mail-go"></i>
						<a onclick="joms.messaging.loadComposeWindow(<?php echo $row->user->id; ?>)" href="javascript:void(0);">
						<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
						</a>
					</div>
					<?php endif; ?>

					<div>
						<i class="com-icon-groups"></i>
						<?php echo JText::sprintf( (CStringHelper::isPlural($row->user->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT' , $row->user->friendsCount);?>
					</div>
				</div>
			</div>
			<!-- .cIndex-Content -->
		</div>
		<!-- .cIndex-Box -->
	</li>
	<?php
		}
	?>
	</ul>
	<?php
	}
	else
	{
	?>
	<div class="cEmpty cAlert">
		<?php echo JText::_('COM_COMMUNITY_PENDING_APPROVAL_EMPTY'); ?>
	</div>
	<?php } ?>

	<?php
	if ( $pagination->getPagesLinks() )
	{
	?>
	<div class="cPagination">
		<?php echo $pagination->getPagesLinks(); ?>
	</div>
	<?php
	}
	?>
</div>