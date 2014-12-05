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

if (!class_exists('CommunityLocationController')) {

    /**
     *
     */
    class CommunityLocationController extends CommunityBaseController {

        /**
         * Get coords by user IP
         * @return type
         */
        public function ajaxGetCoordsByIp() {
            $objResponse = new JAXResponse();
            $config = CFactory::getConfig();
            $curl = new CCurl;
            /* Because this function is called by ajax. We can't detect real vistor IP in this function than we need to get it from session */
            $ip = JFactory::getSession()->get('jomsocial_userip');
            /**
             * We do request to 3rd service with our IP to get coords than return ajax to update current coords
             * We can use many method here to get fews of coords. Just update it into our javascript
             * @todo We can allow extend via addon files ?
             */
            /**
             * @url http://www.ipinfodb.com/ip_location_api.php
             */
            $ipinfodbAPIKey = $config->get('geolocation_ipinfodb_key', '40955706fc1ec858891c0a7e1c76672d02c766cf7cee9b3e3bf00bcb1575d252');
            $ipinfodbRequestUrl = 'http://api.ipinfodb.com/v3/ip-city/?key=' . $ipinfodbAPIKey . '&format=json';
            if ($ip) {
                $ipinfodbRequestUrl .= '&ip=' . $ip;
            }
            $response = $curl->post($ipinfodbRequestUrl);
            $body = $response->getBody();
            if ($body) {
                $body = json_decode($body);
                /* We'll response data as much as we have */
                if ($body->latitude != 0 && $body->longitude != 0) {
                    $coords['coords'] = $body;
                    $objResponse->addScriptCall('joms.location.updateCoords', $coords);
                }
            }
            return $objResponse->sendResponse();
        }

        public function ajaxGetCoordsByAddress($address) {
            $objResponse = new JAXResponse();
            $googleService = new CServiceGoogle();
            $coords = $googleService->getCoordsFromAddress($address);
            $objResponse->addScriptCall('joms.location.setCurrentCoord', $address,$coords->geometry->location->lat,$coords->geometry->location->lng);
            return $objResponse->sendResponse();
        }

        /**
         * 
         * @param type $coords
         */
        public function ajaxGetAddressFromCoords($coords) {
            $objResponse = new JAXResponse();
            $model = CFactory::getModel('activities');
            $user = CFactory::getUser();
            $locations = $model->getUserVisitedLocation($user->id);

            $address = array();

            /**
             * Get address from array of coors
             */
            $coords = array_filter($coords);
            foreach ($coords as $coord) {
                if (isset($coord[0]) && isset($coords[1])) {
                    /* Get address from coords */
                    $geoAddress = $this->_getAddressFromCoord($coord[0], $coord[1]);
                    if ($geoAddress) {
                        /* Store array of address */
                        $address[] = array(
                            'lat' => $coord[0],
                            'lng' => $coord[1],
                            'name' => $geoAddress->formatted_address
                        );
                    }
                }
            }
            /**
             * Get address from database
             */
            if ($locations) {
                foreach ($locations as $location) {
                    $address[] = array(
                        'lat' => $location->latitude,
                        'lng' => $location->longitude,
                        'name' => $location->location
                    );
                }
            }
            /**
             * These address will use for auto complete
             */
            $address = array_filter($address);
            $objResponse->addScriptCall('joms.location.updateAddress', $address);
            /**
             * Do update init location
             * @todo We'll need find out which address is better for init
             */
            $objResponse->addScriptCall('joms.sharebox.location.initLocation', $address[0]['name'], $address[0]['lat'], $address[0]['lng']);
            /*  */
            $objResponse->sendResponse($locations);
        }

        /**
         * Get address from coord
         * @param type $latitude
         * @param type $longitude
         * @return type
         */
        private function _getAddressFromCoord($latitude, $longitude) {
            $googleService = new CServiceGoogle();
            $address = $googleService->getAddressFromCoords($latitude, $longitude);
            /* Do cleanup to remove no need data */
            $session = JFactory::getSession();
            $session->set('ccoords', $address);
            return $address;
        }

    }

}
