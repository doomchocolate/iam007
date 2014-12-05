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

jimport('joomla.filesystem.file');

if (!class_exists('CStream')) {

    class CStream {

        public static function getActivities($filters = array()) {
            jimport('joomla.utilities.date');
            $config = CFactory::getConfig();
            $jinput = JFactory::getApplication()->input;


            $defFilter = array(
                'actid' => $jinput->get('actid', null, 'INT'),
            );
            $filters = array_merge($defFilter, $filters);
        }

        protected function _getData($filters) {
            $model = CFactory::getModel('Stream');
            /* Get activities array */
            $activities = $model->getActivities($filters);
        }

    }

}
