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

/**
 * Class exists checking
 */
if (!class_exists('CFetchHelper')) {

    /**
     * 
     */
    class CFetchHelper {

        public static function fetchUrlsFromContent($content) {
            /* Url extract */
            $urlsParser = new CParserUrls ();
            $urlsParser->setContent($content);
            $urls = $urlsParser->extract();
            return $urls;
        }

        public static function fromContent($content) {
            $urls = self::fetchUrlsFromContent($content);

            /**
             * Crawle data
             * We only work with first url
             */
            if (count($urls) > 0) {
                $url = array_shift($urls);
                $curl = new CCurl();

                $header = $curl->getHeaderOnly($url);

                if (strpos($header['Content-Type'], ';') !== false) {
                    $header = explode(';', $header['Content-Type']);
                    $header = trim($header[0]);
                } else {
                    $header = $header['Content-Type'];
                }

                switch ($header) {
                    case 'text/html': /* now we do curl again to get body */
                        /**
                         * @todo Somehow it's bug here that incorrect body content
                         */
                        //$curl->setCurl(CURLOPT_HEADER); /* ya don't need header again */
                        $response = $curl->get($url);
                        $body = $response->getBody();
                        /* get meta object */
                        $metasParser = new CParserMetas();
                        $metasParser->setContent($body);
                        $graphObject = $metasParser->extract();
                        /* Do image fetch into local and resize */
                        $images = $graphObject->get('image');
                        /**
                         * @todo allow config save dir
                         */
                        if (is_array($images)) {
                            $saveDir = JPATH_ROOT . '/images/community/activities';
                            /* Create save dir if not exists */
                            if (!JFolder::exists($saveDir))
                                JFolder::create($saveDir);
                            /* Do copy into local */
                            $localImages = array();
                            foreach ($images as $image) {
                                /* Hashing remote image url to use as fileName */
                                $localFileName = md5($image);
                                /* Get file extension */
                                $locaFileExt = JFile::getExt($image);
                                /* Generate local filename from hashed and extesion */
                                $localFile = $localFileName . '.' . $locaFileExt;
                                /* Local thumbnail filename by adding _thumb */
                                $localThumbFile = $localFileName . '_thumb.' . $locaFileExt;
                                /* Do save local */
                                copy($image, $saveDir . '/' . $localFile);
                                /* Get image file informantion */
                                $info = getimagesize($saveDir . '/' . $localFile);
                                /* Get image type than use it to createThumb */
                                $imgType = image_type_to_mime_type($info[2]);
                                /* Do make thumb */
                                CImageHelper::createThumb($saveDir . '/' . $localFile, $saveDir . '/' . $localThumbFile, $imgType);
                                $localImages[] = array('image' => $localFile, 'thumb' => $localThumbFile);
                            }
                            /* Save back */
                            $graphObject->set('localImages', $localImages);
                        }
                        return $graphObject;
                        break;
                    default:
                        break;
                }
            }
        }

    }

}