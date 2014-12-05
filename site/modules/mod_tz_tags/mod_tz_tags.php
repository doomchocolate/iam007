<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';
$url = JURI::base();
$document = &JFactory::getDocument();

$document->addStyleSheet('modules/mod_tz_tags/css/mod_tz_tag.css');
//var_dump($document); die();

$list = modTzTagsHelper::getList($params);


if($list){

    require_once JModuleHelper::getLayoutPath('mod_tz_tags','default');

} else{
    echo "no item!";
}

 ?>
