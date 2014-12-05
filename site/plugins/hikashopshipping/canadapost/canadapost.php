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

class plgHikashopshippingCANADAPOST extends hikashopShippingPlugin {
	var $canadapost_methods = array(
		array('key' => 1, 'name' => 'Priority Courier', 'countries' => 'CANADA'),
		array('key' => 2, 'name' => 'Xpresspost', 'countries' => 'CANADA'),
		array('key' => 3, 'name' => 'Regular', 'countries' => 'CANADA'),
		array('key' => 4, 'name' => 'Priority Worldwide USA', 'countries' => 'USA'),
		array('key' => 5, 'name' => 'Xpresspost USA', 'countries' => 'USA'),
		array('key' => 6, 'name' => 'Small Packets Air', 'countries' => 'USA'),
		array('key' => 7, 'name' => 'Small Packets Surface', 'countries' => 'USA'),
		array('key' => 8, 'name' => 'Priority Worldwide INTL', 'countries' => 'ALL'),
		array('key' => 9, 'name' => 'XPressPost International', 'countries' => 'ALL'),
		array('key' => 10, 'name' => 'Small Packets Air', 'countries' => 'ALL'),
		array('key' => 11, 'name' => 'Parcel Surface', 'countries' => 'ALL'),
		array('key' => 12, 'name' => 'Small Packets Surface', 'countries' => 'ALL'),
		array('key' => 13, 'name' => 'Expedited', 'countries' => 'ALL'),
	);
	var $convertUnit = array('kg' => 'KGS', 'lb' => 'LBS', 'cm' => 'CM', 'in' => 'IN', 'kg2' => 'kg', 'lb2' => 'lb', 'cm2' => 'cm', 'in2' => 'in', );
	public $nbpackage = 0;
	public $package_added = 0;

	var $multiple = true;
	var $name = 'canadapost';
	var $doc_form = 'canadapost';


	function shippingMethods(&$main) {
		$methods = array();
		if (!empty($main -> shipping_params -> methodsList)) {
			$main -> shipping_params -> methods = unserialize($main -> shipping_params -> methodsList);
		}
		if (!empty($main -> shipping_params -> methods)) {
			foreach ($main->shipping_params->methods as $key => $value) {
				$selected = null;
				foreach ($this->canadapost_methods as $canadapost) {
					if ($canadapost['name'] == $key)
						$selected = $canadapost;
				}
				if ($selected) {
					$methods[$main->shipping_id . '-' . $selected['key']] = $selected['name'];
				}
			}
		}
		return $methods;
	}

	function onShippingDisplay(&$order, &$dbrates, &$usable_rates, &$messages) {
			if(!hikashop_loadUser())
				return false;
			$local_usable_rates = array();
			$local_messages = array();
			$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
			if($ret === false)
				return false;

			$currentShippingZone = null;
			$currentCurrencyId = null;
			$found = true;
			$usableWarehouses = array();
			$zoneClass = hikashop_get('class.zone');
			$zones = $zoneClass -> getOrderZones($order);
			if (!function_exists('curl_init')) {
				$app = JFactory::getApplication();
				$app -> enqueueMessage('The CANADAPOST shipping plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.', 'error');
				return false;
			}
			foreach ($local_usable_rates as $k => $rate) {
				if (!empty($rate -> shipping_params -> warehousesList)) {
					$rate -> shipping_params -> warehouses = unserialize($rate -> shipping_params -> warehousesList);
				} else {
					$messages['no_warehouse_configured'] = 'No warehouse configured in the CANADA POST shipping plugin options';
					continue;
				}
				foreach ($rate->shipping_params->warehouses as $warehouse) {
					if (empty($warehouse -> zone) || $warehouse -> zone == '-' || in_array($warehouse -> zone, $zones)) {
						$usableWarehouses[] = $warehouse;
					}
				}
				if (empty($usableWarehouses)) {
					$messages['no_warehouse_configured'] = 'No available warehouse found for your location';
					continue;
				}
				if (!empty($rate -> shipping_params -> methodsList)) {
					$rate -> shipping_params -> methods = unserialize($rate -> shipping_params -> methodsList);
				} else {
					$messages['no_shipping_methods_configured'] = 'No shipping methods configured in the CANADA POST shipping plugin options';
					continue;
				}
				if ($order -> weight <= 0 || $order -> volume <= 0) {
					return true;
				}
				$data = null;
				if (empty($order -> shipping_address)) {
					$messages['no_shipping_address_found'] = 'No shipping address entered';
					continue;
				}

				$this -> shipping_currency_id = hikashop_getCurrency();
				$db = JFactory::getDBO();
				$query = 'SELECT currency_code FROM ' . hikashop_table('currency') . ' WHERE currency_id IN (' . $this -> shipping_currency_id . ')';
				$db -> setQuery($query);
				$this -> shipping_currency_code = $db -> loadResult();
				$cart = hikashop_get('class.cart');
				$null = null;
				$cart -> loadAddress($null, $order -> shipping_address -> address_id, 'object', 'shipping');
				$currency = hikashop_get('class.currency');

				$receivedMethods = $this -> _getBestMethods($rate, $order, $usableWarehouses, $null);
				if (empty($receivedMethods)) {
					$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
					continue;
				}
				$i = 0;
				$local_usable_rates = array();
				foreach ($receivedMethods as $method) {
					$local_usable_rates[$i] = (!HIKASHOP_PHP5) ? $rate : clone($rate);
					$local_usable_rates[$i] -> shipping_price += $method['value'];
					$selected_method = '';
					$name = '';
					foreach ($this->canadapost_methods as $canadapost_method) {
						if ($canadapost_method['name'] == $method['name']) {
							$name = $canadapost_method['name'];
							$selected_method = $canadapost_method['key'];
						}
					}
					$local_usable_rates[$i]->shipping_name = $name;
					if(!empty($selected_method))
						$local_usable_rates[$i]->shipping_id .= '-' . $selected_method;

					if ($method['deliveryDate'] != 'www.canadapost.ca') {
						if (is_numeric($method['deliveryDate'])) {
							$timestamp = strtotime($method['deliveryDate']);
							$time = $this -> displayDelaySECtoDAY($timestamp - strtotime('now'), 2);
							$local_usable_rates[$i] -> shipping_description .= 'Estimated delivery date:  ' . $time;
						} else {
							$time = $method['deliveryDate'];
							$local_usable_rates[$i] -> shipping_description .= 'Estimated delivery date:  ' . $time;
						}

					} else {
						$local_usable_rates[$i] -> shipping_description .= ' ' . JText::_('NO_ESTIMATED_TIME_AFTER_SEND');
					}
					if ($rate -> shipping_params -> group_package == 1 && $this -> nbpackage > 1)
						$local_usable_rates[$i] -> shipping_description .= '<br/>' . JText::sprintf('X_PACKAGES', $this -> nbpackage);
					$i++;
				}
				foreach ($local_usable_rates as $i => $rate) {
					$usable_rates[$rate->shipping_id] = $rate;
				}
			}
		}
	function getShippingDefaultValues(&$element){
		$element -> shipping_name = 'CANADA POST';
		$element -> shipping_description = '';
		$element -> group_package = 0;
		$element -> shipping_images = 'canadapost';
		$element -> shipping_params -> post_code = '';
		$element -> shipping_currency_id = $this -> main_currency;
	}
	function onShippingConfiguration(&$element) {
		$config = &hikashop_config();
		$this -> main_currency = $config -> get('main_currency', 1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_get('class.currency');
		$this -> currencyCode = $currency -> get($this -> main_currency)->currency_code;
		$this -> currencySymbol = $currency -> get($this -> main_currency)->currency_symbol;

		$this -> canadapost = JRequest::getCmd('name', 'canadapost');
		$this -> categoryType = hikashop_get('type.categorysub');
		$this -> categoryType -> type = 'tax';
		$this -> categoryType -> field = 'category_id';

		parent::onShippingConfiguration($element);
		$elements = array($element);
		$key = key($elements);
		if (!empty($elements[$key] -> shipping_params -> warehousesList)) {
			$elements[$key] -> shipping_params -> warehouse = unserialize($elements[$key] -> shipping_params -> warehousesList);
		}
		if (!empty($elements[$key] -> shipping_params -> methodsList)) {
			$elements[$key] -> shipping_params -> methods = unserialize($elements[$key] -> shipping_params -> methodsList);
		}
		$js = '
		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display=\'none\';
			}
			return false;
		}
		function deleteZone(zoneName){
			var d = document.getElementById(zoneName);
			if(d){
				d.innerHTML="";
			}
			return false;
		}
		';
	 	$js.="
			function checkAllBox(id, type){
				var toCheck = document.getElementById(id).getElementsByTagName('input');
				for (i = 0 ; i < toCheck.length ; i++) {
					if (toCheck[i].type == 'checkbox') {
						if(type == 'check'){
							toCheck[i].checked = true;
						}else{
							toCheck[i].checked = false;
						}
					}
				}
			}";

		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onShippingConfigurationSave(&$element) {

		$warehouses = JRequest::getVar('warehouse', array(), '', 'array');
		$cats = array();
		$methods = array();
		$db = JFactory::getDBO();
		$zone_keys = '';
		$app = JFactory::getApplication();

		if(empty($element -> shipping_params -> merchant_ID)){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'Canada POST', JText::_('ATOS_MERCHANT_ID')));
		}
		if (isset($_REQUEST['data']['shipping_methods'])) {
			foreach ($_REQUEST['data']['shipping_methods'] as $method) {
				foreach ($this->canadapost_methods as $canadapostMethod) {
					$name = $canadapostMethod['name'];
					if ($name == $method['name']) {
						$obj = new stdClass();
						$methods[strip_tags($method['name'])] = '';
					}
				}
			}
		}else{
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));
		}
		$element -> shipping_params -> methodsList = serialize($methods);

		if (!empty($warehouses)) {
			foreach ($warehouses as $id => $warehouse) {
				if (!empty($warehouse['zone']))
					$zone_keys .= 'zone_namekey=' . $db -> Quote($warehouse['zone']) . ' OR ';
			}
			$zone_keys = substr($zone_keys, 0, -4);
			if (!empty($zone_keys)) {
				$query = ' SELECT zone_namekey, zone_id, zone_name_english FROM ' . hikashop_table('zone') . ' WHERE ' . $zone_keys;
				$db -> setQuery($query);
				$zones = $db -> loadObjectList();
			}
			foreach ($warehouses as $id => $warehouse) {
				$warehouse['zone_name'] = '';
				if (!empty($zones)) {
					foreach ($zones as $zone) {
						if ($zone -> zone_namekey == $warehouse['zone'])
							$warehouse['zone_name'] = $zone -> zone_id . ' ' . $zone -> zone_name_english;
					}
				}
				if (empty($_REQUEST['warehouse'][$id]['zip'])) {
					$_REQUEST['warehouse'][$id]['zip'] = '-';
				}
				if (@$_REQUEST['warehouse'][$id]['zip'] != '-') {
					$obj = new stdClass();
					$obj -> name = strip_tags($_REQUEST['warehouse'][$id]['name']);
					$obj -> zip = strip_tags($_REQUEST['warehouse'][$id]['zip']);
					$obj -> zone = @strip_tags($_REQUEST['warehouse'][$id]['zone']);
					$obj -> zone_name = $warehouse['zone_name'];
					$obj -> units = strip_tags($_REQUEST['warehouse'][$id]['units']);
					$cats[] = $obj;
				}
			}
			$element -> shipping_params -> warehousesList = serialize($cats);
		}
		if (empty($cats)) {
			$obj = new stdClass();
			$obj -> name = '-';
			$obj -> zip = '-';
			$obj -> zone = '-';
			$void[] = $obj;
			$element -> shipping_params -> warehousesList = serialize($void);
		}
		return true;
	}

	function _getBestMethods(&$rate, &$order, &$usableWarehouses, $null) {
		$db = JFactory::getDBO();
		$usableMethods = array();
		$query = 'SELECT zone_id, zone_code_2 FROM ' . hikashop_table('zone') . ' WHERE zone_id = 38';
		$db -> setQuery($query);
		$warehouses_namekey = $db -> loadObjectList();
		foreach ($usableWarehouses as $warehouse) {
			foreach ($warehouses_namekey as $zone) {
				if ($zone -> zone_id == 38) {
					$warehouse -> country_ID = $zone -> zone_code_2;
				}
			}
		}
		foreach ($usableWarehouses as $k => $warehouse) {
			$usableWarehouses[$k] -> methods = $this -> _getShippingMethods($rate, $order, $warehouse, $null);
		}
		if (empty($usableWarehouses)) {
			return false;
		}
		$method_available = '';

		foreach ($usableWarehouses as $k => $warehouse) {
			if (!empty($warehouse -> methods)) {
				$j = 0;
				foreach ($rate->shipping_params->methods as $shipping_method => $empty) {
					$method_available[$j] = $shipping_method;
					$j++;
				}
				foreach ($warehouse->methods as $i => $method) {
					if (!in_array($method['name'], $method_available))
						unset($usableWarehouses[$k] -> methods[$i]);
				}
			}
		}
		$bestPrice = 99999999;

		foreach ($usableWarehouses as $id => $warehouse) {
			if (!empty($warehouse -> methods)) {
				foreach ($warehouse->methods as $method) {
					if ($method['value'] < $bestPrice) {
						$bestPrice = $method['value'];
						$bestWarehouse = $id;
					}
				}
			}
		}
		if (isset($bestWarehouse)) {
			return $usableWarehouses[$bestWarehouse] -> methods;
		} else {
			return false;
		}
	}

	function _getShippingMethods(&$rate, &$order, &$warehouse, $null) {
		$data = array();
		$data['merchant_ID'] = $rate -> shipping_params -> merchant_ID;
		$data['turnaround_time'] = $rate -> shipping_params -> turnaround_time;
		$data['destCity'] = $null -> shipping_address -> address_city;
		$data['destState'] = $null -> shipping_address -> address_state;
		$data['destZip'] = $null -> shipping_address -> address_post_code;
		$data['destCountry'] = $null -> shipping_address -> address_country -> zone_code_2;
		$data['units'] = $warehouse -> units;
		$data['zip'] = $warehouse -> zip;
		$data['XMLpackage'] = '';
		$data['destType'] = '';
		$data['XMLpackage'] = '
					<?xml version="1.0" ?>
					<eparcel>
					<language>EN</language>
					<ratesAndServicesRequest>
					<merchantCPCID> ' . $data['merchant_ID'] . ' </merchantCPCID>
					<fromPostalCode> ' . $data['zip'] . ' </fromPostalCode>
				';
		if (!empty($rate -> shipping_params -> turnaround_time))
			$data['XMLpackage'] .= '<turnAroundTime> ' . $data['turnaround_time'] . ' </turnAroundTime>';
		$data['XMLpackage'] .= ' <lineItems> ';
		$totalPrice = 0;
		if (!$rate -> shipping_params -> group_package || $rate -> shipping_params -> group_package == 0) {
			$data['weight'] = 0;
			$data['height'] = 0;
			$data['length'] = 0;
			$data['width'] = 0;
			$data['price'] = 0;
			$data['quantity'] = 0;
			$data['name'] = '';
			foreach ($order->products as $product) {
				if ($product -> product_parent_id == 0) {
					if (isset($product -> variants)) {
						foreach ($product->variants as $variant) {
							$caracs = $this -> _convertCharacteristics($variant, $data);
							$data['weight'] = round($caracs['weight'], 2) * $variant -> cart_product_quantity;
							$data['height'] = round($caracs['height'], 2) * $variant -> cart_product_quantity;
							$data['length'] = round($caracs['length'], 2) * $variant -> cart_product_quantity;
							$data['width'] = round($caracs['width'], 2) * $variant -> cart_product_quantity;
							$data['price'] = $variant -> prices[0] -> unit_price -> price_value_with_tax * $variant -> cart_product_quantity;
							$data['name'] = $variant -> main_product_name . ' :' . $variant -> characteristics_text;
							$data['quantity'] = $variant -> cart_product_quantity;
							$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
						}
					} else {
						$caracs = $this -> _convertCharacteristics($product, $data);
						$data['weight'] = round($caracs['weight'], 2) * $product -> cart_product_quantity;
						$data['height'] = round($caracs['height'], 2) * $product -> cart_product_quantity;
						$data['length'] = round($caracs['length'], 2) * $product -> cart_product_quantity;
						$data['width'] = round($caracs['width'], 2) * $product -> cart_product_quantity;
						$data['price'] = $product -> prices[0] -> price_value_with_tax * $product -> cart_product_quantity;
						$data['name'] = $product -> product_name;
						$data['quantity'] = $product -> cart_product_quantity;
						$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
					}
				}
			}
			$data['XMLpackage'] .= '</lineItems>';
			$data['XMLpackage'] .= '<city> ' . $data['destCity'] . ' </city>';
			$data['XMLpackage'] .= '<provOrState> ' . $data['destState'] -> zone_name . ' </provOrState>';
			$data['XMLpackage'] .= '<country> ' . $data['destCountry'] . ' </country>
							<postalCode>' . $data['destZip'] . '</postalCode>
							';
			$data['XMLpackage'] .= '</ratesAndServicesRequest>
												</eparcel> ';
			$usableMethods = $this -> _RequestMethods($data, $data['XMLpackage']);
			return $usableMethods;

		} else {
			$data['weight'] = 0;
			$data['height'] = 0;
			$data['length'] = 0;
			$data['width'] = 0;
			$data['price'] = 0;
			$data['quantity'] = 1;
			$data['name'] = 'grouped package';
			$this -> package_added = 0;
			$this -> nbpackage = 0;
			$limitation = array('length' => 50, 'weight' => 10, 'width' => 50, 'height' => 50);
			if (!empty($rate -> shipping_params -> dim_approximation_l)) {
				$l_length = $rate -> shipping_params -> dim_approximation_l;
				$l_weight = $rate -> shipping_params -> dim_approximation_kg;
				$l_height = $rate -> shipping_params -> dim_approximation_h;
				$l_width = $rate -> shipping_params -> dim_approximation_w;
			}
			if ($l_length > 0 && $limitation['length'] > $l_length) {
				$limitation['length'] = $l_length;
			}
			if ($l_weight > 0 && $limitation['weight'] > $l_weight) {
				$limitation['weight'] = $l_weight;
			}
			if ($l_height > 0 && $limitation['height'] > $l_height) {
				$limitation['height'] = $l_height;
			}
			if ($l_width > 0 && $limitation['width'] > $l_width) {
				$limitation['width'] = $l_width;
			}
		}
		foreach ($order->products as $product) {
			if ($product -> product_parent_id == 0) {
				if (isset($product -> variants)) {
					foreach ($product->variants as $variant) {
						for ($i = 0; $i < $variant -> cart_product_quantity; $i++) {
							$caracs = $this -> _convertCharacteristics($variant, $data);
							$data['weight_unit'] = $caracs['weight_unit'];
							$data['dimension_unit'] = $caracs['dimension_unit'];
							$tmpHeight = $data['height'] + round($caracs['height'], 2);
							$tmpWidth = $data['length'] + round($caracs['length'], 2);
							$tmpLength = $data['width'] + round($caracs['width'], 2);
							$dim = $tmpLength + 2 * $tmpWidth + 2 * $tmpHeight;
							$x = min($caracs['width'], $caracs['height'], $caracs['length']);
							if ($x == $caracs['width']) {
								$y = min($caracs['height'], $caracs['length']);
								if ($y == $caracs['height'])
									$z = $caracs['length'];
								else
									$z = $caracs['height'];
							}
							if ($x == $caracs['height']) {
								$y = min($caracs['width'], $caracs['length']);
								if ($y == $caracs['width'])
									$z = $caracs['length'];
								else
									$z = $caracs['width'];
							}
							if ($x == $caracs['length']) {
								$y = min($caracs['height'], $caracs['width']);
								if ($y == $caracs['height'])
									$z = $caracs['width'];
								else
									$z = $caracs['height'];
							}
							if ($data['weight'] + round($caracs['weight'], 2) > $limitation['weight'] || $tmpWidth > $limitation['length'] || $tmpHeight > $limitation['height'] || $tmpLength > $limitation['width']) {
								if ($this -> package_added == 0)
									$this -> nbpackage++;
								$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
								$data['weight'] = round($caracs['weight'], 2);
								$data['height'] = $y;
								$data['length'] = $z;
								$data['width'] = $x;
								$data['price'] = $variant -> prices[0] -> unit_price -> price_value_with_tax;
							} else {
								$data['weight'] += round($caracs['weight'], 2);
								$data['height'] = max($data['height'], $y);
								$data['length'] = max($data['length'], $z);
								$data['width'] += $x;
								$data['price'] += $variant -> prices[0] -> unit_price -> price_value_with_tax;

							}
						}
					}
				} else {
					for ($i = 0; $i < $product -> cart_product_quantity; $i++) {
						$caracs = $this -> _convertCharacteristics($product, $data);
						$x = min($caracs['width'], $caracs['height'], $caracs['length']);
						if ($x == $caracs['width']) {
							$y = min($caracs['height'], $caracs['length']);
							if ($y == $caracs['height'])
								$z = $caracs['length'];
							else
								$z = $caracs['height'];
						}
						if ($x == $caracs['height']) {
							$y = min($caracs['width'], $caracs['length']);
							if ($y == $caracs['width'])
								$z = $caracs['length'];
							else
								$z = $caracs['width'];
						}
						if ($x == $caracs['length']) {
							$y = min($caracs['height'], $caracs['width']);
							if ($y == $caracs['height'])
								$z = $caracs['width'];
							else
								$z = $caracs['height'];
						}
						$data['weight_unit'] = $caracs['weight_unit'];
						$data['dimension_unit'] = $caracs['dimension_unit'];
						$tmpHeight = $data['height'] + round($caracs['height'], 2);
						$tmpWidth = $data['length'] + round($caracs['length'], 2);
						$tmpLength = $data['width'] + round($caracs['width'], 2);
						$dim = $tmpLength + 2 * $tmpWidth + 2 * $tmpHeight;
						if ($data['weight'] + round($caracs['weight'], 2) > $limitation['weight'] || $tmpWidth > $limitation['length'] || $tmpHeight > $limitation['height'] || $tmpLength > $limitation['width']) {
							if ($this -> package_added == 0)
								$this -> nbpackage++;
							$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
							$data['weight'] = round($caracs['weight'], 2);
							$data['height'] = $y;
							$data['length'] = $z;
							$data['width'] = $x;
							$data['price'] = $product -> prices[0] -> price_value_with_tax;
						} else {
							$data['weight'] += round($caracs['weight'], 2);
							$data['height'] = max($data['height'], $y);
							$data['length'] = max($data['length'], $z);
							$data['width'] += $x;
							$data['price'] += $product -> prices[0] -> price_value_with_tax;
						}
					}
				}
			}
		}
		if (($data['weight'] + $data['height'] + $data['length'] + $data['width']) > 0 && $this -> package_added == 0) {
			$this -> package_added = 1;
			$this -> nbpackage++;
			$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
		}
		$data['XMLpackage'] .= '</lineItems>';
		$data['XMLpackage'] .= '<city> ' . $data['destCity'] . ' </city>';
		$data['XMLpackage'] .= '<provOrState> ' . $data['destState'] -> zone_name . ' </provOrState>';
		$data['XMLpackage'] .= '<country> ' . $data['destCountry'] . ' </country>
					<postalCode>' . $data['destZip'] . '</postalCode>
					';
		$data['XMLpackage'] .= '</ratesAndServicesRequest>
										</eparcel> ';
		$usableMethods = $this -> _RequestMethods($data, $data['XMLpackage']);
		return $usableMethods;
	}

	function _createPackage(&$data, &$product, &$rate, &$order) {
		if (empty($data['weight']) && !empty($product -> product_weight_orig)) {
			$data['weight'] = round($product -> product_weight_orig, 2);
			$data['height'] = round($product -> product_height, 2);
			$data['length'] = round($product -> product_length, 2);
			$data['width'] = round($product -> product_width, 2);
		}

		if (isset($data['price'])) {
			$price = $data['price'];
		} else {
			$price = $product -> prices[0] -> unit_price -> price_value;
		}
		if (!empty($rate -> shipping_params -> weight_approximation)) {
			$data['weight'] = $data['weight'] + $data['weight'] * $rate -> shipping_params -> weight_approximation / 100;
		}
		if ($data['weight'] < 1) {
			$data['weight'] = 1;
		}
		if (!empty($rate -> shipping_params -> dim_approximation)) {
			$data['height'] = $data['height'] + $data['height'] * $rate -> shipping_params -> dim_approximation / 100;
			$data['length'] = $data['length'] + $data['length'] * $rate -> shipping_params -> dim_approximation / 100;
			$data['width'] = $data['width'] + $data['width'] * $rate -> shipping_params -> dim_approximation / 100;
		}
		$xml = '';
		$xml .= '	<item>
											 <quantity> ' . $data['quantity'] . ' </quantity>
											 <weight> ' . $data['weight'] . ' </weight>
									 <length> ' . $data['width'] . ' </length>
									 <width> ' . $data['length'] . ' </width>
											 <height> ' . $data['height'] . ' </height>
											 <description> ' . $data['name'] . ' </description>';
		if ($rate -> shipping_params -> readyToShip)
			$xml .= '<readyToShip/>';
		$xml .= '</item>';
		return $xml;
	}

	function _RequestMethods($data, $xml) {
		$app = JFactory::getApplication();
		$session = curl_init("cybervente.postescanada.ca");
		curl_setopt($session, CURLOPT_HEADER, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_PORT, 30000);
		curl_setopt($session, CURLOPT_TIMEOUT, 30);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
		$result = curl_exec($session);
		$error = curl_errno($session);

		if (!$error) {

			$xml = strstr($result, '<?');
			$xml_parser = xml_parser_create();
			$vals = null;
			$index = null;
			xml_parse_into_struct($xml_parser, $xml, $vals, $index);
			xml_parser_free($xml_parser);
			$params = array();
			$level = array();
			$shipmentNumber = 0;
			$errorNumber = 0;
			foreach ($vals as $xml_elem) {
				if ($xml_elem['type'] == 'open') {
					if (array_key_exists('attributes', $xml_elem)) {
						list($level[$xml_elem['level']], $extra) = array_values($xml_elem['attributes']);
						if ($xml_elem['tag'] == 'PRODUCT') {
							$shipmentNumber++;
							$xml_elem['tag'] = $xml_elem['tag'] . '_' . $shipmentNumber;
							$level[$xml_elem['level']] = $xml_elem['tag'];
						}
					} else {
						$level[$xml_elem['level']] = $xml_elem['tag'];
					}
				}
				if ($xml_elem['type'] == 'complete') {
					if ($xml_elem['tag'] == 'STATUSCODE') {
						$stat = (int)trim($xml_elem['value']);
						if ($stat != 1) {
							$errorNumber++;
						}
					}
					if (empty($xml_elem['value'])) {
						$xml_elem['value'] = -1;
					}
					$start_level = 1;
					$php_stmt = '$params';
					while ($start_level < $xml_elem['level']) {
						$php_stmt .= '[$level[' . $start_level . ']]';
						$start_level++;
					}
					$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
					eval($php_stmt);
				}
			}
			$shipment = array();
			for ($i = 1; $i <= $shipmentNumber; $i++) {
				$shipment[$i]['value'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['PRODUCT_' . $i]['RATE'];
				$shipment[$i]['name'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['PRODUCT_' . $i]['NAME'];
				$shipment[$i]['shippingDate'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['PRODUCT_' . $i]['SHIPPINGDATE'];
				$shipment[$i]['deliveryDate'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['PRODUCT_' . $i]['DELIVERYDATE'];
				$shipment[$i]['deliveryDayOfWeek'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['PRODUCT_' . $i]['DELIVERYDAYOFWEEK'];
				$shipment[$i]['nextDayAM'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['PRODUCT_' . $i]['NEXTDAYAM'];
				$shipment[$i]['status_code'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['STATUSCODE'];
				$shipment[$i]['status_message'] = $params['EPARCEL']['RATESANDSERVICESRESPONSE']['STATUSMESSAGE'];
			}
			if (isset($params['EPARCEL']['ERROR']['STATUSCODE']) && $params['EPARCEL']['ERROR']['STATUSCODE'] != 1) {
				$app -> enqueueMessage('Error while sending XML to CANADA POST. Error code: ' . $params['EPARCEL']['ERROR']['STATUSCODE'] . '. Message: ' . $params['EPARCEL']['ERROR']['STATUSMESSAGE'] . '', 'error');
				return false;
			}
			return $shipment;
		} else {
			$app -> enqueueMessage('An error occurred. The connection to the Canada Post server could not be established: ' . curl_error($session));
			$do = false;
		}
		curl_close($session);

	}

	function _convertCharacteristics(&$product, $data, $forceUnit = false) {
		$weightClass = hikashop_get('helper.weight');
		$volumeClass = hikashop_get('helper.volume');
		if (!isset($product -> product_dimension_unit_orig))
			$product -> product_dimension_unit_orig = $product -> product_dimension_unit;
		if (!isset($product -> product_weight_unit_orig))
			$product -> product_weight_unit_orig = $product -> product_weight_unit;
		if (!isset($product -> product_weight_orig))
			$product -> product_weight_orig = $product -> product_weight;
		if ($forceUnit) {
			$carac['weight'] = $weightClass -> convert($product -> product_weight_orig, $product -> product_weight_unit_orig, 'kg');
			$carac['weight_unit'] = 'KGS';
			$carac['height'] = $volumeClass -> convert($product -> product_height, $product -> product_dimension_unit_orig, 'cm', 'dimension');
			$carac['length'] = $volumeClass -> convert($product -> product_length, $product -> product_dimension_unit_orig, 'cm', 'dimension');
			$carac['width'] = $volumeClass -> convert($product -> product_width, $product -> product_dimension_unit_orig, 'cm', 'dimension');
			$carac['dimension_unit'] = 'CM';
			return $carac;
		}
		$data['units'] = 'kg';

		if ($data['units'] == 'kg') {
			if ($product -> product_weight_unit_orig == 'kg') {
				$carac['weight'] = $product -> product_weight_orig;
				$carac['weight_unit'] = $this -> convertUnit[$product -> product_weight_unit_orig];
			} else {
				$carac['weight'] = $weightClass -> convert($product -> product_weight_orig, $product -> product_weight_unit_orig, 'kg');
				$carac['weight_unit'] = 'KGS';
			}
			if ($product -> product_dimension_unit_orig == 'cm') {
				$carac['height'] = $product -> product_height;
				$carac['length'] = $product -> product_length;
				$carac['width'] = $product -> product_width;
				$carac['dimension_unit'] = $this -> convertUnit[$product -> product_dimension_unit_orig];
			} else {
				$carac['height'] = $volumeClass -> convert($product -> product_height, $product -> product_dimension_unit_orig, 'cm', 'dimension');
				$carac['length'] = $volumeClass -> convert($product -> product_length, $product -> product_dimension_unit_orig, 'cm', 'dimension');
				$carac['width'] = $volumeClass -> convert($product -> product_width, $product -> product_dimension_unit_orig, 'cm', 'dimension');
				$carac['dimension_unit'] = 'CM';
			}
		}
		return $carac;
	}

	function displayDelaySECtoDAY($value, $type) {
		if ($type == 0) {
			$value = (int)$value / 60;
			$value = round($value);
		}
		if ($type == 1) {
			$value = (int)$value / 3600;
			$value = round($value);
		}
		if ($type == 2) {
			$value = (int)$value / 86400;
			$value = round($value);
		}
		return $value;
	}

}
