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

if (!class_exists('CParserUrls')) {

    /**
     * 
     */
    class CParserUrls extends CParserAbstract {

        /**
         * 
         * @return array
         */
        public function extract() {
            $regex = "((https?|ftp)\:\/\/)?"; // SCHEME
            $regex .= "([A-Za-z0-9+!*(),;?&=\$_.-]+(\:[A-Za-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
            $regex .= "([A-Za-z0-9-.]*)\.([A-Za-z]{2,4})"; // Host or IP
            $regex .= "(\:[0-9]{2,5})?"; // Port
            $regex .= "(\/([A-Za-z0-9+\$_-]\.?)+)*\/?"; // Path
            $regex .= "(\?[A-Za-z+&\$_.-][A-Za-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
            $regex .= "(#[A-Za-z_.-][A-Za-z0-9+\$_.-]*)?"; // Anchor
            
            //$return = preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $this->get('content'), $matchs);
            $return = preg_match_all("/$regex/", $this->get('content'), $matchs);

            if ($return !== false) {
                return array_unique($matchs[0]);
            }
            return array();
        }

    }

}