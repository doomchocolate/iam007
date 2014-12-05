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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ENABLE_TERMS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ENABLE_TERMS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('enableterms' ,'ace-switch ace-switch-5', null , $this->config->get('enableterms') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_TERMS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_TERMS' ); ?>
							</span>
						</td>
						<td>
							<textarea name="registrationTerms" cols="30" rows="5"><?php echo $this->config->get('registrationTerms');?></textarea>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_ENABLE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_ENABLE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('recaptcha' ,'ace-switch ace-switch-5', null , $this->config->get('recaptcha') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_SECURE_MODE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_SECURE_MODE' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('recaptcha_secure' ,'ace-switch ace-switch-5', null , $this->config->get('recaptcha_secure') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_PUBLIC_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_PUBLIC_KEY' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="recaptchapublic" value="<?php echo $this->config->get('recaptchapublic'); ?>" size="35" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_PRIVATE_KEY_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_PRIVATE_KEY' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="recaptchaprivate" value="<?php echo $this->config->get('recaptchaprivate'); ?>" size="35" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOW_PROFILE_DELETION_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOW_PROFILE_DELETION' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('profile_deletion' ,'ace-switch ace-switch-5', null , $this->config->get('profile_deletion') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_THEME_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_THEME' ); ?>
							</span>
						</td>
						<td>
							<select name="recaptchatheme">
								<option value="red"<?php echo $this->config->get('recaptchatheme') == 'red' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_RED');?></option>
								<option value="white"<?php echo $this->config->get('recaptchatheme') == 'white' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_WHITE');?></option>
								<option value="blackglass"<?php echo $this->config->get('recaptchatheme') == 'blackglass' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_BLACKGLASS');?></option>
								<option value="clean"<?php echo $this->config->get('recaptchatheme') == 'clean' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_CLEAN');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_LANGUAGE_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_RECAPTCHA_LANGUAGE' ); ?>
							</span>
						</td>
						<td>
							<select name="recaptchalang">
								<option value="en"<?php echo $this->config->get('recaptchalang') == 'en' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_ENGLISH');?></option>
								<option value="nl"<?php echo $this->config->get('recaptchalang') == 'nl' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_DUTCH');?></option>
								<option value="fr"<?php echo $this->config->get('recaptchalang') == 'fr' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_FRENCH');?></option>
								<option value="de"<?php echo $this->config->get('recaptchalang') == 'de' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_GERMAN');?></option>
								<option value="pt"<?php echo $this->config->get('recaptchalang') == 'pt' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_PORTUGUESE');?></option>
								<option value="ru"<?php echo $this->config->get('recaptchalang') == 'ru' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_RUSSIAN');?></option>
								<option value="es"<?php echo $this->config->get('recaptchalang') == 'es' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_SPANISH');?></option>
								<option value="tr"<?php echo $this->config->get('recaptchalang') == 'tr' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_TURKISH');?></option>
								<option value="it"<?php echo $this->config->get('recaptchalang') == 'it' ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_ITALIAN');?></option>
							</select>
						</td>
					</tr>

					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOWEDDOMAINS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_ALLOWEDDOMAINS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="alloweddomains" value="<?php echo $this->config->get('alloweddomains'); ?>" size="35" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_DENIEDDOMAINS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REGISTRATIONS_DENIEDDOMAINS' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="denieddomains" value="<?php echo $this->config->get('denieddomains'); ?>" size="35" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>