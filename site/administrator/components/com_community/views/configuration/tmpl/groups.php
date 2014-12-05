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
			<a  href="http://tiny.cc/jsgroup" target="_blank"> <i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
		</div>
	</div>
	<div class="widget-body">
		<div class="widget-main">

			<fieldset class="adminform">
				<table>
					<tbody>
						<tr>
							<td width="250" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_ENABLE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_ENABLE' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enablegroups' ,'ace-switch ace-switch-5', null , $this->config->get('enablegroups') ); ?>
							</td>
						</tr>
						<tr>
							<td width="350" class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_ALLOW_GUEST_SEARCH_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_ALLOW_GUEST_SEARCH' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('enableguestsearchgroups' ,'ace-switch ace-switch-5', null , $this->config->get('enableguestsearchgroups') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_MODERATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_MODERATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('moderategroupcreation' ,'ace-switch ace-switch-5', null , $this->config->get('moderategroupcreation') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_ALLOW_CREATION_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_ALLOW_CREATION' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('creategroups' ,'ace-switch ace-switch-5', null , $this->config->get('creategroups') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_CREATION_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_CREATION_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="groupcreatelimit" value="<?php echo $this->config->get('groupcreatelimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_PHOTO_UPLOAD_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_PHOTO_UPLOAD_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="groupphotouploadlimit" value="<?php echo $this->config->get('groupphotouploadlimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_VIDEO_UPLOAD_LIMIT_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_VIDEO_UPLOAD_LIMIT' ); ?>
								</span>
							</td>
							<td>
								<input type="text" name="groupvideouploadlimit" value="<?php echo $this->config->get('groupvideouploadlimit' );?>" size="10" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_ANNOUNCEMENTS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_ANNOUNCEMENTS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('createannouncement' ,'ace-switch ace-switch-5', null , $this->config->get('createannouncement') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_DISCUSSIONS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_DISCUSSIONS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('creatediscussion' ,'ace-switch ace-switch-5', null , $this->config->get('creatediscussion') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_PHOTOS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_PHOTOS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('groupphotos' ,'ace-switch ace-switch-5', null , $this->config->get('groupphotos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_VIDEOS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_VIDEOS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('groupvideos' ,'ace-switch ace-switch-5', null , $this->config->get('groupvideos') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_EVENTS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_EVENTS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('group_events' ,'ace-switch ace-switch-5', null , $this->config->get('group_events') ); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_GROUPS_DISCUSSION_NOTIFICATIONS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_GROUPS_DISCUSSION_NOTIFICATIONS' ); ?>
								</span>
							</td>
							<td>
								<?php echo CHTMLInput::checkbox('groupdiscussnotification' ,'ace-switch ace-switch-5', null , $this->config->get('groupdiscussnotification') ); ?>
							</td>
						</tr>
			            <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISCUSSION_FILE_SHARING_LIMIT_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISCUSSION_FILE_SHARING_LIMIT' ); ?>
			                    </span>
			                </td>
			                <td>
								<input type="text" name="discussionfilelimit" value="<?php echo $this->config->get('discussionfilelimit' );?>" size="8" />
							</td>
			            </tr>
			            <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISCUSSION_FILE_SHARING_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISCUSSION_FILE_SHARING' ); ?>
			                     </span>
			                 </td>
			                 <td>
			                 	<?php echo CHTMLInput::checkbox('groupdiscussfilesharing' ,'ace-switch ace-switch-5', null , $this->config->get('groupdiscussfilesharing') ); ?>
							</td>
			            </tr>
			            <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_DISCUSSION_FILE_SHARING_MAX_SIZE_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DISCUSSION_FILE_SHARING_MAX_SIZE' ); ?>
			                    </span>
			                </td>
			                <td>
								<input type="text" name="filemaxuploadsize" value="<?php echo $this->config->get('filemaxuploadsize' );?>" size="8" />(MB)
							</td>
			             </tr>
			            <tr>
			                <td class="key">
			                    <span class="editlinktip js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_CONFIGURATION_BULLETIN_FILE_SHARING_TIPS')?>">
			                        <?php echo Jtext::_('COM_COMMUNITY_CONFIGURATION_BULLETIN_FILE_SHARING')?>
			                    </span>
			                </td>
			                <td>
			                	<?php echo CHTMLInput::checkbox('groupbulletinfilesharing' ,'ace-switch ace-switch-5', null , $this->config->get('groupbulletinfilesharing') ); ?>
			                </td>
			            </tr>
					</tbody>
				</table>
			</fieldset>


		</div>
	</div>
</div>





