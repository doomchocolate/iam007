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

<div class="widget-box">
	<div class="widget-header widget-header-flat">
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/dailylimits" target="_blank">
				<i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_MESSAGES_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_MESSAGES' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="pmperday" value="<?php echo $this->config->get('pmperday');?>" class="input-small" /> <?php echo JText::_('COM_COMMUNITY_DAILY');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_GROUPS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_GROUPS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="limit_groups_perday" value="<?php echo $this->config->get('limit_groups_perday');?>" class="input-small" /> <?php echo JText::_('COM_COMMUNITY_DAILY');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_PHOTOS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_PHOTOS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="limit_photos_perday" value="<?php echo $this->config->get('limit_photos_perday');?>" class="input-small" /> <?php echo JText::_('COM_COMMUNITY_DAILY');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_VIDEOS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_VIDEOS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="limit_videos_perday" value="<?php echo $this->config->get('limit_videos_perday');?>" class="input-small" /> <?php echo JText::_('COM_COMMUNITY_DAILY');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_FRIENDS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="limit_friends_perday" value="<?php echo $this->config->get('limit_friends_perday');?>" class="input-small" /> <?php echo JText::_('COM_COMMUNITY_DAILY');?>
						</td>
					</tr>
		                        <tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_FILES_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIMITS_NEW_FILES' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="limit_files_perday" value="<?php echo $this->config->get('limit_files_perday');?>" class="input-small" /> <?php echo JText::_('COM_COMMUNITY_DAILY');?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>