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

$params	= $this->group->getParams();
?>

<script type="text/javascript">
	function jSelectUser_jform_user_id_to(id,name){
	    joms.jQuery("#sbox-window, #sbox-overlay").hide();
	    joms.jQuery("#creator_name").val(name);
	    joms.jQuery("#creator_id").val(id);
	}

	function js_Show(){
	    joms.jQuery("#sbox-window, #sbox-overlay").show();
	}
</script>

<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST">
<table  width="100%" class="paramlist admintable" cellspacing="1">
	<tr>
		<td class="paramlist_key">
			<label for="name" class="title" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE_TIPS'); ?>">
				<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE_TIPS'); ?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE'); ?></span>
                <span class="required-sign"> *</span>
			</label>
		</td>
		<td class="paramlist_value">
			<input type="text" name="name" value="<?php echo $this->group->name; ?>" style="width: 200px;" />
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="avatar"><span class="js-tooltip" title="<?php echo JText::_( 'COM_COMMUNITY_GROUPS_AVATAR_TIPS' );?>"><?php echo JText::_( 'COM_COMMUNITY_GROUPS_AVATAR' );?></span></label>
		</td>
		<td>
			<img src="<?php echo $this->group->getThumbAvatar();?>" />
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="published"><span class="js-tooltip" title="<?php echo JText::_( 'COM_COMMUNITY_GROUP_PUBLISH_TIPS' );?>"><?php echo JText::_( 'COM_COMMUNITY_PUBLISH' );?></span></label>
		</td>
		<td class="paramlist_value">
			<?php echo CHTMLInput::checkbox('published' ,'ace-switch ace-switch-5', null , $this->group->get('published') ); ?>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="creator"><span class="js-tooltip" title="<?php echo JText::_( 'COM_COMMUNITY_GROUPS_CREATOR_TIPS' );?>"><?php echo JText::_( 'COM_COMMUNITY_GROUPS_CREATOR');?></span></label>
		</td>
		<td class="paramlist_value">
		<?php


		    $creator	= CFactory::getUser( $this->group->ownerid );
		    ?>
			<input type="text" name="creator-display" id="creator_name" value="<?php echo $creator->getDisplayName();?>" disabled="disabled"/></div>
				<a class="btn btn-mini btn-info modal modal-button" title="<?php echo JText::_( 'Select a user');?>"  rel="{handler: 'iframe', size: {x: 750, y: 450}}" href="<?php echo $this->url; ?>">
				<?php echo JText::_( 'COM_COMMUNITY_GROUPS_SELECT_CREATOR');?>
				</a>
			<input type="hidden" name="creator" id="creator_id" value="<?php echo $creator->id;?>" />
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="description" class="title">
				<span class="js-tooltip" title="<?php echo JText::_( 'COM_COMMUNITY_GROUPS_BODY_TIPS' );?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION');?></span>
                <span class="required-sign"> *</span>
			</label>
		</td>
		<td class="paramlist_value">
			<?php echo $this->editor->displayEditor('description',  $this->group->description , '50%', '300', '10', '20' , false); ?>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="categoryid" class="title" >
				<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY');?></span>
                <span class="required-sign"> *</span>
			</label>
		</td>
		<td class="paramlist_value">
		<?php
		$select	= '<select name="categoryid" style="visibility:visible;">';

		$select	.= ( $this->group->categoryid == 0 ) ? '<option value="0" selected="true">' : '<option value="0">';
		$select .= JText::_('COM_COMMUNITY_GROUPS_SELECT_CATEGORY') . '</option>';

		for( $i = 0; $i < count( $this->categories ); $i++ )
		{
			$selected	= ( $this->group->categoryid == $this->categories[$i]->id ) ? ' selected="true"' : '';
			$select	.= '<option value="' . $this->categories[$i]->id . '"' . $selected . '>' . $this->categories[$i]->name . '</option>';
		}
		$select	.= '</select>';

		echo $select;
		?>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label class="title" >
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_APPROVAL_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_TYPE'); ?></span>
			</label>
		</td>

		<td class="paramlist_value">
			<select style="visibility:visible;" name='approvals'>
				<option value='0' <?php echo ($this->group->approvals == COMMUNITY_PUBLIC_GROUP ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_OPEN');?></option>
				<option value='1' <?php echo ($this->group->approvals == COMMUNITY_PRIVATE_GROUP ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE');?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label class="title" >
				<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_ORDERING_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER'); ?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<select style="visibility:visible;" name='discussordering'>
				<option value='0' <?php echo ($params->get('discussordering') == 0 ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_LAST_REPLIED');?></option>
				<option value='1' <?php echo ($params->get('discussordering') == 1 ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_CREATION_DATE');?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label class="title">
				<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_PERMISSION_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS'); ?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<select style="visibility:visible;" name='photopermission'>
				<option value='-1' <?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_DISABLE ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_DISABLED');?></option>
				<option value='1'  <?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ADMINS ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALOW_ADMIN');?></option>
				<option value='2'  <?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALLOW_MEMBER');?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="grouprecentphotos-admin" class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTOS_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTO');?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<input type="text" name="grouprecentphotos" id="grouprecentphotos-admin" size="1" value="<?php echo $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="discussordering" class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEOS_PERMISSION_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<div class="space-12"></div>
			<select style="visibility:visible;" name='videopermission'>
				<option value='-1' <?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_DISABLE ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_DISABLED');?></option>
				<option value='1' <?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_ADMINS ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_ADMIN');?></option>
				<option value='2' <?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_ALL ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_MEMBER');?></option>
			</select>
			<div class="space-12"></div>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="grouprecentvideos-admin" class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO');?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<input type="text" name="grouprecentvideos" id="grouprecentvideos-admin" size="1" value="<?php echo $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_PERMISSIONS');?>"><?php echo JText::_('COM_COMMUNITY_EVENTS');?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<div class="space-12"></div>
			<select style="visibility:visible;" name='eventpermission'>
				<option value='-1' <?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_DISABLE ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_DISABLE');?></option>
				<option value='1' <?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_ADMINS ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_ADMIN_CREATION');?></option>
				<option value='2' <?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_ALL ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_MEMBERS_CREATION');?></option>
			</select>
			<div class="space-12"></div>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label for="grouprecentevents-admin" class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_EVENT_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS');?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<input type="text" name="grouprecentevents" id="grouprecentevents-admin" size="1" value="<?php echo $params->get('grouprecentevents', GROUP_EVENT_RECENT_LIMIT);?>" />
		</td>
	</tr>
    <tr>
        <td class="paramlist_key">
            <label class="title">
                <span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_FILES_ENABLE_SHARING_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_FILES_ENABLE_SHARING'); ?></span>
            </label>
        </td>
        <td class="paramlist_value">
            <div class="space-12"></div>
            <select style="visibility:visible;" name='groupannouncementfilesharing'>
                <option value='0'><?php echo JText::_('COM_COMMUNITY_DISABLE');?></option>
                <option value='1'><?php echo JText::_('COM_COMMUNITY_ENABLE');?></option>
            </select>
            <div class="space-12"></div>
        </td>
    </tr>
	<tr>
		<td class="paramlist_key">
			<label class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION'); ?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<div class="space-12"></div>
			<select style="visibility:visible;" name='newmembernotification'>
				<option value='1' <?php echo ($params->get('newmembernotification', '1') == true ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_ENABLE');?></option>
				<option value='0' <?php echo ($params->get('newmembernotification', '1') == false ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_DISABLE');?></option>
			</select>
			<div class="space-12"></div>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION'); ?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<div class="space-12"></div>
			<select style="visibility:visible;" name='joinrequestnotification'>
				<option value='1' <?php echo ($params->get('joinrequestnotification', '1') == true ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_ENABLE');?></option>
				<option value='0' <?php echo ($params->get('joinrequestnotification', '1') == false ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_DISABLE');?></option>
			</select>
			<div class="space-12"></div>
		</td>
	</tr>
	<tr>
		<td class="paramlist_key">
			<label class="title">
				<span class="js-tooltip"  title="<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION'); ?></span>
			</label>
		</td>
		<td class="paramlist_value">
			<div class="space-12"></div>
			<select style="visibility:visible;" name='wallnotification'>
				<option value='1' <?php echo ($params->get('wallnotification', '1') == true ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_ENABLE');?></option>
				<option value='0' <?php echo ($params->get('wallnotification', '1') == false ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_DISABLE');?></option>
			</select>
			<div class="space-12"></div>
		</td>
	</tr>
</table>
<input type="hidden" name="view" value="groups" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="groupid" value="<?php echo $this->group->id; ?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>