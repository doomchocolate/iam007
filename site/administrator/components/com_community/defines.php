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

define('COMMUNITY_ASSETS_PATH', JPATH_COMPONENT.'/assets');
define('COMMUNITY_ASSETS_URL', JURI::base().'components/com_community/assets');
define('COMMUNITY_BASE_PATH', dirname(JPATH_BASE).'/components/com_community');
define('COMMUNITY_BASE_ASSETS_PATH', JPATH_BASE.'/components/com_community/assets');
define('COMMUNITY_BASE_ASSETS_URL', JURI::root().'components/com_community/assets');
define('COMMUNITY_CONTROLLERS', JPATH_COMPONENT.'/controllers');

// @TODO to be removed.
jimport('joomla.version');
$version    = new JVersion();
$joomla_ver = $version->getHelpVersion();
// @ENDTODO