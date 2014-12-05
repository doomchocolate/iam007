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
class CommunityControllerMaintenance extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
		$this->enableAzrulSystem();
	}

	public function display()
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	= JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();

		// Get the view
		$view		=& $this->getView( $viewName , $viewType );

		$model		=& $this->getModel( 'groups' );
		$userModel	=& $this->getModel( 'users' );

		if( $model && $userModel )
		{
			$view->setModel( $model , $viewName );
			$view->setModel( $userModel , $viewName );
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


	public function ajaxCheckHash(){

		$objResponse = new JAXResponse();

		$hashpath = JPATH_ROOT.'/administrator/components/com_community/hash.ini';
		if(!file_exists($hashpath)){
			$objResponse->addScriptCall('joms.jQuery("#no-progress").html("Sorry no files hash.ini file found");');
			return $objResponse->sendResponse();
		}
		$hashlist = parse_ini_file($hashpath);
		$stats = array();
		$objResponse->addScriptCall('joms.jQuery("#no-progress").html("Checking '.count($hashlist).' files");');
		foreach($hashlist as $path => $hash){
			if(!file_exists(JPATH_ROOT.$path)) {
				$stats['missing'][] = $path;
				continue;
			}

			if(md5(file_get_contents(JPATH_ROOT.$path)) != $hash){
				$stats['changed'][] = $path;
			}
		}
		$html = '<p>Check complete: Missing='.count($stats['missing']).' & Changed='.count($stats['changed']).'</p>';
		$html .= "<h2>Changed files</h2>".implode('<br/>',$stats['changed']);
		$html .= "<h2>Missing files</h2>".implode('<br/>',$stats['missing']);
		$objResponse->addScriptCall('joms.jQuery("#progress-status").html("'.$html.'");');
		return $objResponse->sendResponse();
	}


	public function ajaxPatchFriendTable()
	{
		$objResponse	= new JAXResponse();

		$db		= JFactory::getDBO();

		$model	=& $this->getModel( 'Users' );
		$fields = $model->_getFields();

		if(! array_key_exists( 'friendcount' , $fields ) )
		{
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_users' ) . ' '
					. 'ADD '. $db->quoteName('friendcount') . ' INT( 11 ) NOT NULL DEFAULT ' . $db->Quote('0') . ' AFTER ' . $db->quoteName('view');
			$db->setQuery( $query );
			$db->query();
		}

		$objResponse->addScriptCall( 'joms.jQuery("#progress-status").append("<div>Community User Table Updated</div>");' );

		return $objResponse->sendResponse();
	}

	public function ajaxPatchTable()
	{
		$objResponse	= new JAXResponse();

		$model	=& $this->getModel( 'Groups' );
		$fields = $model->_getFields();

		$db		= JFactory::getDBO();

		if((!array_key_exists( 'membercount' , $fields )) || (!array_key_exists( 'wallcount' , $fields )) || (!array_key_exists( 'discusscount' , $fields ))) {
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_groups' ) . ' '
					. 'ADD ' . $db->quoteName('discusscount') . ' INT( 11 ) NOT NULL DEFAULT '. $db->Quote(0) .' AFTER ' . $db->quoteName('thumb') . ' , '
					. 'ADD '. $db->quoteName('wallcount') . ' INT( 11 ) NOT NULL DEFAULT ' . $db->Quote(0) . ' AFTER ' . $db->quoteName('discusscount') . ' , '
					. 'ADD ' . $db->quoteName('membercount') . ' INT( 11 ) NOT NULL DEFAULT ' . $db->Quote(0) . ' AFTER ' . $db->quoteName('wallcount');
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_fields', 'fieldcode')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_fields' ) . ' ADD INDEX ('. $db->quoteName('fieldcode') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_fields_values', 'user_id')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_fields_values' ) . ' ADD INDEX (' . $db->quoteName('user_id') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_fields_values', 'field_id')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_fields_values') . ' ADD INDEX (' . $db->quoteName('field_id') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_apps', 'userid')){
			$query	= 'ALTER TABLE ' . $db->quoteName('#__community_apps') . ' ADD INDEX (' . $db->quoteName('userid') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_connection', 'connect_from')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_connection' ) . ' ADD INDEX (' . $db->quoteName('connect_from') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_connection', 'connect_to')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_connection' ) . ' ADD INDEX (' . $db->quoteName('connect_to') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_connection', 'status')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_connection' ) . ' ADD INDEX (' . $db->quoteName('status'). ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_groups_members', 'approved')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_groups_members' ) . ' ADD INDEX (' . $db->quoteName('approved') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_photos', 'creator')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_photos' ) . ' ADD INDEX (' . $db->quoteName('creator') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_activities', 'created')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_activities' ) . ' ADD INDEX (' . $db->quoteName('created') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		if(! $this->_isExistTableColumnIndex('#__community_activities', 'archived')){
			$query	= 'ALTER TABLE ' . $db->quoteName( '#__community_activities' ) . ' ADD INDEX (' . $db->quoteName('archived') . ')';
			$db->setQuery( $query );
			$db->query();
		}

		$objResponse->addScriptCall( 'joms.jQuery("#progress-status").append("<div>Tables Updated</div>");' );

		return $objResponse->sendResponse();
	}

	/*
	 * Check table column index whether exists or not.
	 * index name == column name.
	 */
	public function _isExistTableColumnIndex($tablename, $columnname){


		$db		= JFactory::getDBO();

		$query	= 'SHOW INDEX FROM ' . $db->quoteName( $tablename );

		$db->setQuery( $query );

		$indexes	= $db->loadObjectList();

		foreach( $indexes as $index )
		{
			$result[ $index->Key_name ]	= $index->Column_name;
		}

		if(array_key_exists($columnname, $result)){
			return true;
		}

		return false;
	}

	public function ajaxPatch()
	{
		$objResponse	= new JAXResponse();

		$model			=& $this->getModel( 'Groups' );
		$groups			= $model->getAllGroups();

		for( $i = 0; $i < count($groups); $i++ )
		{
			$objResponse->addScriptCall( 'jax.call("community","admin,maintenance,ajaxPatchGroup","' . $groups[$i]->id . '");');
		}

		//patch for user friend count.
		$uModel			=& $this->getModel( 'Users' );
		$users			= $uModel->getAllCommunityUsers();

		for( $i = 0; $i < count($users); $i++ )
		{
			$objResponse->addScriptCall( 'jax.call("community","admin,maintenance,ajaxPatchFriend","' . $users[$i]->userid . '");');
		}

		return $objResponse->sendResponse();
	}

	public function ajaxPatchFriend( $userId )
	{
		$objResponse	= new JAXResponse();

		$row		= JTable::getInstance( 'users', 'CommunityTable' );
		$row->load( $userId );

		$row->friendcount	= $row->getFriendCount();

		$row->store();

		$objResponse->addScriptCall( 'joms.jQuery("#progress-status").append("<div>User ID <strong>' . $row->userid . '</strong> Updated</div>");' );

		return $objResponse->sendResponse();
	}

	public function ajaxPatchGroup( $groupId )
	{
		$objResponse	= new JAXResponse();

		$row		= JTable::getInstance( 'groups', 'CommunityTable' );
		$row->load( $groupId );

		$row->discusscount	= $row->getDiscussCount();
		$row->membercount	= $row->getMembersCount();
		$row->wallcount		= $row->getWallCount();

		$row->store();

		$objResponse->addScriptCall( 'joms.jQuery("#progress-status").append("<div>Group <strong>' . $row->name . '</strong> Updated</div>");' );

		return $objResponse->sendResponse();
	}

	public function ajaxPatchPrivacy( $limit = 1 )
	{
		require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

		$limitstart		= $limit - 1;

		$model			=& $this->getModel( 'users' );
		$userId			= $model->getSiteUsers( $limitstart , 1 );

		$objResponse	= new JAXResponse();

		$objResponse->addScriptCall( 'joms.jQuery("#no-progress").css("display","none");');
		$db				= JFactory::getDBO();

		if( !empty( $userId ) )
		{
			$user		= CFactory::getUser( $userId );

			$params		= $user->getParams();

			// Fix old privacy issues.
			if($params->get('privacyPhotoView') == 1 )
			{
				$params->set('privacyPhotoView' , 0);
			}


			$query = 'UPDATE ' . $db->quoteName( '#__community_photos' ) . ' '
					. 'SET ' . $db->quoteName( 'permissions' ) . '=' . $params->get('privacyPhotoView') . ' '
					. 'WHERE ' . $db->quoteName( 'creator' ) . '=' . $db->Quote( $user->id );
			$db->setQuery( $query );
			$db->query();

			$query = 'UPDATE ' . $db->quoteName( '#__community_photos_albums' ) . ' '
					. 'SET ' . $db->quoteName( 'permissions' ) . '=' . $params->get('privacyPhotoView') . ' '
					. 'WHERE ' . $db->quoteName( 'creator' ) . '=' . $db->Quote( $user->id );
			$db->setQuery( $query );
			$user->save('params');


			$status		= '';

			if( $db->query() )
			{
				$status	= '<span style=\"color: green;\">' . JText::_('COM_COMMUNITY_SUCCESS') . '</span>';
			}
			else
			{
				$status	= '<span style=\"color: red;\">' . JText::_('COM_COMMUNITY_NOT_SUCCESS') . '</span>';
			}

			$objResponse->addScriptCall( 'joms.jQuery("#progress-status").append("<div>' . JText::sprintf('Updating user id <strong>%1$s</strong>. %2$s' , $user->id , $status ) . '</div>");');
	 		$objResponse->addScriptCall( 'jax.call("community","admin,maintenance,ajaxPatchPrivacy" , "' . ( $limit + 1 ) . '");');
		}
		else
		{
			// Just to make sure that we remove all references to 'all' once the last ajax query is called.
			$query		= 'UPDATE ' . $db->quoteName( '#__community_photos' ) . ' '
						. 'SET ' . $db->quoteName( 'permissions' ) . '=' . $db->Quote( 0 ) . ' '
						. 'WHERE ' . $db->quoteName('permissions') . '=' . $db->Quote( 'all' );
			$db->setQuery( $query );
			$db->query();

			$query		= 'UPDATE ' . $db->quoteName( '#__community_photos_albums' ) . ' '
						. 'SET ' . $db->quoteName( 'permissions' ) . '=' . $db->Quote( '0' ) . ' '
						. 'WHERE ' . $db->quoteName('permissions') . '=' . $db->Quote( 'all' );
			$db->setQuery( $query );
			$db->query();

			$objResponse->addScriptCall( 'joms.jQuery("#progress-status").append("<div style=\"font-weight:700;\">' . JText::_('COM_COMMUNITY_UPDATED') . '</div>");');
		}

 		$objResponse->sendResponse();
	}

	public function enableAzrulSystem()
	{
		$db = JFactory::getDBO();

		$sql = ' UPDATE ' . $db->quoteName(PLUGIN_TABLE_NAME)
			 . ' SET ' . $db->quoteName(EXTENSION_ENABLE_COL_NAME) . ' = ' . $db->quote('1')
			 . ' WHERE ' . $db->quoteName('element') . ' = ' . $db->quote('azrul.system');

		$db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

		return TRUE;
	}
}