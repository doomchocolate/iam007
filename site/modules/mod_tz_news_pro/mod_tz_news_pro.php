<?php

/*------------------------------------------------------------------------

# MOD_TZ_NEWS_PRO Extension

# ------------------------------------------------------------------------

# author    tuyennv

# copyright Copyright (C) 2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die;
include_once JPATH_LIBRARIES . '/tzm/tz_news.php';
$version = new JVersion();
$document = JFactory::getDocument();
if ($params->get('enable_jquery') == 1) {
    $document->addScript(JUri::base() . 'modules/mod_tz_news_pro/js/jquery-1.9.1.min.js');
}

//$document->addStyleSheet(JUri::base() . 'modules/mod_tz_news_pro/css/mod_tz_news.css');


// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

// Import source and get the class name
$class_name = importSource("modules");

// Create instance of the class
$instance = new $class_name($module, $params);

// Get the items
$items = $instance->getItems();
$tzcontent = $params->get('manager');
$list = modTzNewsHelper::getList($params);
$tabs = $params->get('tz_count', 3);
$tabs_position = $params->get('tabs_position', 'top');
$title = $params->get('show_title');
$hits = $params->get('show_hits');
$image = $params->get('show_image');
$date = $params->get('show_date');
$des = $params->get('show_description');
$readmore = $params->get('show_readmore');
$readtext = $params->get('readmore_text');
$limittext = $params->get('limit_text');
$limittitle = $params->get('limit_title');
$heightsl = $params->get('slide_height');
$widthsl = $params->get('slide_width');
//Config to marquee
$CustomClass = $params->get('tz_customclass');
$orientation = $params->get('tz_orientation');
$direction = $params->get('tz_direction');
$frameRate = $params->get('tz_frameRate');
//end marquee
//Config to tooltip
$fadeinspeed = $params->get('tz_fadeinspeed');
$background_s = $params->get('tz_border_s');
$background_out = $params->get('tz_border_out');
$border_s = $params->get('tz_background_s');
$border_out = $params->get('tz_background_out');
$tooltipStatus = $params->get('tz_tooltip_status');
//end tooltip

//conifg to slider
$auto = 'false';
$pausePlay = 'false';
$animationLoop = 'false';
$effect = $params->get('tz_effect', 'slide');
$directionNav = 'false';
$animationSpeed = $params->get('tz_animationSpeed', 600);
$slideSpeed = $params->get('tz_slideSpeed', 3000);
$itemWidth = $params->get('tz_itemWidth', 5);
$minItems = $params->get('tz_slideMinItem', 1);
$maxItems = $params->get('tz_slideMaxItem', 5);
$move = $params->get('tz_slideMoveItem', 5);
if ($params->get('tz_auto', 1)):    $auto = 'true';endif;
if ($params->get('tz_pausePlay', 1)):$pausePlay = 'true';endif;
if ($params->get('tz_animationLoop', 1)):    $animationLoop = 'true';endif;
if ($params->get('tz_directionNav', 1)):    $directionNav = 'true';endif;
if ($params->get('tz_effect', 'slide')):$effect = $params->get('tz_effect', 'slide');endif;
if ($params->get('tz_animationSpeed')):$animationSpeed = $params->get('tz_animationSpeed', 600);endif;
if ($params->get('tz_slideSpeed')):$slideSpeed = $params->get('tz_slideSpeed', 3000);endif;
if ($params->get('tz_slideItemWidth')):$itemWidth = $params->get('tz_itemWidth', 5);endif;
if ($params->get('tz_slideMinItem')):$minItems = $params->get('tz_slideMinItem', 1);endif;
if ($params->get('tz_slideMaxItem')):$maxItems = $params->get('tz_slideMaxItem', 5);endif;
if ($params->get('tz_slideMoveItem')):$move = $params->get('tz_slideMoveItem', 5);endif;
// end slider
$author_new = $params->get('show_author');
$cats_new = $params->get('show_category');
$module_tab = $params->get('modules');
$type_tab = $params->get('type_tab');
$title_type = $params->get('tabs_title_type');
$show_quote = $params->get('show_quote');
$show_link = $params->get('show_link');
$show_audio = $params->get('show_audio');
//option carousel
$options = new stdClass();
$options->autoPlay = 'false';
$options->stopOnHover = 'false';
$options->singleItem = 'false';
$options->rewindNav = 'false';
$options->pagination = 'false';
$options->paginationNumbers = 'false';
$options->itemsScaleUp = 'false';
$options->items = 0;
$options->slideSpeed = 0;
$options->paginationSpeed = 0;
$options->rewindSpeed = 0;
if ($params->get('autoPlay', 1)):   $options->autoPlay = 'true';endif;
if ($params->get('stopOnHover', 1)):    $options->stopOnHover = 'true';endif;
if ($params->get('singleItem', 1)):    $options->singleItem = 'true';endif;
if ($params->get('rewindNav', 1)):    $options->rewindNav = 'true';endif;
if ($params->get('pagination', 1)):    $options->pagination = 'true';endif;
if ($params->get('paginationNumbers', 1)):    $options->paginationNumbers = 'true';endif;
if ($params->get('itemsScaleUp', 1)):    $options->itemsScaleUp = 'true';endif;
if ($params->get('items', 5)):    $options->items = $params->get('items', 5);endif;
if ($params->get('slideSpeed', 200)):    $options->slideSpeed = $params->get('slideSpeed', 200);endif;
if ($params->get('paginationSpeed', 800)):    $options->paginationSpeed = $params->get('paginationSpeed', 800);endif;
if ($params->get('rewindSpeed', 1000)):    $options->rewindSpeed = $params->get('rewindSpeed', 1000);endif;

require JModuleHelper::getLayoutPath('mod_tz_news_pro', $params->get('layout', 'default'));

?>
