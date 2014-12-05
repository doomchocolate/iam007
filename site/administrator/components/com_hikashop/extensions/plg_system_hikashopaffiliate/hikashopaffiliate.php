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
if(!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);
jimport('joomla.plugin.plugin');
class plgSystemHikashopaffiliate extends JPlugin{
	function plgSystemHikashopaffiliate(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter(@$plugin->params);
			} else {
				$this->params = new JRegistry(@$plugin->params);
			}
		}
	}

	function afterInitialise(){
		$do = $this->params->get('after_init','1');
		if($do) return $this->onAfterRoute();
		return true;
	}

	function onAfterInitialise(){
		$do = $this->params->get('after_init','1');
		if($do) return $this->onAfterRoute();
		return true;
	}


	function afterRoute(){
		return $this->onAfterRoute();
	}

	function onAfterRoute(){

		$app = JFactory::getApplication();
		if ($app->isAdmin()) return true;

		if(@$_REQUEST['option']=='com_gcalendar') return true;
		$key_name = $this->params->get('partner_key_name','partner_id');
		$partner_id = JRequest::getCmd($key_name,0);

		if(empty($partner_id)){
			return true;
		}

		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
		$partner_id = hikashop_decode($partner_id,'partner');

		static $done = false;

		if($done){
			return true;
		}else{
			$done = true;
		}

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($partner_id);

		if(empty($user->user_partner_activated)){
			return true;
		}
		$config = hikashop_config();
		$cookie=true;
		if($config->get('no_affiliation_if_cart_present')){
			$cart_id = $app->getUserState( HIKASHOP_COMPONENT.'.cart_id', 0, 'int' );
			if($cart_id) $cookie = false;
		}
		if($cookie) setcookie('hikashop_affiliate', hikashop_encode($partner_id,'partner'), time()+$config->get('click_validity_period',2592000),'/');

		$ip = hikashop_getIP();
		$clickClass = hikashop_get('class.click');
		$latest = $clickClass->getLatest($partner_id,$ip,$config->get('click_min_delay',86400));

		if(empty($user->user_params->user_custom_fee)){
			$user->user_params->partner_click_fee = $config->get('partner_click_fee',0);
			$user->user_params->partner_fee_currency = $config->get('partner_currency',1);
		}else{
			$user->user_params->partner_click_fee = $user->user_params->user_partner_click_fee;
		}

		if(!$config->get('allow_currency_selection',0) || empty($user->user_currency_id)){
			$user->user_currency_id =  $config->get('partner_currency',1);
		}

		if(bccomp($user->user_params->partner_click_fee,0,5) && $user->user_currency_id!=$user->user_params->partner_fee_currency){
			$user->user_params->partner_click_fee = $this->_convert($user->user_params->partner_click_fee,$user->user_params->partner_fee_currency,$user->user_currency_id);
		}

		if(!empty($latest)){
			$user->user_params->partner_click_fee = 0;
		}

		$click = new stdClass();
		$click->click_partner_id = $partner_id;
		$click->click_ip=$ip;
		$click->click_partner_price = $user->user_params->partner_click_fee;
		$click->click_partner_currency_id = $user->user_currency_id;
		$clickClass->save($click);

		return true;
	}
	function onBeforeOrderUpdate(&$order,&$do){
		if(!empty($order->order_partner_paid)){
			return true;
		}
		if(!isset($order->order_full_price)){
			return true;
		}
		$orderClass = hikashop_get('class.order');
		$oldOrder = $orderClass->get($order->order_id);
		if(!empty($oldOrder)){
			if(!empty($oldOrder->order_partner_paid)){
				return true;
			}

			if(floatval($oldOrder->order_full_price)==floatval($order->order_full_price)){
				return true;
			}

			if(empty($order->order_partner_id)){
				$order->order_partner_id = $oldOrder->order_partner_id;
			}
			return $this->onBeforeOrderCreate($order,$do);
		}

		return true;
	}

	function getPartner(&$order){
		$config =& hikashop_config();
		if($config->get('add_partner_to_user_account',0)){
			if(!empty($order->order_user_id)){
				$class = hikashop_get('class.user');
				$user = $class->get($order->order_user_id);
				if(!empty($user->user_partner_id)) return $user->user_partner_id;
			}
		}
		return hikashop_decode(JRequest::getCmd('hikashop_affiliate',0,'cookie'),'partner');
	}
	function onBeforeOrderCreate(&$order,&$do){
		$app = JFactory::getApplication();
		if(empty($order->order_partner_id)){
			if ($app->isAdmin()) return true;
			if(!empty($order->order_discount_code)){
				$discountClass = hikashop_get('class.discount');
				$coupon = $discountClass->load($order->order_discount_code);
				if($coupon->discount_affiliate==-1){
					return true;
				}elseif($coupon->discount_affiliate){
					$partner_id = $coupon->discount_affiliate;
				}else{
					$partner_id = $this->getPartner($order);
				}
			}else{
				$partner_id = $this->getPartner($order);
			}
			if(empty($partner_id)){
				return true;
			}
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($partner_id);
			if(empty($user->user_partner_activated)){
				return true;
			}
		}else{
			$partner_id = $order->order_partner_id;
		}
		$userClass = hikashop_get('class.user');
		$user = $userClass->get($partner_id);

		if(empty($user)){
			return true;
		}
		$order->order_partner_id=$partner_id;

		$config=&hikashop_config();
		if(empty($user->user_params->user_custom_fee)){
			$user->user_params->partner_percent_fee = $config->get('partner_percent_fee',0);
			$user->user_params->partner_flat_fee = $config->get('partner_flat_fee',0);
			$user->user_params->partner_fee_currency = $config->get('partner_currency',1);
		}else{
			$user->user_params->partner_percent_fee = $user->user_params->user_partner_percent_fee;
			$user->user_params->partner_flat_fee =$user->user_params->user_partner_flat_fee;
		}
		if(!$config->get('allow_currency_selection',0) || empty($user->user_currency_id)){
			$user->user_currency_id =  $config->get('partner_currency',1);
		}

		if(bccomp($user->user_params->partner_flat_fee,0,5) && $user->user_currency_id!=$user->user_params->partner_fee_currency){
			$user->user_params->partner_flat_fee = $this->_convert($user->user_params->partner_flat_fee,$user->user_params->partner_fee_currency,$user->user_currency_id);
		}

		if(bccomp($user->user_params->partner_percent_fee,0,5) || bccomp($user->user_params->partner_flat_fee,0,5)){
			if(bccomp($user->user_params->partner_percent_fee,0,5)){
				$order_price = $order->order_full_price;
				if($config->get('affiliate_fee_exclude_shipping',0)){
					$order_price = $order_price - $order->order_shipping_price;
				}
				$fees=$order_price*$user->user_params->partner_percent_fee/100;
			}else{
				$fees = 0;
			}
			if($order->order_currency_id!=$user->user_currency_id){
				$fees = $this->_convert($fees,$order->order_currency_id,$user->user_currency_id);
			}

			$order->order_partner_price = $fees+$user->user_params->partner_flat_fee;
			$order->order_partner_currency_id = $user->user_currency_id;
		}

		return true;
	}

	function _convert($amount,$src_id,$dst_id){
		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$setcurrencies=null;
		$main_currency = (int)$config->get('main_currency',1);
		$ids[$src_id]=$src_id;
		$ids[$dst_id]=$dst_id;
		$ids[$main_currency]=$main_currency;
		$currencies = $currencyClass->getCurrencies($ids,$setcurrencies);
		$srcCurrency = $currencies[$src_id];
		$dstCurrency = $currencies[$dst_id];
		$mainCurrency =  $currencies[$main_currency];
		if($srcCurrency->currency_id!=$mainCurrency->currency_id){
			$amount=floatval($amount)/floatval($srcCurrency->currency_rate);
			$amount=$amount+$amount*floatval($srcCurrency->currency_percent_fee)/100.0;
		}
		if($dstCurrency->currency_id!=$mainCurrency->currency_id){
			$amount=floatval($amount)*floatval($dstCurrency->currency_rate);
			$amount=$amount+$amount*floatval($dstCurrency->currency_percent_fee)/100.0;
		}
		return $amount;
	}
	function onUserAfterSave($user, $isnew, $success, $msg){
		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}
	function onAfterStoreUser($user, $isnew, $success, $msg){
		if($success===false) return false;

		$app = JFactory::getApplication();
		if ($app->isAdmin()) return true;

		if($isnew){

			$partner_id = JRequest::getCmd('hikashop_affiliate',0,'cookie');
			if(empty($partner_id)){
				return true;
			}
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
			$partner_id = hikashop_decode($partner_id,'partner');
			$userClass = hikashop_get('class.user');
			$partner = $userClass->get($partner_id);
			if(empty($partner->user_partner_activated)){
				return true;
			}


			$config=&hikashop_config();
			if(empty($partner->user_params->user_custom_fee)){
				$partner->user_params->partner_lead_fee = $config->get('partner_lead_fee',0);
				$partner->user_params->partner_fee_currency = $config->get('partner_currency',1);
			}else{
				$partner->user_params->partner_lead_fee = $partner->user_params->user_partner_lead_fee;
			}
			if(!$config->get('allow_currency_selection',0) || empty($partner->user_currency_id)){
				$partner->user_currency_id =  $config->get('partner_currency',1);
			}
			if(bccomp($partner->user_params->partner_lead_fee,0,5) && $partner->user_currency_id!=$partner->user_params->partner_fee_currency){
				$partner->user_params->partner_lead_fee = $this->_convert($partner->user_params->partner_lead_fee,$partner->user_params->partner_fee_currency,$partner->user_currency_id);
			}
			$ip = hikashop_getIP();
			$clickClass = hikashop_get('class.click');
			$latest = $clickClass->getLatest($partner_id,$ip,$config->get('lead_min_delay',24));
			if($config->get('add_partner_to_user_account',0) || (empty($latest) && bccomp($partner->user_params->partner_lead_fee,0,5))){
				$userDataInDb = $userClass->get($user['id'],'cms');
				$userData = new stdClass();
				$userData->user_id = @$userDataInDb->user_id;
				$userData->user_cms_id = $user['id'];
				$userData->user_partner_id = $partner_id;
				$userData->user_partner_price = @$partner->user_params->partner_lead_fee;
				$userData->user_partner_currency_id = $partner->user_currency_id;
				$userClass->save($userData);
			}
		}
		return true;
	}

	function onUserAccountDisplay(&$buttons){
		$buttons[] = array('link'=>hikashop_completeLink('affiliate'),'level'=>1,'image'=>'affiliate','text'=>JText::_('AFFILIATE'),'description'=>'<ul><li>'.JText::_('AFFILIATE_PROGRAM').'</li></ul>');
		return true;
	}

	function onBeforeOrderListing($paramBase, &$extrafilters, &$pageInfo, &$filters) {
		$app = JFactory::getApplication();
		$pageInfo->filter->filter_partner = $app->getUserStateFromRequest($paramBase.".filter_partner",'filter_partner','','int');
		$extrafilters['filter_partner'] =& $this;

		if(!empty($pageInfo->filter->filter_partner)) {
			if($pageInfo->filter->filter_partner==1){
				$filters[]='b.order_partner_id != 0';
			}else{
				$filters[]='b.order_partner_id = 0';
			}
		}
	}

	function onAfterOrderListing(&$rows, &$extrafields, $pageInfo) {
		$myextrafield = new stdClass();
		$myextrafield->name = JText::_('PARTNER');
		$myextrafield->obj =& $this;
		$extrafields['partner'] = $myextrafield;
	}

	function displayFilter($name, $filter) {
		$partner = hikashop_get('type.user_partner');
		return $partner->display("filter_partner", $filter->filter_partner,false);
	}

	function showField($container, $name, &$row) {
		$ret = '';
		if(bccomp($row->order_partner_price,0,5)){
			$ret .= $container->currencyHelper->format($row->order_partner_price,$row->order_partner_currency_id);
			if(empty($row->order_partner_paid)){
				$ret .= JText::_('NOT_PAID').'<img src="'.HIKASHOP_IMAGES.'delete2.png" />';
			}else{
				$ret .= JText::_('PAID').'<img src="'.HIKASHOP_IMAGES.'ok.png" />';
			}
		}
		return $ret;
	}

	function onDiscountBlocksDisplay(&$discount, &$html) {
		$ret = '<tr id="hikashop_discount_affiliate"><td class="key">'. JText::_('FORCE_AFFILIATION_TO') .'</td><td>';
		$options = array(
			JHTML::_('select.option', -1, JText::_('NO_PARTNER')),
			JHTML::_('select.option', 0, JText::_('CURRENT_CUSTOMER_PARTNER'))
		);
		$database = JFactory::getDBO();
		$database->setQuery('SELECT a.user_id, b.name, b.username FROM #__hikashop_user AS a LEFT JOIN #__users AS b ON a.user_cms_id=b.id WHERE a.user_partner_activated = 1 ORDER BY b.username LIMIT 500');
		$partners = $database->loadObjectList();
		if(!empty($partners)){
			foreach($partners as $partner){
				$options[] = JHTML::_('select.option', $partner->user_id, $partner->username.' ('.$partner->name.')');
			}
		}
		$ret .= JHTML::_('hikaselect.genericlist', $options, "data[discount][discount_affiliate]" , '', 'value', 'text', @$discount->discount_affiliate );
		$ret .= '</td></tr>';

		$html[] = $ret;
	}
}
