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


<form method="post" action="<?php echo CRoute::getURI();?>" name="jsform-profile-privacy">

<div class="ctitle"><h2><?php echo JText::_('COM_COMMUNITY_EDIT_YOUR_PRIVACY');?></h2></div>
<p><?php echo JText::_('COM_COMMUNITY_EDIT_PRIVACY_DESCRIPTION');?></p>

<table class="formtable" cellspacing="1" cellpadding="0">
<?php echo $beforeFormDisplay;?>
<!-- profile privacy -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_PROFILE_FIELD');?></label>
	</td>
	<td class="privacyc"><?php echo CPrivacy::getHTML( 'privacyProfileView' , $params->get( 'privacyProfileView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE , array( 'public' => true , 'members' => true , 'friends' => true , 'self' => false ) ); ?></td>
	<td></td>
</tr>


<!-- friends privacy -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyFriendsView' , $params->get( 'privacyFriendsView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td></td>
</tr>


<!-- photos privacy -->
<?php if($config->get('enablephotos')): ?>
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_PHOTOS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyPhotoView' , $params->get( 'privacyPhotoView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td class="value"><input type="checkbox" name="resetPrivacyPhotoView" class="input checkbox" /> <?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_APPLY_TO_ALL'); ?></td>
</tr>
<?php endif;?>

<!-- videos privacy -->
<?php if($config->get('enablevideos')): ?>
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_VIDEOS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyVideoView' , $params->get( 'privacyVideoView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td class="value"><input type="checkbox" name="resetPrivacyVideoView" class="input checkbox" /> <?php echo JText::_('COM_COMMUNITY_VIDEOS_PRIVACY_RESET_ALL'); ?></td>
</tr>
<?php endif; ?>


<?php if( $config->get( 'enablegroups' ) ){ ?>
<!-- groups privacy -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_GROUPS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyGroupsView' , $params->get( 'privacyGroupsView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td></td>
</tr>
<?php } ?>

<?php echo $afterFormDisplay;?>
<tr>
	<td class="key"></td>
	<td class="value">
		<input type="hidden" value="save" name="action" />
		<input type="submit" class="button" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON'); ?>" />
	</td>
</tr>
</table>

</form>

<div id="community-banlists-wrap" style="padding-top: 20px;">
	
	<div id="community-banlists-news-items" class="app-box" style="width: 100%; float: left;margin-top: 0px;">
		<div class="ctitle"><h2><?php echo JText::_('COM_COMMUNITY_MY_BLOCKED_LIST');?></h2></div>
		<ul id="friends-list">
		<?php
			foreach( $blocklists as $row )
			{
				$user	= CFactory::getUser( $row->blocked_userid );
		?>
			<li id="friend-<?php echo $user->id;?>" class="friend-list">
				<span><img width="45" height="45" src="<?php echo $user->getThumbAvatar();?>" alt="" /></span>
				<span class="friend-name">
					<?php echo $user->getDisplayName(); ?>
					<a class="remove" href="javascript:void(0);" onclick="joms.users.unBlockUser('<?php echo $row->blocked_userid;  ?>','privacy');">
					   <?php echo JText::_('COM_COMMUNITY_UNBLOCK'); ?>
					</a>
				</span>
			</li>
		<?php
			}
		?>
		</ul>
	</div>
</div>
<script type="text/javascript">
joms.jQuery( document ).ready( function(){
  	joms.privacy.init();
});
</script>
