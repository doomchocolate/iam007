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
<h2 style="text-decoration: underline;margin-bottom: 10px;"><?php echo JText::_('COM_COMMUNITY_EXISTING_SITE_MEMBER');?></h2>
<div style="margin-bottom: 5px;"><?php echo JText::_('COM_COMMUNITY_EXISTING_SITE_MEMBER_DESCRIPTION');?></div>
<table width="100%">
	<tr>
	    <td width="30%" valign="top"><label for="existingusername"><?php echo JText::_('COM_COMMUNITY_USERNAME');?></label></td>
	    <td><input type="text" id="existingusername" size="30" /></td>
	</tr>
	<tr>
		<td valign="top"><label for="existingpassword"><?php echo JText::_('COM_COMMUNITY_PASSWORD');?></label></td>
		<td><input type="password" id="existingpassword" size="30" /></td>
	</tr>
</table>
<div style="color: red;margin-top:20px;"><?php echo JText::_('COM_COMMUNITY_LINKING_NOTICE');?></div>