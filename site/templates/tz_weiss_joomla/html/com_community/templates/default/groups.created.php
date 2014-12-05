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
<div class="cLayout cGroups-Created">
	<p><?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_SUCCESS');?></p>

	<ul class="cPageOptions cResetList">
		<li>
			<i class="com-icon-avatar"></i>
			<a href="<?php echo $linkUpload; ?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_UPLOAD_AVATAR');?>
			</a>
		</li>
		<li>
			<i class="com-icon-bell-plus"></i>
			<a href="<?php echo $linkBulletin; ?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_BULLETIN_CREATE');?>
			</a>
		</li>
		<li>
			<i class="com-icon-comment-plus"></i>
			<a href="<?php echo $linkDiscussion; ?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_CREATE');?>
			</a>
		</li>
		<li>
			<i class="com-icon-groups-edit"></i>
			<a href="<?php echo $linkEdit;?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_EDIT_DESC');?>
			</a>
		</li>
		<li>
			<i class="com-icon-groups"></i>
			<a href="<?php echo $link; ?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_GOTO');?>
			</a>
		</li>
	</ul>
</div>