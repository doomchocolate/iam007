<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_INFO');?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td width="300" class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_NAME_TIPS'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_NAME' ); ?>
					</span>
				</td>
				<td><?php echo $this->JSNInfo['network_site_name']; ?></td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_URL_TIPS'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_URL' ); ?>
					</span>
				</td>
				<td><a href="<?php echo $this->JSNInfo['network_site_url'] ?>" target="_blank"><?php echo $this->JSNInfo['network_site_url'] ?></a></td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_LANGUAGE_TIPS'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_LANGUAGE' ); ?>
					</span>
				</td>
				<td><?php echo $this->JSNInfo['network_language']; ?></td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_MEMBERS_COUNT_TIPS'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_MEMBERS_COUNT' ); ?>
					</span>
				</td>
				<td><?php echo $this->JSNInfo['network_member_count']; ?></td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_GROUPS_COUNT_TIPS'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JSNETWORK_SITE_GROUPS_COUNT' ); ?>
					</span>
				</td>
				<td><?php echo $this->JSNInfo['network_group_count']; ?></td>
			</tr>
		</tbody>
	</table>
</fieldset>