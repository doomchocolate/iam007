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
if (!class_exists('CParserAbstract')) {

    /**
     * Parser base class
     * This class used to extract data from input HTML document
     */
    abstract class CParserAbstract extends JObject {

        /**
         * 
         * @param type $content
         * @return type
         */
        public function init($properties = array()) {
            $this->setProperties($properties);
            return $this;
        }

        public abstract function extract();
    }

}