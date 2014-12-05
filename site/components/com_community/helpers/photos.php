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


class CPhotosHelper{
    /**
     * Get photo ID of stream ID
     * @param int $streamID Stream id of photo (cover, avatar,...)
     * @return mixed Null when failed.
     */
    public static function getPhotoOfStream($streamID){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        /* Get stream params */
        $query->select($db->quoteName('params'))
              ->from($db->quoteName('#__community_activities'))
              ->where($db->quoteName('id') . '=' .  $db->quote($streamID));
        $db->setQuery($query);
        $params = $db->loadResult();
        /* Params is valid */
        if($params !== null){
            /* Decode JSON */
            $params = json_decode($params);
            /* Get photo ID */
            $query->clear()->select($db->quoteName('id'))
                  ->from($db->quoteName('#__community_photos'))
                  ->where($db->quoteName('image') . '=' .  $db->quote($params->attachment));
            $db->setQuery($query);
            return $db->loadResult();
        }        
        return null;
    }
}
