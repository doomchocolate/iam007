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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ADVANCESEARCH_TITLE' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ADVANCESEARCH_ALLOW_GUESTS_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ADVANCESEARCH_ALLOW_GUESTS' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('guestsearch','ace-switch ace-switch-5', null , $this->config->get('guestsearch') ); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ADVANCESEARCH_EMAIL_SEARCH_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ADVANCESEARCH_EMAIL_SEARCH' ); ?>
							</span>
						</td>
						<td>
							<select name="privacy_search_email">
								<?php
									$selectedUserPrivacy	= ( $this->config->get('privacy_search_email') == '1' ) ? ' selected="true"' : '';
									$selectedDisallow		= ( $this->config->get('privacy_search_email') == '2' ) ? ' selected="true"' : '';
									$selectedAllow			= ( $this->config->get('privacy_search_email') == '0' ) ? ' selected="true"' : '';
								?>
								<option<?php echo $selectedAllow; ?> value="0"><?php echo JText::_('COM_COMMUNITY_ALLOWED_OPTION');?></option>
								<option<?php echo $selectedDisallow; ?> value="2"><?php echo JText::_('COM_COMMUNITY_DISALLOWED_OPTION');?></option>
								<option<?php echo $selectedUserPrivacy; ?> value="1"><?php echo JText::_('COM_COMMUNITY_RESPECT_PRIVACY_OPTION');?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<fieldset class="adminform">
	
	
</fieldset>