<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};
//
//$class  = hikashop_get('class.category');
//$class -> type  = 'product';
//$parents   = $class -> getParents($params -> get('category'),true,array(),'',0,0);
//if(!empty($parents)){
//    foreach($parents as $parent){
//        $parent_cat_ids[]=$parent->category_id;
//    }
//}
//$category   = $class -> getChilds($parent_cat_ids,true,array(),'',0,0,true);

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$doc    = JFactory::getDocument();
$doc -> addScript('modules/mod_hikashop_custom/js/idangerous.swiper-2.1.min.js');
$doc -> addStyleSheet('modules/mod_hikashop_custom/css/idangerous.swiper.css');

$list               = ModHikaShopCustomHelper::getList($params);
$moduleclass_sfx	= htmlspecialchars($params->get('class_sfx'));

$image              = hikashop_get('helper.image');
$config =& hikashop_config();

if($params -> get('image_width')){
   $image -> main_thumbnail_x   = $params-> get('image_width');
}

if($params -> get('image_height')){
   $image -> main_thumbnail_y   = $params-> get('image_height');
}

//$height=$params->get('image_height');
//$width=$params->get('image_width');
//
//$exists=false;
//if(!empty($list)){
//    $row=reset($list);
//    if(!empty($row->file_path)){
//        jimport('joomla.filesystem.file');
//        if(JFile::exists($row->file_path)){
//            $exists=true;
//        }else{
//            $exists=false;
//        }
//    }
//}
//if(!$exists){
//    $path = $config->get('default_image');
//    if($path == 'barcode.png'){
//        $file_path=HIKASHOP_MEDIA.'images'.DS.'barcode.png';
//    }
//    if(!empty($path)){
//        jimport('joomla.filesystem.file');
//        if(JFile::exists($image->main_uploadFolder.$path)){
//            $exists=true;
//        }
//    }else{
//        $exists=false;
//    }
//    if($exists){
//        $file_path=$image->main_uploadFolder.$path;
//    }
//}else{
//    $file_path=$image->main_uploadFolder.$row->file_path;
//}
//if(!empty($file_path)){
//    if(empty($width)){
//        $imageHelper=hikashop_get('helper.image');
//        $theImage = new stdClass();
//        list($theImage->width, $theImage->height) = getimagesize($file_path);
//        list($width, $height) = $imageHelper->scaleImage($theImage->width, $theImage->height, 0, $height);
//    }
//    if(empty($height)){
//        $imageHelper=hikashop_get('helper.image');
//        $theImage = new stdClass();
//        list($theImage->width, $theImage->height) = getimagesize($file_path);
//        list($width, $height) = $imageHelper->scaleImage($theImage->width, $theImage->height, $width, 0);
//    }
//}
//
//var_dump($width,$height); die();
//$image->main_thumbnail_y=$height;
//$image->main_thumbnail_x=$width;

if($list && count($list)){
    require JModuleHelper::getLayoutPath('mod_hikashop_custom', $params->get('layout', 'default'));
}
