<?php
/*------------------------------------------------------------------------

# TZ  Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

abstract class ModHikaShopCustomHelper{
    protected static $module   = true;
    static function getList(&$params,&$image = null){
        if(!defined('DS'))
            define('DS', DIRECTORY_SEPARATOR);
        if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
            echo 'This module can not work without the Hikashop Component';
            return;
        };

        $class  = hikashop_get('class.category');
        $parents   = $class -> getParents($params -> get('category'),'product',true,array(),'',0,0);
//        $parents   = $class -> getParents($params -> get('category'),'product',true,array(),'',0,0);
        if(!empty($parents)){
            foreach($parents as $parent){
//                if(in_array($parent -> category_id,$params -> get('category'))){
                    $parent_cat_ids[]=$parent->category_id;
//                    $rows[] = $parent;
//                }
            }
        }
        $categoryIds    = $params -> get('category');
//        var_dump(array_filter($categoryIds)); die();
        $rows   = array();

        if($data   = $class -> getChilds($parent_cat_ids,'product',array(),'',0,0,true)){
            foreach($data as $row){
                $row ->link = self::getLink($params,$row -> category_id,$row -> category_alias);
                if(in_array($row -> category_id,$params -> get('category',$categoryIds))){
                    $rows[] = $row;
                }
            }
        }
        //var_dump($rows); die();

        if(count($rows)){
            foreach($rows as $row){
            }
            return $rows;
        }
        return null;
    }

    protected static function getLink($params,$cid,$alias){
        global $Itemid;
        if(empty(self::$module)){
            if(!empty($Itemid)){
                $menu_id = '&Itemid='.$Itemid;
            }
        }else{
            $menu_id = $params->get('menu_id',0);
            if(!empty($menu_id)){
            $menu_id = '&Itemid='.$menu_id;
            }else{
                $menu_id = '';
            }
        }
        $config =& hikashop_config();
        if(empty(self::$module) && !empty($Itemid) && $config->get('forward_to_submenus',1)){
            $app = JFactory::getApplication();
            $menus	= $app->getMenu();
            if(!HIKASHOP_J16){
                $query = 'SELECT a.id as itemid FROM `#__menu` as a WHERE a.access = 0 AND a.parent='.(int)$Itemid;
            }else{
                $query = 'SELECT a.id as itemid FROM `#__menu` as a WHERE a.client_id=0 AND a.parent_id='.(int)$Itemid;
            }
            $db = JFactory::getDBO();
            $db->setQuery($query);
            $submenus = $db->loadObjectList();
            foreach($submenus as $submenu){
                $menu	= $menus->getItem($submenu->itemid);
                if(!empty($menu) && !empty($menu->link) && strpos($menu->link,'option='.HIKASHOP_COMPONENT)!==false && (strpos($menu->link,'view=category')!==false || strpos($menu->link,'view=')===false || strpos($menu->link,'view=product')===false)){
                    $_params = $config->get( 'menu_'.$submenu->itemid );
                    if(!empty($_params) && $_params['selectparentlisting']==$cid){
                        return JRoute::_('index.php?option=com_hikashop&Itemid='.$submenu->itemid);
                    }
                }
            }
        }
        return hikashop_completeLink('category&task=listing&cid='.$cid.'&name='.$alias.$menu_id);
    }
}
?>