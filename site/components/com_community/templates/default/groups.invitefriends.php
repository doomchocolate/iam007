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
<div id="community-groups-wrap" style="padding-top: 20px;">
<?php
	if( $friends )
	{
?>
	<div id="community-groups-news-items" class="app-box" style="width: 70%; float: left;margin-top: 0px;">
		<div class="app-box">
			<div class="app-box-l">
				<div class="app-box-r"><h2 class="expand app-box-title"><?php echo JText::_('COM_COMMUNITY_FRIENDS_MY_FRIENDS');?></h2></div>
			</div>
		</div>
		<ul id="friends-list">
		<?php
			foreach( $friends as $friend )
			{
		?>
			<li id="friend-<?php echo $friend->id;?>" class="friend-list">
				<span><img width="45" height="45" src="<?php echo $friend->getThumbAvatar();?>" alt="" /></span>
				<span class="friend-name">
					<a href="javascript:void(0);" onclick="joms.groups.addInvite('friend-<?php echo $friend->id;?>');"><?php echo $friend->getDisplayName();?></a>
				</span>
				<input name="invite-list[]" value="<?php echo $friend->id;?>" type="hidden" />
			</li>
		<?php
			}
		?>
		</ul>
	</div>
	
	<form name="invited-list" id="invited-list" action="<?php echo CRoute::getURI();?>" method="post">
	<div id="friend-selected-list">
		<ul id="friends-invited">
			<li style=""><?php echo JText::_('COM_COMMUNITY_INVITE_SELECTED_FRIENDS');?></li>
		</ul>
	</div>
	<div class="clr"></div>
	<label for="invite-message" style="font-weight: 700;"><?php echo JText::_('COM_COMMUNITY_INVITATION_MESSAGE');?></label>
	<textarea id="invite-message" name="invite-message" class="input textarea" style="margin-top: 10px; width: 92%; height: 100px;"></textarea>
	<div style="text-align: center;">
		<input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SUBMIT_BUTTON');?>" class="button" />
	</div>
	<input type="hidden" name="groupid" value="<?php echo $group->id;?>" /> 
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php
	}
	else
	{
?>
	<div class="cEmpty cAlert">
		<?php echo JText::_('COM_COMMUNITY_GROUPS_FRIENDS_INVITED');?>
	</div>
<?php
	}
?>
</div>