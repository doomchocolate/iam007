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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_TITLE' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/akismet" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ENABLE_AKISMET_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ENABLE_AKISMET' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('antispam_enable' ,'ace-switch ace-switch-5', null , $this->config->get('antispam_enable') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AKISMET_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_KEY' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="antispam_akismet_key" value="<?php echo $this->config->get( 'antispam_akismet_key' );?>" size="50" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('antispam_akismet_messages' ,'ace-switch ace-switch-5', null , $this->config->get('antispam_akismet_messages') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_FRIEND_REQUESTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_FRIEND_REQUESTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('antispam_akismet_friends' ,'ace-switch ace-switch-5', null , $this->config->get('antispam_akismet_friends') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_WALL_POSTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_WALL_POSTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('antispam_akismet_walls' ,'ace-switch ace-switch-5', null , $this->config->get('antispam_akismet_walls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_STATUS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_STATUS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('antispam_akismet_status' ,'ace-switch ace-switch-5', null , $this->config->get('antispam_akismet_status') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_DISCUSSIONS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AKISMET_FILTER_DISCUSSIONS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('antispam_akismet_discussions' ,'ace-switch ace-switch-5', null , $this->config->get('antispam_akismet_discussions') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>