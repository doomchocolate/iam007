<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<div class="cLayout cGroups-Create">
	<form method="post" action="<?php echo CRoute::getURI(); ?>" id="createGroup" name="jsform-groups-create" class="cForm community-form-validate">

	<?php if($isNew) { ?>
		<p>
			<?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_DESC'); ?>
		</p>
		<?php
		if( $groupCreationLimit != 0 && $groupCreated/$groupCreationLimit>=COMMUNITY_SHOW_LIMIT) {
		?>
		<div class="hints">
			<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_LIMIT_STATUS', $groupCreated, $groupCreationLimit ); ?>
		</div>
		<?php } ?>
	<?php } ?>

		<ul class="cFormList cFormHorizontal cResetList">

			<?php if ($beforeFormDisplay) { ?>
			<?php echo $beforeFormDisplay;?>
			<?php } ?>

			<li>
				<label for="name" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE'); ?><span class="required-sign"> *</span>
				</label>

				<div class="form-field">
					<input name="name" id="name" maxlength="255" type="text" size="45" class="required input text title jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE_TIPS'); ?>" value="<?php echo $this->escape($group->name); ?>" />
					<span id="errnamemsg" style="display:none;">&nbsp;</span>
					<!-- group type -->

					<label for="approve-private" class="label-checkbox">
						<input type="checkbox" name="approvals" class="checkbox" id="approve-private" value="1"<?php echo ($group->approvals == COMMUNITY_PRIVATE_GROUP ) ? ' checked="checked"' : '';?> />
						<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_APPROVAL_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_LABEL');?>
						</span>
					</label>
				</div>
			</li>

			<!-- group description -->
			<li>
				<label for="description" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION');?><span class="required-sign"> *</span>
				</label>

				<div class="form-field wysiwyg-field" style="display:none">
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_BODY_TIPS');?>">
					<?php if( $config->get( 'htmleditor' ) == 'none' && $config->getBool('allowhtml') ) { ?>
						<div class="htmlTag"><?php echo JText::_('COM_COMMUNITY_HTML_TAGS_ALLOWED');?></div>
					<?php } ?>

					<?php

					if( !CStringHelper::isHTML($group->description)
						&& $config->get('htmleditor') != 'none'
						&& $config->getBool('allowhtml') )
					{
						//$event = new stdClass();
						$group->description = CStringHelper::nl2br($group->description);
					}
					?>

					<?php echo $editor->displayEditor('description',  $group->description , '90%', '300', '10', '20' , false); ?>
					</span>
				</div>

				<script type="text/javascript">
					joms.jQuery(window).load(function() {

						if(joms.jQuery(this).width() <= 980) {
							joms.jQuery('.wysiwyg-field').empty().append('<textarea name="description">' + <?php echo json_encode($group->description); ?> + '</textarea>').show();
						} else {
							joms.jQuery('.wysiwyg-field').show();
						}
					});
				</script>

			</li>
			<!-- group category -->
			<li>
				<label for="categoryid" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY');?><span class="required-sign"> *</span>
				</label>

				<div class="form-field">
					<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_TIPS');?>">
						<?php echo $lists['categoryid']; ?>
					</span>
				</div>
			</li>

			<?php if($config->get('enablephotos') && $config->get('groupphotos')): ?>
			<!-- group photos -->
			<li class="has-seperator">
				<label for="grouprecentphotos-admin" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTO');?>
				</label>

				<div class="form-field">
					<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTOS_TIPS');?>">
						<input type="text" name="grouprecentphotos" class="input text" id="grouprecentphotos-admin" size="1" value="<?php echo $group->grouprecentphotos;?>" />
					</span>

					<label for="photopermission-admin" class="label-checkbox">
						<input type="checkbox" name="photopermission-admin" class="checkbox" id="photopermission-admin" onclick="checkPhotoPermission()" value="1" <?php echo ($params->get('photopermission',1) >= 1) ? ' checked="checked"' : '';?> />
						<span class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_PERMISSION_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALOW_ADMIN');?>
						</span>
					</label>

					<!-- <div id="photopermission" style="<?php echo ($params->get('photopermission') >= 1)? '':'display:block' ?>"> -->
					<label for="photopermission-member" class="label-checkbox">
						<input type="checkbox" name="photopermission-member" class="checkbox" id="photopermission-member" value="1" <?php echo ( $params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL ) ? ' checked="checked"' : '';?> disabled="disabled" />
						<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALLOW_MEMBER');?>
					</label>
					<!-- </div> -->

					<script type="text/javascript">
						function checkPhotoPermission(){
						if(joms.jQuery('#photopermission-admin').prop('checked')==true){
							joms.jQuery('#photopermission-member').removeAttr('disabled');
						}else{
							joms.jQuery('#photopermission-member').attr('disabled', 'disabled');
						}
						}
					</script>
				</div>
			</li>
			<?php endif;?>



			<?php if($config->get('enablevideos') && $config->get('groupvideos')): ?>
			<!-- group videos -->
			<li class="has-seperator">
				<label for="grouprecentvideos-admin" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO');?>
				</label>

				<div class="form-field">
					<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO_TIPS');?>">
						<input type="text" name="grouprecentvideos" id="grouprecentvideos-admin" size="1" value="<?php echo $group->grouprecentvideos;?>" />
					</span>

					<label for="videopermission-admin" class="label-checkbox">
						<input type="checkbox" name="videopermission-admin" class="checkbox" onclick="checkVideoPermission()" id="videopermission-admin" value="1"<?php echo ($params->get('videopermission',1) >= 1) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEOS_PERMISSION_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_ADMIN');?>
						</span>
					</label>

					<label for="videopermission-member" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="videopermission-member" id="videopermission-member" value="0" <?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_ALL ) ? ' checked="checked"' : '';?> disabled="disabled" />
						<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_MEMBER');?>
					</label>

					<script type="text/javascript">
						function checkVideoPermission(){
							if(joms.jQuery('#videopermission-admin').prop('checked')==true){
								joms.jQuery('#videopermission-member').removeAttr('disabled');
							}else{
								joms.jQuery('#videopermission-member').attr('disabled', 'disabled');
							}
						}
					</script>
				</div>
			</li>
			<?php endif;?>

			<?php if($config->get('enableevents') && $config->get('group_events')): ?>
			<!-- Group event -->
			<li class="has-seperator">
				<label for="grouprecentvideos-admin" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS');?>
				</label>
				<div class="form-field">
					<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_EVENT_TIPS');?>">
						<input type="text" name="grouprecentevents" id="grouprecentevents-admin" size="1" value="<?php echo $group->grouprecentevents;?>" />
					</span>

					<label for="eventpermission-admin" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="eventpermission-admin" onclick="checkEventPermission()" id="eventpermission-admin" value="1" <?php echo ( $params->get('eventpermission',1) >= 1 ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_PERMISSIONS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_ADMIN_CREATION');?>
						</span>
					</label>

					<!-- <div id="eventpermission" style="<?php echo ($params->get('eventpermission') >= 1 ) ? '' : 'display:block' ?>"> -->
					<label for="eventpermission-member" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="eventpermission-member" id="eventpermission-member" value="0"<?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_ALL ) ? ' checked="checked"' : '';?> disabled="disabled" />
						<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_MEMBERS_CREATION');?>
					</label>
					<!-- </div> -->

					<script type="text/javascript">
					function checkEventPermission()
					{
						if(joms.jQuery('#eventpermission-admin').prop('checked')==true)
						{
							joms.jQuery('#eventpermission-member').removeAttr('disabled');
						}
						else
						{
							joms.jQuery('#eventpermission-member').attr('disabled', 'disabled');
						}
					}
					</script>
				</div>
			</li>
			<?php endif;?>


			<?php if($config->get('groupdiscussfilesharing')) { ?>
			<li class="has-seperator">
				<label for="groupdiscussionfilesharing" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_DISCUSSION')?>
				</label>
				<div class="form-field">
					<label for="groupdiscussionfilesharing" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="groupdiscussionfilesharing" id="groupdiscussionfilesharing" value="1" <?php echo ( $params->get('groupdiscussionfilesharing') >= 1 ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_FILES_ENABLE_SHARING')?>">
							<?php echo JText::_('COM_COMMUNITY_FILES_ENABLE_SHARING');?>
						</span>
					</label>

					<label for="discussordering-creation" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="discussordering" id="discussordering-creation" value="1"<?php echo ($group->discussordering == 1 ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_ORDERING_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_CREATION_DATE');?>
						</span>
					</label>
				</div>
			</li>
			<?php }?>

			<?php if($config->get('groupbulletinfilesharing')) { ?>
			<li class="has-seperator">
				<label for="groupannouncementfilesharing" class="form-label">
					<?php echo JText::_('COM_COMMUNITY_ANNOUNCEMENT')?>
				</label>

				 <div class="form-field">
					<label for="groupannouncementfilesharing" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="groupannouncementfilesharing" id="groupdiscussionfilesharing" value="1" <?php echo ( $params->get('groupannouncementfilesharing') >= 1 ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_FILES_ENABLE_SHARING')?>">
							<?php echo JText::_('COM_COMMUNITY_FILES_ENABLE_SHARING');?>
						</span>
					</label>
				 </div>
			</li>
			<?php } ?>



			<li class="has-seperator">
				<label class="form-label">
					<?php echo JText::_('COM_COMMUNITY_GROUPS_NOTIFICATION');?>
				</label>

				<div class="form-field">
					<!-- NEW MEMBER -->
					<label for="newmembernotification-enable" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="newmembernotification" id="newmembernotification-enable" value="1"<?php echo ($params->get('newmembernotification', '1') == true ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION');?>
						</span>
					</label>

					<!-- JOIN REQUEST -->
					<label for="joinrequestnotification-enable" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="joinrequestnotification" id="joinrequestnotification-enable" value="1"<?php echo ($params->get('joinrequestnotification', '1') == true ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION');?>
						</span>
					</label>

					<!-- WALL -->
					<label for="wallnotification-enable" class="label-checkbox">
						<input type="checkbox" class="checkbox" name="wallnotification" id="wallnotification-enable" value="1"<?php echo ($params->get('wallnotification', '1') == true ) ? ' checked="checked"' : '';?> />
						<span class="value jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION_TIPS');?>">
							<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION');?>
						</span>
					</label>
				</div>
			</li>

			<?php if ( $afterFormDisplay ) { ?>
				<?php echo $afterFormDisplay;?>
			<?php } ?>

			<!-- group hint -->
			<li class="has-seperator">
				<div class="form-field">
					<span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span>
				</div>
			</li>

			<!-- group buttons -->
			<li class="form-action">
				<div class="form-field	">
					<?php if($isNew): ?>
					<input name="action" type="hidden" value="save" />
					<?php endif;?>
					<input type="hidden" name="groupid" value="<?php echo $group->id;?>" />
					<input type="button" class="btn" onclick="history.go(-1);return false;" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON');?>" />
					<input type="submit" value="<?php echo ($isNew) ? JText::_('COM_COMMUNITY_GROUPS_CREATE_GROUP') : JText::_('COM_COMMUNITY_SAVE_BUTTON');?>" class="btn btn-primary validateSubmit" />
					<?php echo JHTML::_( 'form.token' ); ?>
				</div>
			</li>
		</ul>
	</form>
</div>

<script type="text/javascript">
	cvalidate.init();
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');
	cvalidate.setMaxLength('#createGroup #description', 65000);

	// We need to enabled certain checkbox is admin enable certain feature
	joms.jQuery(document).ready(function() {
		if( joms.jQuery('#videopermission-admin').val() == '1') {
			joms.jQuery('#videopermission-member').attr('disabled', false);
		}

		if( joms.jQuery('#photopermission-admin').val() == '1') {
			joms.jQuery('#photopermission-member').attr('disabled', false);
		}

		if( joms.jQuery('#eventpermission-admin').val() == '1') {
			joms.jQuery('#eventpermission-member').attr('disabled', false);
		}
	});
</script>