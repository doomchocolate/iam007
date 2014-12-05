<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
Class CActivitiesHelper {

    static protected $_permission = array(0 => 'COM_COMMUNITY_PRIVACY_PUBLIC',
        10 => 'COM_COMMUNITY_PRIVACY_PUBLIC',
        PRIVACY_MEMBERS => 'COM_COMMUNITY_PRIVACY_SITE_MEMBERS',
        PRIVACY_FRIENDS => 'COM_COMMUNITY_PRIVACY_FRIENDS',
        PRIVACY_PRIVATE => 'COM_COMMUNITY_PRIVACY_ME'
    );
    static protected $_icons = array(0 => 'joms-icon-globe',
        10 => 'joms-icon-globe',
        PRIVACY_MEMBERS => 'joms-icon-users',
        PRIVACY_FRIENDS => 'joms-icon-user',
        PRIVACY_PRIVATE => 'joms-icon-lock'
    );

    static public function getStreamPermissionHTML($privacy, $actorId = NULL) {

        $my = CFactory::getUser();

        if (($my->id != $actorId && !is_null($actorId) ) && !COwnerHelper::isCommunityAdmin()) {
            return;
        }

        $html = '<span class="joms-share-meta joms-share-privacy">' . JText::_(self::$_permission[$privacy]) . '</span>';
        $html .= '<div class="joms-privacy-dropdown joms-stream-privacy">';
        $html .= '<button type="button" class="dropdown-toggle" data-value="" data-toggle="dropdown"><span class="dropdown-value"><i class="' . self::$_icons[$privacy] . '"></i></span><span class="dropdown-caret joms-icon-caret-down"></span></button>';
        $html .= '<ul class="dropdown-menu">';


        $permissions = self::$_permission;
        unset($permissions[0]);
        foreach ($permissions as $value => $permission) {
            $html .= '<li><a href="javascript:" data-option-value="' . $value . '"><i class="' . self::$_icons[$value] . '"></i><span>' . JText::_($permission) . '</span></a></li>';
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * 
     * @param type $app
     * @param type $action
     * @return boolean
     */
    public static function isActionAllowed($app, $action) {
        /**
         * @todo Add more app element here
         */
        $apps = array(
            'photos.comment' => array(
                'comment' => false,
                'like' => true
            ),
            'groups.featured' => array(
                'comment' => false,
                'like' => true
            )
        );

        if (isset($apps[$app])) {
            if (isset($apps[$app][$action])) {
                return ($apps[$app][$action]);
            }
        }
        /* By default is app is not exists for now we do return TRUE */
        return true;
    }

    public static function hasTag($id, $message){
        $pattern	= '/@\[\[(\d+):([a-z]+):([^\]]+)\]\]/';
        preg_match_all( $pattern , $message , $matches );

        if( isset($matches[1]) && count($matches[1]) > 0 ){
            foreach($matches[1] as $match){
                if($match == $id){
                    return true;
                }
            }

        }

        return false;
    }

}
