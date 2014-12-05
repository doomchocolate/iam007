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

class CommunityTablePhotos extends JTable{

    var $id = null;
    var $albumid = null;
    var $caption = null;
    var $permissions = null;
    var $created = null;
    var $thumbnail = null;
    var $image = null;
    var $creator = null;
    var $published = null;
    var $original = null;
    var $filesize = null;
    var $storage = 'file';
    var $hits = 0;
    var $ordering = null;
    var $status = null;
    var $params = null;
    private $_params = null;

    /**
     * Constructor
     */
    public function __construct(&$db) {
        parent::__construct('#__community_photos', 'id', $db);
        $this->_params = new JRegistry($this->params);
    }

}