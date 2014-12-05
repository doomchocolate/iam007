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
<div id="wall_<?php echo $id; ?>" class="cComment clearfix">
	<!-- END: .cComment-Avatar -->
	<div class="cComment-Avatar cFloat-L"><?php echo $avatarHTML; ?></div>
	<!-- END: .cComment-Avatar -->

	<!-- END: .cComment-Body -->
	<div class="cComment-Body">
		<a href="<?php echo $authorLink; ?>" class="cComment-Author"><?php echo $author; ?></a>

		<!-- END: .cComment-Content -->
		<div class="cComment-Content">
			<span id="wall-message-<?php echo $id;?>"><?php echo CWallLibrary::_processWallContent(str_replace('[BR]', '<br />', CActivities::format(str_replace('<br />','[BR]', $content)))); ?></span>
			<?php if($isEditable) { ?>
			<div id="wall-edit-container-<?php echo $id;?>"></div>
			<?php } ?>
			<?php echo $commentsHTML; ?>
		</div>
		<!-- END: .cComment-Content -->

		<!-- START: .cComment-Meta -->
		<div class="cComment-Meta small">
			<?php echo $created; ?>

			<?php if($config->get('wallediting')){ ?>

				<!--TIME LEFT TO EDIT REPLY-->
				<?php if($isEditable){?>
					&middot;
					<?php echo JText::sprintf('COM_COMMUNITY_TIME_LEFT_TO_EDIT_REPLY' , $editInterval , '<a onclick="joms.walls.edit(\'' . $id . '\',\'' . $processFunc.'\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_EDIT') . '</a>' );?>
				<?php } ?>
				<!--end: TIME LEFT TO EDIT REPLY-->

			<?php } ?>

			<?php if($isMine) { ?>
				&middot;
				<a onclick="wallRemove(<?php echo $id; ?>);return false;" href="javascript:void(0)" class="remove" ><?php echo JText::_('COM_COMMUNITY_WALL_REMOVE');?></a>
			<?php } ?>
		</div>
		<!-- END: .cComment-Meta -->
	</div>
	<!-- END: .cComment-Body -->
</div>

