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

jimport('joomla.application.component.controller' );
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');

require_once JPATH_COMPONENT.'/controllers/controller.php';
require_once JPATH_ROOT.'/administrator/components/com_community/defaultItems.php';
require_once JPATH_ROOT.'/administrator/components/com_community/installer.updater.php';


define('DBVERSION', '16');
define("JOOMLA_MENU_PARENT", 'parent_id');
define("JOOMLA_MENU_COMPONENT_ID", 'component_id');
define("JOOMLA_MENU_LEVEL", 'level');
define('JOOMLA_MENU_NAME' , 'title');
define('JOOMLA_MENU_ROOT_PARENT', 1);
define('JOOMLA_MENU_LEVEL_PARENT', 1);
define('JOOMLA_PLG_TABLE', '#__extensions');

/**
 * JomSocial Component Controller
 */
class CommunityControllerInstaller extends CommunityController
{
	public function __construct()
	{
		// Clean any buffer. (From ajax script in admin.community.php)
		$out = ob_get_contents();
		ob_end_clean();
		parent::__construct();
	}

	public function display($cachable = false, $urlparams = false){
		$check = array();

		// All this file must exist before we can continue. Should not be an issue
		$check['backend'] 	= file_exists(JPATH_ROOT . '/administrator/components/com_community/backend.zip');
		$check['ajax'] 		= file_exists(JPATH_ROOT . '/components/com_community/azrul.zip');
		$check['frontend']	= file_exists(JPATH_ROOT . '/components/com_community/frontend.zip');
		$check['template'] 	= file_exists(JPATH_ROOT . '/components/com_community/templates.zip');
		$check['plugins'] 	= true; //file_exists(JPATH_ROOT . '/components/com_community/ai_plugins.zip');

		// Supported image function
		$check['lib_jpeg']	= function_exists( 'imagecreatefromjpeg' );
		$check['lib_png']	= function_exists( 'imagecreatefrompng' );
		$check['lib_gif']	= function_exists( 'imagecreatefromgif' );
		$check['lib_gd']	= function_exists( 'imagecreatefromgd' );
		$check['lib_gd2']	= function_exists( 'imagecreatefromgd2' );
		$check['lib_curl']	= in_array  ('curl', get_loaded_extensions());

		// Folder permission
		$check['writable_backend']	= is_writable( JPATH_ROOT . '/administrator/components/com_community/' );
		$check['writable_frontend']	= is_writable( JPATH_ROOT . '/components/com_community/' );
		$check['writable_plugin']	= is_writable( JPATH_ROOT . '/plugins/' );


		// Supported php.ini
		$check['php_min_version']			= $this->_phpMinVersion('5.2.4');
		$check['php_max_execution_time']	= ini_get('max_execution_time');
		$check['php_max_input_time']		= ini_get('max_input_time');
		$check['php_memory_limit']			= ini_get('memory_limit');
		$check['php_post_max_size']			= ini_get('post_max_size');
		$check['php_upload_max_filesize']	= ini_get('upload_max_filesize');

		// Supported mysql configuration
		$db = JFactory::getDBO();
		$db->setQuery("show variables like 'wait_timeout'");
		$info = $db->loadRow();
		$check['my_wait_timeout'] = count($info) > 1? $info[1] :'n/a';

		$db->setQuery("show variables like 'connect_timeout'");
		$info = $db->loadRow();
		$check['my_connect_timeout'] = count($info) > 1? $info[1] :'n/a';

		$db->setQuery("show variables like 'connect_timeout'");
		$info = $db->loadRow();
		$check['my_connect_timeout'] = count($info) > 1? $info[1] :'n/a';

		// Do not allow installation to continue
		$allowContinue = ($check['backend'] && $check['ajax'] && $check['frontend'] && $check['template'] && $check['plugins']);
		$allowContinue = ($allowContinue && $check['writable_backend'] && $check['writable_frontend'] && $check['writable_plugin']);

		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/welcome.html');
		exit;
	}

	private function _phpMinVersion($v)
	{
		$phpV = PHP_VERSION;

		if ($phpV[0] >= $v[0]) {
			if (empty($v[2]) || $v[2] == '*') {
				return true;
			} elseif ($phpV[2] >= $v[2]) {
				if (empty($v[4]) || $v[4] == '*' || $phpV[4] >= $v[4]) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Unpacked file stage
	 */
	public function unpack(){
		$allowContinue = true;
		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/unpack.html');
		exit;
	}

	public function ajax_unpack(){
		$filename 		= JRequest::getWord('filename');
		$destination	= '';
		$source			= JPATH_ROOT . '/components/com_community/'.$filename.'.zip';

		switch($filename){
			case 'backend':
				$source	= JPATH_ROOT . '/administrator/components/com_community/backend.zip';
				$destination = JPATH_ROOT . '/administrator/components/com_community/';
				break;
			case 'frontend':
			case 'templates':
				$destination = JPATH_ROOT . '/components/com_community/';
				break;
			case 'azrul':
				$this->_installAjax();
				exit;
				break;
			case 'ai_plugins':
				$this->_installAiPlugin();
				exit;
				break;
			case 'modules':
				$this->_installModules();
				exit;
				break;
		}
		$destination	= JPath::clean( $destination );
		$source			= JPath::clean( $source );

		JArchive::extract($source, $destination);
		exit;
	}

	/**
	 * Prepare database stage
	 */
	public function prepdatabase(){
		$allowContinue = true;
		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/prepdatabase.html');
		exit;
	}

	/**
	*
	* Update Database
	* @todo Upgrade process from older version of JomSocial.
	*
	*/

	public function ajax_prepdatabase(){
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;
		$stage = $jinput->get('stage', NULL, 'STRING') ;//JRequest::getVar('stage');
		$allowContinue = true;

		switch($stage){
			case 'installschema':
				$this->_installSchema();
				break;
			case 'updateconfig':
				$this->_updateconfig();
				break;
			case 'createmenu':
				$this->_createMenu();
				break;
			case 'createtoolbar':
				$this->_createToolbar();
				break;
			case 'customfields':
				$this->_customFields();
				break;
			case 'upgrade':
				$this->_updateDb();
				break;
		}
		exit;
	}

	/**
	 * Install plugins
	 */
	public function plugins(){
		$allowContinue = true;
		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/plugins.html');
		exit;
	}

	/**
	 * Install modules
	 */
	public function modules(){
		$allowContinue = true;
		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/modules.html');
		exit;
	}

	public function ajax_plugins(){
		$allowContinue = true;
		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/plugins.html');
		exit;
	}

	/**
	 * Final stage. Completed
	 */
	public function done(){
		$allowContinue = true;
		include_once(JPATH_ROOT . '/administrator/components/com_community/installer/done.html');

		// remove dummy instaler marker file
		$file       = JPATH_ROOT.'/administrator/components/com_community/installer.dummy.ini';
		if (JFile::exists($file))
			JFile::delete($file);
		exit;
	}

	/**
	 * Install default schema
	 */
	private function _installSchema()
	{
		$db		= JFactory::getDBO();

		$buffer = file_get_contents(JPATH_ROOT.'/administrator/components/com_community/install.mysql.utf8.sql');
		jimport('joomla.installer.helper');
		$queries = JInstallerHelper::splitSql($buffer);

		if (count($queries) != 0)
		{
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);
					if (!$db->query())
					{
						return $db->getErrorNum().':'.$db->getErrorMsg();
					}
				}
			}
		}

		return false;
	}

	private function _installAjax(){
		if($this->_ajaxSystemNeedsUpdate()){
			$source	= JPATH_ROOT . '/components/com_community/azrul.zip';
			jimport('joomla.installer.installer');
			jimport('joomla.installer.helper');

			$package   = JInstallerHelper::unpack($source);
			$installer = JInstaller::getInstance();

			if ( ! $installer->install($package['dir']))
			{
				// There was an error installing the package

			}

			// Cleanup the install files
			if ( ! is_file($package['packagefile']))
			{
				//$config                 = JFactory::getConfig();
				//$package['packagefile'] = $config->get('tmp_path').'/'.$package['packagefile'];

				$app = JFactory::getApplication();
				$package['packagefile'] = $app->getCfg('tmp_path').'/'.$package['packagefile'];
			}

			JInstallerHelper::cleanupInstall('', $package['extractdir']);
		}

		//enable plugin
		$this->_enablePlugin('azrul.system');
	}

	/**
	 * Method to check if the system plugins exists
	 *
	 * @returns boolean	True if system plugin needs update, false otherwise.
	 **/
	private function _ajaxSystemNeedsUpdate()
	{
		$xml	= JPATH_PLUGINS.'/system/azrul.system.xml';

		// Check if the record also exists in the database.
		$db		= JFactory::getDBO();

		$query	= 'SELECT COUNT(1) FROM '.$db->quoteName(JOOMLA_PLG_TABLE) .' WHERE '
				. $db->quoteName( 'element' ).'='.$db->Quote( 'azrul.system' );
		$db->setQuery( $query );
		$dbExists	= $db->loadResult() > 0;

		if( !$dbExists )
		{
			return true;
		}

		// Test if file exists
		if( file_exists( $xml ) )
		{
			$parser = new SimpleXMLElement($xml, NULL, TRUE);
			$version = $parser->version;

			if( $version >= '3.2' && $version != 0 )
				return false;
		}

		return true;
	}

	/**
	 * Enable installed plugins
	 */
	private function _enablePlugin($plugin)
	{
		$db         = JFactory::getDBO();
		$version    = new JVersion();
		$joomla_ver = $version->getHelpVersion();

		$query	= 'UPDATE '.$db->quoteName('#__extensions').' SET '.$db->quoteName('enabled').' = '.$db->quote(1)
					.' WHERE '.$db->quoteName('element').' = '.$db->quote($plugin);

		$db->setQuery($query);

		if ( ! $db->query())
		{
			return $db->getErrorNum().':'.$db->getErrorMsg();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Update configuration and default categories
	 */
	private function _updateconfig()
	{

		if(CommunityDefaultItem::checkDefaultCategories('events')){
			CommunityDefaultItem::addDefaultEventsCategories();
		}

		if(CommunityDefaultItem::checkDefaultCategories('groups')){
			CommunityDefaultItem::addDefaultGroupCategories();
		}

		if(CommunityDefaultItem::checkDefaultCategories('videos')){
			CommunityDefaultItem::addDefaultVideosCategories();
		}

		if(CommunityDefaultItem::checkDefaultCategories('userpoints')){
			CommunityDefaultItem::addDefaultUserPoints();
		}

		return true;
	}

	/**
	 * Create 1 entry in main menu and a series of menu items of 'JomSocial' type
	 */
	private function _createMenu()
	{

		if(!CommunityDefaultItem::menuTypesExist()){
			CommunityDefaultItem::addDefaultMenuTypes();
		}

		if (CommunityDefaultItem::menuExist()){
			CommunityDefaultItem::updateMenuItems();
		} else {
			// Add menu items and the toolbar
			CommunityDefaultItem::addMenuItems();
		}

		return true;
	}

	/**
	 * Unused
	 */
	private function _createToolbar()
	{
		return true;
	}

	/**
	 * Insert custom fields
	 */
	private function _customFields()
	{
		if(CommunityDefaultItem::checkDefaultCategories('fields'))
		{
			CommunityDefaultItem::addDefaultCustomFields();
		}
		return;
	}

	/**
	 * Run Database upgrade script
	 */

	private function _updateDB()
	{
		$dbVersion = $this->_getDBVersion();

		if($dbVersion)
		{
			while($dbVersion < DBVERSION)
			{
				//it dosent check any error
				call_user_func(array('communityInstallerUpdate', "update_".$dbVersion));

				// increment db version
				$dbVersion++;
			}

			communityInstallerUpdate::updateDBVersion( DBVERSION );

			return;
		}

		communityInstallerUpdate::insertDBVersion( DBVERSION );
		communityInstallerUpdate::insertBasicConfig();
	}

	/**
	 * Get current db version
	 */

	protected function _getDBVersion()
	{
		$db		= JFactory::getDBO();

		$sql = 'SELECT '.$db->quoteName('params').' '
			.'FROM '.$db->quoteName('#__community_config').' '
			.'WHERE '.$db->quoteName('name').' = '.$db->quote('dbversion') .' '
			.'LIMIT 1';
		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}

	private function _installAiPlugin()
	{
		$source			= JPATH_ROOT . '/components/com_community/ai_plugin.zip';
		$destination	= JPATH_ROOT . '/components/com_community/ai_plugin/';

		if (!JFolder::exists($destination))
		{
			JFolder::create($destination);
		}

		if(JArchive::extract($source, $destination))
		{
			$plugins[]     = JPATH_ROOT . '/components/com_community/ai_plugin/plg_jomsocialuser.zip';
			$plugins[]     = JPATH_ROOT . '/components/com_community/ai_plugin/plg_walls.zip';
			$plugins[]     = JPATH_ROOT . '/components/com_community/ai_plugin/plg_jomsocialconnect.zip';
			$plugins[]     = JPATH_ROOT . '/components/com_community/ai_plugin/plg_jomsocialupdate.zip';

			// get plugins from packages
			$sourcePlugins			= JPATH_ROOT . '/components/com_community/all_plugins.zip';
			$destinationPlugins	= JPATH_ROOT . '/components/com_community/all_plugins/';
			if(JArchive::extract($sourcePlugins, $destinationPlugins)){
				$listPlugins = JFolder::files($destinationPlugins);
				foreach($listPlugins as $row){
					$plugins[] = $destinationPlugins.$row;
				}
			}
		}

		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');

		$app = JFactory::getApplication();

		foreach($plugins as $plugin)
		{
			$package   = JInstallerHelper::unpack($plugin);
			$installer = JInstaller::getInstance();

			if ( ! $installer->install($package['dir']))
			{
			// There was an error installing the package

			}

			// Cleanup the install files
			if ( ! is_file($package['packagefile']))
			{
				//$config					= JFactory::getConfig();
				//$package['packagefile']	= $config->get('tmp_path').'/'.$package['packagefile'];

				$package['packagefile'] = $app->getCfg('tmp_path').'/'.$package['packagefile'];
			}

			JInstallerHelper::cleanupInstall('', $package['extractdir']);
		}

		$this->_enablePlugin('jomsocialuser');
		$this->_enablePlugin('walls');
		$this->_enablePlugin('jomsocialconnect');
		$this->_enablePlugin('jomsocialupdate');

		//remove temp folder
		JFolder::delete($destination);
		JFolder::delete($destinationPlugins);
	}

	private function _installModules()
	{

		// get modules from packages
		$modules = array();
		$sourceModules			= JPATH_ROOT . '/components/com_community/all_modules.zip';
		$destinationModules	= JPATH_ROOT . '/components/com_community/all_modules/';
		if(JArchive::extract($sourceModules, $destinationModules)){
			$listModules = JFolder::files($destinationModules);
			foreach($listModules as $row){
				$modules[] = $destinationModules.$row;
			}
		}

		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');

		$app = JFactory::getApplication();

		foreach($modules as $module)
		{
			$package   = JInstallerHelper::unpack($module);
			$installer = JInstaller::getInstance();

			if ( ! $installer->install($package['dir']))
			{
			// There was an error installing the package

			}

			// Cleanup the install files
			if ( ! is_file($package['packagefile']))
			{
				//$config					= JFactory::getConfig();
				//$package['packagefile']	= $config->get('tmp_path').'/'.$package['packagefile'];

				$package['packagefile'] = $app->getCfg('tmp_path').'/'.$package['packagefile'];
			}

			JInstallerHelper::cleanupInstall('', $package['extractdir']);
		}
		//remove temp folder
		JFolder::delete($destinationModules);

		$this-> _enableModule('quicksearch');
		$this-> _enableModule('events');
		$this-> _enableModule('groups');
		$this-> _enableModule('members');
		$this-> _enableModule('photos');
		$this-> _enableModule('videos');
		$this-> _enableModule('whosonline');
	}

	private function _enableModule($module)
	{
		$default = array(
							'quicksearch'=>'',
							'events'=>5,
							'groups'=>5,
							'members'=>20,
							'photos'=>20,
							'videos'=>3,
							'whosonline'=>20
						);

		$params			= new CParameter( '' );
		$params->set('moduleclass_sfx',' app-box');
		$params->set('default',$default[$module]);

		$db         = JFactory::getDBO();
		$module = 'mod_community_'.$module;

		$query	= 'SELECT * FROM ' . $db->quoteName( '#__modules' ) . ' WHERE ';
		$query	.= $db->quoteName( 'module' ) . '=' . $db->Quote( $module );

		$db->setQuery( $query );
		$result = $db->loadObject();

		if(empty($result->position))
		{
			$query	= 'UPDATE '.$db->quoteName('#__modules').' SET '.$db->quoteName('published').' = '.$db->quote(1)
						.' , '.$db->quoteName('position').' = '. $db->quote('js_side_frontpage')
						.' , '.$db->quoteName('ordering'). ' = '.$db->quote(1)
						.' , '.$db->quoteName('params'). ' = '.$db->quote($params->toString())
						.' WHERE '.$db->quoteName('module').' = '.$db->quote($module);

			$db->setQuery($query);

			if ( ! $db->query())
			{
				return $db->getErrorNum().':'.$db->getErrorMsg();
			}

		}

		$query = 'SELECT COUNT(*) FROM ' .$db->quoteName( '#__modules_menu' )
					.' WHERE ' .$db->quoteName( 'moduleid' ) .' = ' . $db->quote( $result->id );

		$db->setQuery($query);

		$count = $db->loadResult();

		if($count < 1)
		{

			$query	= 'INSERT INTO ' . $db->quoteName( '#__modules_menu' )
					. '(' . $db->quoteName( 'moduleid' ) . ', '. $db->quoteName( 'menuid' ). ')'
					. 'VALUES('. $db->quote( $result->id ) . ', '. $db->quote( '' ). ')';

			$db->setQuery( $query );
			$db->Query();
		}
	}
}