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

<table class="cFormTable FormInfo" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="label"><?php echo JText::_('COM_COMMUNITY_APPS_NAME');?></td>
		<td class="field"><?php echo $this->escape($app->name); ?></td>
	</tr>
	<?php if($this->params->get('appsShowAuthor')) { ?>
	<tr>
		<td class="label"><?php echo JText::_('COM_COMMUNITY_APPS_AUTHOR');?></td>
		<td class="field"><?php echo $this->escape($app->author); ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td class="label"><?php echo JText::_('COM_COMMUNITY_APPS_VERSION');?></td>
		<td class="field"><?php echo $this->escape($app->version); ?></td>
	</tr>
	<tr>
		<td class="label"><?php echo JText::_('COM_COMMUNITY_APPS_DESCRIPTION');?></td>
		<td class="field"><?php echo $this->escape( JText::_( $app->description ) ); ?></td>
	</tr>
</table>