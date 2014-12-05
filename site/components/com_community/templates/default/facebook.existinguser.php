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
<div>
	<img src="<?php echo $my->getThumbAvatar();?>" border="0" style="border: 1px solid #000; float: left; margin: 5px;line-height:0;" />
	<div><?php echo JText::sprintf('COM_COMMUNITY_FACEBOOK_SUCCESS_LOGIN' , $userInfo['name'] );?></div>
	<div class="clr"></div>
</div>
<div style="padding: 5px;">
	<?php
	if( $config->get('fbconnectupdatestatus') )
	{
	?>
	<label class="lblcheck"><input type="checkbox" <?php if(intval( $config->get('fbloginimportprofile'))) { ?>checked="checked"<?php } ?> value="1" name="importstatus" id="importstatus" /><?php echo JText::_('COM_COMMUNITY_IMPORT_PROFILE_STATUS');?></label>
	<?php
	}
	?>
	<label class="lblcheck"><input type="checkbox" <?php if(intval( $config->get('fbloginimportavatar'))) { ?>checked="checked"<?php } ?> value="1" name="importavatar" id="importavatar" /><?php echo JText::_('COM_COMMUNITY_IMPORT_PROFILE_AVATAR');?></label>
</div>
