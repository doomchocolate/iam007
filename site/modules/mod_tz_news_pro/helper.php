<?php
/*------------------------------------------------------------------------

# MOD_TZ_NEW_PRO Extension

# ------------------------------------------------------------------------

# author    tuyennv

# copyright Copyright (C) 2013 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_content/helpers/route.php';
if ($content == 'tz_portfolio') {
    require_once JPATH_SITE . '/components/com_tz_portfolio/helpers/route.php';
}
require_once(JPATH_SITE . '/libraries/tzm/sources/modules/modules.php');
jimport('joomla.application.component.model');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tz_portfolio/models', 'TZ_PortfolioModel');
$url = JURI::base();

abstract class modTzNewsHelper
{

    /*
     * Method get data
    */
    public static function getList(&$params)
    {

        $db = JFactory::getDbo();
        $content = $params->get('manager');
        $order = $params->get('order');
        $orderby = $params->get('orderby');
        $limit = $params->get('limit');
        $catids = $params->get('catid');
        $img = $params->get('image');
        $typenews = $params->get('views');
        $featureol = $params->get('show_featured');
        $redirect = $params->get('redirect_page');
        $dispatcher = JDispatcher::getInstance();


        // portfolio and content

        if ($typenews == "featured") {
            if (isset($catids) && !empty($catids)) {

                $catid = implode(",", $catids);

                $where = "WHERE ct.catid IN($catid) and ct.state = 1  and featured = 1 ORDER BY ct.$orderby $order LIMIT $limit";

            } else {

                $where = "WHERE ct.state = 1 and featured = 1 ORDER BY ct.$orderby $order LIMIT $limit";
            }

        } else {

            if (isset($catids) && !empty($catids)) {

                $catid = implode(",", $catids);

                if ($featureol == 1) {

                    $where = "WHERE ct.catid IN($catid) and ct.state = 1 and featured = 1 ORDER BY ct.$orderby $order LIMIT $limit";

                } else {

                    $where = "WHERE ct.catid IN($catid) and ct.state = 1  ORDER BY ct.$orderby $order LIMIT $limit";
                }

            } else {

                $where = "WHERE ct.state = 1 ORDER BY ct.$orderby $order LIMIT $limit";
            }
        }
        if ($content == 'tz_portfolio') {

            $model = JModelLegacy::getInstance('Articles', 'TZ_PortfolioModel', array('ignore_request' => true));

            $model2 = JModelLegacy::getInstance('Media', 'TZ_PortfolioModel', array('ignore_request' => true));

            $app = JFactory::getApplication();

            $appParams = $app->getParams();

            $model->setState('params', $appParams);

            $model->setState('list.start', 0);

            $model->setState('list.limit', (int)$params->get('limit'));

            $model->setState('filter.published', 1);

            $model->setState('filter.category_id', $params->get('catid', array()));

            $order_map = array(
                'm_dsc' => 'a.modified DESC, a.created',
                'mc_dsc' => 'CASE WHEN (a.modified = ' . $db->quote($db->getNullDate()) . ') THEN a.created ELSE a.modified END',
                'c_dsc' => 'a.created',
                'p_dsc' => 'a.publish_up',
            );
            $ordering = JArrayHelper::getValue($order_map, $params->get('ordering'), 'a.publish_up');

            $dir = 'DESC';

            $model->setState('list.ordering', $ordering);

            $model->setState('list.direction', $dir);

            $items = $model->getItems();
            $param_com = JComponentHelper::getComponent('com_content')->params;

            if ($items) {

                foreach ($items as $i => $item) {

                    $item->text = $item->introtext;

                    JPluginHelper::importPlugin('content');
                    $results = $dispatcher->trigger('onContentPrepare', array('com_tz_portfolio.article', &$item, &$param_com, 0));
                    $item->introtext = $item->text;
                    $item->event = new stdClass();

                    $results = $dispatcher->trigger('onContentAfterTitle', array('com_tz_portfolio.article', &$item, &$param_com, 0));
                    $item->event->afterDisplayTitle = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_tz_portfolio.article', &$item, &$param_com, 0));
                    $item->event->beforeDisplayContent = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onContentAfterDisplay', array('com_tz_portfolio.article', &$item, &$param_com, 0));
                    $item->event->afterDisplayContent = trim(implode("\n", $results));

                    //Get Plugins Model
                    $pmodel = JModelLegacy::getInstance('Plugins', 'TZ_PortfolioModel', array('ignore_request' => true));
                    //Get plugin Params for this article
                    $pmodel->setState('filter.contentid', $item->id);
                    $pluginItems = $pmodel->getItems();
                    $pluginParams = $pmodel->getParams();

                    $item->pluginparams = clone($pluginParams);

                    JPluginHelper::importPlugin('tz_portfolio');
                    $results = $dispatcher->trigger('onTZPluginPrepare', array('com_tz_portfolio.article', &$item, &$param_com, &$pluginParams, 0));

                    $results = $dispatcher->trigger('onTZPluginAfterTitle', array('com_tz_portfolio.article', &$item, &$param_com, &$pluginParams, 0));
                    $item->event->TZafterDisplayTitle = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onTZPluginBeforeDisplay', array('com_tz_portfolio.article', &$item, &$param_com, &$pluginParams, 0));
                    $item->event->TZbeforeDisplayContent = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onTZPluginAfterDisplay', array('com_tz_portfolio.article', &$item, &$param_com, &$pluginParams, 0));
                    $item->event->TZafterDisplayContent = trim(implode("\n", $results));

                    $item->media = null;

                    $model2->setState('article.id', $item->id);

                    if ($media = $model2->getMedia()) {

                        $item->media = $media[0];

                    }


                    $item->tztitle = $item->title;

                    $item->hit = $item->hits;

                    echo $item->event->beforeDisplayContent;
                    echo $item->event->TZbeforeDisplayContent;

                    $item->intro = $item->introtext;

                    echo $item->event->afterDisplayContent;
                    echo $item->event->TZafterDisplayContent;

                    $item->slug = $item->id . ':' . $item->alias;

                    $item->catslug = $item->catid . ':' . $item->category_alias;

                    $item->category = $item->category_title;
                    $item->author = $item->author;

                    if ($redirect == 0) {

                        $item->link = JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($item->slug, $item->catslug));

                    }
                    if ($redirect == 1) {

                        $item->link = JRoute::_(TZ_PortfolioHelperRoute::getPortfolioArticleRoute($item->slug, $item->catslug));

                    }

                    $item->media = $item->media;
                    if ($item->media) {
                        if ($item->media->type == 'image') {

                            $images = $item->media->images;
                            $nameimg = JFile::getExt($images);
                            $count = strlen($nameimg);
                            $image_name = substr($images, 0, -($count + 1));
                            $item->image = $image_name . '_' . $img . '.' . $nameimg;

                        }
                        if ($item->media->type == 'imagegallery') {

                            $images = $item->media->images;
                            $arrimages = explode("///", $images);
                            if ($arrimages[0]) {
                                $nameimg = JFile::getExt($arrimages[0]);
                                $count = strlen($nameimg);
                                $image_name = substr($arrimages[0], 0, -($count + 1));
                                $item->image = $image_name . '_' . $img . '.' . $nameimg;
                            }
                        }
                        if ($item->media->type == 'video') {

                            $images = $item->media->thumb;
                            $nameimg = JFile::getExt($images);
                            $count = strlen($nameimg);
                            $image_name = substr($images, 0, -($count + 1));
                            $item->image = $image_name . '_' . $img . '.' . $nameimg;
                        }
                        if ($item->media->type == 'audio') {

                            $images = $item->media->thumb;
                            $nameimg = JFile::getExt($images);
                            $count = strlen($nameimg);
                            $image_name = substr($images, 0, -($count + 1));
                            $item->image = $image_name . '_' . $img . '.' . $nameimg;

                        }
                        if(!$params -> get('show_quote',1) && $media AND $item->media->type == 'quote'){
                            unset($items[$i]);
                        }
                        if(!$params -> get('show_link',1) && $media AND $item->media->type == 'link'){
                            unset($items[$i]);
                        }
                    } else {
                        $item->image = null;
                    }
                    $item -> params = null;

                }

                return array_reverse($items);
            }
        }
        if ($content == 'joomla_content') {

            $query = "SELECT ct.*,ct.id as arid, ct.hits as hit, ct.title as artitle, ct.alias as aralias, cat.title as category_title, us.name as created_by_user, cat.alias as category_alias
                                     FROM #__content ct LEFT JOIN #__categories cat ON(ct.catid = cat.id)
                                     LEFT JOIN #__users us On(ct.created_by = us.id)
                                    $where ";
            $db->setQuery($query);

            $items = $db->loadObjectList();
//var_dump($items);die;
            $param_com = JComponentHelper::getComponent('com_content')->params;
            if ($items) {

                if ($content == 'joomla_content') {

                    foreach ($items as $item) {
                        $item->text = $item->introtext;
                        JPluginHelper::importPlugin('content');
                        $results = $dispatcher->trigger('onContentPrepare', array('com_content.article', &$item, &$param_com, 0));
                        $item->introtext = $item->text;
                        $item->event = new stdClass();
                        $results = $dispatcher->trigger('onContentAfterTitle', array('com_content.article', &$item, &$param_com, 0));
                        $item->event->afterDisplayTitle = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.article', &$item, &$param_com, 0));
                        $item->event->beforeDisplayContent = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.article', &$item, &$param_com, 0));
                        $item->event->afterDisplayContent = trim(implode("\n", $results));
                        $item->title = $item->artitle;

                        $item->media = null;

                        $item->hit = $item->hits;

                        $item->intro = $item->introtext;

                        $item->category = $item->category_title;
                        $item->author = $item->created_by_user;

                        $item->slug = $item->arid . ':' . $item->aralias;

                        $item->catslug = $item->catid . ':' . $item->category_alias;

                        $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));

                        if ($item->images) {

                            $images = new JRegistry;

                            $images->loadString($item->images);

                            $images = json_decode($item->images);

                            $item->image = $images->image_intro;

                            $item->image_full = $images->image_fulltext;

                        } else {
                            $item->image = null;
                        }
                    }

                    return $items;
                }
            }
        }
        return false;
    }
}

?>
