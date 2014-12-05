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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php');
require_once( JPATH_ROOT . '/components/com_community/helpers/string.php' );

class modActiveGroupsHelper {

    function getGroupsData(&$params) {
        $model = CFactory::getModel('groups');

        $db = JFactory::getDBO();

        $count = $params->get('count', '5');

        $query = 'SELECT *, (a.' . $db->quoteName('discusscount') . ' + a.' . $db->quoteName('wallcount') . ' ) AS count FROM ' . $db->quoteName('#__community_groups') . ' as a'
                . ' INNER JOIN ' . $db->quoteName('#__community_groups_members') . ' AS b ON a.' . $db->quoteName('id') . '= b.' . $db->quoteName('groupid')
                . ' WHERE a.' . $db->quoteName('published') . ' = ' . $db->Quote('1')
                . ' GROUP BY a.' . $db->quoteName('id')
                . ' ORDER BY ' . $db->quoteName('count') . ' DESC '
                . ' LIMIT 0, ' . $count;

        $db->setQuery($query);
        $rows = $db->loadObjectList();
        
        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        $_groups = array();

        /**
         * @todo No need use JTable to get group JTable object again
         */
        if (!empty($rows)) {
            foreach ($rows as $row) {

                $group = $model->getGroup($row->id);
                if ($group->id) {
                    $groupAvatar = JTable::getInstance('group', 'CTable');
                    $groupAvatar->bind($group);

                    $_obj = new stdClass();
                    $_obj->id = $group->id;
                    $_obj->name = $group->name;
                    $_obj->avatar = $groupAvatar->getThumbAvatar();
                    $_obj->totalMembers = count($model->getMembers($group->id, NULL, true, false, true));
                    //$_obj->totalMembers	= $model->getMembersCount($group->id);

                    $_groups[] = $_obj;
                }
            }
        }
        return $_groups;
    }

}
