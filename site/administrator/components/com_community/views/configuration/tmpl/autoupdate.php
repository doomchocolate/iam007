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
<fieldset class="adminform">
	<legend><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_TOOLBAR' ); ?></legend>

	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td width="350" class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_ORDERCODE'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_ORDERCODE' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" id="autoupdateordercode" data-orig="<?php echo $this->config->get('autoupdateordercode' , '' );?>" name="autoupdateordercode" value="<?php echo $this->config->get('autoupdateordercode' , '' );?>" size="50" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_EMAIL'); ?>">
						<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_EMAIL' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" id="autoupdateemail" data-orig="<?php echo $this->config->get('autoupdateemail' , '' );?>" name="autoupdateemail" value="<?php echo $this->config->get('autoupdateemail' , '' );?>" size="50" />
				</td>
			</tr>
            <tr>
				<td class="key">&nbsp;

				</td>
				<td valign="top">
					<?php if($this->isuptodate): ?>
                    <p><img src="<?php echo COMMUNITY_ASSETS_URL; ?>/images/notice-success.png"> <?php echo JText::_('COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_UPTODATE_MSG') ?></p>
                    <?php else: ?>
                    <p><img src="<?php echo COMMUNITY_ASSETS_URL; ?>/images/notice-notice.png"> <?php $err=CAutoUpdate::getError(); echo array_shift($err); ?></p>
					<?php endif;?>
					<input data-inprogresstext="<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_AUTOUPDATE_INPROGRESS' ); ?>" type="button" id="autoupdatesubmit" name="checkautoupdate" value="<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_CHECK_AUTOUPDATE' ); ?>" onclick="azcommunity.runAutoUpdate(); return false;" />&nbsp;&nbsp;<img style="display:none;" class="autoupdate-loader" src="<?php echo COMMUNITY_BASE_ASSETS_URL; ?>/ajax-loader.gif">
                    &nbsp;<span id="autoupdate-progress"></span>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
