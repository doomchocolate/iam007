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

<div class="row-fluid">
	<div class="span12">
		<div class="widget-box">
			<div class="widget-header widget-header-flat">
				<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_USER_PRIVACY' ); ?></h5>
				<div class="widget-toolbar no-border">
					<a href="http://tiny.cc/userprivacy" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
				</div>
			</div>
			<div class="widget-body">
				<div class="widget-main">

					<table>
						<tbody>
							<tr>
								<td width="250" class="key">
									<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_PROFILE_PRIVACY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_PROFILE_PRIVACY' ); ?>
									</span>
								</td>
								<td class="privacyprofile">
									<?php echo $this->getPrivacyHTML( 'privacyprofile' , $this->config->get('privacyprofile') ); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_FRIENDS_PRIVACY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_FRIENDS_PRIVACY' ); ?>
									</span>
								</td>
								<td class="privacyfriends">
									<?php echo $this->getPrivacyHTML( 'privacyfriends' , $this->config->get('privacyfriends') , true ); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_PHOTOS_PRIVACY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_PHOTOS_PRIVACY' ); ?>
									</span>
								</td>
								<td class="privacyphotos">
									<?php echo $this->getPrivacyHTML( 'privacyphotos' , $this->config->get('privacyphotos') , true ); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_VIDEOS_PRIVACY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_VIDEOS_PRIVACY' ); ?>
									</span>
								</td>
								<td class="privacyvideos">
									<?php echo $this->getPrivacyHTML( 'privacyvideos' , $this->config->get('privacyvideos') , true ); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_GROUPLIST_PRIVACY_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_GROUPLIST_PRIVACY' ); ?>
									</span>
								</td>
								<td class="privacy_groups_list">
									<?php echo $this->getPrivacyHTML( 'privacy_groups_list' , $this->config->get('privacy_groups_list') , true ); ?>
								</td>
							</tr>
							<tr>
								<td class="key"></td>
								<td>
									<div class="space-12"></div>
									<input class="btn btn-small btn-danger" type="button" value="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_RESET_EXISTING_PRIVACY_BUTTON');?>" onclick="azcommunity.resetprivacy();" />
									<span id="privacy-update-result" class="small block"></span>
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
				<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_ADMINISTRATORS' ); ?></h5>
			</div>
			<div class="widget-body">
				<div class="widget-main">
					<table>
						<tbody>
							<tr>
								<td class="key">
									<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_HIDE_ADMINS_TIPS'); ?>">
									<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_HIDE_ADMINS' ); ?>
									</span>
								</td>
								<td>
									<?php echo CHTMLInput::checkbox('privacy_show_admins' ,'ace-switch ace-switch-5', null , $this->config->get('privacy_show_admins') ); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>


	</div>

	<div class="span12">
		<div class="widget-box">
			<div class="widget-header widget-header-flat">
				<h5><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_PRIVACY_EMAIL_NOTIFICATIONS' ); ?></h5>
			</div>
			<div class="widget-body">
				<div class="widget-main">
					<table>
						<tr>
							<td class="key" width="250">
								<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_NOTIFICATION_LIMIT_TIPS'); ?>">
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_NOTIFICATION_LIMIT' ); ?>
								</span>
							</td>
							<td >
								<input type="text" class="input-medium" name="maxnotification" value="<?php echo $this->config->get('maxnotification',20 );?>" />
								<?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_DAYS' ); ?>
							</td>
						</tr>
					</table>

								<table class="table table-cpndensed table-hover">
										<thead>
											<tr class="title">
												<th>
													<?php echo JText::_( 'COM_COMMUNITY_TYPE' ); ?>
												</th>
												<th width="70">
													<?php echo JText::_( 'COM_COMMUNITY_EMAIL' ); ?>
												</th>
												<th width="70">
													<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATION' ); ?>
												</th>
											</tr>
										</thead>
									<?php
										foreach($this->notificationTypes->getTypes() as $group){
											foreach($group->child as $id => $type){
												$emailId  = $this->notificationTypes->convertEmailId($id);
												$notifId  = $this->notificationTypes->convertNotifId($id);
												$emailset = $this->config->get($emailId);
												$notifset = $this->config->get($notifId);

									?>
									<tr>
										<td class="key">
											<span class="js-tooltip" title="<?php echo JText::_($type->tips); ?>">
											<?php echo JText::_( $type->description ); ?>
											</span>
										</td>
										<td class="text-center">
										<input type="hidden" name="<?php echo $emailId; ?>" value="0" />
										<input class="notification_cfg" id="<?php echo $emailId ?>" type="checkbox" name="<?php echo $emailId; ?>" value="1" <?php if( $emailset == 1) echo 'checked="checked"'; ?> />
										<span class="lbl"></span>
										</td>
										<td class="text-center">
										<input type="hidden" name="<?php echo $notifId; ?>" value="0" />
										<input class="notification_cfg" id="<?php echo $notifId; ?>" type="checkbox" name="<?php echo $notifId; ?>" value="1" <?php if( $notifset == 1) echo 'checked="checked"'; ?> />
										<span class="lbl"></span>
										</td>
									</tr>
									<?php
											}
										}
									?>
									</table>

									<input type="button" class="btn btn-small btn-danger" value="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_PRIVACY_RESET_EXISTING_NOTIFICATION_BUTTON');?>" onclick="azcommunity.resetnotification('<?php echo JText::_('COM_COMMUNITY_PLEASE_WAIT_BUTTON')?>');" />
									<span id="notification-update-result" class="small block"></span>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery(function() {
    jQuery('div.btn-group[data-toggle-name]').each(function() {
        var group = jQuery(this);
        var form = group.parents('form').eq(0);
        var name = group.attr('data-toggle-name');
        var hidden = jQuery('input[name="' + name + '"]', form);
        
        jQuery('button', group).each(function() {
            var button = jQuery(this);
            button.on('click', function() {
                hidden.val(jQuery(this).val());
            	group.find('button').removeClass('btn-success').addClass('btn-light');
				jQuery(this).removeClass('btn-light').addClass('btn-success');
            });
            if (button.val() == hidden.val()) {
                button.addClass('active');
            }
        });
    });
});
</script>