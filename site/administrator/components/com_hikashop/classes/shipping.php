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
class hikashopShippingClass extends hikashopClass{
	var $tables = array('shipping');
	var $pkeys = array('shipping_id');
	var $deleteToggle = array('shipping'=>array('shipping_type','shipping_id'));
	var $toggle = array('shipping_published'=>'shipping_id');

	function save(&$element,$reorder=true){
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if(empty($element->shipping_id))
			$dispatcher->trigger('onBeforeHikaPluginCreate', array('shipping', &$element, &$do));
		else
			$dispatcher->trigger('onBeforeHikaPluginUpdate', array('shipping', &$element, &$do));

		if(!$do)
			return false;

		if(isset($element->shipping_params) && !is_string($element->shipping_params)){
			$element->shipping_params = serialize($element->shipping_params);
		}

		$status = parent::save($element);
		if($status && empty($element->shipping_id)){
			$element->shipping_id = $status;
			if($reorder){
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'shipping_id';
				$orderClass->table = 'shipping';
				$orderClass->groupMap = 'shipping_type';
				$orderClass->groupVal = $element->shipping_type;
				$orderClass->orderingMap = 'shipping_ordering';
				$orderClass->reOrder();
			}
		}

		if($status && !empty($element->shipping_published) && !empty($element->shipping_id)) {
			$db = JFactory::getDBO();
			$query = 'SELECT shipping_type FROM ' . hikashop_table('shipping') . ' WHERE shipping_id = ' . (int)$element->shipping_id;
			$db->setQuery($query);
			$name = $db->loadResult();
			if(!HIKASHOP_J16) {
				$query = 'UPDATE '.hikashop_table('plugins',false).' SET published = 1 WHERE published = 0 AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashopshipping');
			} else {
				$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE enabled = 0 AND type = ' . $db->Quote('plugin') . ' AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashopshipping');
			}
			$db->setQuery($query);
			$db->query();
		}
		return $status;
	}

	function delete(&$elements){
		$status = parent::delete($elements);
		if($status){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'shipping_id';
			$orderClass->table = 'shipping';
			$orderClass->groupMap = 'shipping_type';
			$orderClass->orderingMap = 'shipping_ordering';
			$app =& JFactory::getApplication();
			$orderClass->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.shipping_plugin_type','shipping_plugin_type','manual');
			$orderClass->reOrder();
		}
		return $status;
	}

	function get($id,$default=''){
		$result = parent::get($id);
		if(!empty($result->payment_params)){
			$result->payment_params = unserialize($result->payment_params);
		}
		return $result;
	}

	function getMethods(&$order){
		$pluginClass = hikashop_get('class.plugins');
		$rates = $pluginClass->getMethods('shipping');

		if(isset($order->total->prices[0]->price_value) && bccomp($order->total->prices[0]->price_value,0,5) && !empty($rates)){
			$currencyClass = hikashop_get('class.currency');
			$currencyClass->convertShippings($rates);
		}
		return $rates;
	}

	function &getShippings(&$order, $reset = false) {
		static $usable_methods = null;
		static $shipping_groups = null;
		static $errors = array();
		if($reset) {
			$usable_methods = null;
			$errors = array();
			$shipping_groups = null;
		}
		if(!is_null($usable_methods)) {
			$this->errors = $errors;
			$order->shipping_groups =& $shipping_groups;
			return $usable_methods;
		}

		$this->getShippingProductsData($order);

		$zoneClass = hikashop_get('class.zone');
		$zones = $zoneClass->getOrderZones($order);

		$rates = $this->getMethods($order);
		$usable_methods = array();

		$config =& hikashop_config();
		if(!$config->get('force_shipping') && bccomp(@$order->weight, 0, 5) <= 0) {
			return $usable_methods;
		}

		if(empty($rates)) {
			$errors['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
			$this->errors = $errors;
			return $usable_methods;
		}

		$app = JFactory::getApplication();
		$order_clone = new stdClass();
		$variables = array('products','cart_id','coupon','shipping_address','volume','weight','volume_unit','weight_unit');
		foreach($variables as $var){
			if(isset($order->$var)) $order_clone->$var = $order->$var;
		}
		$shipping_key = sha1(serialize($order_clone).serialize($rates));
		if($app->getUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.key',false)){
			$this->errors = $errors = $app->getUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.errors',array());
			$shipping_groups = $app->getUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.shipping_groups',null);
			$usable_methods = $app->getUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.usable_methods',null);
			$order->shipping_groups =& $shipping_groups;
			return $usable_methods;
		}

		foreach($rates as $k => $rate) {
			if(!empty($rate->shipping_zone_namekey) && !in_array($rate->shipping_zone_namekey, $zones)) {
				unset($rates[$k]);
				continue;
			}

			if(!empty($rate->shipping_params->shipping_zip_prefix) || !empty($rate->shipping_params->shipping_min_zip) || !empty($rate->shipping_params->shipping_max_zip) || !empty($rate->shipping_params->shipping_zip_suffix)) {
				$checkDone = false;
				if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code)) {
					if(preg_match('#([a-z]*)([0-9]+)(.*)#i', preg_replace('#[^a-z0-9]#i', '', $order->shipping_address->address_post_code), $match)) {
						$checkDone = true;
						$prefix = $match[1];
						$main = $match[2];
						$suffix = $match[3];
						if(!empty($rate->shipping_params->shipping_zip_prefix) && $rate->shipping_params->shipping_zip_prefix != $prefix) {
							unset($rates[$k]);
							continue;
						}
						if(!empty($rate->shipping_params->shipping_min_zip) && $rate->shipping_params->shipping_min_zip > $main) {
							unset($rates[$k]);
							continue;
						}
						if(!empty($rate->shipping_params->shipping_max_zip) && $rate->shipping_params->shipping_max_zip < $main) {
							unset($rates[$k]);
							continue;
						}
						if(!empty($rate->shipping_params->shipping_zip_suffix) && $rate->shipping_params->shipping_zip_suffix != $suffix) {
							unset($rates[$k]);
							continue;
						}
					}
				}
				if(!$checkDone) {
					unset($rates[$k]);
					continue;
				}
			}
		}

		if(empty($rates)) {
			if(hikashop_loadUser())
				$errors['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
			$this->errors = $errors;
			return $usable_methods;
		}

		$shipping_groups = $this->getShippingGroups($order, $rates);

		JPluginHelper::importPlugin('hikashopshipping');
		$dispatcher = JDispatcher::getInstance();

		if(!empty($shipping_groups) && count($shipping_groups) > 1) {
			$order_backup = new stdClass();
			$order_backup->products = $order->products;
			$order_backup->weight = $order->weight;
			$order_backup->weight_unit = $order->weight_unit;
			$order_backup->volume = $order->volume;
			$order_backup->volume_unit = $order->volume_unit;
			$order_backup->total_quantity = $order->total_quantity;
			$order_backup->total = $order->total;
			$cartClass = hikashop_get('class.cart');
			$currencyClass = hikashop_get('class.currency');

			foreach($shipping_groups as $key => &$group) {
				$order->products = $group->products;
				$group_usable_methods = array();
				$rates_copy = array();
				if(is_int($key))
					$key = ''.$key;

				$shipping_group_struct = array();
				if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $key, $keys)) {
					$shipping_group_struct = array_combine($keys[1], $keys[2]);
				}

				foreach($rates as $rate) {
					if(empty($rate->shipping_published))
						continue;

					$add_rate = true;
					if(!empty($rate->shipping_params->shipping_warehouse_filter)) {
						$add_rate = false;
						if($key === $rate->shipping_params->shipping_warehouse_filter) {
							$add_rate = true;
						} else {
							$keys = array();
							$tmp = array('' => $rate->shipping_params->shipping_warehouse_filter);
							if(is_string($rate->shipping_params->shipping_warehouse_filter) && preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $rate->shipping_params->shipping_warehouse_filter, $keys)) {
								$tmp = array_combine($keys[1], $keys[2]);
							}
							if(is_array($rate->shipping_params->shipping_warehouse_filter))
								$tmp = $rate->shipping_params->shipping_warehouse_filter;

							if($tmp[''] == $shipping_group_struct[''] || empty($tmp[''])) {
								$add_rate = true;
								foreach($tmp as $k => $v) {
									if($k != '' && (!isset($shipping_group_struct[$k]) || $shipping_group_struct[$k] != $v)) {
										$add_rate = false;
										break;
									}
								}
							}
						}
					}

					if($add_rate)
						$rates_copy[] = clone($rate);
				}

				$cartClass->calculateWeightAndVolume($order);
				$currencyClass->calculateTotal($order->products, $order, $order->total->prices[0]->price_currency_id);

				$dispatcher->trigger('onShippingDisplay', array(&$order, &$rates_copy, &$group_usable_methods, &$errors));

				foreach($group_usable_methods as $method) {
					$group->shippings[] = $method->shipping_id;
					$method->shipping_warehouse_id = $key;
					$usable_methods[] = $method;
				}
				unset($method);
			}

			foreach($order_backup as $k => $v) {
				$order->$k = $v;
			}
		} else {
			$key = array_keys($shipping_groups);
			$key = reset($key);
			if(is_int($key) && !empty($key))
				$key = ''.$key;

			$keys = array();
			if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $key, $keys)) {
				if(count($keys[0]) > 1)
					$key = array_combine($keys[1], $keys[2]);
			}

			foreach($rates as $k => $rate) {
				$rem_rate = false;
				if(!empty($rate->shipping_params->shipping_warehouse_filter)) {
					$rem_rate = true;
					if(!is_array($key)) {
						if($key === $rate->shipping_params->shipping_warehouse_filter) {
							$rem_rate = false;
						} elseif(substr($rate->shipping_params->shipping_warehouse_filter, 0, 1) == '0') {
							$wf = substr($rate->shipping_params->shipping_warehouse_filter, 1);
							$rem_rate = (empty($wf) || substr($key, 1) != $wf);
						}
					} else {
						$keys = array();
						if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $rate->shipping_params->shipping_warehouse_filter, $keys)) {
							$tmp = array_combine($keys[1], $keys[2]);
							if($tmp[''] == $key['']) {
								$rem_rate = false;
								foreach($tmp as $k => $v) {
									if(!isset($key[$k]) || $key[$k] != $v) {
										$rem_rate = true;
										break;
									}
								}
							}
						}
					}
				}

				if($rem_rate) {
					$rates[$k] = null;
					unset($rates[$k]);
				}
			}

			$dispatcher->trigger('onShippingDisplay', array(&$order, &$rates, &$usable_methods, &$errors));

			$g = reset($shipping_groups);
			foreach($usable_methods as $method) {
				$g->shippings[] = $method->shipping_id;
				$method->shipping_warehouse_id = $key;
			}
		}

		if(empty($usable_methods)) {
			$errors['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
			$this->errors = $errors;
			return $usable_methods;
		}
		$this->errors = $errors;

		$app->setUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.key',true);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.errors',$this->errors);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.shipping_groups',$order->shipping_groups);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping.'.$shipping_key.'.usable_methods',$usable_methods);
		return $usable_methods;
	}

	function getShippingProductsData(&$order, $products = array()) {
		if(empty($order->shipping_prices)) {
			$order->shipping_prices = array();
		}

		if(!isset($order->shipping_prices[0])) {
			$order->shipping_prices[0] = new stdClass();
			$order->shipping_prices[0]->all_with_tax = 0;
			$order->shipping_prices[0]->all_without_tax = 0;
			if(isset($order->total->prices[0]->price_value_with_tax)) {
				$order->shipping_prices[0]->all_with_tax = $order->total->prices[0]->price_value_with_tax;
			}
			if(isset($order->full_total->prices[0]->price_value_without_shipping_with_tax)) {
				$order->shipping_prices[0]->all_with_tax = $order->full_total->prices[0]->price_value_without_shipping_with_tax;
			}
			if(isset($order->total->prices[0]->price_value)) {
				$order->shipping_prices[0]->all_without_tax = $order->total->prices[0]->price_value;
			}
			if(isset($order->full_total->prices[0]->price_value_without_shipping)) {
				$order->shipping_prices[0]->all_without_tax = $order->full_total->prices[0]->price_value_without_shipping;
			}

			$order->shipping_prices[0]->weight = @$order->weight;
			$order->shipping_prices[0]->volume = @$order->volume;
			$order->shipping_prices[0]->total_quantity = @$order->total_quantity;
		}

		$key = 0;
		if(!empty($products)) {
			$product_keys = array_keys($products);
			sort($product_keys);
			$key = implode(',', $product_keys);

			if(!isset($order->shipping_prices[$key]))
				$order->shipping_prices[$key] = new stdClass();
		}

		$order->shipping_prices[$key]->real_with_tax = 0.0;
		$order->shipping_prices[$key]->real_without_tax = 0.0;
		$order->shipping_prices[$key]->products = array();
		$order->shipping_prices[$key]->volume = 0.0;
		$order->shipping_prices[$key]->weight = 0.0;
		$order->shipping_prices[$key]->total_quantity = 0;
		if(!empty($order->products)) {
			$all_products = new stdClass();
			$all_products->products = array();
			$real_products = new stdClass();
			$real_products->products = array();

			$volumeClass = hikashop_get('helper.volume');
			$weightClass = hikashop_get('helper.weight');

			foreach($order->products as $k => $row) {
				if(!empty($products) && !isset($products[$k]))
					continue;

				if(empty($order->shipping_prices[$key]->products[$row->product_id]))
					$order->shipping_prices[$key]->products[$row->product_id] = 0;
				$order->shipping_prices[$key]->products[$row->product_id] += @$row->cart_product_quantity;

				if(!empty($row->product_parent_id)) {
					if(!isset($order->shipping_prices[$key]->products[$row->product_parent_id]))
						$order->shipping_prices[$key]->products[$row->product_parent_id] = 0;
					$order->shipping_prices[$key]->products[$row->product_parent_id] += @$row->cart_product_quantity;
				}

				if(@$row->product_weight > 0)
					$real_products->products[] = $row;

				if($key !== 0)
					$all_products->products[] = $row;

				if($key !== 0 && !empty($row->cart_product_quantity)) {

					if(!empty($row->cart_product_parent_id)) {
						if(!bccomp($row->product_length, 0, 5) || !bccomp($row->product_width, 0, 5) || !bccomp($row->product_height, 0, 5)) {
							foreach($order->products as $l => $elem){
								if($elem->cart_product_id == $row->cart_product_parent_id) {
									$row->product_length = $elem->product_length;
									$row->product_width = $elem->product_width;
									$row->product_height = $elem->product_height;
									$row->product_dimension_unit = $elem->product_dimension_unit;
									break;
								}
							}
						}
						if(!bccomp($row->product_weight, 0, 5)) {
							foreach($order->products as $l => $elem){
								if($elem->cart_product_id == $row->cart_product_parent_id){
									$row->product_weight = $elem->product_weight;
									$row->product_weight_unit = $elem->product_weight_unit;
									break;
								}
							}
						}
					}

					if(bccomp($row->product_length, 0, 5) && bccomp($row->product_width, 0, 5) && bccomp($row->product_height, 0, 5)) {
						if(!isset($row->product_total_volume)) {
							$row->product_volume = $row->product_length * $row->product_width * $row->product_height;
							$row->product_total_volume = $row->product_volume * $row->cart_product_quantity;
							$row->product_total_volume_orig = $row->product_total_volume;
							$row->product_dimension_unit_orig = $row->product_dimension_unit;
							$row->product_total_volume = $volumeClass->convert($row->product_total_volume, $row->product_dimension_unit);
							$row->product_dimension_unit = $order->volume_unit;
						}

						$order->shipping_prices[$key]->volume += $row->product_total_volume;
					}

					if(bccomp($row->product_weight, 0, 5)) {

						if($row->product_weight_unit != $order->weight_unit) {
							$row->product_weight_orig = $row->product_weight;
							$row->product_weight_unit_orig = $row->product_weight_unit;
							$row->product_weight = $weightClass->convert($row->product_weight, $row->product_weight_unit);
							$row->product_weight_unit = $order->weight_unit;
						}

						$order->shipping_prices[$key]->weight += $row->product_weight * $row->cart_product_quantity;
					}

					$order->shipping_prices[$key]->total_quantity += $row->cart_product_quantity;
				}
			}

			$currencyClass = hikashop_get('class.currency');
			$currencyClass->calculateTotal($real_products->products, $real_products->total, hikashop_getCurrency());

			$order->shipping_prices[$key]->real_with_tax = $real_products->total->prices[0]->price_value_with_tax;
			$order->shipping_prices[$key]->real_without_tax = $real_products->total->prices[0]->price_value;

			if($key !== 0) {
				$currencyClass->calculateTotal($all_products->products, $all_products->total, hikashop_getCurrency());
				$order->shipping_prices[$key]->all_with_tax =  $all_products->total->prices[0]->price_value_with_tax;
				$order->shipping_prices[$key]->all_without_tax = $all_products->total->prices[0]->price_value;
				if (!empty($order->coupon))
				{
					if ($order->coupon->discount_flat_amount != 0)
					{
						$order->shipping_prices[$key]->all_with_tax -= $order->coupon->discount_flat_amount;
						$order->shipping_prices[$key]->all_without_tax -= $order->coupon->discount_flat_amount;
					}
					elseif ($order->coupon->discount_percent_amount != 0)
					{
						$order->shipping_prices[$key]->all_with_tax *= $order->coupon->discount_percent_amount / 100;
						$order->shipping_prices[$key]->all_without_tax *= $order->coupon->discount_percent_amount / 100;
					}
				}
			}

			unset($real_products->products);
			unset($real_products);
		}

		return $key;
	}

	function &getShippingGroups(&$order, &$rates) {
		if(!empty($order->shipping_groups))
			return $order->shipping_groups;

		$shipping_groups = array();

		$warehouse = new stdClass();
		$warehouse->name = '';
		$warehouse->products = array();
		$warehouse->shippings = array();

		$shipping_groups[0] = $warehouse;

		if(!empty($order->products)){
			foreach($order->products as &$product) {
				if(@$product->cart_product_quantity <= 0)
					continue;

				if(!empty($product->product_warehouse_id)) {
					if(!isset($shipping_groups[$product->product_warehouse_id])) {
						$w = new stdClass();
						$w->name = '';
						$w->products = array();
						$w->shippings = array();

						$shipping_groups[$product->product_warehouse_id] = $w;
					}
					$shipping_groups[$product->product_warehouse_id]->products[] =& $product;
				} else
					$shipping_groups[0]->products[] =& $product;
			}
			unset($product);
		}

		if(empty($shipping_groups[0]->products)) {
			$shipping_groups[0] = null;
			unset($shipping_groups[0]);
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onShippingWarehouseFilter', array(&$shipping_groups, &$order, &$rates));

		foreach($shipping_groups as $group_id => $shipping_group) {
			if(empty($shipping_group->products)) {
				$shipping_groups[$group_id] = null;
				unset($shipping_groups[$group_id]);
			}
		}

		$order->shipping_groups =& $shipping_groups;
		return $shipping_groups;
	}

	function getShippingName($shipping_method, $shipping_id) {
		$shipping_name = $shipping_method . ' ' . $shipping_id;
		if(strpos($shipping_id, '-') !== false) {
			$shipping_ids = explode('-', $shipping_id, 2);
			$shipping = $this->get($shipping_ids[0]);
			if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
				$shipping->shipping_params = unserialize($shipping->shipping_params);
			$shippingMethod = hikashop_import('hikashopshipping', $shipping_method);
			$methods = $shippingMethod->shippingMethods($shipping);
			unset($shippingMethod);

			if(isset($methods[$shipping_id])){
				$shipping_name = $shipping->shipping_name.' - '.$methods[$shipping_id];
			}else{
				$shipping_name = $shipping_id;
			}
			unset($methods);
			unset($shipping);
		}
		return $shipping_name;
	}

	function displayErrors(){
		if(!empty($this->errors)) {

			foreach($this->errors as $k => $errors) {
				if(is_array($errors)){
					foreach($errors as $key => $value){
						$this->_displayErrors($key,$value);
						return true;
					}
				}else{
					$this->_displayErrors($k,$errors);
					return true;
				}
			}
			return true;
		}
		return false;
	}
	function _displayErrors($key,$value){
		static $displayed = array();
		if(isset($displayed[$key.$value])) return;
		$displayed[$key.$value] = true;
		$number = 0;
		if(is_numeric($value)){
			$number = $value;
			switch($key){
				case 'min_price':
					$value = 'ORDER_TOTAL_TOO_LOW_FOR_SHIPPING_METHODS';
					break;
				case 'max_price':
					$value = 'ORDER_TOTAL_TOO_HIGH_FOR_SHIPPING_METHODS';
					break;
				case 'min_volume':
					$value = 'ITEMS_VOLUME_TOO_SMALL_FOR_SHIPPING_METHODS';
					break;
				case 'max_volume':
					$value = 'ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS';
					break;
				case 'min_weight':
					$value = 'ITEMS_WEIGHT_TOO_SMALL_FOR_SHIPPING_METHODS';
					break;
				case 'max_weight':
					$value = 'ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS';
					break;
				case 'min_quantity':
					$value = 'ORDER_QUANTITY_TOO_SMALL_FOR_SHIPPING_METHODS';
					break;
				case 'max_quantity':
					$value = 'ORDER_QUANTITY_TOO_HIGH_FOR_SHIPPING_METHODS';
					break;
				case 'product_excluded':
					$value = 'X_PRODUCTS_ARE_NOT_SHIPPABLE_TO_YOU';
					break;
				default:
					$value = $key;
					break;
			}
		}
		$transKey = strtoupper(str_replace(' ','_',$value));
		$trans = JText::_($transKey);
		if(strpos($trans,'%s')!==false){
			$trans = JText::sprintf($transKey,$number);
		}
		if($trans != $transKey){
			$value = $trans;
		}

		static $translatedDisplayed = array();
		if(isset($translatedDisplayed[$value])) return;
		$translatedDisplayed[$value] = true;

		$app = JFactory::getApplication();
		$app->enqueueMessage($value);
	}

	function fillListingColumns(&$rows, &$listing_columns, &$view) {
		$listing_columns['price'] = array(
			'name' => 'PRODUCT_PRICE',
			'col' => 'col_display_price'
		);
		$listing_columns['restriction'] = array(
			'name' => 'HIKA_RESTRICTIONS',
			'col' => 'col_display_restriction'
		);

		foreach($rows as &$row) {
			if(!empty($row->shipping_params) && is_string($row->shipping_params))
				$row->plugin_params = unserialize($row->shipping_params);

			$row->col_display_price = '';
			if(bccomp($row->shipping_price, 0, 3)) {
				$row->col_display_price = $view->currencyClass->displayPrices(array($row), 'shipping_price', 'shipping_currency_id');
			}
			if(isset($row->plugin_params->shipping_percentage) && bccomp($row->plugin_params->shipping_percentage, 0, 3)) {
				$row->col_display_price .= '<br/>';
				$row->col_display_price .= $row->plugin_params->shipping_percentage.'%';
			}

			$restrictions = array();
			if(!empty($row->plugin_params->shipping_min_volume))
				$restrictions[] = JText::_('SHIPPING_MIN_VOLUME') . ':' . $row->plugin_params->shipping_min_volume . $row->plugin_params->shipping_size_unit;
			if(!empty($row->plugin_params->shipping_max_volume))
				$restrictions[] = JText::_('SHIPPING_MAX_VOLUME') . ':' . $row->plugin_params->shipping_max_volume . $row->plugin_params->shipping_size_unit;

			if(!empty($row->plugin_params->shipping_min_weight))
				$restrictions[] = JText::_('SHIPPING_MIN_WEIGHT') . ':' . $row->plugin_params->shipping_min_weight . $row->plugin_params->shipping_weight_unit;
			if(!empty($row->plugin_params->shipping_max_weight))
				$restrictions[] = JText::_('SHIPPING_MAX_WEIGHT') . ':' . $row->plugin_params->shipping_max_weight . $row->plugin_params->shipping_weight_unit;

			if(isset($row->plugin_params->shipping_min_price) && bccomp($row->plugin_params->shipping_min_price, 0, 5)) {
				$row->shipping_min_price = $row->plugin_params->shipping_min_price;
				$restrictions[] = JText::_('SHIPPING_MIN_PRICE') . ':' . $view->currencyClass->displayPrices(array($row), 'shipping_min_price', 'shipping_currency_id');
			}
			if(isset($row->plugin_params->shipping_max_price) && bccomp($row->plugin_params->shipping_max_price, 0, 5)) {
				$row->shipping_max_price = $row->plugin_params->shipping_max_price;
				$restrictions[] = JText::_('SHIPPING_MAX_PRICE') . ':' . $view->currencyClass->displayPrices(array($row), 'shipping_max_price', 'shipping_currency_id');
			}
			if(!empty($row->plugin_params->shipping_zip_prefix))
				$restrictions[] = JText::_('SHIPPING_PREFIX') . ':' . $row->plugin_params->shipping_zip_prefix;
			if(!empty($row->plugin_params->shipping_min_zip))
				$restrictions[] = JText::_('SHIPPING_MIN_ZIP') . ':' . $row->plugin_params->shipping_min_zip;
			if(!empty($row->plugin_params->shipping_max_zip))
				$restrictions[] = JText::_('SHIPPING_MAX_ZIP') . ':' . $row->plugin_params->shipping_max_zip;
			if(!empty($row->plugin_params->shipping_zip_suffix))
				$restrictions[] = JText::_('SHIPPING_SUFFIX') . ':' . $row->plugin_params->shipping_zip_suffix;
			if(!empty($row->shipping_zone_namekey)) {
				$zone = $view->zoneClass->get($row->shipping_zone_namekey);
				$restrictions[] = JText::_('ZONE') . ':' . $zone->zone_name_english;
			}
			$row->col_display_restriction = implode('<br/>', $restrictions);

			unset($row);
		}
	}
}
