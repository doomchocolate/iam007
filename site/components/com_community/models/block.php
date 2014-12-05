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

jimport('joomla.application.component.model');
require_once( JPATH_ROOT . '/components/com_community/models/models.php' );

class CommunityModelBlock extends JCCModel {

    /**
     * Check for valid user ids to do block
     * @param type $userId
     * @param type $blockUserId
     * @return boolean
     */
    private function _isValidBlocking($userId, $blockUserId) {
        /* Guest have no permission to block anyone and can't block guest to */
        if ($userId == 0 || $blockUserId == 0) {
            return false;
        }
        /* You can block yourself */
        if ($userId == $blockUserId) {
            return false;
        }
        return true;
    }

    /**
     * Check blocking status between $userId & $blockUserId
     * @param type $userId
     * @param type $blockUserId
     * @param type $type
     * @return object|boolean
     */
    public function getBlockStatus($userId, $blockUserId) {
        if (!$this->_isValidBlocking($userId, $blockUserId))
            return false;

        $table = JTable::getInstance('Blocklist', 'CTable');        
        if ($table) {
            return $table->getBlocked($userId, $blockUserId);
        }
        return false;
    }

    /**
     * Do block user
     * @param type $myId
     * @param type $userId
     * @param type $type
     * @return boolean
     */
    public function blockUser($userId, $blockUserId, $type = 'block') {
        if ($this->_isValidBlocking($userId, $blockUserId)) {            
            /* Check if $blockedUserId is not blocked and also not same $userId */
            $block = $this->getBlockStatus($userId, $blockUserId);                  
            /* This user already be blocked before and now we just update type if it */
            if ($block != false) {
                /* Update type is same with current so we don't need waste time to do with it */
                if ($block->type == $type) {
                    return true;
                } else
                    $block->type = $type;
                return $block->store();
            }else {
            $table = JTable::getInstance('Blocklist', 'CTable');
            $table->userid = $userId;
            $table->blocked_userid = $blockUserId;
            $table->type = $type;
            return $table->store();
        } 
        }
        return false;
    }

    /**
     * 
     * @param type $myId
     * @param type $userId
     * @return boolean
     */
    public function removeBlockedUser($myId, $userId) {
        $db = $this->getDBO();

        $query = 'DELETE FROM ' . $db->quoteName('#__community_blocklist')
                . ' WHERE ' . $db->quoteName('blocked_userid') . '=' . $db->Quote($userId)
                . ' AND ' . $db->quoteName('userid') . '=' . $db->Quote($myId);

        $db->setQuery($query);

        $db->query();

        if ($db->getErrorNum()) {
            JError::raiseError(500, $db->stderr());
        }

        return true;
    }

    /**
     * Get blocked users
     * @param type $myId
     * @param type $type
     * @return array
     */
    public function getBlockedList($myId, $type = null) {
        $db = $this->getDBO();

        $query = "SELECT m.*,n.`name` FROM `#__community_blocklist` m "
                . "LEFT JOIN `#__users` n ON m.`blocked_userid`=n.`id` "
                . "WHERE m.`userid`=" . $db->Quote($myId) . " "
                . "AND m.`blocked_userid`!=0";
        /* Get blocked by type */
        if ($type !== null)
            $query .= " AND m.`type` = " . $db->quote($type);

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }

}
