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

/**
 * Class exists checking
 */
if (!class_exists('CParserMetas')) {

    require_once JPATH_ROOT . '/components/com_community/libraries/vendor/simple_html_dom.php';

    /**
     * Extract data in <head>
     */
    class CParserMetas extends CParserAbstract {

        /**
         *
         * @var SimpleHtmlDOM
         */
        private $_dom;

        /**
         * @todo extract more information by know well about html standard
         * @return \JObject
         */
        public function extract() {
            $this->_dom = str_get_html($this->get('content'));

            /* Find <meta> */
            $metas = $this->_dom->find('meta');

            /* Our head metas object */
            $obj = new JRegistry();

            /**
             * Default
             */
            $title = $this->_dom->find('title', 0);
            if ($title) {
                $obj->def('title', $title->plaintext);
            }

            /**
             * <meta> extract
             * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
             */
            foreach ($metas as $meta) {
                $attributes = $meta->attr;
                /**
                 * Opengraph
                 * @todo We can improve to get more opengraph information
                 */
                if (isset($attributes['property'])) {
                    /* We need to work with same specific property */
                    switch ($attributes['property']) {
                        case 'og:image':
                            $images = $obj->get('image', array());
                            $images[] = $attributes['content'];
                            $obj->set('image', $images); /* alias of og:image */
                            $obj->set($attributes['property'], $images);
                            break;
                        case 'og:title':
                            /* This one will override default <title> */
                            $obj->set('title', $attributes['content']); /* alias of og:title */
                            $obj->set($attributes['property'], $attributes['content']);
                            break;
                        case 'og:description':
                            $obj->set('description', $attributes['content']); /* alias of of og:description */
                            $obj->set($attributes['property'], $attributes['content']);
                            break;
                        /* add more opengraph here if need */
                        default:
                            $obj->set($attributes['property'], $attributes['content']);
                            break;
                    }
                } else {
                    /* meta with attribute "name" */
                    if (isset($attributes['name'])) {
                        if (isset($attributes['content']))
                            $obj->set($attributes['name'], $attributes['content']);
                        if (isset($attributes['value']))
                            $obj->set($attributes['name'], $attributes['value']);
                    } elseif (isset($attributes['http-equiv'])) { /* meta with attribute "http-equiv" */
                        $obj->set($attributes['http-equiv'], $attributes['content']);
                    }
                    if (isset($attributes['itemprop'])) {
                        switch ($attributes['itemprop']) {
                            case 'image':
                                $images = $obj->get('image', array());
                                $images[] = $attributes['content'];
                                $obj->set('image', $images); /* alias of og:image */
                                break;
                        }
                    }
                }
            }

            /* If there are no og:image ( ugly site ) */
            if (!$obj->get('image')) {

                /* Find img */
                $images = $this->_dom->find('body img');
                $img = array();

                foreach ($images as $image) {
                    if (count($img) > 5) {
                        break;
                    }
                    $attributes = $image->attr;
                    isset($attributes['src']) ? $img[] = $attributes['src'] : "";
                }
                $obj->set('image', $img);
            }
            $obj->def('url', $this->get('url'));
            return $obj;
        }

    }

}