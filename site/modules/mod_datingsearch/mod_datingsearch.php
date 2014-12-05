<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .'/components/com_community/libraries/core.php' );
require_once ( dirname(__FILE__) .'/helper.php' );

$field = new stdClass();

$lang = JFactory::getLanguage();
$lang->load( 'com_community.country');
$lang->load( 'com_community');

//field code.
$field->code =  new stdClass();
$field->code->gender 	= $params->get('field_gender', 'FIELD_GENDER');
$field->code->birthdate = $params->get('field_birthdate', 'FIELD_BIRTHDATE');
$field->code->country 	= $params->get('field_country', 'FIELD_COUNTRY');
$field->code->state 	= $params->get('field_state', 'FIELD_STATE');
$field->code->city 	= $params->get('field_city', 'FIELD_CITY');

//field value.
$field->value = new stdClass();
$field->value->gender	= modDatingSearchHelper::getGenderValue($field->code->gender);
$field->value->country	= modDatingSearchHelper::getCountryValue($field->code->country);

//field type
$field->type 			= modDatingSearchHelper::getFieldType($field->code);

//history of previous serach if exist.
$gender 	= JRequest::getVar('datingsearch_gender', '', 'GET');
$agefrom 	= JRequest::getVar('datingsearch_agefrom', '', 'GET');
$ageto 		= JRequest::getVar('datingsearch_ageto', '', 'GET');
$country	= JRequest::getVar('datingsearch_country', '', 'GET');
$state		= JRequest::getVar('datingsearch_state', '', 'GET');
$city		= JRequest::getVar('datingsearch_city', '', 'GET');
$itemid		= CRoute::getItemId();

$config               = CFactory::getConfig();

$js   = '/assets/script-1.2.min.js';
CAssets::attach($js, 'js');
require(JModuleHelper::getLayoutPath('mod_datingsearch'));
