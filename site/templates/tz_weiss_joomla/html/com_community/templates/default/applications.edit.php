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

<script type="text/javascript">
joms.jQuery(document).ready(function()
{
	joms.editLayout.activate();
});
</script>

<div class="row-fluid">
	<div class="span12">
		<div class="app-item app-core"><?php echo JText::_('COM_COMMUNITY_PROFILE'); ?></div>
	</div>
</div>

<div class="cApplications joms-apps row-fluid">

<div class="span8">
	<div class="cMain">

		<div class="app-item app-core"><?php echo JText::_('COM_COMMUNITY_ACTIVITY_STREAM'); ?></div>
		<?php echo $appItems['content-core']; ?>

		<div id="pos-profile-content" class="app-position">
			<?php echo $appItems['content']; ?>
		</div>

		<div class="app-add">
			<a class="app-action-add cFloat-R" href="javascript: void(0)" onclick="joms.editLayout.browse('content');">
				<i class="com-icon-add"></i>
				<span><?php echo JText::_('COM_COMMUNITY_ADD_APPLICATIONS'); ?></span>
			</a>
		</div>
	</div>
</div>

<div class="span4">
	<div class="cSidebar">

		<?php echo $appItems['sidebar-top-core']; ?>
		<div id="pos-profile-sidebar-top" class="app-position">
			<?php echo $appItems['sidebar-top']; ?>
		</div>

		<div class="app-add">
			<a class="app-action-add cFloat-R" href="javascript: void(0)" onclick="joms.editLayout.browse('sidebar-top');">
				<i class="com-icon-add"></i>
				<span><?php echo JText::_('COM_COMMUNITY_ADD_APPLICATIONS'); ?></span>
			</a>
		</div>

		<div class="app-item app-core"><?php echo JText::_('COM_COMMUNITY_FRIENDS'); ?></div>

		<?php if( $config->get('enablegroups')){ ?>
			<div class="app-item app-core"><?php echo JText::_('COM_COMMUNITY_GROUPS'); ?></div>
		<?php } ?>

		<?php echo $appItems['sidebar-bottom-core'] ?>
		<div id="pos-profile-sidebar-bottom" class="app-position">
			<?php echo $appItems['sidebar-bottom']; ?>
		</div>

		<div class="app-add">
			<a class="app-action-add cFloat-R" href="javascript: void(0)" onclick="joms.editLayout.browse('sidebar-bottom');">
				<i class="com-icon-add"></i>
				<span><?php echo JText::_('COM_COMMUNITY_ADD_APPLICATIONS'); ?></span>
			</a>
		</div>

	</div>
</div>

</div>