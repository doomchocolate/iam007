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

/**
 * This is the entry point for the AJAX calls.
 * We filter this out instead so that the AJAX methods can be called within the
 * controller.
 *
 * @param  string $func
 * @param  array  $args
 * @return mixed
 **/
function communityAjaxEntry($func, $args = null)
{
	// The first index will always be 'admin' to distinguish between admin ajax
	// calls and front end ajax calls.
	// $method[0]	= 'admin'
	// $method[1]	= CONTROLLER
	// $method[2]	= CONTROLLER->METHOD

	if (substr($func, 0, 5) === 'admin')
	{
		// @TODO: Not sure why we need to fetch ?func from REQUEST again.
		$func = $_REQUEST['func'];

		list($isAdmin, $controller, $method) = explode(',', $func);

		$controllerFile = JString::strtolower($controller);

		if (file_exists(JPATH_COMPONENT.'/controllers/'.$controllerFile.'.php'))
		{
			require_once JPATH_COMPONENT.'/controllers/'.$controllerFile.'.php';

			$controller = JString::ucfirst($controller);
			$controller = 'CommunityController'.$controller;
			$controller = new $controller();

			return call_user_func_array(array(&$controller, $method) , $args);
		}
	}
}