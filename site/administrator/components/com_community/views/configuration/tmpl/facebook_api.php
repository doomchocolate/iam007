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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_API' ); ?></h5>
		<div class="widget-toolbar no-border">
		<!-- 			<a href="http://tiny.cc/jssysreq" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC_REQUIREMENT'); ?></a> -->
			<a href="http://tiny.cc/jsfbsetup" target="_blank"><i class="js-icon-wrench"></i> <?php echo JText::_('COM_COMMUNITY_DOC_SETTING_UP'); ?></a>
			<a href="http://tiny.cc/SetupFBConnect" target="_blank"><i class="js-icon-facetime-video"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-main">

			<table>
				<tbody>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_API_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_API_KEY' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="fbconnectkey" value="<?php echo $this->config->get('fbconnectkey' , '' );?>" size="50" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_FACEBOOK_APPLICATION_SECRET_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_FACEBOOK_APPLICATION_SECRET' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="fbconnectsecret" value="<?php echo $this->config->get('fbconnectsecret' , '' );?>" size="50" />
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
</div>

<div class="space-12"></div>

<div class="widget-box">
	<div class="widget-header widget-header-flat">
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JFBC' ); ?></h5>
		<div class="widget-toolbar no-border">
<!-- 			<a href="http://tiny.cc/SourceCoast" target="_blank"><?php echo JText::_('COM_COMMUNITY_ABOUT_SOURCECOAST'); ?></a> -->
			<a href="http://tiny.cc/fn450w" target="_blank"><i class="js-icon-wrench"></i> <?php echo JText::_('COM_COMMUNITY_DOC_SETTING_UP'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_JFBC_LABEL'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_JFBC_LABEL' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('usejfbc' ,'ace-switch ace-switch-5', null , $this->config->get('usejfbc') ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>


</div>