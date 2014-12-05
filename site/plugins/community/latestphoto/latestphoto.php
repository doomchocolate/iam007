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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php');

/**
 * Class exists checking
 */
if (!class_exists('plgCommunityLatestPhoto')) {

    /**
     * Plugin entrypoint
     */
    class plgCommunityLatestPhoto extends CApplications {

        var $name = 'LatestPhoto';
        var $_name = 'latestphoto';
        var $_user = null;

        /**
         *
         * @param type $subject
         * @param type $config
         */
        public function plgCommunityLatestPhoto(& $subject, $config) {
            parent::__construct($subject, $config);
            $this->db = JFactory::getDbo();
            $this->_my = CFactory::getUser();                        
        }

        /**
         * Ajax function to save a new wall entry
         *
         * @param message	A message that is submitted by the user
         * @param uniqueId	The unique id for this group
         * @return type
         */
        public function onProfileDisplay() {
            $this->loadLanguage();
            $this->loadUserParams();
            $mainframe = JFactory::getApplication();

            // Attach CSS
            $document = JFactory::getDocument();
            $user = CFactory::getRequestUser();
            $userid = $user->id;

            $def_limit = $this->userparams->get('count', 10);
            $limit = JRequest::getVar('limit', $def_limit, 'REQUEST');
            $limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');

            $row = $this->getPhotos($userid, $limitstart, $limit);
            $total = count($row);

            $caching = $this->params->get('cache', 1);
            if ($caching) {
                $caching = $mainframe->getCfg('caching');
            }

            $cache = JFactory::getCache('plgCommunityLatestPhoto');
            $cache->setCaching($caching);
            $callback = array('plgCommunityLatestPhoto', '_getLatestPhotoHTML');
            $content = $cache->call($callback, $userid, $limit, $limitstart, $row, $total);

            return $content;
        }

        /**
         *
         * @param type $userid
         * @param type $limit
         * @param type $limitstart
         * @param type $row
         * @param type $total
         * @return type
         */
        static public function _getLatestPhotoHTML($userid, $limit, $limitstart, $row, $total) {

            //CFactory::load( 'models' , 'photos' );
            $photo = JTable::getInstance('Photo', 'CTable');

            ob_start();
            if (!empty($row)) {
                ?>
                <div class="js-col-layout">

                    <?php
                    $i = 0;
                    foreach ($row as $data) {
                        $photo->load($data->id);

                        $link = plgCommunityLatestPhoto::buildLink($photo->albumid, $data->id);
                        $thumbnail = $photo->getThumbURI();
                        ?>
                        <a href="<?php echo $link; ?>" class="js-col2" >
                            <img class="cAvatar cAvatar-sidebar jomNameTips" width="64" title="<?php echo CTemplate::escape($photo->caption); ?>" src="<?php echo $thumbnail; ?>" />
                        </a>
                        <?php
                    } // end foreach
                    ?>
                </div>
                <?php
            } else {
                ?>
                <div><?php echo JText::_('PLG_LATESTPHOTO_NO_PHOTO') ?></div>
                <?php
            }
            ?>
            <div style='clear:both;'></div>
            <?php
            $contents = ob_get_contents();
            @ob_end_clean();
            $html = $contents;

            return $html;
        }

        /**
         *
         * @param type $userid
         * @param type $limitstart
         * @param type $limit
         * @return type
         */
        public function getPhotos($userid, $limitstart, $limit) {
            $photoType = PHOTOS_USER_TYPE;

            //privacy settings
            //CFactory::load('libraries', 'privacy');
            $permission = CPrivacy::getAccessLevel($this->_my->id, $userid);

            $sql = "	SELECT
								a.id
						FROM
								" . $this->db->quoteName('#__community_photos') . " AS a
						INNER JOIN
								" . $this->db->quoteName('#__community_photos_albums') . " AS b ON a.`albumid` = b.`id`
						WHERE
								a." . $this->db->quoteName('creator') . " = " . $this->db->quote($userid) . " AND
								b." . $this->db->quoteName('type') . " = " . $this->db->quote($photoType) . " AND
								a." . $this->db->quoteName('published') . "=" . $this->db->quote(1) . " AND
								b.permissions <=" . $this->db->quote($permission) . "
						ORDER BY
								a." . $this->db->quoteName('created') . " DESC
						LIMIT
								" . $limitstart . "," . $limit;

            $query = $this->db->setQuery($sql);
            $row = $this->db->loadObjectList();
            if ($this->db->getErrorNum()) {
                JError::raiseError(500, $this->db->stderr());
            }

            return $row;
        }

        /**
         *
         * @param int $albumid
         * @param int $photoid
         * @return string
         */
        static public function buildLink($albumid, $photoid) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($photoid);

            return $photo->getPhotoLink();
        }

    }

}
