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
		<h5>&nbsp;</h5>
		<div class="widget-toolbar no-border">
			<a  href="http://tiny.cc/jsvideolink" target="_blank"> <i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC_REQUIREMENT'); ?></a>
			<a  href="http://tiny.cc/jsvideosetup" target="_blank"> <i class="js-icon-wrench"></i> <?php echo JText::_('COM_COMMUNITY_DOC_SETTING_UP'); ?></a>
			<a  href="http://tiny.cc/SetupVidUploads" target="_blank"><i class="js-icon-facetime-video"></i> <?php echo JText::_('COM_COMMUNITY_DOC_VIDEO'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<fieldset class="adminform">
				<table>
					<tbody>
						<tr>
							<td class="key" width="250">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablevideos' ,'ace-switch ace-switch-5', null , $this->config->get('enablevideos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_GUEST_SEARCH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_GUEST_SEARCH' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableguestsearchvideos' ,'ace-switch ace-switch-5', null , $this->config->get('enableguestsearchvideos') ); ?>
							</td>
						</tr>
			                        <tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_PROFILE_VIDEO_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_PROFILE_VIDEO_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableprofilevideo' ,'ace-switch ace-switch-5', null , $this->config->get('enableprofilevideo') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_UPLOAD_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_UPLOAD_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablevideosupload' ,'ace-switch ace-switch-5', null , $this->config->get('enablevideosupload') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_CREATION_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_CREATION_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="videouploadlimit" value="<?php echo $this->config->get('videouploadlimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_MAP_DEFAULT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_MAP_DEFAULT' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('videosmapdefault' ,'ace-switch ace-switch-5', null , $this->config->get('videosmapdefault') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_DELETE_ORIGINAL_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_DELETE_ORIGINAL' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('deleteoriginalvideos' ,'ace-switch ace-switch-5', null , $this->config->get('deleteoriginalvideos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_ROOT_FOLDER_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_ROOT_FOLDER' ); ?>
								</span>
							</td>
							<td>
								<input type="text" size="40" name="videofolder" value="<?php echo $this->config->get('videofolder');?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_MAXIMUM_UPLOAD_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_MAXIMUM_UPLOAD_SIZE' ); ?>
								</span>
							</td>
							<td>
								<div><input type="text" size="3" name="maxvideouploadsize" value="<?php echo $this->config->get('maxvideouploadsize');?>" /> (MB)</div>
								<div><?php echo JText::sprintf('COM_COMMUNITY_CONFIGURATION_VIDEOS_MAXIMUM_UPLOAD_SIZE_FROM_PHP', $this->uploadLimit );?></div>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_FFMPEG_PATH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_FFMPEG_PATH' ); ?>
								</span>
							</td>
							<td>
								<input name="ffmpegPath" type="text" size="60" value="<?php echo $this->config->get('ffmpegPath');?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_FLVTOOL2_PATH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_FLVTOOL2_PATH' ); ?>
								</span>
							</td>
							<td>
								<input name="flvtool2" type="text" size="60" value="<?php echo $this->config->get('flvtool2');?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_QUANTIZER_SCALE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_QUANTIZER_SCALE' ); ?>
								</span>
							</td>
							<td>
								<?php echo $this->lists['qscale']; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_SIZE' ); ?>
								</span>
							</td>
							<td>
								<?php echo $this->lists['videosSize']; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_THUMB_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_THUMB_SIZE' ); ?>
								</span>
							</td>
							<td>
								<?php echo $this->lists['videoThumbSize']; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_CUSTOM_COMMAND_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_CUSTOM_COMMAND' ); ?>
								</span>
							</td>
							<td>
								<input name="customCommandForVideo" type="text" size="60" value="<?php echo $this->config->get('customCommandForVideo');?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_PSEUDO_STREAMING_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_PSEUDO_STREAMING' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablevideopseudostream' ,'ace-switch ace-switch-5', null , $this->config->get('enablevideopseudostream') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_DEBUGGING_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_DEBUGGING' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('videodebug' ,'ace-switch ace-switch-5', null , $this->config->get('videodebug') ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
	</div>
</div>
<br>
<div class="widget-box">
	<div class="widget-header widget-header-flat">
		<h5>Zencoder</h5>
		<div class="widget-toolbar no-border">
			<a href="http://tiny.cc/jszencoder" target="_blank"><?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">
			<fieldset class="adminform">
				<div class="space-12"></div>
				<p><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_INTEGRATIONS_INFO' );?></p>
				<div class="space-24"></div>
				<table class="admintable" cellspacing="1">
					<tbody>
						<tr>
							<td class="key" width="250">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_ACCOUNT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_ACCOUNT' ); ?>
								</span>
							</td>
							<td>
								<a onclick="azcommunity.registerZencoderAccount()" class="" href="javascript: void(0);"><?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_CREATE_ACCOUNT'); ?></a>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enable_zencoder' ,'ace-switch ace-switch-5', null , $this->config->get('enable_zencoder') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_API_KEY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_VIDEOS_ZENCODER_API_KEY' ); ?>
								</span>
							</td>
							<td>
								<input name="zencoder_api_key" type="text" size="60" value="<?php echo $this->config->get('zencoder_api_key');?>" />
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
	</div>
</div>
