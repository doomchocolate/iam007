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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerUsers extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
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

		$model		= $this->getModel( $viewName );

		if( $model )
		{
			$view->setModel( $model , $viewName );

			$multiprofiles	= $this->getModel( 'MultiProfile' );
			$view->setModel( $multiprofiles  , false );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->display();

		// Display Toolbar. View must have setToolBar method
		if( method_exists( $view , 'setToolBar') )
		{
			$view->setToolBar();
		}
	}

	/**
	 * Element display- Pop-up user window
	 *
	 */

	public function element(){

		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'select' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		= $this->getView( $viewName , $viewType );

		$model		= $this->getModel( $viewName );

		if( $model )
		{
			$view->setModel( $model , $viewName );

			$multiprofiles	= $this->getModel( 'MultiProfile' );
			$view->setModel( $multiprofiles  , false );
		}

		// Set the layout
		$view->setLayout( $layout );

		// Display the view
		$view->element();
	}
	/**
	 * Export users list into respective formats
	 **/
	public function export()
	{
	    $mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$model = $this->getModel( 'Users' );

		$format = $jinput->get('format', 'csv', 'STRING'); //JRequest::getVar( 'format', 'csv' , 'GET' );
		$ids = $model->getAllUserId();

		/**
		 * TODO: Currently it only supports CSV export. In the future we may want to support other types as well
		 **/
		switch( $format )
		{
		    case 'csv':
		    default:
		        $this->_exportCSV( $ids );
		        break;
		}
	}

	public function _exportCSV( $ids )
	{
		header('Content-Description: File Transfer');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-disposition: attachment; filename="users.csv"');

		$model      = CFactory::getModel( 'Profile' );
		$lang       = JFactory::getLanguage();
		$lang->load( 'com_community' , JPATH_ROOT );
		//CFactory::load( 'helpers' , 'string' );

		foreach( $ids as $id )
		{
			if($id->id == ''){
				continue;
			}
			$user       = CFactory::getUser( $id->id );
		    $profile	= $model->getEditableProfile( $id->id , $user->getProfileType() );
			$profileType    = JTable::getInstance( 'MultiProfile' , 'CTable' );
			$profileType->load( $user->getProfileType() );

			echo $user->id . ',' . $profileType->getName() . ',' . $user->name . ',' . $user->username . ',' . $user->email . ',' . $user->getThumbAvatar() . ',' . $user->getAvatar() . ',' . $user->getKarmaPoint() . ',';
			echo $user->registerDate . ',' . $user->lastvisitDate . ',' . $user->block . ',"' . $user->getStatus() . '",' . $user->getViewCount() . ',' . $user->getAlias() . ',' . $user->getFriendCount();

			foreach( $profile['fields'] as $group => $groupFields )
			{
				foreach( $groupFields as $field )
				{
					$field	= JArrayHelper::toObject ( $field );
					$field->value	= CStringHelper::nl2br( $field->value );
					$field->value	= CStringHelper::escape( $field->value );

					echo '"'.$field->value . '",';
				}
			}
			echo "\r\n";
		}
		exit;
	}

	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$db 			= JFactory::getDBO();
		$currentUser 	= JFactory::getUser();
		$acl			= JFactory::getACL();
		$cid 			= $jinput->request->get( 'cid', array(), 'array'); //JRequest::getVar( 'cid', array(), '', 'array' );
		$cacl			= CACL::getInstance();
		JArrayHelper::toInteger( $cid );

		if (count( $cid ) < 1)
		{
			$msg	= JText::_('COM_COMMUNITY_USERS_DELETE');
		}

		foreach ($cid as $id)
		{
			// check for a super admin ... can't delete them
			//$objectID 	= $acl->get_object_id( 'users', $id, 'ARO' );
			//$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
			//$this_group = strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );
			$this_group = $cacl->getGroupsByUserId($id);
			$success = false;
			if ( $this_group == 'super administrator' )
			{
				$msg = JText::_('COM_COMMUNITY_USERS_SUPER_ADMINISTRATOR_DELETE');
			}
			else if ( $id == $currentUser->get( 'id' ) )
			{
				$msg = JText::_('COM_COMMUNITY_USERS_CANNOT_DELETE_YOURSELF');
			}
			else if ( ( $this_group == 'administrator' ) && ( $currentUser->get( 'gid' ) == 24 ) )
			{
				$msg = JText::_('COM_COMMUNITY_USERS_WARNDELETE');
			}
			else
			{
				$user = JUser::getInstance((int)$id);
				$count = 2;

				if ( $user->get( 'gid' ) == 25 )
				{
					// count number of active super admins
					$query = 'SELECT COUNT( ' . $db->quoteName('id') . ' )'
						. ' FROM ' . $db->quoteName('#__users')
						. ' WHERE ' . $db->quoteName('gid') . ' = ' . $db->Quote(25)
						. ' AND ' . $db->quoteName('block') . ' = ' . $db->Quote(0)
					;
					$db->setQuery( $query );
					$count = $db->loadResult();
				}

				if ( $count <= 1 && $user->get( 'gid' ) == 25 )
				{
					// cannot delete Super Admin where it is the only one that exists
					$msg = JText::_('COM_COMMUNITY_USERS_DELETE_ACTIVE_ADMIN');
				}
				else
				{
					// delete user
					$user->delete();
					$msg = JText::_('COM_COMMUNITY_USERS_DELETED');

					JRequest::setVar( 'task', 'remove' );
					JRequest::setVar( 'cid', $id );

					// delete user acounts active sessions
					$this->logout();
				}
			}
		}

		$this->setRedirect( 'index.php?option=com_community&view=users', $msg);
	}

	/**
	 * Force log out a user
	 */
	public function logout( )
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$mainframe 	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$db		= JFactory::getDBO();
		$task 	= $this->getTask();
		$cids 	= $jinput->request->get('cid', array(), 'array'); //JRequest::getVar( 'cid', array(), '', 'array' );
		$client = $jinput->request->get('client', 0, 'int'); //JRequest::getVar( 'client', 0, '', 'int' );
		$id 	= $jinput->request->get('id', 0, 'int'); //JRequest::getVar( 'id', 0, '', 'int' );

		JArrayHelper::toInteger($cids);

		if ( count( $cids ) < 1 )
		{
			$this->setRedirect( 'index.php?option=com_users', JText::_('COM_COMMUNITY_USERS_DELETED') );
			return false;
		}

		foreach($cids as $cid)
		{
			$options = array();

			if ($task == 'logout' || $task == 'block') {
				$options['clientid'][] = 0; //site
				$options['clientid'][] = 1; //administrator
			} else if ($task == 'flogout') {
				$options['clientid'][] = $client;
			}

			$mainframe->logout((int)$cid, $options);
		}


		$msg = JText::_('COM_COMMUNITY_USERS_SESSION_ENDED');
		switch ( $task )
		{
			case 'flogout':
				$this->setRedirect( 'index.php', $msg );
				break;

			case 'remove':
			case 'block':
				return;
				break;

			default:
				$this->setRedirect( 'index.php?option=com_users', $msg );
				break;
		}
	}

	/**
	 * Save controller that receives arguments via HTTP POST.
	 **/
	public function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$lang	= JFactory::getLanguage();
		$lang->load('com_users');

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$userId		= $jinput->post->get('userid' , '', 'INT'); //JRequest::getVar( 'userid' , '' , 'POST' );
		$message	= '';
		$url		= JRoute::_('index.php?option=com_community&view=users' , false );
		$my			= JFactory::getUser();
		$acl		= JFactory::getACL();
		$cacl		= CACL::getInstance();
		$mailFrom	= $mainframe->getCfg('mailfrom');
		$fromName	= $mainframe->getCfg('fromname');
		$siteName	= $mainframe->getCfg('sitename');

		if( empty( $userId ) )
		{
			$message	= JText::_('COM_COMMUNITY_USERS_EMPTY_USER_ID');
			$mainframe->redirect( $url , $message ,'error');
		}

 		// Create a new JUser object
		$user			= new JUser( $userId );
		$original_gid	= $user->get('gid');

		$post				= JRequest::get('post');
		$post['username']	= $jinput->post->get('username', '', 'username'); //JRequest::getVar('username', '', 'post', 'username');
		$post['password']	= $jinput->post->get('password', '', 'string'); //JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$post['password2']	= $jinput->post->get('password2', '', 'string'); //JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$notifyEmailSystem	= $jinput->post->get('sendEmail', '', 'STRING'); //JRequest::getVar('sendEmail', '', 'post', 'sendEmail');
		$redirect			= $jinput->post->get('redirect', false, 'STRING');
		if (!$user->bind($post))
		{
			$message	= JText::_('COM_COMMUNITY_USERS_SAVE_USER_INFORMATION_ERROR') . ' : ' . $user->getError();
			$url		= JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $userId , false );
			$mainframe->redirect( $url , $message ,'error');
			exit;
		}

		//$objectID 	= $acl->get_object_id( 'users', $user->get('id'), 'ARO' );
		//$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
		//$this_group = JString::strtolower( $acl->get_group_name( $groups[0], 'ARO' ) );
		$this_group = $cacl->getGroupsByUserId($user->get('id'));
		if( $user->get('id') == $my->get( 'id' ) && $user->get('block') == 1 )
		{
			$message	= JText::_('COM_COMMUNITY_USERS_BLOCK_YOURSELF');
			$url		= JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $userId , false );
			$mainframe->redirect( $url , $message ,'error');
			exit;
		}

		if(( $this_group == 'super administrator' ) && $user->get('block') == 1 )
		{
			$message	= JText::_('COM_COMMUNITY_USERS_BLOCK_SUPER_ADMINISTRATOR');
			$url		= JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $userId , false );
			$mainframe->redirect( $url , $message ,'error');
			exit;
		}

		if(( $this_group == 'administrator' ) && ( $my->get( 'gid' ) == 24 ) && $user->get('block') == 1 )
		{
			$message	= JText::_('COM_COMMUNITY_USERS_WARNBLOCK');
			$url		= JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $userId , false );
			$mainframe->redirect( $url , $message ,'error');
			exit;
		}

		if(( $this_group == 'super administrator' ) && ( $my->get( 'gid' ) != 25 ) )
		{
			$message	= JText::_('COM_COMMUNITY_USERS_SUPER_ADMINISTRATOR_EDIT');
			$url		= JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $userId , false );
			$mainframe->redirect( $url , $message ,'error');
			exit;
		}

		$isNew	= $user->get('id') == 0;

		if (!$isNew)
		{
			if ( $user->get('gid') != $original_gid && $original_gid == 25 )
			{
				$query = 'SELECT COUNT( ' . $db->quoteName('id') . ' )'
					. ' FROM ' . $db->quoteName('#__users')
					. ' WHERE ' . $db->quoteName('gid') . ' = ' . $db->Quote(25)
					. ' AND ' . $db->quoteName('block') . ' = ' . $db->Quote(0);
				$db->setQuery( $query );
				$count = $db->loadResult();

				if( $count <= 1 )
				{
					$message	= JText::_('COM_COMMUNITY_USERS_WARN_ONLY_SUPER');
					$url		= JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $userId , false );
					$mainframe->redirect( $url , $message ,'message');
					exit;
				}
			}
		}

		//Joomla 1.6 patch to keep the group ID of user intact when saving
		if(property_exists($user, 'groups')){
			foreach($user->groups as $groupid => $groupname){
				$user->groups[$groupid] = $groupid;
			}
		}

		if (!$user->save())
		{
			$message	= JText::_('COM_COMMUNITY_USERS_SAVE_USER_INFORMATION_ERROR') . ' : ' . $user->getError();
			$mainframe->redirect( $url , $message ,'message');
			exit;
		}

		$appsLib	= CAppPlugins::getInstance();
		$appsLib->loadApplications();

		$userRow	= array();
		$userRow[]	= $user;

		$appsLib->triggerEvent( 'onUserDetailsUpdate' , $userRow );

		// @rule: Send out email if it is a new user.
		if($isNew)
		{
			$adminEmail = $my->get('email');
			$adminName	= $my->get('name');

			$subject = sprintf ( JText::_('COM_COMMUNITY_USERS_NEW_USER_MESSAGE_SUBJECT') , $siteName);
			$message = sprintf ( JText::_('COM_COMMUNITY_USERS_NEW_USER_MESSAGE'), $user->get('name'), $siteName, JURI::root(), $user->get('username'), $user->password_clear );

			if ( !empty( $mailfrom ) && !empty( $fromName ) )
			{
				$adminName 	= $fromName;
				$adminEmail = $mailFrom;
			}

			$mail = JFactory::getMailer();

			$mail->sendMail( $adminEmail, $adminName, $user->get('email'), $subject, $message );
		}

		// If updating self, load the new user object into the session
		if ($user->get('id') == $my->get('id'))
		{
			jimport('joomla.version');
			$version = new JVersion();
			$joomla_ver = $version->getHelpVersion();

			// Get the user group from the ACL
			if ($joomla_ver<= '0.15') {
				$grp	    =	$acl->getAroGroup($user->get('id'));

				// Mark the user as logged in
				$user->set('guest', 0);
				$user->set('aid', 1);

				// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
				if ($acl->is_group_child_of($grp->name, 'Registered')	||
				    $acl->is_group_child_of($grp->name, 'Public Backend')){
					$user->set('aid', 2);
				}

				// Set the usertype based on the ACL group name
				$user->set('usertype', $grp->name);
			}elseif ($joomla_ver >= '0.16'){
				$grp_name   =	$cacl->getGroupUser($user->get('id'));

				// Mark the user as logged in
				$user->set('guest', 0);
				$user->set('aid', 1);

				// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
				if ($cacl->is_group_child_of($grp_name, 'Registered')	||
				    $cacl->is_group_child_of($grp_name, 'Public Backend')){
					$user->set('aid', 2);
				}

				// Set the usertype based on the ACL group name
				$user->set('usertype', $grp_name);
			}

			$session = JFactory::getSession();
			$session->set('user', $user);
		}

		// Process and save custom fields
		$user		= CFactory::getUser( $userId );
		$model		= $this->getModel( 'users' );
		$userModel	= CFactory::getModel( 'profile' );
		$values		= array();
		$profile	= $userModel->getEditableProfile( $userId , $user->getProfileType() );

		//CFactory::load( 'libraries' , 'profile' );

		foreach( $profile['fields'] as $group => $fields )
		{
			foreach( $fields as $data )
			{
				// Get value from posted data and map it to the field.
				// Here we need to prepend the 'field' before the id because in the form, the 'field' is prepended to the id.
				$postData				= $jinput->post->get('field' . $data['id'] , '', 'NONE') ;	//JRequest::getVar( 'field' . $data['id'] , '' , 'POST' );
				$values[ $data['id'] ]	= CProfileLibrary::formatData( $data['type']  , $postData );

				if(get_magic_quotes_gpc())
				{
					$values[ $data['id'] ] = stripslashes($values[ $data['id'] ]);
				}

				// @rule: Validate custom profile if necessary
				if( !CProfileLibrary::validateField( $data['id'], $data['type'] , $values[ $data['id'] ] , $data['required'] ) )
				{
					$session = JFactory::getSession();
					$session->set('postData',$post);
					// If there are errors on the form, display to the user.
					$message	= JText::sprintf('COM_COMMUNITY_THE_FIELD_CONTAIN_IMPROPER_VALUES',$data['name'] );
					$mainframe->redirect( 'index.php?option=com_community&view=users&layout=edit&id=' . $user->id , $message , 'error' );
					return;
				}
			}
		}

		// Update user's parameter DST
		$params		= $user->getParams();
		$offset		= $post['daylightsavingoffset'];
		$params->set('daylightsavingoffset', $offset );
		$params->set('notifyEmailSystem', $notifyEmailSystem );
		// Update user's point
		$points			= $jinput->request->get('userpoint' , '' , 'NONE'); 	//JRequest::getVar( 'userpoint' , '' , 'REQUEST' );
		if( $points != '' )
		{
			$user->_points	= $points;
			$user->save();
		}

		//update user's profile
		$profile_id	= $jinput->request->get('profiletype' , '' , 'INT'); //JRequest::getVar( 'profiletype' , '' , 'REQUEST' );
		if( $profile_id > 0 )
		{
			$user->_profile_id	= $profile_id;
			$user->save();
		}

		// Update user's status
		if( $user->getStatus() != $post['status'] )
		{
			$user->setStatus( $post['status'] );
		}

		$user->save('params');

		$valuesCode = array();
		foreach( $values as $key => &$val )
		{
			$fieldCode = $userModel->getFieldCode($key);
			if( $fieldCode )
			{
				$valuesCode[$fieldCode] = &$val;
			}
		}

		// Trigger before onBeforeUserProfileUpdate
		$args 	= array();
		$args[]	= $userId;
		$args[]	= $valuesCode;
		$saveSuccess = false;
		$result = $appsLib->triggerEvent( 'onBeforeProfileUpdate' , $args );

		if(!$result || ( !in_array(false, $result) ) )
		{
			$saveSuccess = true;
			$userModel->saveProfile($userId, $values);
		}

		// Trigger before onAfterUserProfileUpdate
		$args 	= array();
		$args[]	= $userId;
		$args[]	= $saveSuccess;
		$result = $appsLib->triggerEvent( 'onAfterProfileUpdate' , $args );

		if(!$saveSuccess)
		{
			$message	= JText::_('COM_COMMUNITY_USERS_PROFILE_NOT_UPDATED');
			$mainframe->redirect( $url , $message , 'error');
		}

		if($redirect == false)
		{

			$message	= JText::_('COM_COMMUNITY_USERS_UPDATED_SUCCESSFULLY');
			$mainframe->redirect( 'index.php?option=com_community&view=users&layout=edit&id=' . $user->id , $message ,'message');
		}

		$message	= JText::_('COM_COMMUNITY_USERS_UPDATED_SUCCESSFULLY');
		$mainframe->redirect( $url , $message ,'message');
	}

	// Override parent's toggle publish method
	public function ajaxTogglePublish( $id, $field, $viewName= false )
	{
		$user	= JFactory::getUser();

		// @rule: Disallow guests.
		if ( $user->get('guest'))
		{
			JError::raiseError( 403, JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN') );
			return;
		}

		$response	= new JAXResponse();

		// Load the JTable Object.
		$row	= JTable::getInstance( 'User' , 'JTable' );
		$row->load( $id );

		if( isset($row->groups[8]) )
		{
			$response->addScriptCall( 'alert' , JText::_('COM_COMMUNITY_USERS_BLOCK_SUPER_ADMINISTRATORS') );
		}
		else
		{
			if( $row->$field == 1 )
			{
				$row->$field	= 0;
				$row->store();

				$image			= 'tick.png';

				// @rule: If the new user is just activated, send an email to the user.
				if( $row->lastvisitDate == '0000-00-00 00:00:00' && empty($row->activation) )
				{
					$lang	= JFactory::getLanguage();
					$lang->load( 'com_community' , JPATH_ROOT );

					$mainframe	= JFactory::getApplication();
					$config		= CFactory::getConfig();

					$sitename 	= $mainframe->getCfg( 'sitename' );
					$mailfrom 	= $mainframe->getCfg( 'mailfrom' );
					$fromname 	= $mainframe->getCfg( 'fromname' );
					$siteURL	= JURI::root();

					$name 			= $row->get('name');
					$email 			= $row->get('email');
					$username 		= $row->get('username');

					$subject 	= JText::sprintf( 'COM_COMMUNITY_ACCOUNT_APPROVED_SUBJECT' , $name, $sitename);
					$subject 	= html_entity_decode($subject, ENT_QUOTES);

					$message	= sprintf ( JText::_( 'COM_COMMUNITY_ACCOUNT_APPROVED_MESSAGE' ), $siteURL , $row->name , $row->email , $row->username );
					$message	= html_entity_decode($message, ENT_QUOTES);

					// Send email to user
					$mail = JFactory::getMailer();
					$mail->sendMail($mailfrom, $fromname, $email, $subject, $message);
				}

			}
			else
			{
				$row->$field	= 1;
				$row->store();
				$image			= 'publish_x.png';
			}
			// Get the view
			$view		= $this->getView( 'users' , 'html' );

			$html	= $view->getPublish( $row , $field , 'users,ajaxTogglePublish' );

		   	$response->addAssign( $field . $id , 'innerHTML' , $html );
	   	}
	   	return $response->sendResponse();
	}



	public function ajaxRemoveAvatar( $userId )
	{
		require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );
		require_once( JPATH_ROOT .'/components/com_community/libraries/apps.php' );
		$user		= CFactory::getUser( $userId );
		$model		= $this->getModel( 'Users' );

		$model->removeProfilePicture( $user->id , 'avatar' );
		$model->removeProfilePicture( $user->id , 'thumb' );

		$message	= JText::_('COM_COMMUNITY_USERS_PROFILE_PICTURE_REMOVED');
		$response	= new JAXResponse();

		$profileModel = CFactory::getModel ( 'Profile' );
		$gender = $profileModel->getGender($user->id);

		$avatar		= JURI::root() . 'components/com_community/assets/user-'.$gender.'-thumb.png';

		$response->addScriptCall('joms.jQuery("#user-avatar").attr("src","' . $avatar . '");');
		$response->addScriptCall('joms.jQuery("#user-avatar-message").html("' . $message . '");' );
		$response->addScriptCall('joms.jQuery("#user-avatar-message").hide(5000);' );
		return $response->sendResponse();
	}

	public function ajaxToggleStatus($id,$status)
	{
		$response	= new JAXResponse();
		$row	= JTable::getInstance( 'User' , 'JTable' );
		$row->load( $id );
		switch ($status) {
			case '1':
					$row->block	= 0;
					$row->store();

					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").removeClass','label-important');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").removeClass','label-warning');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").addClass','label-success');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").addClass','arrowed-in');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").html',JText::_('approved'));
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' div.inline").remove');

				break;
			case '0':
					$row->block	= 1;
					$row->store();

					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").removeClass','label-important');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").removeClass','label-warning');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").addClass','label-important');
					$response->addScriptCall('joms.jQuery("#member-label-'.$id.' span.label").html',JText::_('blocked'));

				break;
			case '2':
					$user = JUser::getInstance((int)$id);
					$user->delete();
					$response->addScriptCall('joms.jQuery("#member-'.$id.'").remove');
				break;
		}
		return $response->sendResponse();
	}

	public function approveselected()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$ids = $jinput->post->get('cid',array(),'Array');

		foreach($ids as $id)
		{
			$row	= JTable::getInstance( 'User' , 'JTable' );
			$row->load( $id );

			$row->block	= 0;
			$row->store();
		}
		$search			= $jinput->post->get('search','','String');
		$userType		= $jinput->post->get('user','','String');
		$profileType	= $jinput->post->get('profiletype','','String');
		$status			= $jinput->post->get('status','2','String');

		$url = 'index.php?option=com_community&view=users&search='.$search.'&usertype='.$userType.'&profiletype='.$profileType.'&status='.$status;
		$message	= JText::_('COM_COMMUNITY_USERS_UPDATED_SUCCESSFULLY');
		$mainframe->redirect( $url , $message ,'message');
	}
}
