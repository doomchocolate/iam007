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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_SETTINGS' ); ?></h5>
	</div>

	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="350" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_IMPORT_SIGNUP_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_IMPORT_SIGNUP' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('fbsignupimport' ,'ace-switch ace-switch-5', null , $this->config->get('fbsignupimport') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_WATERMARK_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_WATERMARK' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('fbwatermark' ,'ace-switch ace-switch-5', null , $this->config->get('fbwatermark') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_REIMPORT_PROFILE_LOGIN_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_REIMPORT_PROFILE_LOGIN' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('fbloginimportprofile' ,'ace-switch ace-switch-5', null , $this->config->get('fbloginimportprofile') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_REIMPORT_AVATAR_LOGIN_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_REIMPORT_AVATAR_LOGIN' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('fbloginimportavatar' ,'ace-switch ace-switch-5', null , $this->config->get('fbloginimportavatar') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_IMPORT_STATUS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_IMPORT_STATUS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('fbconnectupdatestatus' ,'ace-switch ace-switch-5', null , $this->config->get('fbconnectupdatestatus') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNTIY_CONFIGURATION_FACEBOOK_POST_STATUS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNTIY_CONFIGURATION_FACEBOOK_POST_STATUS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('fbconnectpoststatus' ,'ace-switch ace-switch-5', null , $this->config->get('fbconnectpoststatus') ); ?>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>

</div>