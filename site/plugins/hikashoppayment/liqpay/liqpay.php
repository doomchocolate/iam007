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

defined('_JEXEC') or die('Restricted access');

class plgHikashoppaymentLiqpay extends JPlugin {

	var $accepted_currencies = array('RUB', 'RUR', 'UAH', 'EUR', 'USD');
	var $debugData = array();
	var $payment_name = 'liqpay';
	var $payment_name_up = 'LiqPay';
	var $payment_url = 'https://www.liqpay.com/?do=clickNbuy';

	function onPaymentDisplay(&$order, &$methods, &$usable_methods) {
		if (!empty($methods)) {
			foreach ($methods as $method) {
				if ($method->payment_type != $this->payment_name || !$method->enabled)
					continue;

				if (!empty($method->payment_zone_namekey)) {
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);

					if (!in_array($method->payment_zone_namekey, $zones))
						return true;
				}

				$currencyClass = hikashop_get('class.currency');
				$null = null;

				if (!empty($order->total)) {
					$currency_id = intval(@$order->total->prices[0]->price_currency_id);
					$currency = $currencyClass->getCurrencies($currency_id, $null);

					if (!empty($currency) && !in_array(@$currency[$currency_id]->currency_code, $this->accepted_currencies))
						return true;
				}

				$usable_methods[$method->ordering] = $method;
			}
		}

		return true;
	}

	function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$usable = array();
		$this->onPaymentDisplay($cart, $rates, $usable);
		$payment_id = (int)$payment_id;

		foreach ($usable as $usable_method) {
			if ($usable_method->payment_id == $payment_id)
				return $usable_method;
		}

		return false;
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		global $Itemid;

		$method = $methods[$method_id];
		$tax_total = '';
		$discount_total = '';
		$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id, $currencies);
		$currency = $currencies[$order->order_currency_id];

		if ($currency->currency_locale['int_frac_digits'] > 2)
			$currency->currency_locale['int_frac_digits'] = 2;

		hikashop_loadUser(true, true);
		$user = hikashop_loadUser(true);

		$debug = $method->payment_params->debug;

		$price = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$currency->currency_locale['int_frac_digits']);

		$app =& JFactory::getApplication();
		$address = $app->getUserState(HIKASHOP_COMPONENT.'.billing_address');
		$type = 'billing';

		if (empty($address)) {
			$address = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_address');

			if (!empty($address)) {
				$type = 'shipping';
			}
		}

		$phone = '';

		if (!empty($address)) {
			$address_type = $type.'_address';
			$phone = @$order->cart->$address_type->address_telephone;
		}

		$lang = &JFactory::getLanguage();
		$locale = strtoupper(substr($lang->get('tag'), 0, 2));

		$url_itemid = '';

		if (!empty($Itemid)) {
			$url_itemid = '&amp;Itemid='.$Itemid;
		}

		$server_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment='.$this->payment_name.'&amp;tmpl=component&amp;lang='.$locale;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=after_end&amp;order_id='.$order->order_id.$url_itemid;

		$xml = '<request>
			<version>1.2</version>
			<result_url>'.$return_url.'</result_url>
			<server_url>'.$server_url.'</server_url>
			<merchant_id>'.$method->payment_params->merchant_id.'</merchant_id>
			<order_id>'.$order->order_id.'</order_id>
			<amount>'.$price.'</amount>
			<currency>'.$currency->currency_code.'</currency>
			<description>Order ID '.$order->order_id.'</description>
			<default_phone>'.$phone.'</default_phone>
			<pay_way>card,liqpay</pay_way>
			</request>';

		$secret_word = $method->payment_params->secret_word;
		$sign = base64_encode(sha1($secret_word.$xml.$secret_word, 1));
		$xml_encoded = base64_encode($xml);

		$vars = array(
			'operation_xml' => $xml_encoded,
			'signature' => $sign
		);

		if(empty($element->payment_params->url))
			$element->payment_params->url = $this->payment_url;

		if (!HIKASHOP_J30) {
			JHTML::_('behavior.mootools');
		} else {
			JHTML::_('behavior.framework');
		}

		$app = JFactory::getApplication();
		$name = $method->payment_type.'_end.php';
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;

		if(!file_exists($path)) {
			if(version_compare(JVERSION, '1.6', '<'))
				$path = JPATH_PLUGINS.DS.'hikashoppayment'.DS.$name;
			else
				$path = JPATH_PLUGINS.DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;

			if (!file_exists($path))
				return true;
		}

		require($path);

		return true;
	}

	function onPaymentNotification(&$statuses) {
		$pluginsClass = hikashop_get('class.plugins');
		$elements = $pluginsClass->getMethods('payment', $this->payment_name);

		if (empty($elements))
			return false;

		$element = reset($elements);

		if (!$element->payment_params->notification)
			return false;

		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();

		foreach ($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			if (preg_match("#^[0-9a-z_-]{1,30}$#i", $key) && !preg_match("#^cmd$#i", $key)) {
				$value = JRequest::getString($key);
				$vars[$key] = $value;
			}
		}

		$secret_word = $element->payment_params->secret_word;

		$xml_decoded = base64_decode(JRequest::getVar('operation_xml'));
		$xml = @new SimpleXMLElement($xml_decoded);
		$sign = base64_encode(sha1($secret_word.$xml_decoded.$secret_word, 1));

		if ($element->payment_params->debug)
			echo print_r($vars, true)."\n\n\n";

		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)@$xml->children()->order_id);

		if (empty($dbOrder)) {
			echo 'Could not load any order for your notification '.@$xml->children()->order_id;
			return false;
		}

		$order = null;
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status = $dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));

		if($element->payment_params->debug)
			echo print_r($dbOrder, true)."\n\n\n";

		$mailer = JFactory::getMailer();
		$config = hikashop_config();
		$sender = array(
			$config->get('from_email'),
			$config->get('from_name')
		);

		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',', $config->get('payment_notification_email')));

		$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified = 0;
		$order->history->history_amount = @$xml->children()->amount;
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method = $element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
	 	$currencyClass = hikashop_get('class.currency');
		$currencies = null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id, $currencies);
		$currency = $currencies[$dbOrder->order_currency_id];
	 	$price_check = round($dbOrder->order_full_price, (int)$currency->currency_locale['int_frac_digits']);

		$order->order_status = $element->payment_params->invalid_status;

		if ($price_check != @$xml->children()->amount) {
	 		$orderClass->save($order);
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->payment_name_up).JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>', "\r\n", JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER', $this->payment_name_up, $order->history->history_amount,$price_check.$currency->currency_code))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION', HIKASHOP_HELPURL.'payment-'.$this->payment_name.'-error#amount').$order_text;
			$mailer->setBody($body);
			$mailer->Send();

			return false;
	 	}

		if ($sign != JRequest::getVar('signature')) {
			$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->payment_name_up).' invalid response');
			$body = JText::sprintf("Hello,\r\n A ".$this->payment_name_up." notification was refused because the response from the ".$this->payment_name_up." server was invalid")."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();

			if ($element->payment_params->debug)
				echo 'invalid response'."\n\n\n";

			return false;
		}

		if (@$xml->children()->status == 'success') {
			$order->order_status = $element->payment_params->verified_status;
		}

		if (@$xml->children()->status == 'wait_secure') {
			$order->order_status = $element->payment_params->pending_status;
		}

	 	if ($dbOrder->order_status == $order->order_status)
			return true;

		$config = hikashop_config();

		if ($config->get('order_confirmed_status', 'confirmed') == $order->order_status)
			$order->history->history_notified = 1;

		$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', $this->payment_name_up, $order->order_status, $dbOrder->order_number));
		$body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', $this->payment_name_up, $order->order_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED', $order->order_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
	 	$orderClass->save($order);

		return 'OK'.$dbOrder->order_id;
	}

	function onPaymentConfiguration(&$element) {
		$this->$this->payment_name = JRequest::getCmd('name', $this->payment_name);

		if (empty($element)) {
			$element = null;
			$element->payment_name = $this->payment_name_up;
			$element->payment_description = 'You can pay by credit card and '.$this->payment_name_up.' using this payment method';
			$element->payment_images = $this->payment_name;
			$element->payment_type = $this->$this->payment_name;
			$element->payment_params = null;
			$element->payment_params->url = $this->payment_url;
			$element->payment_params->invalid_status = 'cancelled';
			$element->payment_params->pending_status = 'created';
			$element->payment_params->verified_status = 'confirmed';
			$element = array($element);
		}

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Pophelp', 'payment-'.$this->payment_name.'-form');
		hikashop_setTitle($this->payment_name_up, 'plugin', 'plugins&plugin_type=payment&task=edit&name='.$this->$this->payment_name);
		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.payment_plugin_type', $this->$this->payment_name);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}

	function onPaymentConfigurationSave(&$element) {
		if (empty($element->payment_params->currency))
			$element->payment_params->currency = $this->accepted_currencies[0];

		return true;
	}
}
