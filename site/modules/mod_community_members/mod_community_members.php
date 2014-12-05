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

include_once(JPATH_BASE . '/components/com_community/defines.community.php');
require_once(JPATH_BASE . '/components/com_community/libraries/core.php');

$document = JFactory::getDocument();
$document->addStyleSheet(rtrim(JURI::root(), '/') . '/components/com_community/assets/modules/module.css');
$default = ($params->get('default')) ? $params->get('default') : 15;

CAssets::attach('assets/script-1.2.min.js', 'js');

$config = CFactory::getConfig();
$document = JFactory::getDocument();
$frontpageUsers = intval($config->get('frontpageusers',5));

$document->addScriptDeclaration("var frontpageUsers	= " . $default . ";");
$document->addScriptDeclaration('joms.filters.bind()');

$model = CFactory::getModel('user');
$latestMembers = $model->getLatestMember($default);
$totalMembers = $model->getMembersCount();

$data = array();

if (!empty($latestMembers)) {
    shuffle($latestMembers);
}

require( JModuleHelper::getLayoutPath('mod_community_members') );