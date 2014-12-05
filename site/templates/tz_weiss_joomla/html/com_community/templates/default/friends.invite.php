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

<!--COMMUNITY FORM-->
<div class="cLayout cFriends-Invite">
	<form name="jsform-friends-invite" action="<?php echo CRoute::getURI(); ?>" method="post" class="cForm community-form">
		<ul class="cFormList cFormHorizontal cResetList">
			<li>
				<?php echo JText::_('COM_COMMUNITY_INVITE_TEXT'); ?>
			</li>

			<?php echo $beforeFormDisplay;?>

			<li class="has-seperator">
				<label class="form-label">
					<?php echo JText::_('COM_COMMUNITY_INVITE_FROM'); ?>:
				</label>
				<div class="form-field">
					<span class="uneditable-input">
						<b><?php echo $my->email; ?></b>
					</span>
				</div>
			</li>

			<li>
				<label class="form-label">
					*<?php echo JText::_('COM_COMMUNITY_INVITE_TO'); ?>:
				</label>
				<div class="form-field">
					<textarea class="required" name="emails"><?php echo (! empty($post['emails'])) ? $post['emails'] : '' ; ?></textarea>
					<span class="form-helper"><?php echo JText::_('COM_COMMUNITY_SEPARATE_BY_COMMA'); ?></span>
				</div>
			</li>

			<li>
				<label class="form-label">
				<?php echo JText::_('COM_COMMUNITY_INVITE_MESSAGE'); ?>:
				</label>
				<div class="form-field">
					<textarea name="message"><?php echo (! empty($post['message'])) ? $post['message'] : '' ; ?></textarea>
					<span class="form-helper"><?php echo JText::_('COM_COMMUNITY_OPTIONAL');?></span>
				</div>
			</li>

			<?php echo $afterFormDisplay;?>

			<li class="has-seperator">
				<div class="form-field">
					<span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span>
				</div>
			</li>
			<li class="form-action">
				<div class="form-field">
					<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_INVITE_BUTTON'); ?>">
					<input type="hidden" name="action" value="invite" />
				</div>
			</li>
		</ul>
	</form>

</div>
<!--end: COMMUNITY FORM-->





<?php if( !empty( $friends ) ) : ?>
<hr class="cSeperator" />

<div class="cLayout cFriendsSuggestions">
	<h3 class="cTitle"><?php echo JText::_('COM_COMMUNITY_FRIENDS_SUGGESTIONS'); ?></h3>

	<ul class="cIndexList forFriendsInvite cResetList">
		<?php foreach( $friends as $user ) : ?>
		<li>
			<div class="cIndex-Box clearfix">
				<a href="<?php echo $user->profileLink; ?>" class="cIndex-Avatar cFloat-L">
					<img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" class="cAvatar" />
					<?php if($user->isOnline()) { ?>
					<b class="cStatus-Online"><?php echo JText::_('COM_COMMUNITY_ONLINE'); ?></b>
					<?php } ?>
				</a>

				<div class="cIndex-Content">
					<h3 class="cIndex-Name cResetH">
						<a href="<?php echo $user->profileLink; ?>"><?php echo $user->getDisplayName(); ?></a>
					</h3>

					<?php
					if ( $user->getStatus() )
					{
					?>
					<div class="cIndex-Status"><?php echo $user->getStatus() ;?></div>
					<?php
					}
					?>

					<div class="cIndex-Actions">
						<div>
							<i class="com-icon-user-plus"></i>
							<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $user->id;?>')">
								<?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?>
							</a>
						</div>

						<?php if ($my->authorise('community.view', 'friends.pm.' . $user->id)):?>
				        <div>
				        	<i class="com-icon-mail-go"></i>
				        	<a onclick="joms.messaging.loadComposeWindow(<?php echo $user->id; ?>)" href="javascript:void(0);">
				        		<?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?>
				        	</a>
				        </div>
				        <?php endif; ?>

					    <div>
					    	<i class="com-icon-groups"></i>
					    	<a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid=' . $user->id );?>">
					    		<?php echo JText::sprintf( (CStringHelper::isPlural($user->friendsCount)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT' , $user->friendsCount);?>
					    	</a>
					    </div>
					</div>
				</div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>