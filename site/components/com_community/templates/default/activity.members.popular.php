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

<div class="joms-stream-box joms-responsive clearfix" style="margin-bottom:0px;">
  <aside>
      <i class="joms-icon-bullhorn joms-icon-thumbnail"></i>
  </aside>
  <article>
		<a href="javascript:void(0);">
			<i class="joms-icon-bullhorn portrait-phone-only"></i> <?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOP_PROFILES'); ?>
		</a>
		<div class="separator"></div>
		<ul class="unstyled">
		<?php foreach( $members as $user ) {
				$numFriends = $user->getFriendCount();
		?>

		<li class="clearfix space-12">
			<img alt="<?php echo $this->escape($user->getDisplayName());?>" src="<?php echo $user->getThumbAvatar();?>" class="pull-left joms-stream-avatar" style="margin-right:8px; width:36px;" />
			<div>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id );?>" >
						<h5 class="reset-gap"><?php echo CTooltip::cAvatarTooltip($user); ?></h5>
				</a>
				<p class="reset-gap">
					<a href="#"><?php //echo JText::sprintf( (CStringHelper::isPlural($numFriends)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $numFriends); // removed as requested in sprint 7 ?></a>
						<?php
							$isFriend =  CFriendsHelper::isConnected( $user->id, $my->id );

							$addFriend 	= ((! $isFriend) && ($my->id != 0) && $my->id != $user->id) ? true : false;
							if($addFriend)
							{
								$isWaitingApproval =	CFriendsHelper::isWaitingApproval($my->id, $user->id);
							?>
									<?php if(isset($user->isMyFriend) && $user->isMyFriend==1){ ?>
										&nbsp;<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $user->id;?>')"><span><?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?></span></a>
									<?php } else { ?>
										<?php if(!$isWaitingApproval){?>
											&nbsp;<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $user->id;?>')"><span><?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?></span></a>
										<?php }else{ ?>
											&nbsp;<span><?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?></span>
										<?php }?>
									<?php } ?>
							<?php
							}
							else
							{
							?>
							<?php
								if( ($my->id != $user->id) && ($my->id !== 0) )
								{
							?>
								<span> <?php echo JText::_('COM_COMMUNITY_PROFILE_ADDED_AS_FRIEND'); ?></span>
							<?php
								}
							}
							?>
				</p>
			</div>
		</li>

		<?php } ?>
		</ul>

  </article>
</div>
