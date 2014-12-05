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
// Must global variable $jaxFuncNames to add function
// declaration to Community API.
global $jaxFuncNames;

// First argument should always be plugins to let Community know that its a plugin AJAX call.
// Second argument should be the plugin name, for instance 'profile'
// Third argument should be the plugin's function name to be called.
// It must be comma separated.
$jaxFuncNames[]	= 'plugins,profile,test';
$jaxFuncNames[]	= 'plugins,friendslocation,ajaxGoogleMap';
