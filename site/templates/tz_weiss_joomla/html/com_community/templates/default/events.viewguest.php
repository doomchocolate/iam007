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
CAssets::attach('assets/easytabs/jquery.easytabs.min.js', 'js');
?>
<?php if( $guests ) { ?>
<div id="notice"></div>
	<ul class="cIndexList forEventGuest cResetList">
	<?php foreach( $guests as $guest ){ ?>
	<li id="member_<?php echo $guest->id;?>">
		<div class="cIndex-Box clearfix">

			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $guest->id); ?>" class="cIndex-Avatar cFloat-L">
				<img class="cAvatar" src="<?php echo $guest->getThumbAvatar(); ?>" alt="<?php echo $guest->getDisplayName(); ?>" />
				<?php if($guest->isOnline()){ ?>
				<b class="cStatus-Online"><?php echo JText::_('COM_COMMUNITY_ONLINE'); ?></b>
				<?php } ?>
			</a>

			<div class="cIndex-Content">
				<h3 class="cIndex-Name cResetH">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $guest->id); ?>"><strong><?php echo $guest->getDisplayName(); ?></strong></a>
				</h3>

				<?php if ( $guest->getStatus() ) { ?>
				<div class="cIndex-Status"><?php echo $guest->getStatus() ;?></div>
				<?php } ?>

				<div class="cIndex-Actions">
					<div class="action">
						<i class="com-icon-groups"></i>
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid=' . $guest->id );?>">
							<?php echo JText::sprintf( (CStringHelper::isPlural($guest->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT' , $guest->friendsCount);?>
						</a>
					</div>

					<?php
						if( $my->id != $guest->id && $config->get('enablepm') )
						{
					?>
					<div>
						<i class="com-icon-mail-go"></i>
						<a onclick="joms.messaging.loadComposeWindow(<?php echo $guest->id; ?>)" href="javascript:void(0);">
							<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
						</a>
					</div>
					<?php
						}
					?>

					<?php
						if( ($guest->statusType== COMMUNITY_EVENT_STATUS_REQUESTINVITE) && $handler->manageable() )
							{
					?>
					<div id="events-approve-<?php echo $guest->id;?>">
						<i class="com-icon-tick"></i>
						<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxApproveInvite', '<?php echo $guest->id;?>' , '<?php echo $eventid;?>');">
							<?php echo JText::_('COM_COMMUNITY_PENDING_ACTION_APPROVE'); ?>
						</a>
					</div>
					<?php
						}
					?>

					<?php
						if( $handler->manageable() && !$guest->isMe && $guest->statusType == COMMUNITY_EVENT_STATUS_ATTEND && !$guest->isAdmin )
						{
					?>
						<div>
							<i class="com-award-silver"></i>
							<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxManageAdmin','<?php echo $guest->id;?>','<?php echo $eventid;?>','add');">
								<?php echo JText::_('COM_COMMUNITY_EVENTS_ADMIN_SET'); ?>
							</a>
						</div>
					<?php } else if( $handler->manageable() && !$guest->isMe && $guest->isAdmin ) { ?>
						<div>
							<i class="com-award-silver"></i>
							<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxManageAdmin','<?php echo $guest->id;?>','<?php echo $eventid;?>','remove');"><?php echo JText::_('COM_COMMUNITY_EVENTS_ADMIN_REVERT'); ?></a>
						</div>
					<?php
						}
					?>
					<?php if (!$guest->isMe && ($handler->manageable() || $event->isAdmin($my->id)) ){ ?>
						<?php if($guest->statusType == COMMUNITY_EVENT_STATUS_BLOCKED){ ?>
						<div>
							<i class="com-award-block"></i>
							<a href="javascript:void(0);" onclick="joms.events.confirmUnblockGuest('<?php echo $guest->id;?>','<?php echo $eventid;?>');">
								<?php echo JText::_('COM_COMMUNITY_EVENTS_UNBLOCK'); ?>
							</a>
						</div>
						<?php } ?>
					<?php } ?>

					<?php if( $handler->manageable() ){ ?>
					<div class="cFloat-R">
						<i class="com-icon-groups-delete"></i>
						<a href="javascript:void(0);" onclick="joms.events.confirmRemoveGuest('<?php echo $guest->id; ?>','<?php echo $eventid;?>');">
							<?php echo JText::_('COM_COMMUNITY_REMOVE');?>
						</a>
					</div>
					<?php } ?>
				</div>
			</div>
			<!-- END: .cIndex-Content -->
		</div>
		<!-- END: .cIndex-Box -->
	</li>
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
<?php } else { ?>
<div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_USERS'); ?></div>
<?php } ?>