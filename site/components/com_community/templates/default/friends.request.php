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
<div class="cLayout cFriends-Request">
	<?php
		if ( $rows ) 
		{
	?>
	<ul class="cIndexList forFriendsRequest cResetList">
	<?php
			foreach( $rows as $row ) 
			{ 
	?>

		<li>
			<div class="cIndex-Box clearfix">
				<a href="<?php echo $row->user->profileLink; ?>" class="cIndex-Avatar cFloat-L">
					<img class="cAvatar" src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?>" />
					<?php if($row->user->isOnline()) { ?>
					<b class="cStatus-Online"><?php echo JText::_('COM_COMMUNITY_ONLINE'); ?></b>
					<?php } ?>
				</a>
				
				<div class="cIndex-Content">
					<h3 class="cIndex-Name cResetH">
						<a href="<?php echo $row->user->profileLink; ?>"><strong><?php echo $row->user->getDisplayName(); ?></strong></a>
					</h3>
					
					<?php 
					if ( $row->user->getStatus() ) 
					{
					?>
					<div class="cIndex-Status"><?php echo $row->user->getStatus(); ?></div>
					<?php
					}
					?>

					<div class="cIndex-Actions">
						<?php if ($my->authorise('community.view', 'friends.pm.' . $row->user->id)):?>
						<div>
							<i class="com-icon-mail-go"></i>
							<a onclick="joms.messaging.loadComposeWindow(<?php echo $row->user->id; ?>)" href="javascript:void(0);">
							<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
							</a>
						</div>
						<?php endif; ?>
						
						<div>
							<i class="com-icon-groups"></i>
							<span>
								<?php echo JText::sprintf( (CStringHelper::isPlural($row->user->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT' , $row->user->friendsCount);?>
							</span>
						</div>

						<div class="cFloat-R">
							<i class="com-icon-groups-delete"></i>
							<a href="javascript:void(0);"  onclick="joms.friends.cancelRequest('<?php echo $row->user->id; ?>');">
								<?php echo JText::_('COM_COMMUNITY_REMOVE'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</li>
	<?php 
			}
	?>
	</ul>
	<?php
		} else { 
	?>
	<div class="cEmpty cAlert">
		<?php echo JText::_('COM_COMMUNITY_PENDING_REQUEST_EMPTY'); ?>
	</div>
	<?php
		} 
	?>
</div>