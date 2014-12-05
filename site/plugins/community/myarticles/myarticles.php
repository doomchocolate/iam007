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
require_once( JPATH_ROOT . '/components/com_content/helpers/route.php');
require_once( JPATH_ROOT . '/components/com_community/libraries/core.php');

if (!class_exists('plgCommunityMyArticles')) {

    class plgCommunityMyArticles extends CApplications {

        var $name = "My Articles";
        var $section;
        var $_name = "myarticles";

        function plgCommunityMyArticles($subject, $config) {
            parent::__construct($subject, $config);

            $this->section = trim($this->params->get('section'), ',');
            $this->_path = JPATH_ROOT . '/administrator/components/com_myblog';
            $this->db = JFactory::getDbo();
        }

        /**
         * Ajax function to save a new wall entry
         *
         * @param message	A message that is submitted by the user
         * @param uniqueId	The unique id for this group
         *
         * */
        function onProfileDisplay() {
            //Load language file.
            JPlugin::loadLanguage('plg_community_myarticles', JPATH_ADMINISTRATOR);

            // Attach CSS
            $document = JFactory::getDocument();
            $css = JURI::base() . 'plugins/community/myarticles/myarticles/style.css';
            $document->addStyleSheet($css);

            if (JRequest::getVar('task', '', 'REQUEST') == 'app') {
                $app = 1;
            } else {
                $app = 0;
            }

            $user = CFactory::getRequestUser();
            $userid = $user->id;

            $def_limit = $this->params->get('count', 10);
            $limit = JRequest::getVar('limit', $def_limit, 'REQUEST');
            $limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');

            if (!file_exists($this->_path . '/config.myblog.php')) {
                $row = $this->getArticle($userid, $limitstart, $limit, $this->section);
                $myblogItemId = "";
            } else {
                $row = $this->getArticle_with_myblog($userid, $limitstart, $limit, $this->section);
                include_once (JPATH_ROOT . "/components/com_myblog/functions.myblog.php");
                $myblogItemId = myGetItemId();
            }

            $cat = $this->getCatAlias();
            $total = $this->countArticle($userid, $this->section);
            $introtext = $this->params->get("introtext", 0);

            $mainframe = JFactory::getApplication();
            $caching = $this->params->get('cache', 1);

            if ($caching) {
                $caching = $mainframe->getCfg('caching');
            }

            $cache = JFactory::getCache('plgCommunityMyArticles');
            $cache->setCaching($caching);

            $callback = array('plgCommunityMyArticles', '_getArticleHTML');
            $content = $cache->call($callback, $userid, $limit, $limitstart, $row, $app, $total, $cat, $myblogItemId, $introtext, $this->params);

            return $content;
        }

        static public function _getArticleHTML($userid, $limit, $limitstart, $row, $app, $total, $cat, $myblogItemId, $introtext, $params) {

            JPluginHelper::importPlugin('content');
            $dispatcher = JDispatcher::getInstance();
            $html = "";

            if (!empty($row)) {
                $html .= '<div id="application-myarticles">';
                $html .= '<ul class="list-articles cResetList">';
                foreach ($row as $data) {
                    $text_limit = $params->get('limit', 50);
                    if (JString::strlen($data->introtext) > $text_limit) {
                        $content = strip_tags(JString::substr($data->introtext, 0, $text_limit));
                        $content .= " .....";
                    } else {
                        $content = $data->introtext;
                    }

                    $data->text = $content;
                    $result = $dispatcher->trigger('onPrepareContent', array(& $data, & $params, 0));

                    if (empty($data->permalink)) {
                        $myblog = 0;
                        $permalink = "";
                    } else {
                        $myblog = 1;
                        $permalink = $data->permalink;
                    }

                    if (empty($cat[$data->catid])) {
                        $cat[$data->catid] = "";
                    }

                    $data->sectionid = (empty($data->sectionid)) ? 0 : $data->sectionid;
                    $link = plgCommunityMyArticles::buildLink($data->id, $data->alias, $data->catid, $cat[$data->catid], $data->sectionid, $myblog, $permalink, $myblogItemId);

                    $created = new JDate($data->created);
                    $date = CTimeHelper::timeLapse($created);


                    $html .= '	<li>';
                    $html .= '		<span>' . $date . '</span>';
                    $html .= '		<a href="' . $link . '">' . htmlspecialchars($data->title) . '</a>';
                    if ($introtext == 1) {
                        $html .= '<div>' . $content . '</div>';
                    }
                    $html .= '	</li>';
                }
                $html .= '</ul>';
                $html .= '</div>';

                if ($app == 1) {
                    jimport('joomla.html.pagination');

                    $pagination = new JPagination($total, $limitstart, $limit);
                    $html .= '
					<!-- Pagination -->
					<div style="text-align: center;">
						' . $pagination->getPagesLinks() . '
					</div>
					<!-- End Pagination -->';
                } else {
                    $showall = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid . '&task=app&app=myarticles');
                    $html .= "<div style='float:right;'><a href='" . $showall . "'>" . JText::_('PLG_MYARTICLES_SHOWALL') . "</a></div>";
                }
            } else {
                $html .= "<div>" . JText::_("PLG_MYARTICLES_NO_ARTICLES") . "</div>";
            }

            return $html;
        }

        function onAppDisplay() {
            ob_start();
            $limit = 0;
            $html = $this->onProfileDisplay($limit);
            echo $html;

            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        static public function buildLink($id, $alias, $catid, $catAlias, $sectionid, $myblog, $permalink, $myblogItemId) {

            if (!$myblog) {
                $link = ContentHelperRoute::getArticleRoute($id . ':' . $alias, $catid . ':' . $catAlias, $sectionid);
                $link = JRoute::_($link);
            } else {
                $link = JRoute::_("index.php?option=com_myblog&show=" . $permalink . "&Itemid=" . $myblogItemId);
            }

            return $link;
        }

        function getArticle($userid, $limitstart, $limit, $section) {

            if (!empty($section)) {
                $condition = " AND " . $this->db->quoteName('sectionid') . " IN (" . $section . ")";
            } else {
                $condition = "";
            }

            if ($this->params->get('display_expired', 1)) {
                $expired = "";
            } else {
                $expired = $this->getExpiredCondition();
            }

            $sql = "	SELECT
								*
						FROM
								" . $this->db->quoteName('#__content') . "
						WHERE
								" . $this->db->quoteName('created_by') . " = " . $this->db->quote($userid) . " AND
								" . $this->db->quoteName('state') . "=" . $this->db->quote(1) . "
								" . $condition . "
								" . $expired . "
						ORDER BY
								" . $this->db->quoteName('created') . " DESC
						LIMIT
								" . $limitstart . "," . $limit;

            $query = $this->db->setQuery($sql);
            $row = $this->db->loadObjectList();
            if ($this->db->getErrorNum()) {
                JError::raiseError(500, $this->db->stderr());
            }
            return $row;
        }

        function getArticle_with_myblog($userid, $limitstart, $limit, $section) {

            if (!empty($section)) {
                $condition = " AND a." . $this->db->quoteName('sectionid') . " IN (" . $section . ")";
            } else {
                $condition = "";
            }

            if ($this->params->get('display_expired', 1)) {
                $expired = "";
            } else {
                $expired = $this->getExpiredCondition();
            }

            $sql = "	SELECT
								a.*,
								b.permalink
						FROM
								" . $this->db->quoteName('#__content') . " AS a LEFT JOIN
								" . $this->db->quoteName('#__myblog_permalinks') . " AS b ON
								a.id = b.contentid
						WHERE
								" . $this->db->quoteName('created_by') . " = " . $this->db->quote($userid) . " AND
								" . $this->db->quoteName('state') . "=" . $this->db->quote(1) . "
								" . $condition . "
								" . $expired . "
						ORDER BY
								" . $this->db->quoteName('created') . " DESC
						LIMIT
								" . $limitstart . "," . $limit;

            $query = $this->db->setQuery($sql);
            $row = $this->db->loadObjectList();
            if ($this->db->getErrorNum()) {
                JError::raiseError(500, $this->db->stderr());
            }
            return $row;
        }

        function countArticle($userid, $section) {
            if (!empty($section)) {
                $condition = " AND " . $this->db->quoteName('sectionid') . " IN (" . $section . ")";
            } else {
                $condition = "";
            }

            $sql = "	SELECT
								count(id) as total
						FROM
								" . $this->db->quoteName('#__content') . "
						WHERE
								" . $this->db->quoteName('created_by') . " = " . $this->db->quote($userid) . " AND
								" . $this->db->quoteName('state') . "=" . $this->db->quote(1) . "
								" . $condition;
            $query = $this->db->setQuery($sql);
            $count = $this->db->loadObject();
            if ($this->db->getErrorNum()) {
                JError::raiseError(500, $this->db->stderr());
            }

            return $count->total;
        }

        function getCatAlias() {
            $cat = array();

            $sql = "	SELECT
								" . $this->db->quoteName("id") . ",
								" . $this->db->quoteName("alias") . "
						FROM
								" . $this->db->quoteName("#__categories");

            $this->db->setQuery($sql);
            $row = $this->db->loadObjectList();

            foreach ($row as $data) {
                $cat[$data->id] = $data->alias;
            }

            return $cat;
        }

        /**
         * 
         * @return string
         */
        public function getExpiredCondition() {
            $date = new JDate();
            $now = $date->toSql();

            $condition = " AND ( " . " "
                    . "( "
                    . $this->db->quoteName('publish_up') . " <= " . $this->db->quote($now) . " AND "
                    . $this->db->quoteName('publish_down') . " >= " . $this->db->quote($now) . " "
                    . ") OR "
                    . $this->db->quoteName('publish_down') . " = " . $this->db->quote("0000-00-00 00:00:00") . " "
                    . " ) ";

            return $condition;
        }

    }

}
