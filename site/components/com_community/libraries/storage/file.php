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
if (!class_exists('S3')) {
    include_once( JPATH_ROOT . '/components/com_community/libraries/storage/s3_lib.php');
}

class File_CStorage {

    public function _init() {

    }

    /**
     * Check if the given storage id exist. We perform local check via db since
     * checking remotely is time consuming
     *
     * @return true is file exits
     * */
    public function exists($storageid, $checkRemote = false) {
        return JFile::exists(JPATH_ROOT . '/' . $storageid);
    }

    /**
     * Put the file into remote storage,
     * @return true if successful
     */
    public function put($storageid, $file) {
        $storageid = JPATH_ROOT . '/' . $storageid;
        JFile::copy($file, $storageid);
        return true;
    }

    /**
     * Retrive the file from remote location and store it locally
     * @param storageid The unique file we want to retrive
     * @param file String	filename where we want to save the file
     */
    public function get($storageid, $file) {
        $storageid = JPATH_ROOT . '/' . $storageid;
        JFile::copy($storageid, $file);
        return true;
    }

    /**
     * Return the absolute URI path to the resource
     */
    public function getURI($storageId) {
        $root = JString::rtrim(JURI::root(), '/');
        $storageId = JString::ltrim($storageId, '/');
        return $root . '/' . $storageId;
    }

    /**
     * Storage file delete
     * @param string $storageid
     * @return boolean
     */
    public function delete($storageid) {
        $storageid = JPATH_ROOT . '/' . $storageid;
        if (JFile::exists($storageid)) {
            return JFile::delete($storageid);
        } else {
            return false;
        }
    }

}