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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ACTIVITY_TITLE' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ACTIVITY_PRIVACY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ACTIVITY_PRIVACY'); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('respectactivityprivacy' ,'ace-switch ace-switch-5', null , $this->config->get('respectactivityprivacy') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_NEW_TAB_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_NEW_TAB' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('newtab' ,'ace-switch ace-switch-5', null , $this->config->get('newtab') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ACTIVITY_COMMENT_SETTING_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ACTIVITY_COMMENT_SETTING'); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('allmemberactivitycomment' ,'ace-switch ace-switch-5', null , $this->config->get('allmemberactivitycomment') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>