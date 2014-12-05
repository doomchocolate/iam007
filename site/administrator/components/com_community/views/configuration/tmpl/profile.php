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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MULTIPROFILES' ); ?></h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/jsmultiprofile" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
			<a href="http://tiny.cc/SetupAmazonS3" target="_blank"><i class="js-icon-facetime-video"></i> <?php echo JText::_('COM_COMMUNITY_DOC_VIDEO'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<div class="space-12"></div>
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_MULTIPROFILES_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MULTIPROFILES_ENABLE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('profile_multiprofile' ,'ace-switch ace-switch-5', null , $this->config->get('profile_multiprofile') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>