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
<form name="privacyForm" action="">
	<table class="cWindowForm" cellspacing="1" cellpadding="0">
		<tr>
			<td><input type="radio" class="input radio" value="0" name="privacy"<?php echo $showCheck0;?> /></td>
			<td width="100%">
				<strong><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_EVERYONE');?></strong>
				<p><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_EVERYONE_DESC');?></p>
			</td>
		</tr>
		<tr>
			<td><input type="radio" class="input radio" value="10" name="privacy"<?php echo $showCheck1;?> /></td>
			<td>
				<strong><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_FRIENDS');?></strong>
				<p><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_FRIENDS_DESC');?></p>
			</td>
		</tr>
		<tr>
			<td><input type="radio" class="input radio" value="20" name="privacy"<?php echo $showCheck2;?> /></td>
			<td>
				<strong><?php echo JText::_('COM_COMMUNITY_PRIVACY_ME');?></strong>
				<p><?php echo JText::_('COM_COMMUNITY_APPS_PRIVACY_ME_DESC');?></p>
			</td>
		</tr>
	</table>
	<input type="hidden" name="appname" value="<?php echo $appName;?>" />
</form>