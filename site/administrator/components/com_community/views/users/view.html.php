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

jimport( 'joomla.application.component.view' );

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
require_once( JPATH_ROOT . '/components/com_community/libraries/apps.php' );
require_once( JPATH_ROOT . '/components/com_community/libraries/profile.php' );

/**
 * Configuration view for JomSocial
 */
class CommunityViewUsers extends JViewLegacy
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 *
	 * @param	string template	Template file name
	 **/
	public function display( $tpl = null )
	{
		if( $this->getLayout() == 'edit' )
		{
			$this->_displayEditLayout( $tpl );
			return;
		}

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_USERS'), 'users' );

		// Add the necessary buttons
		JToolBarHelper::custom( 'export' , 'csv' , 'csv' , JText::_( 'COM_COMMUNITY_USERS_EXPORT_TO_CSV' ), false );
		JToolBarHelper::trash('delete', JText::_('COM_COMMUNITY_DELETE'));
		JToolBarHelper::custom('approveselected','','', JText::_('COM_COMMUNITY_APPROVE_SELECTED'));

		$search				= $jinput->get('search' , '', 'STRING');
		$model				= $this->getModel( 'Users' );
		$users				= $model->getAllUsers();
		$pagination			= $model->getPagination();

		$filter_order		= $mainframe->getUserStateFromRequest( "com_community.users.filter_order",		'filter_order',		'a.name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_community.users.filter_order_Dir",	'filter_order_Dir',	'',	'word' );

		foreach($users as $key=>$data)
		{
			$users[$key] = CFactory::getUser($data->id);
		}
		//var_dump($users);exit;
		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$usertype			= $jinput->get('usertype' , 'all', 'NONE');
		$multiprofileModel	= $this->getModel( 'Multiprofile' );
		$profileTypes		= $multiprofileModel->getMultiprofiles();
		$profileType		= $jinput->post->get('profiletype', 'all', 'NONE');

		$this->assignRef( 'profileType' , $profileType	);
		$this->assignRef( 'profileTypes', $profileTypes );
		$this->assignRef( 'search'		, $search );
		$this->assignRef( 'usertype'	, $usertype );
		$this->assignRef( 'users' 		, $users );
		$this->assignRef( 'lists' 		, $lists );
		$this->assignRef( 'pagination'	, $pagination );

		parent::display( $tpl );
	}

	public function element( $tpl = null )
	{
	    if( $this->getLayout() == 'edit' )
		{
			$this->_displayEditLayout( $tpl );
			return;
		}

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		// Set the titlebar text
		JToolBarHelper::title( JText::_('COM_COMMUNITY_USERS'), 'users' );

		// Add the necessary buttons
		JToolBarHelper::back( JText::_('COM_COMMUNITY_HOME') , 'index.php?option=com_community');
		JToolBarHelper::divider();
		JToolBarHelper::custom( 'export' , 'csv' , 'csv' , JText::_( 'COM_COMMUNITY_USERS_EXPORT_TO_CSV' ) );
		JToolBarHelper::trash('delete', JText::_('COM_COMMUNITY_DELETE'));

		$search		= $jinput->get('search', '', 'STRING');
		$model		=& $this->getModel( 'Users' );

		$users		=& $model->getAllUsers();
		$pagination	=& $model->getPagination();

		$filter_order		= $mainframe->getUserStateFromRequest( "com_community.users.filter_order",		'filter_order',		'a.name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_community.users.filter_order_Dir",	'filter_order_Dir',	'',			'word' );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$usertype			= $jinput->post->get('usertype' , 'all', 'NONE');

		$multiprofileModel	= & $this->getModel( 'Multiprofile' );
		$profileTypes		= $multiprofileModel->getMultiprofiles();
		$profileType		= $jinput->post->get('profiletype' , 'all', 'NONE');

		$this->assignRef( 'profileType' , $profileType	);
		$this->assignRef( 'profileTypes', $profileTypes );
		$this->assignRef( 'search'		, $search );
		$this->assignRef( 'usertype'	, $usertype );
		$this->assignRef( 'users' 		, $users );
		$this->assignRef( 'lists' 		, $lists );
		$this->assignRef( 'pagination'	, $pagination );

		parent::display( $tpl );
	}

	public function _displayEditLayout( $tpl )
	{
		// Load frontend language file.
		$lang	= JFactory::getLanguage();
		$lang->load('com_community' , JPATH_ROOT );

        $lang->load( 'com_community.country',JPATH_ROOT);
		//Load com user language file for J!1.6
		$lang->load('com_users' , JPATH_ROOT);

		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$userId		= $jinput->request->get('id' , '', 'INT');
		$user		= CFactory::getUser( $userId );

		// Set the titlebar text
		JToolBarHelper::title( $user->username , 'users' );

 		// Add the necessary buttons
 		JToolBarHelper::cancel('removeavatar',JText::_('COM_COMMUNITY_USERS_REMOVE_AVATAR') );
		JToolBarHelper::save();
		JToolBarHelper::save( 'saveonly', JText::_('COM_COMMUNITY_SAVE') );
        JToolBarHelper::cancel();


		$model      = CFactory::getModel( 'Profile' );
		$ptype 		= $model->getProfileTypes();
		$profile	= $model->getEditableProfile( $user->id , $user->getProfileType() );

		$config		= CFactory::getConfig();

		$params		= $user->getParams();
		$userDST	= $params->get('daylightsavingoffset' );
		$offset		= (!empty($userDST) ) ? $userDST : $config->get( 'daylightsavingoffset' );

		$counter	= -4;
		$options	= array();
		for( $i=0 ; $i <= 8; $i++ , $counter++ )
		{
			$options[]	= JHTML::_( 'select.option' , $counter , $counter );
		}
		$offsetList	= JHTML::_(	'select.genericlist',  $options , 'daylightsavingoffset', 'class="inputbox" size="1"', 'value', 'text', $offset );

		$session = JFactory::getSession();
		$sessionData = $session->get('postData');
		$session->clear('postData');

		if(isset($sessionData))
		{
			foreach($profile['fields'] as $key=>$field)
			{
				foreach($field as $_key=>$_field)
				{
					if(isset($sessionData['field'.$_field['id']]))
					{
						if(is_array($sessionData['field'.$_field['id']]))
						{
							if($_field['type']=='birthdate')
							{
								$sessionData['field'.$_field['id']] = implode('-',$sessionData['field'.$_field['id']]);
							}
							if($_field['type']=='url')
							{
								$sessionData['field'.$_field['id']] = implode('',$sessionData['field'.$_field['id']]);
							}
							if( $_field['type'] == 'checkbox')
							{
								$sessionData['field'.$_field['id']] = implode(',',$sessionData['field'.$_field['id']]);
							}
						}

						$profile['fields'][$key][$_key]['value'] = $sessionData['field'.$_field['id']];
					}
				}

			}
		}

		$user->profile	= $profile;
 		$this->assignRef( 'user' , $user );

		$params = $user->getParameters(true);
		$params	= CJForm::getInstance('editDetails', JPATH_ADMINISTRATOR.'/components/com_users/models/forms/user.xml');
		$vals	= $user->getParameters();
		$vals	= $vals->toArray();

		//set data for the form
		foreach($vals as $k => $v){
			$params->setValue($k , 'params' , $v);
		}


		$options	= array();

		foreach($ptype as $pr){
			$options[]	= JHTML::_( 'select.option' , $pr->id , $pr->name );
		}

		if($options){
			$profilelist = JHTML::_('select.genericlist',  $options , 'profiletype', 'class="inputbox" size="1"', 'value', 'text', $user->getProfileType() );
		}else{
			$profilelist = '';
		}

 		$this->assignRef( 'params' , $params );
		$this->assignRef( 'profilelist' , $profilelist );
 		$this->assignRef( 'offsetList'	, $offsetList );

 		parent::display( $tpl );
	}

	/**
	 * Method to get the publish status HTML
	 *
	 * @param	object	Field object
	 * @param	string	Type of the field
	 * @param	string	The ajax task that it should call
	 * @return	string	HTML source
	 **/
	public function getPublish( &$row , $type , $ajaxTask )
	{
		$version = new Jversion();
		$currentV = $version->getHelpVersion();

		$class = 'jgrid';

		$alt	= $row->$type ? JText::_('COM_COMMUNITY_PUBLISHED') : JText::_('COM_COMMUNITY_UNPUBLISH');
		$state = $row->$type == 0 ? 'publish' : 'unpublish';
		$span = '<span class="state '.$state.'"><span class="text">'.$alt.'</span></span></a>';

		if($currentV >= '0.30')
		{
			$class = $row->$type == 0 ? 'disabled jgrid': '';

			$span = '<i class="icon-'.$state.'""></i>';
		}

		$href = '<a class="'.$class.'" href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';

		$href .= $span;

		return $href;
	}

	public function getConnectType( $userId )
	{
		$model	= $this->getModel( 'Users' );
		$type	= $model->getUserConnectType( $userId );
		$image	= '';

		switch( $type )
		{
			case 'facebook':
				$image	= '<img src="' . rtrim( JURI::root() , '/' ) . '/administrator/components/com_community/assets/icons/facebook.gif" />';
				break;
			case 'joomla':
			default:
				$image	= '<img src="' . rtrim( JURI::root() , '/' ) . '/administrator/components/com_community/assets/icons/joomla-icon.png" />';
				break;
		}
		return $image;
	}

	public function getProfileName($obj)
	{
		$profileId = $obj->getProfileType();

		$profile = JTable::getInstance('MultiProfile', 'CTable');
        $profile->load($profileId);

        return $profile->getName();
	}

	/**
	 * Private method to set the toolbar for this view
	 *
	 * @access private
	 *
	 * @return null
	 **/
	public function setToolBar()
	{

	}

	public function _getStatusHTML()
	{
		// Check if there are any categories selected
		$status	= JRequest::getInt( 'status' , 2 );

		$select	= '<select class="no-margin" name="status" onchange="submitform();">';

		$statusArray = array(2=>JText::_('COM_COMMUNITY_ALL_USER'),0=>JText::_('COM_COMMUNITY_ACTIVITIES_ACTIVE'),1=>JText::_('COM_COMMUNITY_PENDING'));

		foreach($statusArray as $key=>$array)
		{
			$selected = ($status == $key) ? 'selected="true"' : '';
			$select .='<option value="'.$key.'"'.$selected.' >'.JText::_($array).'</option>';
		}

		$select	.= '</select>';

		return $select;
	}
}