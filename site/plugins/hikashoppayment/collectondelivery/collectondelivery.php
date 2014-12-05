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

class plgHikashoppaymentCollectondelivery extends hikashopPaymentPlugin {
	var $multiple = true;
	var $name = 'collectondelivery';
	var $doc_form = 'collectondelivery';
	var $pluginConfig = array(
		'status_notif_email' => array('ORDER_STATUS_NOTIFICATION', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'order_status' => array('ORDER_STATUS', 'orderstatus')
	);

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Collect on delivery';
		$element->payment_description = 'You can pay when your package is delivered by using this payment method.';
		$element->payment_images = 'Collect_on_delivery';
		$element->payment_params->order_status = 'created';
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$method = $methods[$method_id];
		if(isset($order->order_status) && !empty($method->payment_params->order_status) && $method->payment_params->order_status != $order->order_status) {
			$orderObj = new stdClass();
			$orderObj->order_id = $order->order_id;
			$orderObj->order_status = $method->payment_params->order_status;
			if(!empty($method->payment_params->status_notif_email))
				$orderObj->history->history_notified = $method->payment_params->status_notif_email;
			$orderClass = hikashop_get('class.order');
			$orderClass->save($orderObj);
		}

		$this->removeCart = true;

		$currencyClass = hikashop_get('class.currency');
		$this->amount = $currencyClass->format($order->order_full_price, $order->order_currency_id);
		$this->order_number = $order->order_number;

		$this->showPage('end');
	}
}
