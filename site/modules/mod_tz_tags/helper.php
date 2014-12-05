<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    TemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

 // no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
$url = JURI::base();

abstract class modTzTagsHelper
{
  public static function getList(&$params){
      $db     = &JFactory::getDbo();
      $content = $params->get('manager');
      $limit = $params->get('tag-limit');
      $catids = $params->get('tag-cat');
      if($catids){
      $catid = implode(",",$catids);
      }

      if($content == 'tz_portfolio'){
          require_once JPATH_SITE.'/components/com_tz_portfolio/helpers/route.php';
        if($catid){
        $query =
          "SELECT COUNT(tag.name) as total, tag.name as tag, tag.id as id  FROM #__tz_portfolio_tags_xref tagx
        LEFT JOIN #__tz_portfolio_tags tag ON (tagx.tagsid = tag.id)
        LEFT JOIN #__content cont ON (tagx.contentid = cont.id)
        WHERE cont.catid in($catid) group by tag.name
          ";
        } else{
            $query =
            "SELECT COUNT(tag.name) as total, tag.name as tag, tag.id as id  FROM #__tz_portfolio_tags_xref tagx
            LEFT JOIN #__tz_portfolio_tags tag ON (tagx.tagsid = tag.id)
            LEFT JOIN #__content cont ON (tagx.contentid = cont.id)
            group by tag.name
          ";
        }
      }
      $db -> setQuery($query,0,$limit);
      $items = $db->loadObjectList();

      $cloud = array();
      if($items){
          if($content == 'tz_portfolio'){
              foreach($items as $item){
                  $cloud[] = $item->total;

                  $item->tagname = $item->tag;
                  $item->taglink = JRoute::_('index.php?option=com_tz_portfolio&view=tags&id='.$item -> id.'&Itemid='.JRequest::getCmd('Itemid'));

              }

              $max_size = $params->get('tag-maxfont');
              $min_size = $params->get('tag-minfont');
              $max_qty = max(array_values($cloud));
              $min_qty = min(array_values($cloud));

              $spread = $max_qty - $min_qty;
              if (0 == $spread) {
                  $spread = 1;
              }

              $step = ($max_size - $min_size) / ($spread);

              $counter = 0;

              foreach ($items as $tag) {
                  $size = $min_size + (($tag->total - $min_qty) * $step);
                  $size = ceil($size);
                  $tag->size = $size;
                  $tag->tagname = $tag->tag;
                  $tag->taglink = JRoute::_('index.php?option=com_tz_portfolio&view=tags&id='.$tag -> id.'&Itemid='.JRequest::getCmd('Itemid'));

              }
              return $items;
          }



      }
      return false;
  }


}
?>
