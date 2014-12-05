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

<div class="cModule cGroups-UserGroups app-box">
	<h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'); ?></h3>
<?php if($usergroups) { ?>

	<div class="app-box-content">
		<ul class="cResetList cThumbsList clearfix">
		<?php foreach($usergroups as $grp): ?>
			<!-- thumbnail -->
			<li>
				<a href="<?php echo $grp['group_url'] ?>"><img class="cAvatar jomNameTips" src="<?php echo $grp['avatar']; ?>" title="<?php echo $grp['group_name']; ?>" /></a>
			</li>
		<?php endforeach;?>
		</ul>
	</div>

	<!-- click to go to my groups -->
	<div class="app-box-footer">
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_MY_GROUPS'); ?></a>
	</div>
<?php
	} 
?>
</div>