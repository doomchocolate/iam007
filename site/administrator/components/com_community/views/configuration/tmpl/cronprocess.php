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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_CRONJOB' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/jscron" target="_blank"><i class="js-icon-facetime-video"></i> <?php echo JText::_('COM_COMMUNITY_DOC_AND_VIDEO'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<div class="space-12"></div>
				<table>
					<tbody>
						<tr>
							<td width="250" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_CRONJOB_SENDMAIL_PAGELOAD_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_CRONJOB_SENDMAIL_PAGELOAD'); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('sendemailonpageload','ace-switch ace-switch-5', null , $this->config->get('sendemailonpageload') ); ?>
							</td>
						</tr>
			                        <tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_CRONJOB_ARCHIVE_MAX_DAY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_CRONJOB_ARCHIVE_MAX_DAY'); ?>
								</span>
							</td>
							<td>
								<input type="text" name="archive_activity_max_day" value="<?php echo $this->config->get('archive_activity_max_day' );?>" size="4" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_CRONJOB_ARCHIVE_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_CRONJOB_ARCHIVE_LIMIT'); ?>
								</span>
							</td>
							<td>
								<input type="text" name="archive_activity_limit" value="<?php echo $this->config->get('archive_activity_limit' );?>" size="4" />
							</td>
						</tr>
					</tbody>
				</table>
		</div>
	</div>
</div>