<?php
/*------------------------------------------------------------------------

# TZ Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

defined('_JEXEC') or die();

abstract class modTZStatistcHelper{
    public static function getList(&$params){
        $services   = base64_decode($params -> get('services'));

        $data   = array();

        preg_match_all('/(\{.*?\})/msi',$services,$match);
        if(count($match[1])){
            foreach($match[1] as $value){
                $registry   = new JRegistry($value);
                $data[] = $registry -> toObject();
            }
        }

        return $data;
    }
}