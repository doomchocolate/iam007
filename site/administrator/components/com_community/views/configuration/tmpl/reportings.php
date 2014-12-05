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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPORTINGS' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/reportingsystem" target="_blank">
				<i class="js-icon-info-sign"></i> 
			<?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REPORTINGS_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPORTINGS_ENABLE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enablereporting' ,'ace-switch ace-switch-5', null , $this->config->get('enablereporting') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REPORTINGS_EXECUTE_DEFAULT_TASK_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPORTINGS_EXECUTE_DEFAULT_TASK' ); ?>
							</span>
						</td>
						<td>
							<input type="text" class="input-small" name="maxReport"  value="<?php echo $this->config->get('maxReport'); ?>"  />
							<?php echo JText::_('COM_COMMUNITY_REPORTS');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REPORTINGS_NOTIFICATION_EMAIL_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPORTINGS_NOTIFICATION_EMAIL' ); ?>
							</span>
						</td>
						<td>
							<div><input type="text" name="notifyMaxReport" value="<?php echo $this->config->get('notifyMaxReport'); ?>" size="45" /></div>
							<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REPORTINGS_NOTIFICATION_EMAIL_COMMA_SEPARATED');?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REPORTINGS_ALLOW_GUEST_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPORTINGS_ALLOW_GUEST' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enableguestreporting' ,'ace-switch ace-switch-5', null , $this->config->get('enableguestreporting') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REPORTINGS_PREDEFINED_TEXT_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REPORTINGS_PREDEFINED_TEXT' ); ?>
							</span>
						</td>
						<td>
							<textarea name="predefinedreports" cols="30" rows="5"><?php echo $this->config->get('predefinedreports');?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>