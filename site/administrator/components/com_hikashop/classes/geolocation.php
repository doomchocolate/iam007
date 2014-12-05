<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopGeolocationClass extends hikashopClass{
	var $tables = array('geolocation');
	var $pkeys = array('geolocation_id');

	function save(&$element){
		if(empty($element->geolocation_id) || !empty($element->geolocation_ip)){
			if(empty($element->geolocation_ip)){
				return false;
			}

			$location = $this->getIPLocation($element->geolocation_ip);
			if(empty($location)){
				return false;
			}
			$element->geolocation_latitude = @$location->Latitude;
			$element->geolocation_longitude = @$location->Longitude;
			$element->geolocation_postal_code = @$location->ZipPostalCode;
			$element->geolocation_country = @$location->CountryName;
			$element->geolocation_country_code = @$location->CountryCode;
			$element->geolocation_state = @$location->RegionName;
			$element->geolocation_state_code = @$location->RegionCode;
			$element->geolocation_city = @$location->City;
			$element->geolocation_created = time();
		}
		return parent::save($element);
	}

	function getIPLocation($ip){
		$plugin = JPluginHelper::getPlugin('system', 'hikashopgeolocation');
		if(empty($plugin) || empty($plugin->params)) return false;
		jimport('joomla.html.parameter');
		$this->params = new HikaParameter( $plugin->params );
		$geoClass = hikashop_get('inc.geolocation');
		$api_key = $this->params->get('geoloc_api_key','');
		if(empty($api_key)) return false;
		$timeout = $this->params->get('geoloc_timeout',2);
		if(!empty($timeout)) $geoClass->setTimeout($timeout);
		$geoClass->setKey($api_key);
		$locations = $geoClass->getCountry($ip);
		if(!empty($location->countryCode) && $location->countryCode =='UK'){
			$location->countryCode='GB';
		}
		return $locations;
	}
}
