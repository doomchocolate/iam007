<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class exists checking
 */
if (!class_exists('CCrawler')) {

    /**
     * Crawl class
     * Used to crawl data from external site
     */
    abstract class CCrawler extends JObject {

        /**
         * Default user
         */
        const COMMUNITY_USER_AGENT = "Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36";

        /**
         * Array of redirect codes
         * @var array 
         */
        protected $_redirects = array(300, 301, 302, 303, 304, 305, 306, 307);

        /**
         * Get crawler class
         * @param type $type
         * @return \object|boolean
         */
        public static function getCrawler($type = 'curl') {
            include_once __DIR__ . '/crawlers/' . $type . '.php';
            $className = 'CCrawler' . ucfirst($type);
            if (class_exists($className)) {
                $class = new $className();
                return $class;
            }
            return false;
        }

        /**
         * Build url with parameters from data array
         * @param string $url
         * @param array $data
         * @return string
         */
        protected function _buildUrl($url, $data = array()) {
            return $url . (empty($data) ? '' : '?' . http_build_query($data));
        }

        /**
         * Class init
         */
        public abstract function init();

        /**
         * Add header data to request
         */
        public abstract function addHeader($header);

        /**
         * Execute crawl
         */
        public abstract function crawl($type, $url = null, $data = array());
    }

}