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

/* class exist checking */
if (!class_exists('CHeadHelper')) {

    /**
     * HTML head helper
     * This class provide method to set HTML head and opengraph metas
     * @since 3.0.1
     */
    class CHeadHelper {

        /**
         * Set page title
         * @param string $title
         */
        public static function setTitle($title) {
            $document = JFactory::getDocument();
            $document->setTitle($title);
            self::addOpengraph('og:title', $title);
        }

        /**
         * Set page description
         * @param string $content
         */
        public static function setDescription($content) {
            if ($content !== '') {
                $document = JFactory::getDocument();
                $document->setDescription($content);
                self::addOpengraph('og:description', $content);
            }
        }

        /**
         * Add Opengraph meta into head
         * @staticvar array $metas
         * @param string $property
         * @param string $content
         * @param boolean $isArray
         */
        public static function addOpengraph($property, $content, $isArray = false) {
            static $metas = array();
            $documentHTML = JFactory::getDocument();

            /* check if property already added */
            if (isset($metas[$property])) {
                /* only adding if it's array type */
                if ($isArray) {
                    $meta = '<meta property="' . $property . '" content="' . $content . '"/>';
                    $metas[$property][] = $meta;
                    $documentHTML->addCustomTag($meta);
                }
            } else { /* property is not exist than add it */
                $meta = '<meta property="' . $property . '" content="' . $content . '"/>';
                /* if this's array we'll store into array too */
                if ($isArray) {
                    $metas[$property][] = $meta;
                } else {
                    $metas[$property] = $meta;
                }
                $documentHTML->addCustomTag($meta);
            }
        }

        /**
         * Apply complete opengraph for a type
         * @param type $type
         * @param type $title
         * @param type $image
         */
        public static function setType($type, $title, $description = null, $images = null) {
            /**
             * We do get menu override title
             */
            $jinput = JFactory::getApplication()->input;
            /* Get link for special toolbar items */
            if ($jinput->get('view') === 'memberlist')
                $activeLink = 'index.php?option=' . $jinput->get('option') . '&view=' . $jinput->get('view') . '&listid=' . $jinput->get('listid');
            $activeLink = 'index.php?option=' . $jinput->get('option') . '&view=' . $jinput->get('view');

            $model = CFactory::getModel('Toolbar');
            $active = $model->getActiveId($activeLink);
            $activeMenu = JFactory::getApplication()->getMenu()->getItem($active);
            /* Fix for frontpage menu. If no valid activeMenu return than we use Joomla! getActive */
            if (is_null($activeMenu))
                $activeMenu = JFactory::getApplication()->getMenu()->getActive();
            if (is_object($activeMenu)) {
                $pageTitle = trim($activeMenu->params->get('page_title'));
                if ($pageTitle != '') {
                    $title = $pageTitle . ' - ' . $title;
                }
                $menuDescription = trim($activeMenu->params->get('menu-meta_description'));
                /**
                 * @todo Should we provide og:tags by keywords ?
                 */
            }

            self::addOpengraph('og:type', $type);
            self::addOpengraph('og:url', JURI::getInstance()->toString());
            self::addOpengraph('og:title', $title);
            /* Generate description if not provided */
            if ($description === null) {
                /* Use Joomla! global description if menu description is not provided */
                if (!isset($menuDescription) || $menuDescription == '') {
                    $description = JFactory::getConfig()->get('MetaDesc');
                } else {
                    $description = $menuDescription;
                }
            }
            if (trim($description) != '') {
                self::addOpengraph('og:description', trim($description));
            }
            if ($images !== null) {
                foreach ($images as $image) {
                    self::addOpengraph('og:image', $image, true);
                }
            }
            switch ($type) {
                /**
                 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/website
                 */
                case 'website':
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=frontpage'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=groups&task=display'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=photos&task=display'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=videos&task=display'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=events&task=display'), true);
                    self::addOpengraph('og:site_name', JFactory::getConfig()->get('sitename'));
                    break;
                /**
                 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/profile/
                 */
                case 'profile':
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=frontpage'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=groups&task=display'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=photos&task=display'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=videos&task=display'), true);
                    self::addOpengraph('og:see_also', CRoute::_('index.php?option=com_community&view=events&task=display'), true);
                    self::addOpengraph('og:site_name', JFactory::getConfig()->get('sitename'));
                    break;
            }
            $document = JFactory::getDocument();
            $document->setTitle($title);
        }

    }

}