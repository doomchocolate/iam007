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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table>
				<tbody>
					<tr>
						<td  width="250" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_HIDE_MENU_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_HIDE_MENU' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('show_toolbar' ,'ace-switch ace-switch-5', null , $this->config->get('show_toolbar') ); ?>
						</td>
					</tr>
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_NAME_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_NAME' ); ?>
							</span>
						</td>
						<td>
							<select name="displayname">
								<?php
									$selectedRealName	= ( $this->config->get('displayname') == 'name' ) ? 'selected="true"' : '';
									$selectedUserName	= ( $this->config->get('displayname') == 'username' ) ? 'selected="true"' : '';
								?>
								<option <?php echo $selectedRealName; ?> value="name"><?php echo JText::_('COM_COMMUNITY_REALNAME_OPTION');?></option>
								<option <?php echo $selectedUserName; ?> value="username"><?php echo JText::_('COM_COMMUNITY_USERNAME_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_DATE_STYLE_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_DATE_STYLE' ); ?>
							</span>
						</td>
						<td>
							<select name="activitydateformat">
								<?php
									$selectedFixedDate	= ( $this->config->get('activitydateformat') == 'fixed' ) ? 'selected="true"' : '';
									$selectedLapseDate	= ( $this->config->get('activitydateformat') == 'lapse' ) ? 'selected="true"' : '';
								?>
								<option <?php echo $selectedFixedDate; ?> value="fixed"><?php echo JText::_('COM_COMMUNITY_FIXED_OPTION');?></option>
								<option <?php echo $selectedLapseDate; ?> value="lapse"><?php echo JText::_('COM_COMMUNITY_LAPSED_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ALLOW_HTML_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ALLOW_HTML' ); ?>
							</span>
						</td>
						<td>
							<?php echo CHTMLInput::checkbox('allowhtml' ,'ace-switch ace-switch-5', null , $this->config->get('allowhtml') ); ?>
						</td>
					</tr>
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_USE_EDITOR_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_USE_EDITOR' ); ?>
							</span>
						</td>
						<td>
							<?php
								$editor	= $this->config->get('htmleditor', 'none');
							 	if( $editor == '1' || $editor == '0' )
							 	{
							 		$editor	= 'none';
								}
							?>
							<?php echo JHTML::_('select.genericlist' , $this->getEditors() , 'htmleditor' , null , 'value' , 'text' , $editor );?>
						</td>
					</tr>
		            <input type="hidden" name="showactivityavatar" value="1" />
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_CONTENTS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_CONTENTS' ); ?>
							</span>
						</td>
						<td>
							<select name="showactivitycontent">
								<?php
									$showActivityContent	= ( $this->config->get('showactivitycontent') == '1' ) ? 'selected="true"' : '';
									$hideActivityContent	= ( $this->config->get('showactivitycontent') == '0' ) ? 'selected="true"' : '';
								?>
								<option <?php echo $showActivityContent; ?> value="1"><?php echo JText::_('COM_COMMUNITY_YES_OPTION');?></option>
								<option <?php echo $hideActivityContent; ?> value="0"><?php echo JText::_('COM_COMMUNITY_NO_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_CONTENT_LENGTH_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_ACTIVITY_CONTENT_LENGTH' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="streamcontentlength" value="<?php echo $this->config->get('streamcontentlength');?>" size="20" /> 
						</td>
					</tr>
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_SINGULAR_NUMBER_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_SINGULAR_NUMBER' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="singularnumber" value="<?php echo $this->config->get('singularnumber');?>" size="20" /> 
						</td>
					</tr>
					<tr>
						<td  class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_PROFILE_DATE_FORMAT_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISPLAY_PROFILE_DATE_FORMAT' ); ?>
							</span>
						</td>
						<td>
							<input type="text" name="profileDateFormat" value="<?php echo $this->config->get('profileDateFormat');?>" size="20" />
							<a href="http://php.net/manual/en/function.date.php" target="_blank"><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISPLAY_AVAILABLE_DATE_FORMATS');?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>