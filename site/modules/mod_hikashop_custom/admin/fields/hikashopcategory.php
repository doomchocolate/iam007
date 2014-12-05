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

defined('JPATH_BASE') or die;

class JFormFieldHikaShopCategory extends JFormField{
    var $type = 'HikaShopCategory';

    function __construct($form = null){
        if(!defined('DS'))
            define('DS', DIRECTORY_SEPARATOR);
        if(!JFile::exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
            return;
        }
        if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
            return;
        };
        include_once (JPATH_ROOT.DS.'modules'.DS.'mod_hikashop_custom'.DS.'admin'.DS.'categorysub.php');
        parent::__construct($form);
    }

    function getInput() {


        $attribute    = null;
        if($this -> element['multiple']){
            $attribute   .= ' multiple="true"';
        }
        $category   = hikashop_get('type.categorysub');
        $category->type='product';
        $category->load();
        return $category -> display($this -> name,$this -> value,$attribute,true,$this -> id);
    }
}