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
<script>
	joms.jQuery(document).ready(function(){
		joms.jQuery('input#join').click(function(){
			joms.groups.join('<?php echo $groupid;?>','yes');
			joms.jQuery('input#join').val('<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_PROCESS_NOTICE'); ?>');
			joms.jQuery('.loading-icon').show();
		});
	});
</script>
<div id="community-groups-wrap">
	<div class="cNotice cNotice-GroupDiscJoin">
		<div class="cNotice-Notice"><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_NOTICE'); ?></div>
		<div class="cNotice-Actions">
			<input id="join" type="button" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_BUTTON'); ?>"/>
			<div class="cNotice-Loader"><img class="loading-icon" style="display:none" src="<?php echo JURI::root(); ?>components/com_community/assets/ajax-loader.gif"/></div>
		</div>
		<div class="cNotice-Footer" id="add-reply" style="display:none">
			<a href="javascript:void(0)" ><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_ADD_REPLY_NOTICE'); ?></a>
		</div>
	</div>
</div>