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

<div class="cToolBox cProfile-ToolBox clearfix">
	<div class="row-fluid">
	<div class="span4">
		<a class="cToolBox-Avatar cFloat-L" href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id); ?>">
			<img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" class="cAvatar" />
		</a>
		<b class="cToolBox-Name"><?php echo $user->getDisplayName(); ?></b>
		<div class="small">
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$user->id); ?>">
				<?php echo JText::_('COM_COMMUNITY_GO_TO_PROFILE'); ?>
			</a>
		</div>
	</div>
	<div class="span8">
		<ul class="cToolBox-Options unstyled">
			<?php if(!$isFriend && !$isMine && !CFriendsHelper::isWaitingApproval($my->id, $user->id)) { ?>
			<li>
				<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $user->id;?>')">
					<i class="com-icon-user-plus"></i>
					<span><?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?></span>
				</a>
			</li>
			<?php } ?>

			<?php if($config->get('enablephotos')): ?>
			<li>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$user->id); ?>">
					<i class="com-icon-photos"></i>
					<span><?php echo JText::_('COM_COMMUNITY_PHOTOS'); ?></span>
				</a>
			</li>
			<?php endif; ?>

			<?php if($config->get('enablevideos')): ?>
			<li>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$user->id); ?>">
					<i class="com-icon-videos"></i>
					<span><?php echo JText::_('COM_COMMUNITY_VIDEOS_GALLERY'); ?></span>
				</a>
			</li>
			<?php endif; ?>

			<?php if( !$isMine && $config->get('enablepm') ): ?>
			<li>
				<a onclick="<?php echo $sendMsg; ?>" href="javascript:void(0);">
					<i class="com-icon-mail-go"></i>
					<span><?php echo JText::_('COM_COMMUNITY_INBOX_SEND_MESSAGE'); ?></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	</div>
</div>
