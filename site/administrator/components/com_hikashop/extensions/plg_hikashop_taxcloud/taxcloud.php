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
class plgHikaShopTaxcloud extends JPlugin {

	protected $soap = null;
	protected $debug = false;

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_hash', '');
	}

	private function init() {
		static $init = null;
		if($init !== null)
			return $init;

		$init = defined('HIKASHOP_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKASHOP_COMPONENT');
			}
		}
		return $init;
	}

	public function onAfterCartProductsLoad(&$cart) {
		$verify_address = $this->verifyAddress();
		if($verify_address == 1) {
			$this->lookup($cart);
		}
	}


	public function onProductFormDisplay(&$product, &$html) {
		$db = JFactory::getDBO();
		if(!HIKASHOP_J25) {
			$tmp = $db->getTableFields(hikashop_table('product'));
			$current = reset($tmp);
			unset($tmp);
		} else {
			$current = $db->getTableColumns(hikashop_table('product'));
		}
		if(!isset($current['product_taxability_code'])) {
			$query = 'ALTER IGNORE TABLE `'.hikashop_table('product').'` ADD COLUMN `product_taxability_code` INT(10) NOT NULL DEFAULT 0';
			$db->setQuery($query);
			$db->query();
		}

		$doc = JFactory::getDocument();
		if(HIKASHOP_J25)
			$doc->addScript(HIKASHOP_LIVE.'plugins/hikashop/taxcloud/taxcloud.js');
		else
			$doc->addScript(HIKASHOP_LIVE.'plugins/hikashop/taxcloud.js');

		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');

		$doc->addScriptDeclaration('
window.addEvent("domready", function(){ var taxcloudField = new taxcloud("hikashop_data_product_taxability_code"); });
');

		$html[] = '
<tr>
	<td class="key">
		<label for="">'.JText::_('TAXABILITY_CODE').'</label>
	</td>
	<td>
		<input type="text" name="data[product][product_taxability_code]" value="'.@$product->product_taxability_code.'" id="hikashop_data_product_taxability_code">
		<input type="hidden" name="product_taxability_code_field" value="1"/>
	</td>
</tr>
		';
	}


	public function onAfterProductCreate(&$product) {
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			$this->productFormSave($product);
		}
	}

	public function onAfterProductUpdate(&$product) {
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			$this->productFormSave($product);
		}
	}

	protected function productFormSave(&$product) {
		$field = JRequest::getInt('product_taxability_code_field', '0');
		if(!empty($field) && empty($product->product_taxability_code)) {
			$product->product_taxability_code = 0;
		}
	}

	public function onAfterProcessShippings(&$usable_rates) {
		$verify_address = $this->verifyAddress();
		if($verify_address !== 1)
			return;

		if(!$this->initSoap())
			return false;

		$app = JFactory::getApplication();
		$this->loadOptions();
		$user_id = hikashop_loadUser(false);
		$usps_address = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', array());

		$shipping_tic = (int)@$this->plugin_options['shipping_tic'];

		foreach($usable_rates as $method) {
			$cart_items[] = array(
				'Index' => $method->shipping_id,
				'ItemID' => 'shipping_rate_'.$method->shipping_id,
				'TIC' => $shipping_tic,
				'Price' => $method->shipping_price,
				'Qty' => 1
			);
		}

		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $user_id,
			'cartID' => 'sp0',
			'cartItems' => $cart_items,
			'origin' => array(
				'Address1' => $this->plugin_options['origin_address1'],
				'Address2' => $this->plugin_options['origin_address2'],
				'City' => $this->plugin_options['origin_city'],
				'State' => $this->plugin_options['origin_state'],
				'Zip5' => $this->plugin_options['origin_zip5'],
				'Zip4' => $this->plugin_options['origin_zip4']
			),
			'destination' => $usps_address,
			'deliveredBySeller' => true,
			'exemptCert' => null
		);

		static $soapCache = array();

		$hash = md5(serialize($parameters));
		$session_hash = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache_hash', '');
		if($hash == $session_hash) {
			$ret = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache', '');
			if(!empty($ret) && !empty($ret->ResponseType)) {
				$useCache = true;
				if(!isset($soapCache[$hash]))
					$soapCache[$hash] = $ret;
			} else {
				unset($ret);
			}
		} else {
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache_hash', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache', null);
		}

		if(!isset($soapCache[$hash])) {
			try {
				$soapRet = $this->soap->__soapCall('Lookup', array($parameters)); //, array('uri' => 'http://taxcloud.net','soapaction' => ''));
				$soapCache[$hash] = $soapRet->LookupResult;
				$ret = $soapRet->LookupResult;
			} catch(Exception $e) {
				$ret = false;
			}

			if($ret !== false) {
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache_hash', $hash);
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.shipping_cache', $ret);
			}

			if($this->debug) {
				var_dump($ret->ResponseType);
				if($ret->ResponseType == 'OK')
					var_dump($ret->CartItemsResponse->CartItemResponse);
				else
					var_dump($ret);
			}
		} else {
			$ret = $soapCache[$hash];
			$useCache = true;
		}

		$rates = array();

		if(!empty($ret) && $ret->ResponseType == 'OK') {
			if(!is_array($ret->CartItemsResponse->CartItemResponse))
				$ret->CartItemsResponse->CartItemResponse = array($ret->CartItemsResponse->CartItemResponse);
			foreach($ret->CartItemsResponse->CartItemResponse as $item) {
				foreach($usable_rates as &$method) {
					if((int)$method->shipping_id == $item->CartItemIndex) {
						$tic = (int)@$this->plugin_options['shipping_tic'];
						if(!empty($method->shipping_taxability_code))
							$tic = (int)$method->shipping_taxability_code;

						$price_value = $method->shipping_price;
						$new_price = $price_value + $item->TaxAmount;

						$t = new stdClass();
						$t->tax_rate = round(($new_price / $price_value) - 1, 4);
						$t->tax_amount = $item->TaxAmount;
						$t->tax_namekey = $this->taxName($t->tax_rate); // JText::sprintf('TAXCLOUD_TAX', $t->tax_rate);

						$method->shipping_price_with_tax = $new_price;
						$method->taxes = array($t->tax_namekey => $t);

						if(!isset($rates[$tic])) {
							$rates[$tic] = new stdClass();
							$rates[$tic]->amount = 0.0;
						}
						$rates[$tic]->amount += $item->TaxAmount;

						if($this->debug && empty($useCache))
							var_dump($method);
					}
				}
				unset($method);
			}
		}
	}

	protected function initSoap() {
		if($this->soap !== null)
			return true;

		if(!extension_loaded('soap') && !class_exists('SoapClient'))
			return false;

		$wsdl = 'https://api.taxcloud.net/1.0/?wsdl';
		try {
			$this->soap = new SoapClient($wsdl, array('trace' => true, 'exceptions' => true));
		} catch(Exception $e) {
			unset($this->soap);
			$this->soap = null;
			return false;
		}
		return true;
	}

	protected function taxName($rate) {
		$key = 'TAXCLOUD_TAX';
		if(JText::_($key) == $key)
			$key = 'Tax (%s)';
		$rate = round($rate * 100, 2) . '%';
		$ret = JText::sprintf($key, $rate);
		return $ret;
	}

	protected function loadOptions() {
		if(!empty($this->plugin_options))
			return;

		$this->plugin_options = array(
			'api_id' => '',
			'api_key' => '',
			'usps_id' => '',
			'default_tic' => '0',
			'shipping_tic' => '0',
			'origin_address1' => '',
			'origin_address2' => '',
			'origin_city' => '',
			'origin_state' => '',
			'origin_zip5' => '',
			'origin_zip4' => ''
		);

		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'taxcloud');

			foreach($this->plugin_options as $key => &$value) {
				if(!empty($plugin->params[$key])) $value = $plugin->params[$key];
			}
			unset($value);
		} else {
			foreach($this->plugin_options as $key => &$value) {
				$value = $this->params->get($key, $value);
			}
			unset($value);
		}
	}

	protected function verifyAddress() {
		$app = JFactory::getApplication();

		$shipping_address = (int)$app->getUserState(HIKASHOP_COMPONENT.'.shipping_address', 0);
		if(empty($shipping_address))
			$shipping_address = (int)$app->getUserState(HIKASHOP_COMPONENT.'.billing_address', 0);

		if(empty($shipping_address))
			return false;

		$addressClass = hikashop_get('class.address');
		$address = $addressClass->get($shipping_address);
		if(!empty($address)) {
			$array = array(&$address);
			$addressClass->loadZone($array,'object');
		}

		if(empty($address->address_country)) {
			return false;
		}

		$address_hash = md5(serialize($address));

		$taxcloud_checkaddress = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.address_hash', '');
		if($taxcloud_checkaddress == $address_hash) {
			return (int)$app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 0);
		}

		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_hash', $address_hash);
		$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 0);

		if(!$this->initSoap())
			return false;

		if($address->address_country->zone_code_3 != 'USA') {
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 2);
			return 2;
		}

		$this->loadOptions();

		$parameters = array(
			'uspsUserID' => $this->plugin_options['usps_id'],
			'address1' => $address->address_street,
			'address2' => $address->address_street2,
			'city' => $address->address_city,
			'state' => $address->address_state->zone_name_english,
			'zip5' => $address->address_post_code,
			'zip4' => ''
		);
		try {
			$ret = $this->soap->__soapCall('verifyAddress', array($parameters)); //, array('uri' => 'http://taxcloud.net','soapaction' => ''));
		} catch(Exception $e) {
			$ret = false;
		}

		if(!empty($ret) && !empty($ret->VerifyAddressResult)) {
			$errNumber = $ret->VerifyAddressResult->ErrNumber;
			if($errNumber === '0') {
				$usps_address = array(
					'Address1' => $ret->VerifyAddressResult->Address1,
					'Address2' => @$ret->VerifyAddressResult->Address2,
					'City' => $ret->VerifyAddressResult->City,
					'State' => $ret->VerifyAddressResult->State,
					'Zip5' => $ret->VerifyAddressResult->Zip5,
					'Zip4' => $ret->VerifyAddressResult->Zip4
				);

				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', $usps_address);
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.address_result', 1);
				return 1;
			} else {
				$option = JRequest::getCmd('option', '');
				$ctrl = JRequest::getCmd('ctrl', '');
				if($option == 'com_hikashop' && $ctrl == 'checkout') {
					$app->enqueueMessage(JText::_('WRONG_SHIPPING_ADDRESS'), 'error');
				}
			}
		}
		return 0;
	}

	protected function lookup(&$cart) {
		if(!$this->initSoap())
			return false;

		$app = JFactory::getApplication();
		$this->loadOptions();
		$user_id = hikashop_loadUser(false);
		$usps_address = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.full_address', array());

		$cart_items = array();
		$tics = array();
		foreach($cart->products as $product) {
			$tic = (int)$this->plugin_options['default_tic'];
			if(!empty($product->product_taxability_code))
				$tic = (int)$product->product_taxability_code;

			if(!isset($tics[$tic])) {
				$cart_items[] = array(
					'Index' => -$tic,
					'ItemID' => 'tic_rate_'.$tic,
					'TIC' => $tic,
					'Price' => 1,
					'Qty' => 1
				);
				$tics[$tic] = true;
			}
		}

		foreach($cart->products as $product) {
			$tic = (int)$this->plugin_options['default_tic'];
			if(!empty($product->product_taxability_code))
				$tic = (int)$product->product_taxability_code;

			$cart_items[] = array(
				'Index' => $product->cart_product_id,
				'ItemID' => $product->product_code,
				'TIC' => $tic,
				'Price' => $product->prices[0]->unit_price->price_value,
				'Qty' => $product->cart_product_quantity
			);
		}

		$parameters = array(
			'apiLoginID' => $this->plugin_options['api_id'],
			'apiKey' => $this->plugin_options['api_key'],
			'customerID' => $user_id,
			'cartID' => $cart->cart_id,
			'cartItems' => $cart_items,
			'origin' => array(
				'Address1' => $this->plugin_options['origin_address1'],
				'Address2' => $this->plugin_options['origin_address2'],
				'City' => $this->plugin_options['origin_city'],
				'State' => $this->plugin_options['origin_state'],
				'Zip5' => $this->plugin_options['origin_zip5'],
				'Zip4' => $this->plugin_options['origin_zip4']
			),
			'destination' => $usps_address,
			'deliveredBySeller' => true,
			'exemptCert' => null
		);

		static $soapCache = array();

		$hash = md5(serialize($parameters));

		$session_hash = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.cache_hash', '');
		if($hash == $session_hash) {
			$ret = $app->getUserState(HIKASHOP_COMPONENT.'.taxcloud.cache', '');
			if(!empty($ret) && !empty($ret->ResponseType)) {
				$useCache = true;
				if(!isset($soapCache[$hash]))
					$soapCache[$hash] = $ret;
			} else {
				unset($ret);
			}
		} else {
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache_hash', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache', null);
		}

		if(!isset($soapCache[$hash])) {
			try {
				$soapRet = $this->soap->__soapCall('Lookup', array($parameters)); //, array('uri' => 'http://taxcloud.net','soapaction' => ''));
				$soapCache[$hash] = $soapRet->LookupResult;
				$ret = $soapRet->LookupResult;
			} catch(Exception $e) {
				$ret = false;
			}

			if($ret !== false) {
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache_hash', $hash);
				$app->setUserState(HIKASHOP_COMPONENT.'.taxcloud.cache', $ret);
			}

			if($this->debug) {
				var_dump($ret->ResponseType);
				if($ret->ResponseType == 'OK')
					var_dump($ret->CartItemsResponse->CartItemResponse);
				else
					var_dump($ret);
			}
		} else {
			$ret = $soapCache[$hash];
			$useCache = true;
		}

		$rates = array();

		if(!empty($ret) && $ret->ResponseType == 'OK') {
			foreach($cart->products as &$product) {
				$product->prices[0]->price_value_with_tax = $product->prices[0]->price_value;
				$product->prices[0]->taxes = array();
			}
			unset($product);
			if(!is_array($ret->CartItemsResponse->CartItemResponse))
				$ret->CartItemsResponse->CartItemResponse = array($ret->CartItemsResponse->CartItemResponse);

			foreach($ret->CartItemsResponse->CartItemResponse as $item) {
				if($item->CartItemIndex <= 0) {
					if(!isset($rates[ -$item->CartItemIndex ])) {
						$r = new stdClass();
						$r->rate = $item->TaxAmount;
						$r->amount = 0.0;
						$rates[	-$item->CartItemIndex ] = $r;
					} else {
						$rates[ -$item->CartItemIndex ]->rate = $item->TaxAmount;
					}
					continue;
				}

				foreach($cart->products as &$product) {
					if((int)$product->cart_product_id == $item->CartItemIndex) {
						$tic = (int)$this->plugin_options['default_tic'];
						if(!empty($product->product_taxability_code))
							$tic = (int)$product->product_taxability_code;

						$price_value = $product->prices[0]->price_value;
						$new_price = $price_value + $item->TaxAmount;

						$t = new stdClass();
						$t->tax_rate = round(($new_price / $price_value) - 1, 4);
						$t->tax_amount = $item->TaxAmount;
						$t->tax_namekey = $this->taxName($t->tax_rate); // JText::sprintf('TAXCLOUD_TAX', $t->tax_rate);

						$product->prices[0]->price_value_with_tax = $new_price;
						$product->prices[0]->taxes[$t->tax_namekey] = $t;

						if(!isset($rates[$tic])) {
							$rates[$tic] = new stdClass();
							$rates[$tic]->amount = 0.0;
						}
						$rates[$tic]->amount += $item->TaxAmount;

						if($this->debug && empty($useCache))
							var_dump($product->prices[0]);
					}
				}
				unset($product);
			}

			$cart->total->prices[0]->taxes = array();
			foreach($rates as $k => $rate) {
				$key = $this->taxName($rate->rate); // JText::sprintf('TAXCLOUD_TAX', $rate->rate);
				if(!isset($cart->total->prices[0]->taxes[$key])) {
					$t = new stdClass();
					$t->tax_amount = 0.0;
					$t->tax_rate = $rate->rate;
					$t->tax_namekey = $this->taxName($t->tax_rate); // JText::sprintf('TAXCLOUD_TAX', $t->tax_rate);
					$cart->total->prices[0]->taxes[$key] = $t;
				}
				$cart->total->prices[0]->taxes[$key]->tax_amount += $rate->amount;
			}

			$total_taxes = 0;
			foreach($cart->total->prices[0]->taxes as &$tax) {
				$total_taxes += $tax->tax_amount;
			}
			unset($tax);
			$cart->total->prices[0]->price_value_with_tax = $cart->total->prices[0]->price_value + $total_taxes;

			if($this->debug && empty($useCache))
				var_dump($cart->total->prices[0]);
		} else {

		}
	}


	public function check_address() {
		JToolBarHelper::title('TaxCloud' , 'plugin.png' );

		if(!$this->init())
			return;

		if(!$this->initSoap())
			return false;

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop', 'taxcloud');

		if(!HIKASHOP_J25)
			$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$plugin->id);
		else
			$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id);

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $url);

		$this->loadOptions();
		$parameters = array(
			'uspsUserID' => $this->plugin_options['usps_id'],
			'address1' => $this->plugin_options['origin_address1'],
			'address2' => $this->plugin_options['origin_address2'],
			'city' => $this->plugin_options['origin_city'],
			'state' => $this->plugin_options['origin_state'],
			'zip5' => $this->plugin_options['origin_zip5'],
			'zip4' => $this->plugin_options['origin_zip4']
		);
		$ret = $this->soap->__soapCall('verifyAddress', array($parameters));

		if(!empty($ret) && !empty($ret->VerifyAddressResult)) {
			$errNumber = $ret->VerifyAddressResult->ErrNumber;
			if($errNumber === '0') {
				echo '<fieldset><h1>Check Address</h1><table width="100%" style="width:100%"><thead><tr>'.
					'<th>Name</th>'.
					'<th>Original value</th>'.
					'<th>Processed value</th>'.
					'</thead><tbody>'.
					'<tr><td>Address 1</td><td>'.$this->plugin_options['origin_address1'].'</td><td>'.@$ret->VerifyAddressResult->Address1.'</td></tr>'.
					'<tr><td>Address 2</td><td>'.$this->plugin_options['origin_address2'].'</td><td>'.@$ret->VerifyAddressResult->Address2.'</td></tr>'.
					'<tr><td>City</td><td>'.$this->plugin_options['origin_city'].'</td><td>'.@$ret->VerifyAddressResult->City.'</td></tr>'.
					'<tr><td>State</td><td>'.$this->plugin_options['origin_state'].'</td><td>'.@$ret->VerifyAddressResult->State.'</td></tr>'.
					'<tr><td>Zip5</td><td>'.$this->plugin_options['origin_zip5'].'</td><td>'.@$ret->VerifyAddressResult->Zip5.'</td></tr>'.
					'<tr><td>Zip4</td><td>'.$this->plugin_options['origin_zip4'].'</td><td>'.@$ret->VerifyAddressResult->Zip4.'</td></tr>'.
					'</tbody></table></fieldset>';
			} else {
				echo '<fieldset><h1>Check Address Error</h1><p>'.$ret->VerifyAddressResult->ErrDescription.'</p></fieldset>';
			}
		} else {
			echo '<fieldset><h1>Check Address Error</h1><p>';
			var_dump($ret);
			echo '</p></fieldset>';
		}
	}

	public function browse_tic() {
		JToolBarHelper::title('TaxCloud' , 'plugin.png' );

		if(!$this->init())
			return;

		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop', 'taxcloud');

		if(!HIKASHOP_J25)
			$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$plugin->id);
		else
			$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$plugin->extension_id);

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $url);

		$doc = JFactory::getDocument();
		if(HIKASHOP_J25)
			$doc->addScript(HIKASHOP_LIVE.'plugins/hikashop/taxcloud/taxcloud.js');
		else
			$doc->addScript(HIKASHOP_LIVE.'plugins/hikashop/taxcloud.js');
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');

		$doc->addScriptDeclaration('
window.addEvent("domready", function(){ var taxcloudField = new taxcloud("taxability_code"); });
');

		echo '<fieldset><h1>Browse TIC</h1><div><input type="text" value="" id="taxability_code"/></div></fieldset>';
	}
}
