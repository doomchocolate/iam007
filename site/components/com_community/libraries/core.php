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

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.event.dispatcher');

// Define autoload function
spl_autoload_register('CFactory::autoload_models');
spl_autoload_register('CFactory::autoload_libraries');
spl_autoload_register('CFactory::autoload_helpers');
spl_autoload_register('CFactory::autoload_events');
spl_autoload_register('CFactory::autoload_tables');

// @todo: to be removed once the functions has be autoloaded
require_once (JPATH_ROOT . '/components/com_community/defines.community.php');

/**
 * Register Joomla! autoloading
 */
/* Register our APIs classes */
JLoader::discover('CApi', COMMUNITY_PATH_SITE . '/apis');
JLoader::discover('C', COMMUNITY_PATH_SITE . '/libraries');

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_community/tables');
JTable::addIncludePath(COMMUNITY_COM_PATH . '/tables');

// In case our core.php files are loaded by 3rd party extensions, we need to define the plugin path environments for Zend.
$paths = explode(PATH_SEPARATOR, get_include_path());

JFactory::getLanguage()->load('com_community');

class CFactory {

    /**
     * Function to allow caller to get a user object while
     * it is not authenticated provided that it has a proper tokenid
     * */
    public function getUserFromTokenId($tokenId, $userId) {
        $db = JFactory::getDBO();

        $query = 'SELECT COUNT(*) '
                . 'FROM ' . $db->quoteName('#__community_photos_tokens') . ' '
                . 'WHERE ' . $db->quoteName('token') . '=' . $db->Quote($tokenId) . ' '
                . 'AND ' . $db->quoteName('userid') . '=' . $db->Quote($userId);

        $db->setQuery($query);

        $count = $db->loadResult();

        // We can assume that the user parsed in correct token and userid. So,
        // we return them the proper user object.
        if ($count >= 1) {
            $user = CFactory::getUser($userId);

            return $user;
        }

        // If it doesn't bypass our tokens, we assume they are really trying
        // to hack or got in here somehow.
        $user = CFactory::getUser(null);

        return $user;
    }

    /**
     * Load multiple users at a same time to save up on the queries.
     * @return	boolean		True upon success
     * @param	Array	$userIds	An array of user ids to be loaded.
     */
    static public function loadUsers($userIds) {
        if (empty($userIds)) {
            return;
        }

        $ids = implode(",", $userIds);
        $db = JFactory::getDBO();
        $query = 'SELECT  '
                . ' a.' . $db->quoteName('userid') . ' as _userid ,'
                . ' a.' . $db->quoteName('status') . ' as _status , '
                . ' a.' . $db->quoteName('points') . ' as _points, '
                . ' a.' . $db->quoteName('posted_on') . ' as _posted_on, '
                . ' a.' . $db->quoteName('avatar') . ' as _avatar , '
                . ' a.' . $db->quoteName('thumb') . ' as _thumb , '
                . ' a.' . $db->quoteName('invite') . ' as _invite, '
                . ' a.' . $db->quoteName('params') . ' as _cparams,  '
                . ' a.' . $db->quoteName('view') . ' as _view, '
                . ' a.' . $db->quoteName('friends') . ' as _friends, '
                . ' a.' . $db->quoteName('groups') . ' as _groups, '
                . ' a.' . $db->quoteName('alias') . '	as _alias, '
                . ' a.' . $db->quoteName('profile_id') . ' as _profile_id, '
                . ' a.' . $db->quoteName('friendcount') . ' as _friendcount, '
                . ' a.' . $db->quoteName('storage') . ' as _storage, '
                . ' a.' . $db->quoteName('watermark_hash') . ' as _watermark_hash, '
                . ' a.' . $db->quoteName('search_email') . ' AS _search_email, '
                . ' s.' . $db->quoteName('userid') . ' as _isonline, u.* '
                . ' FROM ' . $db->quoteName('#__community_users') . ' as a '
                . ' LEFT JOIN ' . $db->quoteName('#__users') . ' u '
                . ' ON u.' . $db->quoteName('id') . '=a.' . $db->quoteName('userid')
                . ' LEFT OUTER JOIN ' . $db->quoteName('#__session') . 's '
                . ' ON s.' . $db->quoteName('userid') . '=a.' . $db->quoteName('userid')
                . ' WHERE a.' . $db->quoteName('userid') . ' IN (' . $ids . ')';

        $db->setQuery($query);
        $objs = $db->loadObjectList();

        foreach ($objs as $obj) {
            $user = new CUser($obj->_userid);
            $isNewUser = $user->init($obj);

            $user->getThumbAvatar();

            // technically, we should not fetch any new user here
            if ($isNewUser) {
                // New user added to jomSocial database
                // trigger event onProfileInit
                $appsLib = CAppPlugins::getInstance();
                $appsLib->loadApplications();

                $args = array();
                $args[] = $user;
                $appsLib->triggerEvent('onProfileCreate', $args);
            }

            CFactory::getUser($obj->_userid, $user);
        }
    }

    /**
     * Retrieves a CUser object given the user id.
     *
     * @return	CUser	A CUser object
     * @param	int		$id		A user id (optional)
     * @param	CUser	$obj	An existing user object (optional)
     */
    public static function getUser($id = null, $obj = null) {
        static $instances = array();

        if ($id != 0 && !is_null($obj)) {
            $instances[$id] = $obj;
            return;
        }

        if ($id === 0) {
            $user = JFactory::getUser(0);
            $id = $user->id;
        } else {
            if ($id == null) {
                $user = JFactory::getUser();
                $id = $user->id;
            }

            if ($id != null && !is_numeric($id)) {
                $db = JFactory::getDBO();
                $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users') . ' WHERE UCASE(' . $db->quoteName('username') . ') like UCASE(' . $db->Quote($id) . ')';
                $db->setQuery($query);
                $id = $db->loadResult();
            }
        }

        if (empty($instances[$id])) {
            if (!is_numeric($id) && !is_null($id)) {
                JError::raiseError(500, JText::sprintf('COM_COMMUNITY_CANNOT_LOAD_USER', $id));
            }

            $instances[$id] = new CUser($id);
            $isNewUser = $instances[$id]->init();
            $instances[$id]->getThumbAvatar();

            if ($isNewUser) {
                // New user added to jomSocial database
                // trigger event onProfileInit
                $appsLib = CAppPlugins::getInstance();
                $appsLib->loadApplications();

                $args = array();
                $args[] = $instances[$id];
                $appsLib->triggerEvent('onProfileCreate', $args);
            }

            // Guess need to have avatar as well.
            if ($id == 0) {
                JFactory::getLanguage()->load('com_community');

                $instances[$id]->name = JText::_('COM_COMMUNITY_ACTIVITIES_GUEST');
                $instances[$id]->username = JText::_('COM_COMMUNITY_ACTIVITIES_GUEST');
                $instances[$id]->_avatar = 'components/com_community/assets/default.jpg';
                $instances[$id]->_thumb = 'components/com_community/assets/default_thumb.jpg';
            }
        }
        return $instances[$id];
    }

    /**
     * Retrieves CConfig configuration object
     * @return	object	CConfig object
     * @param
     */
    public static function getConfig() {
        return CConfig::getInstance();
    }

    /**
     * Return fast, memory-based JCache object. For now, only APC cache
     * will be supported
     */
    public static function getFastCache($type = 'output') {
        static $instance = null;

        if (is_null($instance)) {
            jimport('joomla.cache.cache');

            // Check for APC
            $options = array('storage' => 'apc', 'lifetime' => '300');
            if (extension_loaded('apc')) {
                $options['caching'] = true;
            } else {
                // Disable caching
                $options['caching'] = false;
            }

            $cache = JCache::getInstance($type, $options);
            $instance = new CFastCache($cache);
        }

        return $instance;
    }

    /**
     * Returns a configured version of Zend_Cache object
     *
     * @param	string	$frontendEngine		Frontend engine to use
     * @param	string	$frontendOptions	Additional options for the frontend object
     * @return	object	Zend_Cache object
     */
    public function deprecated_getCache($frontendEngine, $frontendOptions = array()) {
        $app = JFactory::getApplication();
        //$jConfig	= JFactory::getConfig();
        // If Joomla cache folder does not exist, try to create them.
        if (!JFolder::exists(JPATH_ROOT . '/cache')) {
            JFolder::create(JPATH_ROOT . '/cache');
            JFile::copy(JPATH_ROOT . '/components/com_community/index.html', JPATH_ROOT . '/cache/index.html');
        }

        // Configure additional options for frontend
        $defaultOptions = array(
            'lifetime' => 86400, // cache lifetime of 24 hours (time is in seconds)
            'automatic_serialization' => true, //default is false
            'cache_id_prefix' => '_com_community', //prefix
            'caching' => $app->getCfg('caching') //$jConfig->getValue('caching') // enable or disable caching
        );

        switch ($frontendEngine) {
            case 'Core':
                break;
            case 'Output':
                break;
            case 'Class':
                break;
            case 'Function':
                break;
        }
        $frontendOptions = array_merge($defaultOptions, $frontendOptions);

        $backendOptions = array();
        $backendEngine = '';

        //switch($jConfig->getValue('cache_handler')){
        switch ($app->getCfg('cache_handler')) {
            case 'file':
                $backendOptions = array(
                    'cache_dir' => JPATH_ROOT . '/cache/');
                $backendEngine = 'File';
                break;
            case 'apc':
                $backendOptions = array(
                    'cache_dir' => JPATH_ROOT . '/cache');
                $backendEngine = 'Apc';
                break;

            // not supportted for now, return a dummy cache object
            case 'xcache':
            case 'eaccelerator':
            case 'memcache':
            default:
                $backendOptions = array(
                    'cache_dir' => JPATH_ROOT . '/cache/');
                $backendEngine = 'File';
                $frontendOptions['caching'] = FALSE;
                break;
        }

        $zend_cache = Zend_Cache::factory($frontendEngine, $backendEngine, $frontendOptions, $backendOptions);
        return $zend_cache;
    }

    /**
     * Register autoload functions, using JImport::JLoader
     */
    public function autoload() {
        //JLoader::register('classname', 'filename');
    }

    /**
     * Return CInput instance
     */
    static public function getInput() {
        static $inputInstance = null;
        if (!isset($inputInstance)) {
            $inputInstance = new CInput;
        }
        return $inputInstance;
    }

    /**
     * Return the model object, responsible for all db manipulation. Singleton
     *
     * @param	string		model name
     * @param	string		any class prefix
     * @return	object		model object
     */
    static public function getModel($name = '', $prefix = '', $config = array()) {
        static $modelInstances = null;

        if (!isset($modelInstances)) {
            $modelInstances = array();
            include_once(JPATH_ROOT . '/components/com_community/libraries/error.php');
        }

        if (!isset($modelInstances[$name . $prefix])) {
            include_once( JPATH_ROOT . '/components/com_community/models/models.php');

            // @rule: We really need to test if the file really exists.
            $modelFile = JPATH_ROOT . '/components/com_community/models/' . strtolower($name) . '.php';
            if (!JFile::exists($modelFile)) {
                $modelInstances[$name . $prefix] = false;
            } else {
                include_once( $modelFile );
                $classname = $prefix . 'CommunityModel' . $name;
                $modelInstances[$name . $prefix] = new $classname;
            }
        }

        return $modelInstances[$name . $prefix];
    }

    // @getBookmarks deprecated.
    // since 1.5
    static public function getBookmarks($uri) {
        static $bookmark = null;

        if (is_null($bookmark)) {
            //CFactory::load( 'libraries' , 'bookmarks' );
            $bookmark = new CBookmarks($uri);
        }
        return $bookmark;
    }

    static public function getToolbar() {
        // We need to load the language code here since some plugin
        // apparently modify this before language code is loaded
        JFactory::getLanguage()->load('com_community');

        static $toolbar = null;

        if (is_null($toolbar)) {
            //CFactory::load( 'libraries' , 'toolbar' );

            $toolbar = new CToolbar();
        }
        return $toolbar;
    }

    /**
     * Return the view object, responsible for all db manipulation. Singleton
     *
     * @param	string		model name
     * @param	string		any class prefix
     * @return	object		model object
     */
    static public function getView($name = '', $prefix = '', $viewType = '') {
        static $viewInstances = null;

        if (!isset($viewInstances)) {
            $viewInstances = array();
            include_once(JPATH_ROOT . '/components/com_community/libraries/error.php');
        }

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $viewType = $jinput->request->get('format', 'html', 'NONE'); //JRequest::getVar('format', 'html', 'REQUEST');
        // if(CTemplate::mobileTemplate())
        // 	$viewType = 'mobile';

        if ($viewType == 'json')
            $viewType = 'html';

        if (!isset($viewInstances[$name . $prefix . $viewType])) {
            jimport('joomla.filesystem.file');

            $viewFile = JPATH_COMPONENT . '/views' . '/' . $name . '/view.' . $viewType . '.php';

            if (JFile::exists($viewFile)) {
                include_once( $viewFile );
            } else {
                //@rule: when feed is not available, we include the main view file.
                if ($viewType == 'feed') {
                    include_once( JPATH_COMPONENT . '/views' . '/' . $name . '/view.html.php' );
                } else {
                    return JError::raiseError(404, JText::_('COM_COMMUNITY_VIEW_NOT_FOUND'));
                }
            }

            $classname = $prefix . 'CommunityView' . ucfirst($name);
            if (!class_exists($classname)) {
                return JError::raiseError(404, JText::_('COM_COMMUNITY_VIEW_NOT_FOUND'));
            }

            $viewInstances[$name . $prefix . $viewType] = new $classname;
        }

        return $viewInstances[$name . $prefix . $viewType];
    }

    /**
     * return the currently viewed user profile object,
     * for now, just return an object with username, id, email
     * @deprecated since 1.6.x
     */
    static public function getActiveProfile() {

        $my = JFactory::getUser();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $uid = $jinput->request->get('userid', 0, 'INT'); //JRequest::getVar('userid', 0, 'REQUEST');

        if ($uid == 0) {
            $uid = JRequest::getVar('activeProfile', null, 'COOKIE');
        }

        $obj = CFactory::getUser($uid);

        return $obj;
    }

    /**
     * Returns the current user requested via JRequest::getVar. 'userid' should be part of the request
     * parameter
     *
     * @param
     * @return	object	Current CUser object
     */
    static public function getRequestUser() {
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $id = $jinput->get('userid', '', 'INT');

        return CFactory::getUser($id);
    }

    /**
     * Return standard joomla filter objects
     *
     * @param	boolean		$allowHTML	True if you want to allow safe HTML through
     * @param	boolean		$simpleHTML	True if you want to allow simple HTML tags
     * @return	object		JFilterInput object
     */
    static public function getInputFilter($allowHTML = false, $simpleHTML = false) {
        jimport('joomla.filter.filterinput');
        $safeTags = array();
        $safeAttr = array();

        if ($allowHTML) {
            $safeAttr = array('abbr', 'accept', 'accept-charset', 'accesskey', 'action', 'align', 'alt', 'axis', 'border', 'cellpadding', 'cellspacing', 'char', 'charoff', 'charset', 'checked', 'cite', 'class', 'clear', 'cols', 'colspan', 'color', 'compact', 'coords', 'datetime', 'dir', 'disabled', 'enctype', 'for', 'frame', 'headers', 'height', 'href', 'hreflang', 'hspace', 'id', 'ismap', 'label', 'lang', 'longdesc', 'maxlength', 'media', 'method', 'multiple', 'name', 'nohref', 'noshade', 'nowrap', 'prompt', 'readonly', 'rel', 'rev', 'rows', 'rowspan', 'rules', 'scope', 'selected', 'shape', 'size', 'span', 'src', 'start', 'style', 'summary', 'tabindex', 'target', 'title', 'type', 'usemap', 'valign', 'value', 'vspace', 'width');
            $safeTags = array('a', 'abbr', 'acronym', 'address', 'area', 'b', 'big', 'blockquote', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'fieldset', 'font', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'map', 'menu', 'ol', 'optgroup', 'option', 'p', 'pre', 'q', 's', 'samp', 'select', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var');
        }
        $safeHtmlFilter = JFilterInput::getInstance($safeTags, $safeAttr);

        return $safeHtmlFilter;
    }

    /**
     * Set current active profile
     * @param	integer id	Current user id
     * @deprecated	since 1.6.x
     */
    static public function setActiveProfile($id = '') {
        if (empty($id)) {
            $my = CFactory::getUser();
            $id = $my->id;
        }

        $app = JFactory::getApplication();
        $lifetime = $app->getCfg('lifetime');

        setcookie('activeProfile', $id, time() + ($lifetime * 60), '/');
    }

    /**
     * @deprecated since 1.6.x
     */
    static public function unsetActiveProfile() {

        $app = JFactory::getApplication();
        $lifetime = $app->getCfg('lifetime');

        setcookie('activeProfile', false, time() + ($lifetime * 60 ), '/');
    }

    /**
     * Sets the current requested URI in the cookie so the system knows where it should
     * be redirected to.
     *
     * @param
     * @return
     */
    static public function setCurrentURI() {
        $uri = JFactory::getURI();
        $current = $uri->toString();

        setcookie('currentURI', $current, time() + 60 * 60 * 24, '/');
    }

    /**
     * Gets the last accessed URI from the cookie if user is coming from another page.
     *
     * @param
     * @return	string	The last accessed URI in the cookie (E.g http://site.com/index.php)
     */
    static public function getLastURI() {
        $uri = JRequest::getVar('currentURI', null, 'COOKIE');

        if (is_null($uri)) {
            $uri = JURI::root();
        }
        return $uri;
    }

    /**
     * Return the view object, responsible for all db manipulation. Singleton
     *
     * @param	string		type	libraries/helper
     * @param	string		name 	class prefix
     */
    static public function load($type, $name) {
        if ($type == 'models')
            return;

        include_once(JPATH_ROOT . '/components/com_community/libraries/error.php');

        // Test if file really exists before php throws errors.
        $path = JPATH_ROOT . '/components/com_community/' . $type . '/' . strtolower($name) . '.php';
        if (JFile::exists($path)) {
            include_once( $path );
        }

        // If it is a library, we call the object and call the 'load' method
        if ($type == 'libraries') {
            $classname = 'C' . $name;
            if (class_exists($classname)) {
                // OK, class exist
            }
        }
    }

    /**
     * Autoload class
     */
    static function autoload_models($classname) {
        // These interfaces doesn't have specific naming logic. Define them manually
        $classes = array(
            'CTaggable_Item'                 => '/models/tags.php',
            'CGeolocationInterface'          => '/models/models.php',
            'CLimitsInterface'               => '/models/models.php',
            'CommunityModelActivities'       => '/models/activities.php',
            'CommunityModelApplications'     => '/models/applications.php',
            'CommunityModelApps'             => '/models/apps.php',
            'CommunityModelAvatar'           => '/models/avatar.php',
            'CommunityModelBlock'            => '/models/block.php',
            'CommunityModelBulletins'        => '/models/bulletins.php',
            'CommunityModelCommunity'        => '/models/community.php',
            'CommunityModelConfiguration'    => '/models/configurations.php',
            'CommunityModelConnect'          => '/models/connect.php',
            'CommunityModelDiscussions'      => '/models/discussion.php',
            'CommunityModelEventCategories'  => '/models/eventcategories.php',
            'CommunityModelEvents'           => '/models/events.php',
            'CommunityModelFeatured'         => '/models/featured.php',
            'CommunityModelFiles'            => '/models/files.php',
            'CommunityModelFriends'          => '/models/friends.php',
            'CommunityModelGroupCategories'  => '/models/groupcategories.php',
            'CommunityModelGroups'           => '/models/groups.php',
            'CommunityModelInbox'            => '/models/inbox.php',
            'CommunityModelLike'             => '/models/like.php',
            'CommunityModelMailq'            => '/models/mailq.php',
            'CommunityModelMailqueue'        => '/models/mailqueue.php',
            'CommunityModelMemberlist'       => '/models/memberlist.php',
            'JCCModel'                       => '/models/models.php',
            'CommunityModelMultiProfile'     => '/models/multiprofile.php',
            'CommunityModelNetwork'          => '/models/network.php',
            'CommunityModelNotification'     => '/models/notification.php',
            'CommunityModelPhotos'           => '/models/photos.php',
            'CommunityModelPhotoTagging'     => '/models/phototagging.php',
            'CommunityModelProfile'          => '/models/profile.php',
            'CommunityModelProfiles'         => '/models/profiles.php',
            'CommunityModelRegister'         => '/models/register.php',
            'CommunityModelReporters'        => '/models/reporters.php',
            'CommunityModelReports'          => '/models/reports.php',
            'CommunityModelSearch'           => '/models/search.php',
            'CommunityModelStatus'           => '/models/status.php',
            'CommunityModelTags'             => '/models/tags.php',
            'CommunityModelToolbar'          => '/models/toolbar.php',
            'CommunityModelUser'             => '/models/user.php',
            'CommunityModelUserPoints'       => '/models/userpoints.php',
            'CommunityModelUsers'            => '/models/users.php',
            'CommunityModelVideos'           => '/models/videos.php',
            'CommunityModelVideosCategories' => '/models/videoscategories.php',
            'CommunityModelVideoTagging'     => '/models/videotagging.php',
            'CommunityModelWall'             => '/models/wall.php'
        );

        if (array_key_exists($classname, $classes)) {
            require_once(JPATH_ROOT . '/components/com_community' . $classes[$classname]);
        }
    }

    /**
     * Autoload class
     */
    static function autoload_libraries($classname) {
        $classes = array(
            'CInput'                   => '/libraries/input.php',
            'CAppPlugins'              => '/libraries/apps.php',
            'CError'                   => '/libraries/error.php',
            'CFastCache'               => '/libraries/cache.php',
            'CStorage'                 => '/libraries/storage.php',
            'CStorageMethod'           => '/libraries/storage.php',
            'CUser'                    => '/libraries/user.php',
            'CParameter'               => '/libraries/parameter.php',
            'CActivityStream'          => '/libraries/activities.php',
            'CActivities'              => '/libraries/activities.php',
            'CTemplate'                => '/libraries/template.php',
            'CActivities'              => '/libraries/activities.php',
            'CAdminstreams'            => '/libraries/adminstreams.php',
            'CAkismet'                 => '/libraries/akismet.php',
            'Akismet'                  => '/libraries/akismet_base.php',
            'CAppPlugins'              => '/libraries/apps.php',
            'CAvatar'                  => '/libraries/avatar.php',
            'blockUser'                => '/libraries/block.php',
            'CBookmarks'               => '/libraries/bookmarks.php',
            'CBrowser'                 => '/libraries/browser.php',
            'CFastCache'               => '/libraries/cache.php',
            'CCarousel'                => '/libraries/carousel.php',
            'CComment'                 => '/libraries/comment.php',
            'CConnectionLibrary'       => '/libraries/connection.php',
            'CCron'                    => '/libraries/cron.php',
            'CDatetime'                => '/libraries/datetime.php',
            'CEditor'                  => '/libraries/editor.php',
            'CEmailTypes'              => '/libraries/emailtypes.php',
            'CError'                   => '/libraries/error.php',
            'CEvents'                  => '/libraries/events.php',
            'CFacebook'                => '/libraries/facebook.php',
            'CFeatured'                => '/libraries/featured.php',
            'CFiles'                   => '/libraries/files.php',
            'CFilesLibrary'            => '/libraries/files.php',
            'CFilterBar'               => '/libraries/filterbar.php',
            'CFormElement'             => '/libraries/formelement.php',
            'CFriends'                 => '/libraries/friends.php',
            'CGroups'                  => '/libraries/groups.php',
            'CICal'                    => '/libraries/ical.php',
            'CImage'                   => '/libraries/image.php',
            'CInvitationMail'          => '/libraries/invitation.php',
            'CInvitation'              => '/libraries/invitation.php',
            'CJForm'                   => '/libraries/jform.php',
            'CJSMin'                   => '/libraries/jsmin.php',
            'JSNetworkLibrary'         => '/libraries/jsnetwork.php',
            'CKarma'                   => '/libraries/karma.php',
            'CLike'                    => '/libraries/like.php',
            'CLimitsLibrary'           => '/libraries/limits.php',
            'CMailq'                   => '/libraries/mailq.php',
            'CMapping'                 => '/libraries/mapping.php',
            'CMessaging'               => '/libraries/messaging.php',
            'CMiniHeader'              => '/libraries/miniheader.php',
            'CDocumentMobile'          => '/libraries/mobile.php',
            'CMyBlog'                  => '/libraries/myblog.php',
            'CNotification'            => '/libraries/notification.php',
            'CNotificationLibrary'     => '/libraries/notification.php',
            'CNotificationTypes'       => '/libraries/notificationtypes.php',
            'CParameter'               => '/libraries/parameter.php',
            'CPhotos'                  => '/libraries/photos.php',
            'CPhotoTagging'            => '/libraries/phototagging.php',
            'CPrivacy'                 => '/libraries/privacy.php',
            'CProfile'                 => '/libraries/profile.php',
            'CProfileLibrary'          => '/libraries/profile.php',
            'CommunityRelationshipLib' => '/libraries/relationship.php',
            'CReporting'               => '/libraries/reporting.php',
            'CReportingLibrary'        => '/libraries/reporting.php',
            'CSpamFilter'              => '/libraries/spamfilter.php',
            'CStaticmap'               => '/libraries/staticmap.php',
            'CStorage'                 => '/libraries/storage.php',
            #'CStorage'                => '/libraries/streamer.php',
            'CTags'                    => '/libraries/tags.php',
            'CAdvanceSearch'           => '/libraries/advancesearch.php',
            'CToolbar'                 => '/libraries/toolbar.php',
            'cAvatarTooltip'           => '/libraries/tooltip.php',
            #'CAdvanceSearch'          => '/libraries/url.php',
            'CUser'                    => '/libraries/user.php',
            'CUserPoints'              => '/libraries/userpoints.php',
            'CUserStatus'              => '/libraries/userstatus.php',
            'CUserStatusCreator'       => '/libraries/userstatus.php',
            'CVideos'                  => '/libraries/videos.php',
            'CVideoTagging'            => '/libraries/videotagging.php',
            'CWallLibrary'             => '/libraries/wall.php',
            'CWall'                    => '/libraries/wall.php',
            'CWindow'                  => '/libraries/window.php',
            'CZencoderOutput'          => '/libraries/zencoder.php',
            'CMinitip'                 => '/libraries/minitip.php',
            'CVideoLibrary'            => '/libraries/videos.php',
            'CToolbarLibrary'          => '/libraries/toolbar.php',
            'CTooltip'                 => '/libraries/tooltip.php',
            'CCommentInterface'        => '/libraries/comment.php',
            'CFieldsBirthdate'         => '/libraries/fields/birthdate.php',
            'CFieldsCheckbox'          => '/libraries/fields/checkbox.php',
            'JFormFieldCMultiList'     => '/libraries/fields/cmultilist.php',
            'CFieldsCountry'           => '/libraries/fields/country.php',
            'CFieldsDate'              => '/libraries/fields/date.php',
            'CFieldsEmail'             => '/libraries/fields/email.php',
            'CFieldsGender'            => '/libraries/fields/gender.php',
            'CFieldsLabel'             => '/libraries/fields/label.php',
            'CFieldsList'              => '/libraries/fields/list.php',
            'CProfileField'            => '/libraries/fields/profilefield.php',
            'CFieldsRadio'             => '/libraries/fields/radio.php',
            'CFieldsSelect'            => '/libraries/fields/select.php',
            'CFieldsSingleselect'      => '/libraries/fields/singleselect.php',
            'CFieldsText'              => '/libraries/fields/text.php',
            'CFieldsTextarea'          => '/libraries/fields/textarea.php',
            'CFieldsTime'              => '/libraries/fields/time.php',
            'CFieldsUrl'               => '/libraries/fields/url.php',
            'FacebookApiException'     => '/libraries/facebook/base_facebook.php',
            'BaseFacebook'             => '/libraries/facebook/base_facebook.php',
            'FacebookLib'              => '/libraries/facebook/facebook.php',
            'File_CStorage'            => '/libraries/storage/file.php',
            'S3_CStorage'              => '/libraries/storage/s3.php',
            'CTableStorageS3'          => '/libraries/storage/s3.php',
            'S3'                       => '/libraries/storage/s3_lib.php',
            'S3Request'                => '/libraries/storage/s3_lib.php',
            'tmhOAuth'                 => '/libraries/twitter/tmhOAuth.php',
            'tmhUtilities'             => '/libraries/twitter/tmhUtilities.php',
            'CFilesLibrary'            => '/libraries/files.php',
            'CTwitter'                 => '/libraries/twitter.php',
            'CEngagement'              => '/libraries/engagement.php',
            'CActivity'                => '/libraries/activity.php',
            /* Services */
            'CServiceAbstract'         => '/libraries/services/abstract.php',
            'CServiceGoogle'           => '/libraries/services/google.php',
            'CCurl'                    => '/libraries/curl.php',
            /* Parsers */
            'CParserAbstract'          => '/libraries/parsers/abstract.php',
            'CParserUrls'              => '/libraries/parsers/urls.php',
            'CParserMetas'             => '/libraries/parsers/metas.php',
        );

        if (array_key_exists($classname, $classes)) {
            if (!class_exists($classname)) {
                require_once(JPATH_ROOT . '/components/com_community' . $classes[$classname]);
            }
        }
    }

    /**
     * Autoload helper class
     */
    static function autoload_helpers($classname) {
        $classes = array(
            'CAccess'                   => '/helpers/access.php',
            'CKses'                     => '/helpers/kses.php',
            'CCalendar'                 => '/helpers/Calendar.php',
            'CPluginHelper'             => '/helpers/plugins.php',
            'CFriendsHelper'            => '/helpers/friends.php',
            'CAccess'                   => '/helpers/access.php',
            'CAjaxHelper'               => '/helpers/ajax.php',
            'CAlbumsGroupHelperHandler' => '/helpers/albums.php',
            'CAutoSuggest'              => '/helpers/autousersuggest.php',
            #''                         => '/helpers/azrul.php',
            'CCalendar'                 => '/helpers/calendar.php',
            'CCategoryHelper'           => '/helpers/category.php',
            #''                         => '/helpers/community.php',
            'CContentHelper'            => '/helpers/content.php',
            'CCSV'                      => '/helpers/csv.php',
            'CEmails'                   => '/helpers/emails.php',
            'CEventGroupHelperHandler'  => '/helpers/event.php',
            'CFileHelper'               => '/helpers/file.php',
            'CFriendsHelper'            => '/helpers/friends.php',
            'CGroupHelper'              => '/helpers/group.php',
            'CImageHelper'              => '/helpers/image.php',
            'CKses'                     => '/helpers/kses.php',
            'CLimitsHelper'             => '/helpers/limits.php',
            'CLinkGeneratorHelper'      => '/helpers/linkgenerator.php',
            'CMapsHelper'               => '/helpers/maps.php',
            'CMenuHelper'               => '/helpers/menu.php',
            'CNotificationTypesHelper'  => '/helpers/notificationtypes.php',
            'COwnerHelper'              => '/helpers/owner.php',
            'CPhone'                    => '/helpers/phone.php',
            'CPhotosHelper'             => '/helpers/photos.php',
            'CPluginHelper'             => '/helpers/plugins.php',
            'CProgressbarHelper'        => '/helpers/progressbar.php',
            'CRecaptchaHelper'          => '/helpers/recaptcha.php',
            'ReCaptchaResponse'         => '/helpers/recaptcha.php',
            'CRemoteHelper'             => '/helpers/remote.php',
            'CStringHelper'             => '/helpers/string.php',
            'CTemplateHelper'           => '/helpers/template.php',
            'CTimeHelper'               => '/helpers/time.php',
            'CDate'                     => '/helpers/time.php',
            'CUrl'                      => '/helpers/url.php',
            'CUrlHelper'                => '/helpers/url.php',
            'CUserHelper'               => '/helpers/user.php',
            'CValidateHelper'           => '/helpers/validate.php',
            'CVideosHelper'             => '/helpers/videos.php',
            'CEventHelper'              => '/helpers/event.php',
            'CAlbumsHelper'             => '/helpers/albums.php',
            'CHeadHelper'               => '/helpers/head.php',
            'CFetchHelper'              => '/helpers/fetch.php',
            'CLikesHelper'              => '/helpers/likes.php',
            'CActivitiesHelper'         => '/helpers/activities.php'
        );

        if (array_key_exists($classname, $classes)) {
            require_once(JPATH_ROOT . '/components/com_community' . $classes[$classname]);
        }
    }

    /**
     * Autoload helper class
     */
    static function autoload_tables($classname) {
        $classes = array(
            'CTableActivity'           => '/tables/activity.php',
            'CTableAlbum'              => '/tables/album.php',
            'CTableApp'                => '/tables/app.php',
            'CTableBulletin'           => '/tables/bulletin.php',
            'CTableCategory'           => '/tables/category.php',
            'CTableConnect'            => '/tables/connect.php',
            'CTableDiscussion'         => '/tables/discussion.php',
            'CTableEvent'              => '/tables/event.php',
            'CTableEventCategory'      => '/tables/eventcategory.php',
            'CTableEventMembers'       => '/tables/eventmembers.php',
            'CTableFeatured'           => '/tables/featured.php',
            'CTableFieldValue'         => '/tables/fieldvalue.php',
            'CTableFile'               => '/tables/file.php',
            'CTableGroup'              => '/tables/group.php',
            'CTableGroupCategory'      => '/tables/groupcategory.php',
            'CTableGroupInvite'        => '/tables/groupinvite.php',
            'CTableGroupMembers'       => '/tables/groupmembers.php',
            'CTableInvitation'         => '/tables/invitation.php',
            'CTableLike'               => '/tables/like.php',
            'CTableLocationCache'      => '/tables/locationcache.php',
            'CTableMemberList'         => '/tables/memberlist.php',
            'CTableMemberListCriteria' => '/tables/memberlistcriteria.php',
            'CTableMessage'            => '/tables/message.php',
            'CTableMultiProfile'       => '/tables/multiprofile.php',
            'CTableMultiProfileFields' => '/tables/multiprofilefields.php',
            'CTableNotification'       => '/tables/notification.php',
            'CTableOauth'              => '/tables/oauth.php',
            'CTablePhoto'              => '/tables/photo.php',
            'CTableProfile'            => '/tables/profile.php',
            'CTableProfileField'       => '/tables/profilefield.php',
            'CTableTag'                => '/tables/tag.php',
            'CTableTagword'            => '/tables/tagword.php',
            'CTableVideo'              => '/tables/video.php',
            'CTableVideosCategory'     => '/tables/videoscategory.php',
            'CTableWall'               => '/tables/wall.php'
        );

        $backendClassname = array(
            'CommunityTableConfiguration'    => '/tables/configuration.php',
            'CommunityTableProfiles'         => '/tables/profiles.php',
            'CommunityTableReports'          => '/tables/reports.php',
            'CommunityTableUserPoints'       => '/tables/userpoints.php',
            'CommunityTableUsers'            => '/tables/users.php',
            'CommunityTableVideosCategories' => '/tables/videocategories.php',
            'CommunityTableEventCategories'  => '/tables/eventcategories.php',
            'CommunityTableGroupCategories'  => '/tables/groupcategories.php',
            'CommunityTableGroups'           => '/tables/groups.php',
            'CommunityTableMailQueue'        => '/tables/mailqueue.php',
            'CommunityTableNetwork'          => '/tables/network.php',
            'CommunityTableEvents'           => '/tables/events.php'
        );

        if (array_key_exists($classname, $backendClassname)) {
            require_once(JPATH_ROOT . '/administrator/components/com_community' . $backendClassname[$classname]);
        }

        if (array_key_exists($classname, $classes)) {
            require_once(JPATH_ROOT . '/components/com_community' . $classes[$classname]);
        }
    }

    /**
     * Autoload helper class
     */
    static function autoload_events($classname) {
        $classes = array(
            'CEventTrigger' => '/events/router.php'
        );

        if (array_key_exists($classname, $classes)) {
            require_once(JPATH_ROOT . '/components/com_community' . $classes[$classname]);
        }
    }

}

/**
 * Global Asset manager
 */
class CAssets {

    /**
     * Centralized location to attach asset to any page. It avoids duplicate
     * attachement
     */
    static function attach($path, $type, $assetPath = '') {

        $document = JFactory::getDocument();

        if ($document->getType() != 'html')
            return;

        $template = new CTemplateHelper();
        $config = CFactory::getConfig();

        if (!defined('C_ASSET_JQUERY')) {
            $jQuery = $template->getTemplateAsset('joms.jquery', 'js');

            $document->addScript(JURI::root() . 'components/com_community/assets/joms.jquery-1.8.1.min.js');

            define('C_ASSET_JQUERY', 1);
        }

        static $added = false;
        $app = JFactory::getApplication();
        if (!$added && $app->isSite()) {
            $script = $template->getTemplateAsset('script-1.2', 'js');
            $document->addScript($script->url);

            $signature = md5($script->url);
            define('C_ASSET_' . $signature, 1);
            $added = true;
        }

        if (!empty($assetPath)) {
            $path = $assetPath . $path;
        } else {
            $path = JURI::root() . 'components/com_community/' . JString::ltrim($path, '/');
        }

        if (!defined('C_ASSET_' . md5($path))) {
            define('C_ASSET_' . md5($path), 1);

            switch ($type) {
                case 'js':
                    $document->addScript($path);
                    break;

                case 'css':
                    $document->addStyleSheet($path);
            }
        }
    }

}

/**
 * Provide global access to JomSocial configuration and paramaters
 */
jimport('joomla.html.parameter');

/**
 * Provide global access to JomSocial configuration and paramaters
 */
class CConfig /* extends JParameter */ {

    var $_defaultParam;
    private $_jparam = null;

    /**
     * Return reference to global config object
     *
     * @return	object		JParams object
     */
    static public function &getInstance() {
        static $instance = null;
        if (!$instance) {
            jimport('joomla.filesystem.file');

            // First we need to load the default INI file so that new configuration,
            // will not cause any errors.
            $ini = JPATH_ROOT . '/administrator/components/com_community/default.ini';
            $data = JFile::read($ini);

            $instance = new CConfig();
            $instance->_jparam = new CParameter($data);

            JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_community/tables');
            $config = JTable::getInstance('configuration', 'CommunityTable');
            $config->load('config');

            $instance->_jparam->bind($config->params);

            // call trigger to allow configuration override
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $args = array();
            $args[] = $instance;
            $appsLib->triggerEvent('onAfterConfigCreate', $args);

            /*
             * To fix the date format when setting format is set to Fixed.
             * This function retrieves the wrong format and set it to the correct one.
             */
            $overrideTimeFormat = array('%b %d' => 'M d Y', '%d %b' => 'd M Y');
            $eventdateFormat = $instance->_jparam->get('eventdateformat');
            if (strpos($eventdateFormat, '%') !== false) {
                $eventdateFormat = $overrideTimeFormat[$eventdateFormat];
            }

            $profileDateFormat = $instance->_jparam->get('profileDateFormat');
            $profileDateFormat = str_replace('%', '', $profileDateFormat);

            $instance->_jparam->set('profileDateFormat', $profileDateFormat);
            $instance->_jparam->set('eventdateformat', $eventdateFormat);
            $instance->_jparam->set('activitiesdayformat', 'F d');
            $instance->_jparam->set('activitiestimeformat', 'h:i A');
        }

        return $instance;
    }

    /**
     * Sets a specific property of the config value
     *
     * @param	string	$key
     * @param	string	$value
     *
     * */
    public function set($key, $value) {
        $this->_jparam->set($key, $value);
    }

    /**
     * Redirect all call to JParams object to $this->_jparam->
     * @param <type> $name
     * @param <type> $arguments
     */
    public function __call($name, $arguments) {
        // call_user_func($this->_jparam->$name, $arguments);
    }

    /**
     * Get a value
     *
     * @access	public
     * @param	string The name of the param
     * @param	mixed The default value if not found
     * @return	string
     */
    public function get($key, $default = '', $group = '_default') {
        $value = $this->_jparam->get($key, $default, $group);

        // Backward compatibility support since now configuration words are split by an underscore.
        if (empty($value)) {
            $key = CString::str_ireplace('_', '', $key);
            $value = $this->_jparam->get($key, $default, $group);
        }

        return $value;
    }

    public function getString($key, $default = '', $group = '_default') {
        $value = $this->get($key, $default, $group);
        return (string) $value;
    }

    public function getBool($key, $default = '', $group = '_default') {
        $value = $this->get($key, $default, $group);
        return (bool) $value;
    }

    public function getInt($key, $default = '', $group = '_default') {
        $value = $this->get($key, $default, $group);
        return (int) $value;
    }

}

class CApplications extends JPlugin {

    //@todo: Do some stuff so the childs get inheritance?
    var $params = '';

    public function CApplications(& $subject, $config = null) {
        // Set the params for the current object
        parent::__construct($subject, $config);
        //$this->_getUserParams(  );
    }

    /**
     * Function is Deprecated.
     * -	 Should only be used in profile area.
     * */
    public function loadUserParams() {
        $model = CFactory::getModel('apps');
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $my = CFactory::getUser();
        $userid = $jinput->get('userid', $my->id, 'INT'); //JRequest::getVar('userid', $my->id);
        $user = CFactory::getUser($userid);

        //$user	= CFactory::getActiveProfile();
        $appName = $this->_name;

        $position = $model->getUserAppPosition($user->id, $appName);
        $this->params->set('position', $position, 'content');

        $params = $model->getUserAppParams($model->getUserApplicationId($appName, $user->id));

        $this->userparams = new CParameter($params);
    }

    public function setNewLayout($position = "") {
        $layout = !empty($position) ? $position : 'content';
        $this->params->set('newlayout', $layout);
        return true;
    }

    public function getLayout() {
        $newLayout = $this->params->get('newlayout', '');
        $currentLayout = $this->params->get('position');

        $layout = empty($newLayout) ? $currentLayout : $newLayout;
        return $layout;
    }

    public function getRefreshAction($script = array()) {
        return $script;
    }

}

class CRoute {

    var $menuname = 'mainmenu';

    /**
     * Method to wrap around getting the correct links within the email
     * DEPRECATED since 1.5
     */
    static function emailLink($url, $xhtml = false) {
        return CRoute::getExternalURL($url, $xhtml);
    }

    /**
     * Method to wrap around getting the correct links within the email
     *
     * @return string $url
     * @param string $url
     * @param boolean $xhtml
     */
    static function getExternalURL($url, $xhtml = false) {
        $uri = JURI::getInstance();
        $base = $uri->toString(array('scheme', 'host', 'port'));

        return $base . CRoute::_($url, $xhtml);
    }

    static function getURI($xhtml = true) {
        return htmlspecialchars(JUri::getInstance()->toString());
    }

    /**
     * Wrapper to JRoute to handle itemid
     * We need to try and capture the correct itemid for different view
     */
    static function _($url, $xhtml = true, $ssl = null) {
        global $Itemid;

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        $cache = CFactory::getFastCache();
        $cacheid = __FILE__ . __LINE__ . serialize(func_get_args()) . $Itemid;
        if ($data = $cache->get($cacheid)) {
            $data = JRoute::_($data, $xhtml, $ssl);
            return $data;
        }

        static $itemid = array();

        parse_str(JString::str_ireplace('index.php?', '', $url));

        if (empty($view)) {
            $view = 'frontpage';
        }

        if (isset($option) && $option != 'com_community') {
            if (!$Itemid) {
                $db = JFactory::getDBO();
                $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__menu') . ' '
                        . 'WHERE ' . $db->quoteName('link') . ' LIKE ' . $db->Quote('%' . $url . '%');
                $db->setQuery($query);
                $id = $db->loadResult();

                $url .= '&Itemid=' . $id;
            }

            return JRoute::_($url, $xhtml, $ssl);
        }

        if (empty($itemid[$view])) {
            global $Itemid;
            $isValid = false;

            $currentView = $jinput->get('view', 'frontpage', 'NONE'); //JRequest::getVar('view', 'frontpage');
            $currentOption = $jinput->get('option', '', 'STRING'); //JRequest::getVar('option');
            // If the current Itemid match the expected Itemid based on view
            // we'll just use it
            $db = JFactory::getDBO();
            $viewId = CRoute::_getViewItemid($view);

            // if current itemid
            if ($currentOption == 'com_community' && $currentView == $view && $Itemid != 0) {
                $itemid[$view] = $Itemid;
                $isValid = true;
            } else if ($viewId === $Itemid && !is_null($viewId) && $Itemid != 0) {
                $itemid[$view] = $Itemid;
                $isValid = true;
            } else if ($viewId !== 0 && !is_null($viewId)) {
                $itemid[$view] = $viewId;
                $isValid = true;
            }

            if (!$isValid) {
                $id = CRoute::_getDefaultItemid();
                if ($id !== 0 && !is_null($id)) {
                    $itemid[$view] = $id;
                }
                $isValid = true;
            }

            // Search the mainmenu for the 1st itemid of jomsocial we can find, that match the current language code
            if (!$isValid) {


                $db = JFactory::getDBO();
                $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__menu') . ' WHERE '
                        . $db->quoteName('link') . ' LIKE ' . $db->Quote('%com_community%')
                        . 'AND ' . $db->quoteName('published') . '=' . $db->Quote(1) . ' '
                        . 'AND ' . $db->quoteName('menutype') . '=' . $db->Quote('{CRoute::menuname}')
                        . 'AND ' . $db->quoteName('menutype') . '!=' . $db->Quote($config->get('toolbar_menutype')) . ' '
                        . 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component');
                $db->setQuery($query);
                $isValid = $db->loadResult();

                if (!empty($isValid)) {
                    $itemid[$view] = $isValid;
                }
            }

            // If not in mainmenu, seach in any menu
            if (!$isValid) {
                $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__menu') . ' WHERE '
                        . $db->quoteName('link') . ' LIKE ' . $db->Quote('%com_community%')
                        . 'AND ' . $db->quoteName('published') . '=' . $db->Quote(1) . ' '
                        . 'AND ' . $db->quoteName('menutype') . '!=' . $db->Quote($config->get('toolbar_menutype')) . ' '
                        . 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component');
                $db->setQuery($query);
                $isValid = $db->loadResult();
                if (!empty($isValid))
                    $itemid[$view] = $isValid;
            }
        }

        $pos = strpos($url, '#');
        if ($pos === false) {
            if (isset($itemid[$view])) {
                if (strpos($url, 'Itemid=') === false && strpos($url, 'com_community') !== false) {
                    $url .= '&Itemid=' . $itemid[$view];
                }
            }
        } else {
            if (isset($itemid[$view]))
                $url = str_ireplace('#', '&Itemid=' . $itemid[$view] . '#', $url);
        }

        $data = JRoute::_($url, $xhtml, $ssl);
        $cache->store($url, $cacheid);
        return $data;
    }

    /**
     * Return the Itemid specific for the given view.
     */
    static function _getViewItemid($view) {
        static $itemid = array();

        if (empty($itemid[$view])) {
            $db = JFactory::getDBO();
            $config = CFactory::getConfig();
            $url = $db->quote('%option=com_community&view=' . $view . '%');
            $type = $db->quote('component');

            $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__menu') . ' '
                    . 'WHERE ' . $db->quoteName('link') . ' LIKE ' . $url . ' '
                    . 'AND ' . $db->quoteName('published') . '=' . $db->Quote(1) . ' '
                    . 'AND ' . $db->quoteName('menutype') . '!=' . $db->Quote($config->get('toolbar_menutype')) . ' '
                    . 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component');
            $db->setQuery($query);
            $val = $db->loadResult();
            $itemid[$view] = $val;
        } else {
            $val = $itemid[$view];
        }
        return $val;
    }

    /**
     * Retrieve the Itemid of JomSocial's menu. If you are creating a link to JomSocial, you
     * will need to retrieve the Itemid.
     * */
    static function getItemId() {
        return CRoute::_getDefaultItemid();
    }

    /**
     * Return the Itemid for default view, frontpage
     */
    static function _getDefaultItemid() {
        static $defaultId = null;

        if ($defaultId != null)
            return $defaultId;

        $db = JFactory::getDBO();

        $url = $db->quote("index.php?option=com_community&view=frontpage");
        $type = $db->quote('component');

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        // See if language is there
        $lang = $jinput->get('language', '', 'NONE'); //JRequest::getVar('language', '');
        $langFilter = !empty($lang) ? ' AND (`language`= ' . $db->Quote($lang) . ' OR `language`="*")' : '';
        $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__menu')
                . ' WHERE ' . $db->quoteName('link') . ' = ' . $url . ' AND ' . $db->quoteName('published') . '=' . $db->Quote(1) . ' '
                . 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component') . $langFilter;
        $db->setQuery($query);
        $val = $db->loadResult();

        if (!$val) {
            $url = $db->quote("%option=com_community%");

            $query = 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__menu')
                    . ' WHERE ' . $db->quoteName('link') . ' LIKE ' . $url . ' AND ' . $db->quoteName('published') . '=' . $db->Quote(1) . ' '
                    . 'AND ' . $db->quoteName('type') . '=' . $db->Quote('component');
            $db->setQuery($query);
            $val = $db->loadResult();
        }

        $defaultId = $val;
        return $val;
    }

}

/**
 *
 */
class CDispatcher extends JDispatcher {

    /**
     * Contructor, inherrit from parrent.
     * */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Overide JDispatcher::getInstance function.
     * @staticvar CDispatcher $instance
     * @return \CDispatcher
     */
    public static function getInstanceStatic() {
        static $instance;

        if (!is_object($instance)) {
            $instance = new CDispatcher();
            //link JDispatcher's properties to CDispatcher's properties
            $dispatcher = JDispatcher::getInstance();
            $arr_p = get_object_vars($dispatcher);
            foreach ($arr_p as $key => $value) {
                $instance->$key = &$dispatcher->$key;
            }
        }
        return $instance;
    }

    /**
     * Contructor, inherrit from parrent.
     * */
    public function getObservers() {
        return $this->_observers;
    }

}

class CPluginWrapper extends JPlugin {

    private $JplgObj;

    /**
     * Contructor, inherrit from parrent.
     * */
    public function __construct($JplgObj) {
        if (is_object($JplgObj) && is_subclass_of($JplgObj, 'JPlugin')) {
            $this->JplgObj = $JplgObj;
        }
    }

    public function getPluginType() {
        if ($this->JplgObj) {
            return $this->JplgObj->_type;
        }
        return '';
    }

    public function getPluginName() {
        if ($this->JplgObj) {
            return $this->JplgObj->_name;
        }
        return '';
    }

}

class CACL {

    public static function getInstance() {
        static $instance;
        if (!is_object($instance)) {
            $instance = new CACLAccess();
        }
        return $instance;
    }

}

class CACLAccess extends JAccess {

    public function getGroupsByUserId($userId) {
        $groupIds = JAccess::getGroupsByUser($userId);
        return $this->getGroupName(end($groupIds));
    }

    public function getGroupUser($userId) {
        $groupIds = JAccess::getGroupsByUser($userId, false);
        return $this->getGroupName($groupIds[0]);
    }

    public function getGroupID($groupname) {
        $db = JFactory::getDbo();
        $query = 'Select '
                . $db->quoteName('id') . ' from '
                . $db->quoteName('#__usergroups') . ' where '
                . $db->quoteName('title') . '='
                . $db->quote($groupname);
        $db->setQuery($query);
        return $db->loadResult();
    }

    public function getGroupName($groupId) {
        if ($groupId !== '') {
            $db = JFactory::getDbo();
            $query = 'Select '
                    . $db->quoteName('title') . ' from '
                    . $db->quoteName('#__usergroups') . ' where '
                    . $db->quoteName('id') . '='
                    . $db->quote($groupId);
            $db->setQuery($query);
            return $db->loadResult();
        }

        return '';
    }

    public function is_group_child_of($grp_src, $grp_tgt) {
        $gid_src = $this->getGroupID($grp_src);
        $gid_tgt = $this->getGroupID($grp_tgt);
        if ($gid_src && $gid_tgt) {
            $group_path = JAccess::getGroupPath($gid_src);
            if (is_array($group_path) && in_array($gid_tgt, $group_path)) {
                return true;
            }
        }

        return false;
    }

}

class CString {

    static public function str_ireplace($search, $replace, $str, $count = NULL) {
        if ($count === FALSE) {
            return self::_utf8_ireplace($search, $replace, $str);
        } else {
            return self::_utf8_ireplace($search, $replace, $str, $count);
        }
    }

    static public function _utf8_ireplace($search, $replace, $str, $count = NULL) {

        if (!is_array($search)) {

            $slen = strlen($search);
            $lendif = strlen($replace) - $slen;
            if ($slen == 0) {
                return $str;
            }

            $search = JString::strtolower($search);

            $search = preg_quote($search, '/');
            $lstr = JString::strtolower($str);
            $i = 0;
            $matched = 0;
            while (preg_match('/(.*)' . $search . '/Us', $lstr, $matches)) {
                if ($i === $count) {
                    break;
                }
                $mlen = strlen($matches[0]);
                $lstr = substr($lstr, $mlen);
                $str = substr_replace($str, $replace, $matched + strlen($matches[1]), $slen);
                $matched += $mlen + $lendif;
                $i++;
            }
            return $str;
        } else {

            foreach (array_keys($search) as $k) {

                if (is_array($replace)) {

                    if (array_key_exists($k, $replace)) {

                        $str = CString::_utf8_ireplace($search[$k], $replace[$k], $str, $count);
                    } else {

                        $str = CString::_utf8_ireplace($search[$k], '', $str, $count);
                    }
                } else {

                    $str = CString::_utf8_ireplace($search[$k], $replace, $str, $count);
                }
            }
            return $str;
        }
    }

}

/**
 *
 * For list of social object, please refer to http://code.jomsocial.com/default.asp?W287
 */
class CSocialObject extends JObject {

}

interface CStreamable {

    /** Return HTML formatted stream data * */
    public function getStreamHTML($object);

    /** Return true is the user can post to the stream * */
    public function isAllowStreamPost($userid, $itemid);

    /** Return an array of activities app code * */
    static public function getStreamAppCode();
}

interface CAccessInterface {

    /** Return authorise in bool * */
    static public function authorise();
}

/**
 * Load core scripts
 * @since 3.2
 */
$app = JFactory::getApplication();
if($app->isSite()){
    $js = 'assets/stream.js';
    CAssets::attach($js, 'js');

    $map = 'https://maps.googleapis.com/maps/api/js?libraries=places&sensor=false';
    $doc = JFactory::getDocument();
    $doc->addScript($map);
}

/**
 * Get and store into session current user IP
 * @since 3.2
 */
$session = JFactory::getSession();
if ( !$session->get('jomsocial_userip') ) {
     /**
     * Get vistor IP
     */
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    $session->set('jomsocial_userip',$ip);
}
