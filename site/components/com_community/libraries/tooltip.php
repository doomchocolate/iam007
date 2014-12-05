<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
class CTooltip {
        static function cAvatarTooltip( &$row ){
                $user			= CFactory::getUser($row->id);
                return $user->getDisplayName();

                /*
                $numFriends		= $user->getFriendCount();

                if($user->isOnline()) 
                        $isOnline = '<img style="vertical-align:middle;padding: 0px 4px;" src="'.JURI::base().'components/com_community/assets/status_online.png" />'. JText::_('COM_COMMUNITY_ONLINE');
                else
                        $isOnline = '<img style="vertical-align:middle;padding: 0px 4px;" src="'.JURI::base().'components/com_community/assets/status_offline.png" />'.JText::_('COM_COMMUNITY_OFFLINE');

                //CFactory::load( 'helpers' , 'string');
                $html  = $row->getDisplayName() . '::';
                $html .= $user->getStatus().'<br/>';
                $html .= '<hr noshade="noshade" height="1"/>';
                $html .= $isOnline. ' | <img style="vertical-align:middle;padding: 0px 4px;" src="'.JURI::base().'components/com_community/assets/default-favicon.png" />'.JText::sprintf( (CStringHelper::isPlural($numFriends)) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $numFriends);
                return htmlentities($html, ENT_COMPAT, 'UTF-8');
                */
}
}

