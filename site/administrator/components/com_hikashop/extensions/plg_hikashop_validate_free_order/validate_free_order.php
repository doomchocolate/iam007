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
class plgHikashopValidate_free_order extends JPlugin
{
	function plgHikashopValidate_free_order(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('hikashop', 'validate_free_order');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}
	}

	function onBeforeOrderCreate(&$order,&$send_email){
		if(empty($order) || !isset($order->order_full_price))
			return;
		if(!$this->params->get('send_confirmation',1) && bccomp($order->order_full_price,0,5)==0){
			$order->order_status = 'confirmed';
		}
	}

	function onAfterOrderCreate(&$order){
		if(!$this->params->get('send_confirmation',1) && $order->order_status == 'confirmed'){
			$class = hikashop_get('class.cart');
			$class->cleanCartFromSession();
		}
		if(empty($order) || !isset($order->order_full_price))
			return;
		if($this->params->get('send_confirmation',1) && bccomp($order->order_full_price,0,5)==0){
			$orderObj = new stdClass();
			$orderObj->order_status = 'confirmed';
			$orderObj->history = new stdClass();
			$orderObj->history->history_notified = 1;
			$orderObj->order_id = $order->order_id;
			$orderClass = hikashop_get('class.order');
			$orderClass->save($orderObj);
			$class = hikashop_get('class.cart');
			$class->cleanCartFromSession();
		}
	}

}
