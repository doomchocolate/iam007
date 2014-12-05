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
		<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_METHODS' ); ?></h5>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<table class="admintable" cellspacing="1">
				<tbody>
					<tr>
						<td width="300" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_PHOTOS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_PHOTOS' ); ?>
							</span>
						</td>
						<td valign="top">
							<select name="photostorage">
								<option <?php echo ( $this->config->get('photostorage') == 'file' ) ? 'selected="true"' : ''; ?> value="file"><?php echo JText::_('COM_COMMUNITY_LOCALSERVER_OPTION');?></option>
								<option <?php echo ( $this->config->get('photostorage') == 's3' ) ? 'selected="true"' : ''; ?> value="s3"><?php echo JText::_('COM_COMMUNITY_AMAZONS3_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="300" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_VIDEOS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_VIDEOS' ); ?>
							</span>
						</td>
						<td valign="top">
							<select name="videostorage">
								<option <?php echo ( $this->config->get('videostorage') == 'file' ) ? 'selected="true"' : ''; ?> value="file"><?php echo JText::_('COM_COMMUNITY_LOCALSERVER_OPTION');?></option>
								<option <?php echo ( $this->config->get('videostorage') == 's3' ) ? 'selected="true"' : ''; ?> value="s3"><?php echo JText::_('COM_COMMUNITY_AMAZONS3_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="300" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_PROFILEAVATARS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_PROFILEAVATARS' ); ?>
							</span>
						</td>
						<td valign="top">
							<select name="user_avatar_storage">
								<option <?php echo ( $this->config->get('user_avatar_storage') == 'file' ) ? 'selected="true"' : ''; ?> value="file"><?php echo JText::_('COM_COMMUNITY_LOCALSERVER_OPTION');?></option>
								<option <?php echo ( $this->config->get('user_avatar_storage') == 's3' ) ? 'selected="true"' : ''; ?> value="s3"><?php echo JText::_('COM_COMMUNITY_AMAZONS3_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="300" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_GROUPAVATARS_TIPS'); ?>">
							<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_GROUPAVATARS' ); ?>
							</span>
						</td>
						<td valign="top">
							<select name="groups_avatar_storage">
								<option <?php echo ( $this->config->get('groups_avatar_storage') == 'file' ) ? 'selected="true"' : ''; ?> value="file"><?php echo JText::_('COM_COMMUNITY_LOCALSERVER_OPTION');?></option>
								<option <?php echo ( $this->config->get('groups_avatar_storage') == 's3' ) ? 'selected="true"' : ''; ?> value="s3"><?php echo JText::_('COM_COMMUNITY_AMAZONS3_OPTION');?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="200" class="key">
							<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_FILES_TIPS')?>"> <?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_REMOTE_STORAGE_FILES' )?></span>
						</td>
						<td valign="top">
							<select name="file_storage">
								<option <?php echo ( $this->config->get('file_storage') == 'file' ) ? 'selected="true"' : ''; ?> value="file"><?php echo JText::_('COM_COMMUNITY_LOCALSERVER_OPTION');?></option>
								<option <?php echo ( $this->config->get('file_storage') == 's3' ) ? 'selected="true"' : ''; ?> value="s3"><?php echo JText::_('COM_COMMUNITY_AMAZONS3_OPTION');?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>