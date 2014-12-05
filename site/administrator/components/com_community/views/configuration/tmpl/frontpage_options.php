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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_FRONTPAGE' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_ACTIVITIES_COUNT_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_ACTIVITIES_COUNT' ); ?>
							</span>
						</td>
						<td valign="top">
							<input type="text" name="maxactivities" value="<?php echo $this->config->get('maxactivities');?>" size="4" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_DEFAULT_ACTIVITY_FILTER_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_DEFAULT_ACTIVITY_FILTER' ); ?>
							</span>
						</td>
						<td valign="top">
							<select name="frontpageactivitydefault">
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'all' ) ? 'selected="true"' : ''; ?> value="all"><?php echo JText::_('COM_COMMUNITY_SHOW_ALL_OPTION');?></option>
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'privacy:me-and-friends' ) ? 'selected="true"' : ''; ?> value="privacy:me-and-friends"><?php echo JText::_('COM_COMMUNITY_USER_AND_FRINEDS_OPTION');?></option>
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'apps:profile' ) ? 'selected="true"' : ''; ?> value="apps:profile"><?php echo JText::_('COM_COMMUNITY_FILTERBAR_STATUS');?></option>
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'apps:photo' ) ? 'selected="true"' : ''; ?> value="apps:photo"><?php echo JText::_('COM_COMMUNITY_FILTERBAR_PHOTO');?></option>
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'apps:video' ) ? 'selected="true"' : ''; ?> value="apps:video"><?php echo JText::_('COM_COMMUNITY_FILTERBAR_VIDEO');?></option>
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'apps:group' ) ? 'selected="true"' : ''; ?> value="apps:group"><?php echo JText::_('COM_COMMUNITY_FILTERBAR_GROUP');?></option>
								<option <?php echo ( $this->config->get('frontpageactivitydefault') == 'apps:event' ) ? 'selected="true"' : ''; ?> value="apps:event"><?php echo JText::_('COM_COMMUNITY_FILTERBAR_EVENT');?></option>
							</select>
						</td>
					</tr>

					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_SHOW_ACTIVITY_STREAM_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_SHOW_ACTIVITY_STREAM' ); ?>
							</span>
						</td>
						<td valign="top">
							<select name="showactivitystream">
								<option <?php echo ( $this->config->get('showactivitystream') == '0' ) ? 'selected="true"' : ''; ?> value="0"><?php echo JText::_('COM_COMMUNITY_HIDE_OPTION');?></option>
								<option <?php echo ( $this->config->get('showactivitystream') == '1' ) ? 'selected="true"' : ''; ?> value="1"><?php echo JText::_('COM_COMMUNITY_SHOW_OPTION');?></option>
								<option <?php echo ( $this->config->get('showactivitystream') == '2' ) ? 'selected="true"' : ''; ?> value="2"><?php echo JText::_('COM_COMMUNITY_MEMBERSONLY_OPTION');?></option>
							</select>
						</td>
					</tr>

					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FRONTPAGE_SHOW_CUSTOM_ACTIVITY_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FRONTPAGE_SHOW_CUSTOM_ACTIVITY' ); ?>
							</span>
						</td>
						<td valign="top">
							<?php echo CHTMLInput::checkbox('custom_activity' ,'ace-switch ace-switch-5', null , $this->config->get('custom_activity') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JTexT::_('COM_COMMUNITY_CONFIGURATION_INFINITE_SCROLL_TIPS'); ?>">
								<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_INFINITE_SCROLL')?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('infinitescroll', 'ace-switch ace-switch-5', null, $this->config->get('infinitescroll'))?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>