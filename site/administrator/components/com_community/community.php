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

// During ajax calls, the following constant might not be called
defined('JPATH_COMPONENT') or define('JPATH_COMPONENT', dirname(__FILE__));

define('COMMUNITY_ASSETS_PATH', JPATH_BASE.'/components/com_community/assets');
define('COMMUNITY_ASSETS_URL', JURI::base().'components/com_community/assets');
define('COMMUNITY_BASE_PATH', dirname(JPATH_BASE).'/components/com_community');
define('COMMUNITY_BASE_ASSETS_PATH', JPATH_BASE.'/components/com_community/assets');
define('COMMUNITY_BASE_ASSETS_URL', JURI::root().'components/com_community/assets');
define('COMMUNITY_CONTROLLERS', JPATH_COMPONENT.'/controllers');

// @todo: Do some check if user is really allowed to access this section of the back end.
// Just in case we need to impose ACL on the component

// During ajax calls, the following constant might not be called
defined('JPATH_COMPONENT') or define('JPATH_COMPONENT', dirname(__FILE__));

// Load necessary language file since we dont store it in the language folder
$lang = JFactory::getLanguage();
$lang->load('com_community', JPATH_ROOT.'/administrator');

//check php version
$installedPhpVersion = floatval(phpversion());
$supportedPhpVersion = 5;

$jinput = JFactory::getApplication()->input;
$install = $jinput->request->get('install', '', 'NONE');
$view    = $jinput->get->get('view', '');
$task    = $jinput->request->get('task', '');

if ($task == 'reinstall')
{

	$destination = JPATH_ROOT.'/administrator/components/com_community/';
	$buffer      = "installing";

	JFile::write($destination.'installer.dummy.ini', $buffer);
}

// IS Install Mode ?
if (((file_exists(JPATH_ROOT.'/administrator/components/com_community/installer.dummy.ini') || $install) && $view!='maintenance' && $task != 'azrul_ajax') || ($installedPhpVersion < $supportedPhpVersion))
{
	// set to installer view
	$controller	= JRequest::getWord('view' , 'community');
	if($controller != 'installer'){
		$mainframe	= JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_community&view=installer');
		return;
	}
}
else
{
	if (file_exists(JPATH_ROOT.'/administrator/components/com_jsupdater/jsupdater.dummy.ini'))
	{
		$mainframe	= JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_jsupdater');
	}

	// Load JomSocial core file
	require_once JPATH_ROOT.'/components/com_community/libraries/core.php';

	// Load any helpers
	require_once JPATH_COMPONENT.'/helpers/community.php';

	// Load any libraries
	require_once JPATH_COMPONENT.'/libraries/chtmlinput.php';

		// Load any libraries
	require_once JPATH_COMPONENT.'/libraries/cadminactivity.php';

	// Require the base controller
	require_once JPATH_COMPONENT.'/controllers/controller.php';

	// Set the tables path
	JTable::addIncludePath(JPATH_COMPONENT.'/tables');
}

// Get the task
$task = JRequest::getCmd('task' , 'display');

// Load the required libraries
if ( ! defined('JAX_SITE_ROOT') && defined('AZRUL_SYSTEM_PATH'))
{
	require_once AZRUL_SYSTEM_PATH.'/pc_includes/ajax.php';
}

// Let's test if the task is azrul_ajax , we skip the controller part at all.
if (isset($task) && ($task == 'azrul_ajax'))
{
	require_once JPATH_ROOT.'/administrator/components/com_community/ajax.community.php';
}
else
{
	ob_start();

	// Load AJAX library for the back end. Only if the plugin exist
	if(class_exists('JAX')){
		$jax = new JAX(AZRUL_SYSTEM_LIVE.'/pc_includes');
		$jax->setReqURI(rtrim(JURI::root(), '/').'/administrator/index.php');

		// @rule: We do not want to add these into tmpl=component or no_html=1 in the request.
		if ($jinput->request->get('no_html' , '', 'NONE' ) != 1 && $jinput->request->get( 'tmpl' , '' , 'NONE') != 'component')
		{
			// Override previously declared jax_live_site stuffs
			if ( ! $jax->process())
			{
				echo $jax->getScript();
			}
		}
	}

	// We treat the view as the controller. Load other controller if there is any.
	$controller	= JRequest::getWord('view' , 'community');

	if ( ! empty($controller))
	{
		$controller = JString::strtolower($controller);
		$path       = JPATH_ROOT.'/administrator/components/com_community/controllers/'.$controller.'.php';

		// Test if the controller really exists
		if (file_exists($path))
		{
			require_once $path;
		}
		else
		{
			JError::raiseError(500 , $path . JText::_('COM_COMMUNITY_CONTROLLER_NOT_EXISTS'));
		}
	}

	$class = 'CommunityController'.JString::ucfirst($controller);

	//check if zend plugin is installed
	if(JPluginHelper::getPlugin('system', 'zend') || file_exists(JPATH_ROOT.'/plugins/system/zend/zend.xml'))
	{
		$message 		= JText::_('COM_COMMUNITY_ZEND_REMOVE_MESSAGE');
		$mainframe 		= JFactory::getApplication();
		$mainframe->enqueueMessage($message, 'error');
	}
	// Test if the object really exists in the current context
	if ( ! class_exists($class))
	{
		// Throw some errors if the system is unable to locate the object's existance
		JError::raiseError(500 , 'Invalid Controller Object. Class definition does not exists in this context.');
	}

	$controller	= new $class();

	// Task's are methods of the controller. Perform the Request task
	$controller->execute($task);

	// Redirect if set by the controller
	$controller->redirect();

	$out = ob_get_contents();
	ob_end_clean();

	$document = JFactory::getDocument();
	preg_match('/[^-]+$/', $document->getTitle(), $match);

	$communityController = new CommunityController();

	$groups 	= $communityController->getModel( 'Groups','CommunityAdminModel' );
	$reports	= $communityController->getModel( 'Reports' );
	$mailque 	= $communityController->getModel( 'MailQueue' );
	$events		= $communityController->getModel( 'Events' );
	$users		= $communityController->getModel( 'Users' );

	/*Optimize query*/
	$unsendMail    = $mailque->getUnsendMail();
	$pendingGroup  = $groups->getPendingGroups();
	$pendingEvent  = $events->getPendingEvents();
	$pendingReport = $reports->getPendingCount();
	$pendingUser   = $users->getPendingMember();

	$view = $communityController->getView('community', 'html');

	$stableVersion = $communityController->_getCurrentVersionData();
	$localVersion =  $communityController->_getLocalVersionNumber();

	$isLatest = version_compare($localVersion, $stableVersion->version,'<');

	$total = ($isLatest) ? 1:0;

	$version = ($isLatest) ? $stableVersion->version : 0 ;

	$total =  $pendingGroup + $pendingEvent + $pendingReport + $pendingUser + $unsendMail;

	$versionUrl = '';
	$my = CFactory::getUser();

	if(JFile::exists(JPATH_ROOT.'/administrator/components/com_ijoomlainstaller/ijoomlainstaller.xml')){
		$versionUrl = JRoute::_('index.php?option=com_ijoomlainstaller');
	}

	$view->set('pageTitle', $match[0]);
	$view->set('pageContent', $out);
	$view->set('pendingGroup',$pendingGroup);
	$view->set('pendingEvent',$pendingEvent);
	$view->set('reportCount',$pendingReport);
	$view->set('pendingUser',$pendingUser);
	$view->set('unsendCount',$unsendMail);
	$view->set('total',$total);
	$view->set('version',$version);
	$view->set('versionUrl',$versionUrl);
	$view->set('my',$my);

	$view->loadLayout();
}