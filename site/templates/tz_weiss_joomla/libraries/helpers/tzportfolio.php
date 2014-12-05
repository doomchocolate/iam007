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


JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_tz_portfolio/models');
/**
 * Content Component HTML Helper.
 */
class JHtmlTZPortfolio
{
    public static function extraFields(&$articles,$params){
        $template       = JFactory::getApplication() -> getTemplate(true);
        $templateParams = $template -> params;
        if($extrafields = self::_getExtraFields($articles,$params)){
            if($articles){
                //Get Plugins Model
                $pmodel = JModelLegacy::getInstance('Plugins','TZ_PortfolioModel',array('ignore_request' => true));

                foreach($articles as $i => $item){
                    //Get plugin Params for this article
                    $pmodel -> setState('filter.contentid',$item -> id);
                    $pluginItems    = $pmodel -> getItems();
                    $pluginParams   = $pmodel -> getParams();

                    $item -> pluginparams       = clone($pluginParams);
                    $item -> custom_introtext   = null;

                    if(isset($pluginParams -> tz_templaza) && $pluginParams -> tz_templaza){
                        $tzparams   = $pluginParams -> tz_templaza;
                        $item -> custom_introtext   = $tzparams -> get('introtext',null);
                    }

                    $item -> extrafield         = null;
                    $fieldsIcon = array();
                    foreach($extrafields as $j => $extrafield){
                        if($item -> id == $extrafield -> contentid){
                            if($templateParams){
//                                if((int) $extrafield -> fieldsid == (int) $templateParams -> get('introtext_custom')){
//                                    $item -> custom_introtext   = $extrafield -> value[0];
//                                }else{
                                    if(in_array($extrafield -> fieldsid,$templateParams -> get('extrafield_id',array()))){
                                        $item -> extrafield = $extrafield;
                                    }
//                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected static function _getExtraFields($articles,$params){
        if($articles){
            foreach($articles as $i => $item){
                $articleIds[]   = $item -> id;
            }

            if($articleIds && is_array($articleIds)){
                $articleIds = implode(',',$articleIds);
            }
            $where      = null;

            if($params -> get('tz_fieldsid')){
                $fieldsId   = $params -> get('tz_fieldsid');
                if($fieldsId && !empty($fieldsId)){
                    if(is_array($fieldsId)){
                        if(count($fieldsId) > 0){
                            if(empty($fieldsId[0])){
                                array_shift($fieldsId);
                            }

                            $fieldsId   = implode(',',$fieldsId);
                        }
                    }
                    else{
                        $fieldsId   = null;
                    }

                    if($fieldsId)
                        $where  = ' AND t.fieldsid IN('.$fieldsId.')';

                }
            }
            $orderBy    = null;
            $order      = null;

            $order  = $params -> get('fields_order');

            switch($order){
                default:
                    $orderBy    = 'f.id DESC';
                    break;
                case 'rid':
                    $orderBy    = 'f.id DESC';
                    break;
                case 'id':
                    $orderBy    = 'f.id ASC';
                    break;
                case 'alpha':
                    $orderBy    = 'f.title ASC';
                    break;
                case 'ralpha':
                    $orderBy    = 'f.title DESC';
                    break;
                case 'order':
                    $orderBy    = 'f.ordering ASC';
                    break;
            }

            if($params -> get('fields_option_order')){
                switch($params -> get('fields_option_order')){
                    case 'alpha':
                        $fieldsOptionOrder  = 't.value ASC';
                        break;
                    case 'ralpha':
                        $fieldsOptionOrder  = 't.value DESC';
                        break;
                    case 'ordering':
                        $fieldsOptionOrder  = 't.ordering ASC';
                        break;
                }
                if($fieldsOptionOrder){
                    $orderBy    .= ','.$fieldsOptionOrder;
                }
            }

            if($orderBy){
                $orderBy    = ' ORDER BY '.$orderBy;
            }

            $data   = array();
            $query  = 'SELECT t.*,f.title FROM #__tz_portfolio AS t'
                .' LEFT JOIN #__tz_portfolio_fields AS f ON f.id=t.fieldsid'
                .' LEFT JOIN #__content AS c ON c.id=t.contentid'
                .' WHERE c.state=1 AND t.contentid IN('.$articleIds.')'
                .$where
                .$orderBy;

            $db     = JFactory::getDbo();
            $db -> setQuery($query);

            if(!$db -> query()){
                var_dump($db -> getErrorMsg());
                return false;
            }
            $rows   = $db -> loadObjectList();

            if(count($rows)>0){
                $k  = 0;
                for($i = 0;$i < count($rows);$i ++){
                    $tg     = array();
                    $images = array();
                    $count  = 0;

                    for($j=0;$j<count($rows);$j++){
                        if(($rows[$i] -> fieldsid) == ($rows[$j] -> fieldsid) && ($rows[$i] -> contentid) == ($rows[$j] -> contentid)){
                            $tg[$count]     = $rows[$j] -> value;
                            $images[$count] = $rows[$j] -> images;
                            $count++;
                            $i=$j;
                        }
                    }
                    $data[$k]   = new stdClass();

                    $data[$k] -> id             = $rows[$i] -> id;
                    $data[$k] -> contentid      = $rows[$i] -> contentid;
                    $data[$k] -> fieldsid       = $rows[$i] -> fieldsid;
                    $data[$k] -> title          = strip_tags($rows[$i] -> title);
                    $data[$k] -> value          = $tg;
                    $data[$k] -> images         = $images;


                    $k++;
                }
            }

            return $data;
        }
        return false;
    }

    public static function tags($article){
        if($article){
            $articleIds = array();
            foreach($article as $i => $item){
                $articleIds[]   = $item -> id;
            }
            if(is_array($articleIds)){
                $articleIds = implode(',',$articleIds);
            }
            $db     = JFactory::getDbo();
            $query  = $db -> getQuery(true);
            $query -> select('t.*,x.contentid');
            $query -> from($db -> quoteName('#__tz_portfolio_tags').' AS t');
            $query -> where('x.contentid IN('.$articleIds.')');
            $query -> join('INNER',$db -> quoteName('#__tz_portfolio_tags_xref').'AS x ON t.id=x.tagsid');
            $db -> setQuery($query);
            if($rows = $db -> loadObjectList()){
                return $rows;
            }
            return false;
        }
    }
}