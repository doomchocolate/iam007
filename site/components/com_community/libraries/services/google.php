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

if (!class_exists('CServiceGoogle')) {

    class CServiceGoogle extends CServiceAbstract {

        private $_googleMapUrl = 'maps.googleapis.com';

        public function getAddressFromCoords($latitude, $longitude) {
            $url = $this->_googleMapUrl . '/maps/api/geocode/json?latlng=' . $latitude . ',' . $longitude . '&sensor=false';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, "");
            $curlData = curl_exec($curl);
            curl_close($curl);
            $address = json_decode($curlData);
            if ($address->status == 'OK') {
                return $address->results[0];
            };
            return false;
        }

        public function getCoordsFromAddress($address) {
            $address = str_replace(" ", "+",$address);
            $url = $this->_googleMapUrl . '/maps/api/geocode/json?address=' . $address . '&sensor=false';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, "");
            $curlData = curl_exec($curl);
            curl_close($curl);
            $coords = json_decode($curlData);
            if ($coords->status == 'OK') {
                return $coords->results[0];
            };
            return false;
        }

    }

}