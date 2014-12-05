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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIKES' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIKES_GROUPS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIKES_GROUPS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('likes_groups' ,'ace-switch ace-switch-5', null , $this->config->get('likes_groups') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIKES_EVENTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIKES_EVENTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('likes_events' ,'ace-switch ace-switch-5', null , $this->config->get('likes_events') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIKES_PHOTOS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIKES_PHOTOS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('likes_photo' ,'ace-switch ace-switch-5', null , $this->config->get('likes_photo') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIKES_VIDEOS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIKES_VIDEOS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('likes_videos' ,'ace-switch ace-switch-5', null , $this->config->get('likes_videos') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LIKES_PROFILE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LIKES_PROFILE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('likes_profile' ,'ace-switch ace-switch-5', null , $this->config->get('likes_profile') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>