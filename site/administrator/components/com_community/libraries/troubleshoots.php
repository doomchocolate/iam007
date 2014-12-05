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

if (!class_exists('CTroubleshoots')) {

    class CTroubleshoots {

        public function coreFilesCheck($path = '.', $level = 0) {

            /* Directories to ignore when listing output. Many hosts will deny PHP access to the cgi-bin. */
            $ignore = array('cgi-bin', '.', '..');
            /* Open the directory to the handle $dh */
            $dh = @opendir($path);
            $flag = true;
            echo '<ul>';
            /* Loop through the directory */
            while (false !== ( $file = readdir($dh) )) {
                /* Check that this file is not to be ignored */
                if (!in_array($file, $ignore)) {
                    if (is_dir("$path/$file")) {
                        echo "<li><strong>$file</strong></li>";
                        /* Its a directory, so we need to keep reading down... */
                        $this->coreFilesCheck("$path/$file", ($level + 1));
                        /* Re-call this same function but on a new directory. this is what makes function recursive. */
                    } else {
                        if ($file == 'index.html')
                            $flag = false;
                        $filePath = trim(str_replace(JPATH_ROOT, '', $path) . '/' . $file);
                        $checkSum = md5_file($path . '/' . $file);
                        $hashList = $this->getHash();
                        if (!isset($hashList[$filePath])) {
                            echo '<li>' . $filePath . '<small> ' . '<span class="label">No checksum data</span></small></li>';
                        } else if ((isset($hashList[$filePath]) && $hashList[$filePath] != $checkSum)) {
                            echo '<li>' . $filePath . '<small> ' . '<span class="label label-important">Modified</span></small></li>';
                        } else {
                            echo '<li>' . $filePath . ' <i class="icon-ok"></i></li>';
                        }
                    }
                }
            }
            /* index.html checking */
            if ($flag) {
                echo '<span class="label label-important">No index.html</span>';
            }
            echo '</ul>';
            closedir($dh);
        }

        public function filesCheck($path = '.', $level = 0) {

            /* Directories to ignore when listing output. Many hosts will deny PHP access to the cgi-bin. */
            $ignore = array('cgi-bin', '.', '..');
            /* Open the directory to the handle $dh */
            $dh = @opendir($path);

            echo '<ul>';
            /* Loop through the directory */
            while (false !== ( $file = readdir($dh) )) {
                /* Check that this file is not to be ignored */
                if (!in_array($file, $ignore)) {
                    if (is_dir("$path/$file")) {
                        echo "<li><strong>$file</strong></li>";
                        /* Its a directory, so we need to keep reading down... */
                        $this->filesCheck("$path/$file", ($level + 1));
                        /* Re-call this same function but on a new directory. this is what makes function recursive. */
                    } else {
                        if ($file == 'index.html')
                            $flag = false;
                        $filePath = trim(str_replace(JPATH_ROOT, '', $path) . '/' . $file);
                        $content = JFile::read($path . '/' . $file);
                        if (strpos($content, 'window.jQuery = window.$ = jQuery;') !== false) {
                            echo '<li class="warning">' . $filePath . ' <small><span class="label label-warning">jQuery detected</span></small></i></li>';
                        } else {
                            //echo '<li>' . $filePath . '</li>';
                        }
                    }
                }
            }

            echo '</ul>';
            closedir($dh);
        }

        public function getHash() {
            static $list;
            if (!isset($list)) {
                $content = JFIle::read(JPATH_COMPONENT_ADMINISTRATOR . '/hash.ini');
                $array = explode("\n", $content);
                foreach ($array as $el) {
                    $parts = explode('=', $el);
                    if (count($parts) == 2) {
                        $list[trim($parts[0])] = $parts[1];
                    }
                }
            }

            return $list;
        }

        public function getCommunityPlugins() {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__extensions')
                    ->where('type =' . $db->quote('plugin'))
                    ->where(' ( 
                        folder = ' . $db->quote('community')
                            . ' OR folder = ' . $db->quote('content')
                            . ' OR folder = ' . $db->quote('editors-xtd')
                            . ' OR folder = ' . $db->quote('kunena')
                            . ' OR folder = ' . $db->quote('system')
                            . ' OR folder = ' . $db->quote('user')
                            . ' ) ')
                    ->order('folder')
                    ->order('ordering');
            $db->setQuery($query);
            return $db->loadObjectList();
        }

    }

}