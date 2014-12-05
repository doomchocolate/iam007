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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_SOCIALBOOKMARKS' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/socbookmark" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_SOCIALBOOKMARKS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_SOCIALBOOKMARKS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enablesharethis' ,'ace-switch ace-switch-5', null , $this->config->get('enablesharethis') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_SOCIALBOOKMARKS_SHARE_VIA_EMAIL'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_SOCIALBOOKMARKS_SHARE_VIA_EMAIL' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('shareviaemail' ,'ace-switch ace-switch-5', null , $this->config->get('shareviaemail') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>