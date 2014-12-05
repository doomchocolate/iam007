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

// During ajax calls, the following constant might not be called
defined('JPATH_COMPONENT') or define('JPATH_COMPONENT', dirname(__FILE__));

require_once JPATH_ROOT . '/components/com_community/defines.community.php';

// Require the base controller
require_once COMMUNITY_COM_PATH . '/libraries/error.php';
require_once COMMUNITY_COM_PATH . '/controllers/controller.php';
require_once COMMUNITY_COM_PATH . '/libraries/apps.php';
require_once COMMUNITY_COM_PATH . '/libraries/core.php';
require_once COMMUNITY_COM_PATH . '/libraries/template.php';
require_once COMMUNITY_COM_PATH . '/views/views.php';
require_once COMMUNITY_COM_PATH . '/helpers/url.php';
require_once COMMUNITY_COM_PATH . '/helpers/ajax.php';
require_once COMMUNITY_COM_PATH . '/helpers/time.php';
require_once COMMUNITY_COM_PATH . '/helpers/owner.php';
require_once COMMUNITY_COM_PATH . '/helpers/azrul.php';
require_once COMMUNITY_COM_PATH . '/helpers/string.php';
require_once COMMUNITY_COM_PATH . '/events/router.php';

JTable::addIncludePath(COMMUNITY_COM_PATH . '/tables');

jimport('joomla.utilities.date');

// @todo: only load related language file
$view = JRequest::getCmd('view', 'frontpage');
$task = JRequest::getCmd('task', '');
$tmpl = JRequest::getCmd('tmpl', '', 'GET');

$lang = JFactory::getLanguage();
$config = CFactory::getConfig();


// DISABLE FORMAT=FEED for now 17Jan13 as requested by Fuqaha
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$viewType = $jinput->request->get('format', 'html', 'NONE');
if (strtolower($viewType) == 'feed') {
    $uri = JUri::getInstance();
    /* remove format param */
    $uri->setVar('format', null);
    $mainframe->redirect(CRoute::_($uri->toString()));
    exit('Redirecting to Non-Feed page');
}


// Run scheduled task and exit.
if (JRequest::getCmd('task', '', 'GET') == 'cron') {


    $cron = new CCron();
    $cron->run();
    exit;
}

if ($config->get('sendemailonpageload')) {


    $cron = new CCron();
    $cron->sendEmailsOnPageLoad();
}

// If the task is 'azrul_ajax', it would be an ajax call and core file
// should not be processing it.
if ($task != 'azrul_ajax') {
    jimport('joomla.filesystem.file');

    $mainframe = JFactory::getApplication();

    // Trigger system start
    if (function_exists('xdebug_memory_usage')) {
        $mem = xdebug_memory_usage();
        $tm = xdebug_time_index();

        $db = JFactory::getDBO();
        $db->debug(1);
    }

    require_once JPATH_COMPONENT . '/libraries/apps.php';
    $appsLib = CAppPlugins::getInstance();
    $appsLib->loadApplications();

    // Only trigger applications and set active URI when needed
    if ($tmpl != 'component') {
        $args = array();
        $appsLib->triggerEvent('onSystemStart', $args);

        // Set active URI
        CFactory::setCurrentURI();
    }

    // Normal call
    // Component configuration
    $config = array('name' => JString::strtolower(JRequest::getCmd('view', 'frontpage')));

    // Create the controller
    $viewController = JString::strtolower($config['name']);

    if (JFile::exists(JPATH_COMPONENT . '/controllers/' . $viewController . '.php')) {
        // If the controller is one of our controller, include the file
        // If not, it could be other 3rd party controller. Do not throw error message yet
        require_once JPATH_COMPONENT . '/controllers/' . $viewController . '.php';
    }

    $viewController = JString::ucfirst($viewController);
    $viewController = 'Community' . $viewController . 'Controller';

    // Trigger onBeforeControllerCreate (pass controller name by reference to allow override)
    $args = array();
    $args[] = &$viewController;

    $results = $appsLib->triggerEvent('onBeforeControllerCreate', $args);

    if (!JFile::exists(JPATH_COMPONENT . '/controllers/' . JString::strtolower($config['name']) . '.php') && !empty($results) && in_array(false, $results)) {
        return JError::raiseWarning(404, JText::_('COM_COMMUNITY_CONTROLLER_MISSING'));
    }
    // make sure none of the $result is false
    // If true, then one of the plugin is trying to override the controller creation
    // since we could only create 1 controller, we will pick the very first one only
    // plugin trigger function will return true if plugin want to intercept it
    if (!empty($results) && (in_array(true, $results))) {
        // 3rd party override used
        // @todo: use Reflection API to ensure that the class actually implement
        // our controller interface to avoid error
    }

    if (!class_exists($viewController)) {
        $mainframe = JFactory::getApplication();
        $mainframe->redirect(JRoute::_('index.php'), false);
    }

    $controller = new $viewController($config);
    $controller->execute(JRequest::getCmd('task', ''));

    //$jConfig = JFactory::getConfig();
    // Some hosting providers has xdebug installed for debugging purposes. We really shouldn't show this
    // on live site unless they turn on debugging mode.
    //if (function_exists('xdebug_memory_usage') && $jConfig->getValue('debug'))
    if (function_exists('xdebug_memory_usage') && $mainframe->getCfg('debug')) {
        $memNow = xdebug_memory_usage();
        $db = JFactory::getDBO();
        $db->debug(1);

        echo '<div style="clear:both">&nbsp;</div><pre>';
        echo 'Start usage : ' . cConvertMem($mem) . '<br/>';
        echo 'End usage   : ' . cConvertMem($memNow) . '<br/>';
        echo 'Mem usage   : ' . cConvertMem($memNow - $mem) . '<br/>';
        echo 'Peak mem    : ' . cConvertMem(xdebug_peak_memory_usage()) . '<br/>';
        echo 'Time        : ' . (xdebug_time_index() - $tm) . '<br/>';
        echo 'Query       : ' . $db->getTicker();
        echo '</pre>';

        // Log average page load
        jimport('joomla.filesystem.file');

        $logFile = COMMUNITY_COM_PATH . '/access.log';
        $content = '';

        if (JFile::exists($logFile)) {
            $content = JFile::read(COMMUNITY_COM_PATH . '/access.log');
        }

        $params = new CParameter($content);
        $today = strftime('%Y-%m-%d');
        $loadTime = $params->get($today, 0);

        if ($loadTime > 0) {
            $loadTime = ($loadTime + (xdebug_time_index() - $tm)) / 2;
        } else {
            $loadTime = (xdebug_time_index() - $tm);
        }

        $params->set($today, $loadTime);

        $paramsText = $params->toString();
        JFile::write(COMMUNITY_COM_PATH . '/access.log', $paramsText);
    }

    echo getJomSocialPoweredByLink();

//	getTriggerCount
//	$appLib = CAppPlugins::getInstance();
//	echo 'Trigger count: '. $appLib->triggerCount . '<br/>';
//	$time_end = microtime(true);
//	$time = $time_end - $time_start;
//	echo $time;
}

/**
 * Entry poitn for all ajax call
 */
function communityAjaxEntry($func, $args = null) {
    // For AJAX calls, we need to load the language file manually.
    $lang = JFactory::getLanguage();
    $lang->load('com_community');

    $response = new JAXResponse();
    $output = '';

    require_once JPATH_COMPONENT . '/libraries/apps.php';

    $appsLib = CAppPlugins::getInstance();
    $appsLib->loadApplications();

    $triggerArgs = array();
    $triggerArgs[] = $func;
    $triggerArgs[] = $args;
    $triggerArgs[] = $response;

    $results = $appsLib->triggerEvent('onAjaxCall', $triggerArgs);

    if (in_array(false, $results)) {
        $output = $response->sendResponse();
    } else {
        $calls = explode(',', $func);

        if (is_array($calls) && $calls[0] == 'plugins') {
            // Plugins ajax calls go here
            $func = $_REQUEST['func'];

            // Load CAppPlugins
            if (!class_exists('CAppPlugins')) {
                require_once JPATH_COMPONENT . '/libraries/apps.php';
            }

            $apps = CAppPlugins::getInstance();
            $plugin = & $apps->get($calls[1]);
            $method = $calls[2];

            // Move the $response object to be the first in the array so that the plugin knows
            // the first argument is always the JAXResponse object
            array_unshift($args, $response);

            // Call plugin AJAX method. Caller method's should only return the JAXResponse object.
            $response = call_user_func_array(array($plugin, $method), $args);
            $output = $response->sendResponse();
        } else {
            // Built-in ajax calls go here
            $config = array();
            $func = $_REQUEST['func'];
            $callArray = explode(',', $func);

            $viewController = JString::strtolower($callArray[0]);
            $viewControllerFile = JPATH_ROOT . '/components/com_community/controllers/' . $viewController . '.php';

            if (JFile::exists($viewControllerFile)) {
                require_once JPATH_ROOT . '/components/com_community/controllers/' . $viewController . '.php';

                $viewController = JString::ucfirst($viewController);
                $viewController = 'Community' . $viewController . 'Controller';
                $controller = new $viewController($config);

                // Perform the Request task
                $output = call_user_func_array(array(&$controller, $callArray[1]), $args);
            } else {
                echo JText::sprintf('Controller %1$s not found!', $viewController);
                exit;
            }
        }
    }

    return $output;
}

function cConvertMem($size) {
    $unit = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}