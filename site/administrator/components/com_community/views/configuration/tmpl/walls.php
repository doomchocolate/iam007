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
		<h5><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS'); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_EDIT_COMMENTS_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_EDIT_COMMENTS_ENABLE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('wallediting' ,'ace-switch ace-switch-5', null , $this->config->get('wallediting') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_WALLS_TO_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_WALLS_TO_FRIENDS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockprofilewalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockprofilewalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_VIDEO_WALLS_TO_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_VIDEO_WALLS_TO_FRIENDS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockvideoswalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockvideoswalls') ); ?>
						</td>
					</tr>
					<tr>
						<td width="300" class="key">
							<span class="hasTip" title="::<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_PHOTO_WALLS_TO_FRIENDS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_PHOTO_WALLS_TO_FRIENDS' ); ?>
							</span>
						</td>
						<td valign="top">
							<?php /*echo JHTML::_('select.booleanlist' , 'lockphotoswalls' , null , $this->config->get('lockphotoswalls') , JText::_('COM_COMMUNITY_YES_OPTION') , JText::_('COM_COMMUNITY_NO_OPTION') ); */?>
							<?php echo CHTMLInput::checkbox('lockphotoswalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockphotoswalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_GROUP_WALLS_TO_MEMBERS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_GROUP_WALLS_TO_MEMBERS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockgroupwalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockgroupwalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_LOCK_EVENT_WALLS_TO_RECIPIENTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_LOCK_EVENT_WALLS_TO_RECIPIENTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('lockeventwalls' ,'ace-switch ace-switch-5', null , $this->config->get('lockeventwalls') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_AUTO_REFRESH_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_AUTO_REFRESH' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enable_refresh' ,'ace-switch ace-switch-5', null , $this->config->get('enable_refresh') ); ?>
						</td>
					</tr>
		            <tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_WALLS_INTERVAL_TIME_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_WALLS_INTERVAL_TIME' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="stream_refresh_interval" value="<?php echo $this->config->get('stream_refresh_interval' );?>" size="4" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>