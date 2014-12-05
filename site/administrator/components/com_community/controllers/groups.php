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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerGroups extends CommunityController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask( 'publish' , 'savePublish' );
		$this->registerTask( 'unpublish' , 'savePublish' );
	}

	public function display( $cachable = false, $urlparams = array() )
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );

		$model		= $this->getModel( $viewName ,'CommunityAdminModel' );

		if( $model )
		{
			$view->setModel( $model , $viewName );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();
	}

	public function ajaxTogglePublish( $id , $type, $viewName = false )
	{
		// Send email notification to owner when a group is published.
		$config	= CFactory::getConfig();
		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );

        if( $type == 'published' && $group->published == 0 && $config->get( 'moderategroupcreation' ) )
		{
           $this->notificationApproval($group);
        }

		return parent::ajaxTogglePublish( $id , $type , 'groups' );
	}

	public function ajaxChangeGroupOwner( $groupId )
	{
		$response	= new JAXResponse();

		$group		= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );
		$model			= CFactory::getModel( 'Groups' );

		$group->owner	= JFactory::getUser( $group->ownerid );
		$rows			= $model->getMembers( $group->id , NULL , true , false , true );
		ob_start();
?>
<div class="alert alert-info">
	<?php echo JText::_('COM_COMMUNITY_GROUPS_CHANGE_OWNERSHIP');?>
</div>
<form name="editgroup" method="post" action="">
<table width="100%">
	<tbody>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_OWNER');?></td>
			<td align="left">
				<?php echo $group->owner->name; ?>
			</td>
		</tr>
		<tr>
			<td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_OWNER_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_OWNER');?></span></td>
			<td align="left">
				<?php
				if($rows)
				{
				?>
				<select name="ownerid">
					<?php
						foreach( $rows as $row )
						{
							$user	= CFactory::getUser( $row->id );
					?>
						<option value="<?php echo $user->id;?>"><?php echo JText::sprintf('%1$s [ %2$s ]' , $user->name , $user->email );?></option>
					<?php
						}
					?>
				</select>
				<?php
				}
				else
				{
				?>
				<div><?php echo JText::_('COM_COMMUNITY_GROUPS_CHANGE_OWNER_WARN');?></div>
				<?php
				}
				?>
			</td>
		</tr>
	</tbody>
</table>
<input name="id" value="<?php echo $group->id;?>" type="hidden" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="updateGroupOwner" />
<input type="hidden" name="view" value="groups" />
</form>
<?php
		$contents	= ob_get_contents();
		ob_end_clean();

		$response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

		$action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.saveGroupOwner();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
		$action .= '<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
		$response->addScriptCall( 'cWindowActions' , $action );

		return $response->sendResponse();
	}

	public function ajaxAssignGroup( $memberId )
	{
		require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
		$response	= new JAXResponse();

		$model		= $this->getModel( 'groups', 'CommunityAdminModel' );
		$groups		= $model->getAllGroups();
		$user		= CFactory::getUser( $memberId );
		ob_start();
?>
<form name="assignGroup" action="" method="post" id="assignGroup">
<div class="alert alert-info">
	<?php echo JText::sprintf('COM_COMMUNITY_GROUP_ASSIGN_MEMBER', $user->getDisplayName() );?>
</div>
<table width="100%">
	<tbody>
		<tr>
			<td class="key"><span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_ASSIGN_GROUPS_TIPS');?>"><?php echo JText::_('COM_COMMUNITY_GROUPS');?></span></td>
			<td>
				<select name="groupid[]" id="groupid" multiple="true">
					<!--option value="-1" selected="selected"><?php echo JText::_('COM_COMMUNITY_GROUPS_SELECT');?></option-->
				<?php
					foreach($groups as $row )
					{
						$selected = $model->isMember($user->id , $row->id)?'selected="true"':'';
				?>
					<option value="<?php echo $row->id;?>" <?php echo $selected?> ><?php echo $row->name;?></option>
				<?php

					}
				?>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<div id="group-error-message" style="color: red;font-weight:700;"></div>
<input type="hidden" name="memberid" value="<?php echo $user->id;?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="addmember" />
<input type="hidden" name="view" value="groups" />
<?php
		$contents	= ob_get_contents();
		ob_end_clean();

		$response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

		$action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.saveAssignGroup();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
		$action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
		$response->addScriptCall( 'cWindowActions' , $action );
		$response->addScriptCall( 'joms.jQuery("#cwin_logo").html("' . JText::_('COM_COMMUNITY_GROUPS_ASSIGN_USER') . '");');
		return $response->sendResponse();
	}

	public function ajaxEditGroup( $groupId )
	{
		$response	= new JAXResponse();
		$model		= $this->getModel( 'groupcategories' );
		$categories	= $model->getCategories();

		$group		= JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );

		$requireApproval	= ($group->approvals) ? ' checked="true"' : '';
		$noApproval			= (!$group->approvals) ? '' : ' checked="true"';

		// Escape the output
		$group->name	= CStringHelper::escape($group->name);
		$group->description	= CStringHelper::escape($group->description);

		$params	= $group->getParams();

		ob_start();
?>
<form name="editgroup" action="" method="post" id="editgroup">
<div class="alert alert-info">
	<?php echo JText::_('COM_COMMUNITY_GROUPS_EDIT_GROUP');?>
</div>
<table cellspacing="0" class="admintable" border="0" width="100%">
	<tbody>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE'); ?></td>
			<td>
				<input type="text" name="name" value="<?php echo $group->name; ?>" style="width: 200px;" />
			</td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_AVATAR');?></td>
			<td>
				<img width="90" src="<?php echo $group->getThumbAvatar();?>" style="border: 1px solid #eee;"/>
			</td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_PUBLISH_STATUS');?></td>
			<td>
				<?php echo CHTMLInput::checkbox('published' ,'ace-switch ace-switch-5', null , $group->get('published') ); ?>
			</td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUP_TYPE');?></td>
			<td>
				<select style="visibility:visible;" name='approvals'>
					<option value='0' <?php echo ($group->approvals == COMMUNITY_PUBLIC_GROUP ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_PUBLIC');?></option>
					<option value='1' <?php echo ($group->approvals == COMMUNITY_PRIVATE_GROUP ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUP_PRIVATE');?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_CATEGORY');?></td>
			<td>
				<select name="categoryid">
				<?php

					for( $i = 0; $i < count( $categories ); $i++ )
					{
						$selected	= ($group->categoryid == $categories[$i]->id ) ? ' selected="selected"' : '';
				?>
						<option value="<?php echo $categories[$i]->id;?>"<?php echo $selected;?>><?php echo $categories[$i]->name;?></option>
				<?php
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_COMMUNITY_DESCRIPTION');?></td>
			<td>
				<textarea name="description" style="width: 250px;" rows="5"><?php echo $group->description;?></textarea>
			</td>
		</tr>
		<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER'); ?></td>
		<td class="paramlist_value">
			<select style="visibility:visible;" name='discussordering'>
				<option value='0' <?php echo ($params->get('discussordering') == 0 ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_LAST_REPLIED');?></option>
				<option value='1' <?php echo ($params->get('discussordering') == 1 ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_CREATION_DATE');?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_PHOTOS'); ?></td>
		<td class="paramlist_value">
			<select style="visibility:visible;" name='photopermission'>
				<option value='-1' <?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_DISABLE ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_DISABLED');?></option>
				<option value='1'  <?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ADMINS ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALOW_ADMIN');?></option>
				<option value='2'  <?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ALL ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALLOW_MEMBER');?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTO');?></td>
		<td class="paramlist_value">
			<input type="text" name="grouprecentphotos" id="grouprecentphotos-admin" size="1" value="<?php echo $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?></td>
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
		<td class="key">
			<label for="grouprecentvideos-admin" class="title"><?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO');?></td>
		<td class="paramlist_value">
			<input type="text" name="grouprecentvideos" id="grouprecentvideos-admin" size="1" value="<?php echo $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_EVENTS');?></td>
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
		<td class="key">
			<label for="grouprecentevents-admin" class="title"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS');?></td>
		<td class="paramlist_value">
			<input type="text" name="grouprecentevents" id="grouprecentevents-admin" size="1" value="<?php echo $params->get('grouprecentevents', GROUP_EVENT_RECENT_LIMIT);?>" />
		</td>
	</tr>
    <tr>
        <td class="key"><?php echo JText::_('COM_COMMUNITY_FILES_ENABLE_SHARING'); ?></td>
        <td class="paramlist_value">
            <div class="space-12"></div>
            <select style="visibility:visible;" name='groupannouncementfilesharing'>
                <option value='0' <?php echo ($params->get('groupannouncementfilesharing', '1') == false ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_DISABLE');?></option>
                <option value='1' <?php echo ($params->get('groupannouncementfilesharing', '1') == true ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_ENABLE');?></option>
            </select>
            <div class="space-12"></div>
        </td>
    </tr>
	<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION'); ?></td>
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
		<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION'); ?></td>
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
		<td class="key"><?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION'); ?></td>
		<td class="paramlist_value">
			<div class="space-12"></div>
			<select style="visibility:visible;" name='wallnotification'>
				<option value='1' <?php echo ($params->get('wallnotification', '1') == true ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_ENABLE');?></option>
				<option value='0' <?php echo ($params->get('wallnotification', '1') == false ) ? ' selected="selected"' : '';?> ><?php echo JText::_('COM_COMMUNITY_DISABLE');?></option>
			</select>
			<div class="space-12"></div>
		</td>
	</tr>
	</tbody>
</table>
<input type="hidden" name="id" value="<?php echo $group->id;?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="savegroup" />
<input type="hidden" name="view" value="groups" />
<?php
		$contents	= ob_get_contents();
		ob_end_clean();

		$response->addAssign( 'cWindowContent' , 'innerHTML' , $contents );

		$action = '<input type="button" class="btn btn-small btn-primary pull-right" onclick="azcommunity.saveGroup();" name="' . JText::_('COM_COMMUNITY_SAVE') . '" value="' . JText::_('COM_COMMUNITY_SAVE') . '" />';
		$action .= '&nbsp;<input type="button" class="btn btn-small pull-left" onclick="cWindowHide();CKEDITOR.instances[\'description\'].destroy(true);" name="' . JText::_('COM_COMMUNITY_CLOSE') . '" value="' . JText::_('COM_COMMUNITY_CLOSE') . '" />';
		$response->addScriptCall( 'cWindowActions' , $action );
		$response->addScriptCall('if( CKEDITOR.instances["description"] ){ CKEDITOR.instances["description"].destroy(true);} CKEDITOR.replace( "description",{height:"300px",width:"100%",customConfig : "config.js"});');

		return $response->sendResponse();
	}

	public function updateGroupOwner()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$group	= JTable::getInstance( 'Groups' , 'CommunityTable' );

		$groupId	= $jinput->post->get('id', '', 'INT');
		$group->load( $groupId );

		$oldOwner	= $group->ownerid;
		$newOwner	= $jinput->get('ownerid', NULL, 'INT');

		// Add member if member does not exist.
		if( !$group->isMember( $newOwner , $group->id ) )
		{
			$data 	= new stdClass();
			$data->groupid			= $group->id;
			$data->memberid		= $newOwner;
			$data->approved		= 1;
			$data->permissions	= 1;

			// Add user to group members table
			$group->addMember( $data );

			// Add the count.
			$group->addMembersCount( $group->id );

			$message	= JText::_('COM_COMMUNITY_GROUP_SAVED');
		}
		else
		{
			// If member already exists, update their permission
			$member	= JTable::getInstance( 'GroupMembers' , 'CommunityTable' );
			$keys = array('groupId'=>$group->id, 'memberId'=>$newOwner);
			$member->load( $keys );
			$member->permissions	= '1';

			$member->store();
		}

		$group->ownerid	= $newOwner;
		$group->store();

		$message	= JText::_('COM_COMMUNITY_GROUP_OWNER_SAVED');

		$mainframe	= JFactory::getApplication();
		$mainframe->redirect( 'index.php?option=com_community&view=groups' , $message ,'message');
	}

	/**
	 *	Adds a user to an existing group
	 **/
	public function addMember()
	{
		require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$groupId	= $jinput->request->get('groupid' , array(), 'array');
		$memberId	= $jinput->request->get('memberid' , '', 'INT');

		if( empty($memberId) || $groupId == '-1' )
		{
			$message	= JText::_('COM_COMMUNITY_INVALID_ID');
			$mainframe->redirect( 'index.php?option=com_community&view=users' , $message , 'error');
		}

		$group		= JTable::getInstance( 'Groups' , 'CommunityTable' );
		$model		= $this->getModel( 'groups' ,'CommunityAdminModel' );
		$user		= CFactory::getUser($memberId);


		$all_groups		= $model->getAllGroups();
		// reset the current groups
		foreach($all_groups as $group_row)
		{
			$data		= new stdClass();
			$data->groupid	= $group_row->id;
			$data->memberid	= $user->id;

			$model->removeMember($data);
			// STore the group and update the data
			$group->load($group_row->id);
			$group->updateStats();
			$group->store();
		}

		// update the group membership
		if(!empty($groupId))
		{
			foreach($groupId as $groupId_row)
			{
				$group->load( $groupId_row );

				$data 	= new stdClass();
				$data->groupid		= $groupId_row;
				$data->memberid		= $user->id;
				$data->approved		= 1;
				$data->permissions	= 0;

				// Add user to group members table
				$group->addMember( $data );

				// Add the count.
				$group->addMembersCount( $groupId_row );

				$groups_name_array[] = $group->name;
			}

			$groups_name = implode($groups_name_array, ', ');

			$message	= JText::sprintf('%1$s has been assigned into the group %2$s.' , $user->getDisplayName() , $groups_name );
			$user->updateGroupList(true);
		}
		else
		{
			$message	= JText::_('%1$s has been un-assigned from any groups.' );

		}


		$mainframe->redirect( 'index.php?option=com_community&view=users' , $message ,'message');

		//$message	= JText::sprintf('Cannot assign %1$s to the group %2$s. User is already assigned to the group %2$s.' , $user->getDisplayName() , $group->name );
		//$mainframe->redirect( 'index.php?option=com_community&view=users' , $message , 'error');
	}

	public function saveGroup()
	{
		$group	= JTable::getInstance( 'Groups' , 'CommunityTable' );
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$id			= $jinput->post->get('id' , '', 'INT');

		if( empty($id) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		$postData	= JRequest::get( 'post' );
		$group->load( $id );

		$groupParam = new CParameter($group->params);
		$group->bind( $postData );

		foreach($postData as $key=>$data){

			if(!is_null($groupParam->get($key,NULL))) {
				$groupParam->set($key,$data);
			}
		}

		$group->params = $groupParam->toString();

		$message	= '';
		if( $group->store() )
		{
			$message	= JText::_('COM_COMMUNITY_GROUP_SAVED');
		}
		else
		{
			$message	= JText::_('COM_COMMUNITY_GROUP_SAVE_ERROR');
		}

		$mainframe	= JFactory::getApplication();

		$mainframe->redirect( 'index.php?option=com_community&view=groups' , $message ,'message' );
	}

	public function deleteGroup()
	{
		require_once(JPATH_ROOT . '/components/com_community/libraries/featured.php');
    	require_once(JPATH_ROOT . '/components/com_community/defines.community.php');

		$featured	= new CFeatured(FEATURED_GROUPS);

		$groupWithError = array();

		$group	= JTable::getInstance( 'Group' , 'CTable' );

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$id			= $jinput->post->get('cid' , '', 'NONE'); //JRequest::getVar( 'cid' , '' , 'post' );

		if( empty($id) )
		{
			JError::raiseError( '500' , JText::_('COM_COMMUNITY_INVALID_ID') );
		}

		foreach($id as $data)
		{
			require_once( JPATH_ROOT . '/components/com_community/models/groups.php' );

			//delete group bulletins
			CommunityModelGroups::deleteGroupBulletins($data);

			//delete group members
			CommunityModelGroups::deleteGroupMembers($data);

			//delete group wall
			CommunityModelGroups::deleteGroupWall($data);

			//delete group discussions
			CommunityModelGroups::deleteGroupDiscussions($data);

			//delete group media files
			CommunityModelGroups::deleteGroupMedia($data);

			//load group data before delete
			$group->load( $data );
			$groupData = $group;

			//delete group avatar.
			jimport( 'joomla.filesystem.file' );
			if( !empty( $groupData->avatar) )
			{
				//images/avatar/groups/d203ccc8be817ad5b6a8335c.png
				$path = explode('/', $groupData->avatar);
				$file = JPATH_ROOT .'/'. $path[0] .'/'. $path[1] .'/'. $path[2] .'/'. $path[3];
				if(file_exists($file))
				{
					JFile::delete($file);
				}
			}

			if( !empty( $groupData->thumb ) )
			{
				//images/avatar/groups/thumb_d203ccc8be817ad5b6a8335c.png
				$path = explode('/', $groupData->thumb);
				$file = JPATH_ROOT .'/'. $path[0] .'/'. $path[1] .'/'. $path[2] .'/'. $path[3];
				if(file_exists($file))
				{
					JFile::delete($file);
				}
			}

			if( !$group->delete( $data ) )
			{
				array_push($groupWithError, $data.':'.$groupData->name);
			}

    		$featured->delete( $data );
		}

		$message	= '';
		if( empty($error) )
		{
			$message	= JText::_('COM_COMMUNITY_GROUP_DELETED');
		}
		else
		{
			$error = implode(',', $groupWithError);
			$message	= JText::sprintf('COM_COMMUNITY_GROUPS_DELETE_GROUP_ERROR' , $error);
		}

		$mainframe	= JFactory::getApplication();

		$mainframe->redirect( 'index.php?option=com_community&view=groups' , $message ,'message');
	}

	/**
	 *  Responsible to save an existing or a new group.
	 */
	public function save()
	{
		JRequest::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		if( JString::strtoupper($jinput->getMethod()) != 'POST')
		{
			$mainframe->redirect( 'index.php?option=com_community&view=groups' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
		}

		// Load frontend language file.
		$lang	= JFactory::getLanguage();
		$lang->load( 'com_community' , JPATH_ROOT );

		$group			= JTable::getInstance( 'Group' , 'CTable' );
		$id				= JRequest::getInt( 'groupid' );
		$group->load( $id );

		$tmpPublished	= $group->published;
		$name			= $jinput->post->get('name' , '', 'STRING') ;
		$published		= $jinput->post->get('published' , '', 'NONE') ;
		$description	= $_POST['description'];
		$categoryId		= $jinput->post->get('categoryid' , '', 'INT') ;
		$creator		= JRequest::getInt( 'creator' , 0 , 'POST' );
		$website		= $jinput->post->get('website' , '', 'STRING') ;
		$validated		= true;
		$model 			= $this->getModel( 'Groups','CommunityAdminModel' );
        $isNew			= $group->id < 1;
		$ownerChanged	= $group->ownerid != $creator && $group->id >= 1 ;

		// @rule: Test for emptyness
		if( empty( $name ) )
		{
			$validated	= false;
			$mainframe->enqueueMessage( JText::_('COM_COMMUNITY_GROUPS_EMPTY_NAME_ERROR'), 'error');
		}

		// @rule: Test if group exists
		if( $model->groupExist( $name , $group->id ) )
		{
			$validated	= false;
			$mainframe->enqueueMessage( JText::_('COM_COMMUNITY_GROUPS_NAME_TAKEN_ERROR'), 'error');
		}

		// @rule: Test for emptyness
		if( empty( $description ) )
		{
			$validated	= false;
			$mainframe->enqueueMessage( JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION_EMPTY_ERROR'), 'error');
		}

		if( empty( $categoryId ) )
		{
			$validated	= false;
			$mainframe->enqueueMessage(JText::_('COM_COMMUNITY_GROUPS_CATEGORY_ERROR'), 'error');
		}

		if($validated)
		{
			// Get the configuration object.
			$config	= CFactory::getConfig();

			$group->bindRequestParams();

            // Bind the post with the table first
			$group->name		= $name;
            $group->published		= $published;
			$group->description	= $description;
			$group->categoryid	= $categoryId;
			$group->website		= $website;
			$group->approvals	= JRequest::getInt('approvals' , '0' , 'POST');
			$oldOwner			= $group->ownerid;
			$group->ownerid		= $creator;
			if( $isNew )
			{
				$group->created		= gmdate('Y-m-d H:i:s');
			}

			$group->store();

			if( $isNew )
			{
				// Since this is storing groups, we also need to store the creator / admin
				// into the groups members table
				$member				= JTable::getInstance( 'GroupMembers' , 'CTable' );
				$member->groupid	= $group->id;
				$member->memberid	= $group->ownerid;

				// Creator should always be 1 as approved as they are the creator.
				$member->approved	= 1;

				// @todo: Setup required permissions in the future
				$member->permissions	= '1';
				$member->store();
			}

			if( !$isNew && $ownerChanged )
			{
				$group->updateOwner( $oldOwner , $creator );
			}

            // send notification if necessary
            if($tmpPublished==0 && $group->published == 1 && $config->get( 'moderategroupcreation' )){
                $this->notificationApproval($group);
            }

			$message	= $isNew ? JText::_( 'COM_COMMUNITY_GROUPS_CREATED' ) : JText::_( 'COM_COMMUNITY_GROUPS_UPDATED' );
			$mainframe->redirect( 'index.php?option=com_community&view=groups' , $message, 'message' );
		}

		$document	= JFactory::getDocument();

		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );

		$view->setLayout( 'edit' );

        $model		= $this->getModel( $viewName ,'CommunityAdminModel' );

        if( $model )
        {
            $view->setModel( $model , $viewName );
        }

		$view->display();
	}

    public function notificationApproval($group)
    {
        $lang = JFactory::getLanguage();
        $lang->load( 'com_community', JPATH_ROOT );

        $my			= CFactory::getUser();

        // Add notification
        //Send notification email to owner
        $params	= new CParameter( '' );
        $params->set('url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );
        $params->set('groupName' , $group->name );
        $params->set('group' , $group->name );
        $params->set('group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );

        CNotificationLibrary::add( 'groups_notify_creator' , $my->id , $group->ownerid , JText::_( 'COM_COMMUNITY_GROUPS_PUBLISHED_MAIL_SUBJECT') , '' , 'groups.notifycreator' , $params );

    }
}