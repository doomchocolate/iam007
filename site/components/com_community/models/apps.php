<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

// Deprecated since 1.8.x to support older modules / plugins
//CFactory::load( 'tables' , 'app' );


class CommunityModelApps extends JCCModel
{

	  /**
	   * Items total
	   * @var integer
	   */
	  var $_total = null;

	  /**
	   * Pagination object
	   * @var object
	   */
	  var $_pagination = null;

	/**
	 *Constructor
	 *
	 */
 	 public function CommunityModelApps()
	 {
 	 	parent::JCCModel();

 	 	$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

 	 	// Get pagination request variables
 	 	$limit		=10;
 	 	$limitstart = $jinput->get('limitstart', 0, 'INT'); //JRequest::getVar('limitstart', 0, 'REQUEST');

 	 	if(empty($limitstart))
 	 	{
 	 		$limitstart = $jinput->get('start', 0, 'INT');
 	 	}

		$this->setState('limit',$limit);
 	 	$this->setState('limitstart',$limitstart);
 	 }

	/**
	 * Gets the pagination Object
	 *
	 *	return JPagination object
	 */
	public function &getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}


	/**
	 * Return the total number of applications that is installed on this Joomla site
	 *
	 *	return int Total count of applications
	 **/
	public function getTotal()
	{
		if (empty($this->_total))
		{
			$this->_total	= count( $this->getAvailableApps() );
		}
		return $this->_total;
	}

	// Return the title given its element name
	public function getAppTitle($appname){
		static $instances = array();

		if(empty($instances[$appname]))
		{
			$db	 = $this->getDBO();
			$sql = 'SELECT ' . $db->quoteName('name')
					.' FROM ' . $db->quoteName(PLUGIN_TABLE_NAME)
					.' WHERE ' . $db->quoteName('element') .'='. $db->Quote($appname)
                                        .' AND ' . $db->quoteName('folder') .'='. $db->Quote('community');
			$db->setQuery($sql);
			$instances[$appname] = $db->loadResult();
		}

		return $instances[$appname];
	}


	public function setOrder( $userId, $newOrder )
	{
		$db	 = $this->getDBO();

		foreach( $newOrder as $order )
		{
			// $order = 'appId,position,order'
			$order = explode(',', $order);

			$query	= 'UPDATE ' . $db->quoteName( '#__community_apps' ) . ' '
					. 'SET ' . $db->quoteName( 'ordering' ) . '=' . $db->Quote( $order[2] ) . ', '
					.		$db->quoteName( 'position' ) . '=' . $db->Quote( $order[1] ) . ' '
					. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $order[0] ) . ' '
					. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userId );

			$db->setQuery( $query );
			$db->query();

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		}
		return $this;
	}

	/***
	 * Save the new application ordering in db. The caller should have called
	 * 3 times in a row for all the 3 positions. Otherwise, old data might not
	 * be properly updated
	 */
	public function setOrdering( $userId , $position, $orderings )
	{
		$db	 = $this->getDBO();

		foreach( $orderings as $appId => $order )
		{
			$query	= 'UPDATE ' . $db->quoteName( '#__community_apps' ) . ' '
					. 'SET ' . $db->quoteName( 'ordering' ) . '=' . $db->Quote( $order ) . ', '
					.		$db->quoteName( 'position' ) . '=' . $db->Quote( $position ) . ' '
					. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $appId ) . ' '
					. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userId );

			$db->setQuery( $query );
			$db->query();

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		}
		return $this;
	}

	/**
	 * Return the list of all user-apps, in proper order
	 *
	 * @param	int		user id
	 * @return	array	of objects
	 */
	public function getUserApps($userid, $state = 0)
	{
		$db	 = $this->getDBO();

		$query	= 'SELECT ' . $db->quoteName('element')
					.' FROM '. $db->quoteName(PLUGIN_TABLE_NAME)
					.' WHERE ' . $db->quoteName(EXTENSION_ENABLE_COL_NAME).'=' . $db->Quote( 1 ) . ' '
					. 'AND ' . $db->quoteName('folder') .'=' . $db->Quote( 'community' );
		$db->setQuery( $query );
		$elementsResult	= $db->loadColumn();

		if($elementsResult)
		{
			$elements		= "'" . implode( $elementsResult , "','" ) . "'";
		}

		$query	= 'SELECT DISTINCT a.* FROM ' . $db->quoteName('#__community_apps') .' AS a '
				. 'WHERE a.' . $db->quoteName('userid') .'=' . $db->Quote( $userid ) . ' '
				. 'AND a.' . $db->quoteName('apps') .'!=' . $db->Quote('news_feed')
				. 'AND a.' . $db->quoteName('apps') .'!=' . $db->Quote('profile')
				. 'AND a.' . $db->quoteName('apps') .'!=' . $db->Quote('friends');

		if( !empty( $elements ) )
		{
			$query	.= 'AND a.' . $db->quoteName('apps') .' IN (' . $elements . ') ';
		}

		$query	.= 'ORDER BY a.' . $db->quoteName('ordering');

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		// If no data yet, we load default apps
		// and add them to db
		if(empty($result))
		{
			$result = $this->getCoreApps();
			foreach($result as $row)
			{
				$row->userid = $userid;

				// We need to load the positions based on plugins default config
				$dispatcher = CDispatcher::getInstanceStatic();
				$observers = $dispatcher->getObservers();
				for( $i = 0; $i < count($observers); $i++ )
				{
					$plgObj = $observers[$i];
					if( is_object($plgObj) )
					{
						$plgObjWrapper = new CPluginWrapper($plgObj);
						if( $plgObjWrapper->getPluginType() == 'community'
							&& ($plgObj->params != null)
							&& ($plgObjWrapper->getPluginName()  == $row->apps))
						{
							$row->position	= $plgObj->params->get('position', 'content');
							$row->privacy 	= $plgObj->params->get('privacy', 0);
						}
					}
				}

				$db->insertObject('#__community_apps', $row);
				if($db->getErrorNum()) {
					JError::raiseError( 500, $db->stderr());
				}
			}

			// Reload the apps
			// @todo: potential duplicate code
			$sql = 'SELECT * FROM ' . $db->quoteName('#__community_apps')
				.' WHERE ' . $db->quoteName('userid') .'=' . $db->Quote($userid)
				.' AND ' . $db->quoteName('apps') .'!=' . $db->Quote('news_feed')
				.' AND ' . $db->quoteName('apps') .'!=' . $db->Quote('profile')
				.' AND ' . $db->quoteName('apps') .'!=' . $db->Quote('friends')
				.' ORDER BY ' . $db->quoteName('ordering');
			$db->setQuery( $sql );
			$result = $db->loadObjectList();


			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
		}

		// For 2.2 onwards, wall apps WILL NOT be displayed in the profile page, we need
		// to splice the array out!
		$offset = null;
		for($i = 0; $i < count($result); $i++)
		{
			if($result[$i]->apps == 'walls')
			{
				$offset = $i;
			}
		}

		if( !is_null($offset) )
		{
			array_splice($result, $offset, 1 );
		}

		return $result;
	}

	/**
	 * Get user privacy setting
	 */
	public function getPrivacy($userid, $appname){
		static $privacy = array();

		if( empty($privacy[$userid]) )
		{
			// Preload all this user's privacy settings
			$db	 = $this->getDBO();
			$sql = 'SELECT ' . $db->quoteName('privacy') .', ' . $db->quoteName('apps')
					.' FROM ' . $db->quoteName('#__community_apps')
				  	.' WHERE ' . $db->quoteName('userid') .'=' . $db->Quote($userid);

			$db->setQuery( $sql );
			$db->query();

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}

		    $result = $db->loadObjectList();
		    $privacy[$userid] = array();

		    foreach($result as $row)
			{
				$privacy[$userid][$row->apps] = $row->privacy;
			}
	    }

	    if(empty($privacy[$userid][$appname]))
	    	$privacy[$userid][$appname] = 0;

	    $result = $privacy[$userid][$appname];

		return $result;
	}


	/**
	 * Store user privacy setting
	 */
	public function setPrivacy($userid, $appname, $val){
		$db	 = $this->getDBO();
		$sql = 'UPDATE ' . $db->quoteName('#__community_apps') .' SET ' . $db->quoteName('privacy') .'=' . $db->Quote($val)
			.' WHERE ' . $db->quoteName('userid').'=' . $db->Quote($userid) . ' AND ' . $db->quoteName('apps') .'=' . $db->Quote($appname);

		$db->setQuery( $sql );
		$db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $this;
	}

	/**
	 * Return the list of all available apps.
	 * @todo: need to display apps that are only permitted for the user
	 *
	 * @return	array	of objects
	 */
	public function getAvailableApps( $enableLimit = true )
	{
		$db		= $this->getDBO();

		// This is bad, we load everything and slice them up
		$applications	= JPluginHelper::getPlugin('community');


		$apps	= array();

		// $applications are already filtered by the plugin helper.
		// where disabled applications are automatically filtered.
		for( $i = 0; $i < count( $applications ); $i++ )
		{
			$row	= $applications[$i];
			$obj	= $this->getAppInfo( $row->name );

			//@rule: Application may be removed, so we need to test if the data really exists or not.
			if( isset( $obj->title ) )
			{
				$obj->title = JText::_($obj->title);

				if($obj->isApplication )
					$apps[]	= $obj;
			}
		}

		$totalApps = count($apps);

		if( $enableLimit )
		{
			$limitstart = $this->getState('limitstart');
			$limit		= $this->getState('limit');

			$apps = array_slice($apps, $limitstart, $limit);
		}

		// Appy pagination
		if(empty($this->_pagination))
		{
	 	    jimport('joomla.html.pagination');
	 	    $this->_pagination = new JPagination($totalApps, $this->getState('limitstart'), $this->getState('limit') );
	 	}
		return $apps;
	}

	public function getAppInfo($appname)
	{
		static $instances = array();

		if(empty($instances[$appname]))
		{
			$app		= new stdClass();
			$xmlPath	= CPluginHelper::getPluginPath('community',$appname)  .'.xml';

			jimport('joomla.filesystem.file');

			if(!JFile::exists($xmlPath))
			{
				switch($appname)
				{
					case 'status':
						$app->title = 'Status';
						break;
					default:
						break;
				}
				return $app;
			}

			//@since 2.6, to load the description from the language file
			$lang = JFactory::getLanguage();
			$lang->load('plg_community_'.$appname, JPATH_ADMINISTRATOR);

			$document			= new SimpleXMLElement($xmlPath , NULL , true);

			// Get the title from db
			$app->title			= JText::_($this->getAppTitle($appname));

			//$element			= $document->getElementByPath('name');
			//$app->title		= $element->data();

			$app->description	= JText::_($document->description);
			$app->author		= $document->author;
			$app->version		= $document->version;
			$app->creationDate	= $document->creationdate;

			// Determine whether the application is core application
			$params		= $this->getPluginParams( $this->getPluginId( $appname ) , null );
			$params		= new CParameter( $params );
			$app->coreapp		= $params->get( 'coreapp' );
			$app->isApplication = (isset($document->isapplication) && $document->isapplication == 'true') ? true : false;

			//$app->creationDate = 'test';

			$app->name = $appname;
			//$app->path = $appname;
			$instances[$appname] = $app;
		}

		return $instances[$appname];
	}

	/**
	 * Return list of core apps, as assigned by admin
	 */
	public function getCoreApps()
	{
		$applications	= array();
		$enableLimit	= false;
		$availableApps	= $this->getAvailableApps( $enableLimit );

		for( $i = 0; $i < count( $availableApps ); $i++ )
		{
			$application	= $availableApps[$i];

			$params			= $this->getPluginParams( $this->getPluginId( $application->name ) );
			$params			= new CParameter( $params );

			if($params->get( 'coreapp' ) )
			{
				$obj		= new stdClass();
				$obj->apps	= $application->name;
				$applications[]	= $obj;
			}

		}
		return $applications;
	}

	public function deleteApp($userid, $appid)
	{
		$db	 = $this->getDBO();
		$sql = 'DELETE FROM ' . $db->quoteName('#__community_apps')
				.' WHERE ' . $db->quoteName('userid') .'=' . $db->Quote($userid)
				.' AND ' . $db->quoteName('id') .'=' . $db->Quote($appid);
		$db->setQuery( $sql );
		$result = $db->query();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return true;
	}

	/**
	 * Add new apps for this user
	 */
	public function addApp($userid, $appName, $position='content')
	{
		$db	 = $this->getDBO();

		// @todo: make sure this apps is not inserted yet
		$sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_apps')
				.' WHERE ' . $db->quoteName('userid') .'=' . $db->Quote($userid)
			 	.' AND ' . $db->quoteName('apps') .'=' . $db->Quote($appName);
		$db->setQuery( $sql );
		$exist = $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		if(!$exist){
			// Fix the position to the last spot
			// @todo: make sure this apps is not inserted yet
			$sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_apps')
					.' WHERE ' . $db->quoteName('userid') .'=' . $db->Quote($userid)
				 	. ' AND ' . $db->quoteName('position').'=' . $db->Quote($position);
			$db->setQuery( $sql );
			$currentPost = $db->loadResult();


			$sql = 'INSERT INTO ' . $db->quoteName('#__community_apps') .' SET ' . $db->quoteName('userid') .'=' . $db->Quote($userid) . ", "
				 	. $db->quoteName('apps') .'=' . $db->Quote($appName) . ", "
				 	. $db->quoteName('position') .'=' . $db->Quote($position). ", "
				 	. $db->quoteName('ordering') .'=' . $db->Quote($currentPost);

			$db->setQuery( $sql );
			$result = $db->query();

			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
		}
		return $this;
	}

	/**
	 * Return parameter object of the given app
	 */
	public function getUserAppParams( $id , $userId = null )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT ' . $db->quoteName( 'params' ) . ' '
				. 'FROM ' . $db->quoteName( '#__community_apps' ) . ' '
				. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $id );

		if( !is_null( $userId ) )
		{
			$query	.= ' AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userId );
		}
		$db->setQuery($query);
		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	/**
	 * Return parameter object of the given app
	 */
	public function getPluginParams( $pluginId )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT ' . $db->quoteName( 'params' ) . ' '
				. 'FROM ' . $db->quoteName( PLUGIN_TABLE_NAME ) . ' '
				. 'WHERE ' . $db->quoteName( EXTENSION_ID_COL_NAME ) . '=' . $db->Quote( $pluginId );

		$db->setQuery( $query );

		$result = $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	/**
	 * Return default parameter of the given application from Joomla.
	 *
	 * param	string	name	The element of the application
	 **/
// 	public function getDefaultParams( $element )
// 	{
// 		$db		= $this->getDBO();
//
// 		$query	= 'SELECT ' . $db->quoteName( 'params' ) . ' FROM '
// 				. $db->quoteName( '#__plugins' ) . ' WHERE '
// 				. $db->quoteName( 'element' )
// 	}
	/**
	 * Return parameter object of the given app
	 */
	public function storeParams($id, $params){
		$db	 = $this->getDBO();
		$sql = 'UPDATE ' . $db->quoteName('#__community_apps') .' SET  ' . $db->quoteName('params') .'=' . $db->Quote($params)
			  .' WHERE ' . $db->quoteName('id') .'=' . $db->Quote($id);

		$db->setQuery( $sql );
		$db->query();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return true;
	}

	/**
	 * Return true if the user own the given appid
	 */
	public function isOwned($userid, $appid)
	{
		$db	 = $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM '
				. $db->quoteName( '#__community_apps' ) . ' '
				. 'WHERE ' . $db->quoteName( 'id' ) . '=' . $db->Quote( $appid ) . ' '
				. 'AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userid );

		$db->setQuery( $query );
		$result = $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	/**
	 * Return true if the user already enable
	 */
	public function isAppUsed($userid, $apps){
		$db	 = $this->getDBO();
		$sql = 'SELECT count(*) FROM ' . $db->quoteName('#__community_apps')
				.' WHERE ' . $db->quoteName('apps') .'=' . $db->Quote($apps)
				.' AND ' . $db->quoteName('userid') .'=' . $db->Quote($userid);
		$db->setQuery( $sql );
		$result = ($db->loadResult() > 0) ? true : false;

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Return the app name given the app id
	 * @param	int		row id in __community_apps
	 */
	public function getAppName($id){
		$db	 = $this->getDBO();
		$sql = 'SELECT ' . $db->quoteName('apps')
				.' FROM ' . $db->quoteName('#__community_apps')
				.' WHERE ' . $db->quoteName('id') .'=' . $db->Quote($id);
		$db->setQuery( $sql );
		$result = $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	/**
	 * Return the application id in Joomla's plugin table.
	 *
	 * @param	string	Element of the plugin.
	 */
	public function getPluginId( $element )
	{
		$db		= $this->getDBO();
		$query	= 'SELECT ' . $db->quoteName( EXTENSION_ID_COL_NAME ) . ' '
				. 'FROM ' . $db->quoteName( PLUGIN_TABLE_NAME ) . ' '
				. 'WHERE ' . $db->quoteName( 'element' ) . '=' . $db->Quote( $element )
				. 'AND ' . $db->quoteName('folder') . '=' . $db->Quote('community');

		$db->setQuery( $query );

		$result = $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * return the position of the given app.
	 */
	public function getUserAppPosition($userid, $element)
	{

		$db		= $this->getDBO();
		$query	= 'SELECT ' . $db->quoteName( 'position' ) . ' FROM ' . $db->quoteName( '#__community_apps' ) . ' '
				. 'WHERE ' . $db->quoteName( 'apps' ) . '=' . $db->Quote( $element )
				. '	AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userid );

		$db->setQuery( $query );

		$result = $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		// if empty, then it is a core apps and its position is set by the admin
		if(empty($result))
			$result = 'content';

		return $result;
	}

	public function getUserApplicationId( $element , $userId = null )
	{
		$db		= $this->getDBO();
		$query	= 'SELECT ' . $db->quoteName( 'id' ) . ' FROM ' . $db->quoteName( '#__community_apps' ) . ' '
				. 'WHERE ' . $db->quoteName( 'apps' ) . '=' . $db->Quote( $element );

		if( !is_null($userId) )
			$query	.= ' AND ' . $db->quoteName( 'userid' ) . '=' . $db->Quote( $userId );

		$db->setQuery( $query );

		$result = $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	public function checkObsoleteApp($obsoleteApp)
	{
		$db		= $this->getDBO();
		$query	= 'SELECT ' . $db->quoteName( EXTENSION_ENABLE_COL_NAME ) . ' FROM ' . $db->quoteName( PLUGIN_TABLE_NAME ) . ' '
				. 'WHERE ' . $db->quoteName( 'element' ) . '=' . $db->Quote( $obsoleteApp )
				. 'AND ' . $db->quoteName('folder') . '=' . $db->Quote('community');
		$db->setQuery( $query );
		$result = $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	public function removeObsoleteApp($obsoleteApp)
	{
		$db		= $this->getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName( '#__community_apps' ) . ' '
				. 'WHERE ' . $db->quoteName( 'apps' ) . '=' . $db->Quote( $obsoleteApp );
		$db->setQuery( $query );
		$result = $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $result;
	}

	public function hasConfig( $element )
	{
		jimport('joomla.filesystem.file' );

		return JFile::exists( CPluginHelper::getPluginPath('community',JString::trim( $element )) .'/'. JString::trim( $element ) .'/config.xml' );
	}
}