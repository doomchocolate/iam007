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
if (!class_exists('modVideoCommentsHelper')) {

    /**
     *
     */
    class modVideoCommentsHelper {

        /**
         *
         * @param type $params
         * @return type
         */
        static public function getList(&$params) {
            $my = CFactory::getUser();

            /* Do query */
            $db = JFactory::getDBO();
            $query = 'SELECT * FROM ' . $db->quoteName('#__community_wall') . ' AS a '
                    . ' INNER JOIN ' . $db->quoteName('#__community_videos') . ' AS b '
                    . ' ON a.' . $db->quoteName('contentid') . '=b.' . $db->quoteName('id')
                    . ' WHERE a.' . $db->quoteName('type') . ' =' . $db->Quote('videos')
                    . 'AND b.' . $db->quoteName('status') . ' =' . $db->Quote('ready')
                    . 'ORDER BY a.' . $db->quoteName('date') . ' DESC ';
            $db->setQuery($query);
            $comments = $db->loadObjectList();

            //Once results are loaded, filter the count and the user premission level
            $counter = $params->get('count', 5);
            $data = array();

            foreach ($comments as $key => $comment) {
                /* permission checking */
                $permission = CPrivacy::getAccessLevel($my->id, $comment->creator);
                if ($permission >= $comment->permissions) {
                    $data[] = $comment;
                    if (--$counter == 0) {
                        break;
                    }
                }
            }

            return $data;
        }

    }

}