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

class CUrl {

    static public function build($view, $task = '', $keys = null, $route = true) {
        // View cannot be empty. Assertion must be included here.
        CError::assert($view, '', '!empty', __FILE__, __LINE__);

        $url = 'index.php?option=com_community&view=' . $view;

        // Task might be optional
        $url .= (!empty($task) ) ? '&task=' . $task : '';

        if (!is_null($keys) && is_array($keys)) {
            foreach ($keys as $key => $value) {
                $url .= '&' . $key . '=' . $value;
            }
        }

        // Test if it needs JRoute
        if ($route) {
            return CRoute::_($url);
        }

        return $url;
    }

    function test() {
        return 'CUrl::test()';
    }

}

class CUrlHelper {

    /**
     * Create a link to a user profile
     *
     * @param	id		integer		ther user id
     * @param	route   bool		do we want to wrap it with Jroute func ?
     */
    static public function userLink($id, $route = true) {
        $url = 'index.php?option=com_community&view=profile&userid=' . $id;
        if ($route)
            $url = CRoute::_($url);
        return $url;
    }

    /**
     * Create a link to a group page
     *
     * @param	id		integer		ther user id
     * @param	route   bool		do we want to wrap it with Jroute func ?
     */
    static public function groupLink($id, $route = true) {
        $url = 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $id;
        if ($route)
            $url = CRoute::_($url);
        return $url;
    }

    /**
     * Create a link to a event page
     *
     * @param	id		integer		ther user id
     * @param	route   bool		do we want to wrap it with Jroute func ?
     */
    static public function eventLink($id, $route = true) {
        $url = 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $id;
        if ($route)
            $url = CRoute::_($url);
        return $url;
    }

    /**
     * Create a link to a group event page
     *
     * @param	id		integer		their event id
     * @param	groupid integer		their group id
     * @param	route   bool		do we want to wrap it with Jroute func ?
     */
    static public function groupeventLink($id, $groupid, $route = true) {
        $url = 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $id . '&groupid=' . $groupid;
        if ($route)
            $url = CRoute::_($url);
        return $url;
    }

    /** Method to get avatar's uri
     *
     */
    static public function avatarURI($file = '', $default = '') {
        $config = CFactory::getConfig();

        // Default avatar
        if (empty($file) || !JFile::exists(JPATH_ROOT . '/' . $file)) {
            $template = new CTemplateHelper();
            $asset = $template->getTemplateAsset($default, 'images');
            $uri = $asset->url;
            return $uri;
        }

        // Strip cdn path if exists.
        // Note: At one point, cdn path was stored along with the avatar path
        //       in the db which is the mistake we are trying to rectify here.
        $file = str_ireplace($config->get('imagecdnpath'), '', $file);

        // CDN or local
        $baseUrl = $config->get('imagebaseurl') or
                $baseUrl = JURI::root();
        $uri = str_replace('\\', '/', rtrim($baseUrl, '/') . '/' . ltrim($file, '/'));
        return $uri;
    }

    static public function httpsURI($uri = '') {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTPS'])) {
                if (strtolower($_SERVER['HTTPS']) == "on") {
                    $uri = str_replace("http://", "https://", $uri);
                }
            }
        } else if (isset($_SERVER['REQUEST_URI'])) {
            // use REQUEST_URI method
            if (strpos($_SERVER['REQUEST_URI'], 'https://') === FALSE) {
                // not a https
            } else {
                $uri = str_replace("http://", "https://", $uri);
            }
        }

        return $uri;
    }

    /**
     * @todo get coverUri and avatarUri should use same function to prevent duplicate code
     * @param type $file
     * @param type $default
     * @return string
     */
    static public function coverURI($file = '', $default = '') {
        $config = CFactory::getConfig();

        /* Default cover */        
        if (empty($file) || !JFile::exists(JPATH_ROOT . '/' . $file)) {
            $template = new CTemplateHelper();
            $asset = $template->getTemplateAsset($default, 'images');
            $uri = $asset->url;
            return $uri;
        }
        
        $file = str_ireplace($config->get('imagecdnpath'), '', $file);

        // CDN or local
        $baseUrl = $config->get('imagebaseurl') or
                $baseUrl = JURI::root();
        $uri = str_replace('\\', '/', rtrim($baseUrl, '/') . '/' . ltrim($file, '/'));
        return $uri;
    }

}

/**
 * Deprecated since 1.8
 * Use CUrlHelper::userLink instead.
 */
function cUserLink($id, $route = true) {
    return CUrlHelper::userLink($id, $route);
}

/**
 * Deprecated since 1.8
 * Use CUrlHelper::groupLink instead.
 */
function cGroupLink($id, $route = true) {
    return CUrlHelper::groupLink($id, $route);
}

/**
 * Deprecated since 1.8
 * Use CUrlHelper::groupLink instead.
 */
function cEventLink($id, $route = true) {
    return CUrlHelper::eventLink($id, $route);
}
