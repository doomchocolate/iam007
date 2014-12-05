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
class plgHikashoppaymentPostfinance extends hikashopPaymentPlugin {
	var $accepted_currencies = array('CHF', 'EUR', 'GBP', 'USD', 'DZD', 'AUD', 'CAD', 'HRK', 'CZK', 'DKK', 'EGP', 'HKD', 'HUF', 'INR', 'IDR', 'ILS', 'JPY', 'KES', 'LVL', 'LTL', 'MYR', 'MUR', 'MAD', 'NAD', 'NZD', 'NOK', 'PHP', 'PLN', 'RON', 'SGD', 'ZAR', 'LKR', 'SEK', 'TWD', 'THB', 'TND', 'TRY', 'VND', );
	var $debugData = array();
	var $multiple = true;
	var $name = 'postfinance';
	var $pluginConfig = array(
		'returnurl' => array('RETURN_URL', 'html', ''),
		'shop_ID' => array('ATOS_MERCHANT_ID', 'input'),
		'sha_in_phrase' => array('SHA-IN_Pass_phrase', 'input'),
		'sha_out_phrase' => array('SHA-OUT_Pass_phrase', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'address_type' => array('PAYPAL_ADDRESS_TYPE', 'address'),
		'url' => array('URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'return_url' => array('RETURN_URL', 'input')

	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$tax_total = '';
		$discount_total = '';

		$home_url = HIKASHOP_LIVE.'index.php';
		$notify_url = $home_url.'?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=postfinance&tmpl=component&lang='.$this->locale.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		if(!isset($this->payment_params->no_shipping)) $this->payment_params->no_shipping = 1;
		if(!empty($this->payment_params->rm)) $this->payment_params->rm = 2;
		$vars = array(
			"PSPID" => $this->payment_params->shop_ID,
			"LANGUAGE" => 'en_US',
			"ORDERID" => $order->order_id,
			"AMOUNT" => round($order->order_full_price, 2)*100,
			"CURRENCY" => $this->currency->currency_code,
			"ACCEPTURL"=> $notify_url,
			"CANCELURL"=> $notify_url,
			"DECLINEURL"=> $notify_url,
			"EXCEPTIONURL"=> $notify_url,
			"HOMEURL"=> $home_url,
			"CATALOGURL"=> $home_url,
		);
		if(!empty($billing_address) && $this->payment_params->address_type == 'billing'){
			$billing_address1 = '';
			$billing_address2 = '';
			if(!empty($order->cart->billing_address->address_street2)){
				$billing_address2 = substr($order->cart->billing_address->address_street2,0,99);
			}
			if(!empty($order->cart->billing_address->address_street)){
				if(strlen($order->cart->billing_address->address_street)>100){
					$billing_address1 = substr($order->cart->billing_address->address_street,0,99);
					if(empty($billing_address2)) $billing_address2 = substr($order->cart->billing_address->address_street,99,199);
				}else{
					$billing_address1 = $order->cart->billing_address->address_street;
				}
			}
			if(!empty($billing_address1))$vars["OWNERADDRESS"]=$billing_address1;
			if(!empty($billing_address2))$vars["OWNERADDRESS"].=$billing_address2;
			if(!empty($order->cart->billing_address->address_post_code))$vars["OWNERZIP"]=@$order->cart->billing_address->address_post_code;
			if(!empty($order->cart->billing_address->address_city))$vars["OWNERCTY"]=@$order->cart->billing_address->address_city;
			if(!empty($this->user->user_email))$vars["EMAIL"]=$this->user->user_email;
			if(!empty($order->cart->billing_address->address_telephone))$vars["OWNERTELNO"]=@$order->cart->billing_address->address_telephone;
		}
		if(!empty($shipping_address) && $this->payment_params->address_type == 'shipping'){
			$shipping_address1 = '';
			$shipping_address2 = '';
			if(!empty($order->cart->shipping_address->address_street2)){
				$shipping_address2 = substr($order->cart->shipping_address->address_street2,0,99);
			}
			if(!empty($order->cart->shipping_address->address_street)){
				if(strlen($order->cart->shipping_address->address_street)>100){
					$shipping_address1 = substr($order->cart->shipping_address->address_street,0,99);
					if(empty($shipping_address2)) $shipping_address2 = substr($order->cart->shipping_address->address_street,99,199);
				}else{
					$shipping_address1 = $order->cart->shipping_address->address_street;
				}
			}
			if(!empty($shipping_address1))$vars["OWNERADDRESS"]=$shipping_address1;
			if(!empty($shipping_address2))$vars["OWNERADDRESS"].=$shipping_address2;
			if(!empty($order->cart->shipping_address->address_post_code))$vars["OWNERZIP"]=@$order->cart->shipping_address->address_post_code;
			if(!empty($order->cart->shipping_address->address_city))$vars["OWNERCTY"]=@$order->cart->shipping_address->address_city;
			if(!empty($this->user->user_email))$vars["EMAIL"]=$this->user->user_email;
			if(!empty($order->cart->shipping_address->address_telephone))$vars["OWNERTELNO"]=@$order->cart->shipping_address->address_telephone;
		}
		ksort($vars);
		$txtSha_tosecure ='';
		foreach($vars as $key => $var){
			$txtSha_tosecure.=strtoupper($key).'='.$var.$this->payment_params->sha_in_phrase;
		}
		$txtSha = strtoupper(sha1($txtSha_tosecure));
		$vars["SHASIGN"] = $txtSha;
		$this->vars=$vars;
		return $this->showPage('end');

	}

	function onPaymentNotification(&$statuses){
		$app =& JFactory::getApplication();
		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();
		$httpsHikashop = HIKASHOP_LIVE;
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		foreach($_REQUEST as $key => $value){
			$key = $filter->clean($key);
			if(preg_match("#^[0-9a-z_-]{1,30}$#i",$key)&&!preg_match("#^cmd$#i",$key)){
				$value = JRequest::getString($key);
				$vars[$key]=$value;
				$data[]=$key.'='.urlencode($value);
			}
		}
		$data = implode('&',$data).'&cmd=_notify-validate';

		$order_id = (int)@$vars['orderID'];
		$dbOrder = $this->getOrder($order_id);

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
			echo print_r($vars,true)."\n\n\n";
		}

		$result = array();
		$acceptedKeys = array(
			'AAVADDRESS','AAVCHECK','AAVZIP','ACCEPTANCE','ALIAS','AMOUNT','BIN','BRAND','CARDNO','CCCTY','CN','COMPLUS','CREATION_STATUS','CURRENCY','CVCCHECK','DCC_COMMPERCENTAGE','DCC_CONVAMOUNT',
			'DCC_CONVCCY','DCC_EXCHRATE','DCC_EXCHRATESOURCE','DCC_EXCHRATETS','DCC_INDICATOR','DCC_MARGINPERCENTAGE','DCC_VALIDHOURS','DIGESTCARDNO','ECI','ED','ENCCARDNO','IP','IPCTY',
			'NBREMAILUSAGE','NBRIPUSAGE','NBRIPUSAGE_ALLTX','NBRUSAGE','NCERROR','ORDERID','PAYID','PM','SCO_CATEGORY','SCORING','STATUS','SUBBRAND','SUBSCRIPTION_ID','TRXDATE','VC'
		);
		foreach($_REQUEST as $key=>$value) {
			if($value != '' && in_array(strtoupper($key), $acceptedKeys)) {
				$result[strtoupper($key)] = $value;
			}
			elseif($key == 'SHASIGN') $shasign = $value;
		}
		if($this->payment_params->debug){
			echo "---------------------------------------START----------------------------------------<br/>";
			echo '$_REQUEST :'.print_r($_REQUEST,true).'<br/>';
			echo 'date :'.print_r(getdate(),true).'<br/>';
		}
		ksort($result);
		$txtSha_tosecure ='';
		foreach($result as $key => $var){
			$txtSha_tosecure.=$key.'='.$var.$this->payment_params->sha_out_phrase;
		}
		$txtSha = strtoupper(sha1($txtSha_tosecure));

		if(empty($dbOrder)){
			if($this->payment_params->debug){
				echo "Could not load any order for your notification ".$vars['orderID']."NO ORDER ID <br/>";
			}
			return false;
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		if($this->payment_params->debug){
			echo 'result :'.print_r($result,true).'<br/>';
			echo 'MYSHA : '.$txtSha.' THEIRCHA : '.$shasign.'<br/>';
			echo 'sha_out :'.$this->payment_params->sha_out_phrase.'<br/>';
		}

		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$url_itemid;
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$url_itemid;
		if(($txtSha==$shasign) && ($result['STATUS'] == 9 || $result['STATUS'] == 91)) {

			$history = new stdClass();
			$email = new stdClass();
			$history->notified = 1;
			$history->amount = $result['AMOUNT'];
			$history->data = ob_get_clean();

			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Postfinance',$result['STATUS'],$dbOrder->order_number);
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Postfinance',$result['STATUS'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$this->payment_params->verified_status)."\r\n\r\n".$order_text;
			$email->body = $body;

			$Orderclass = hikashop_get('class.order');
			$order = $Orderclass->get($order_id);
			if($order->order_status != $this->payment_params->verified_status)
				$this->modifyOrder($order_id, $this->payment_params->verified_status, $history, $email);

			$app->redirect($return_url);
			return true;
		} else if($result['STATUS'] != 5){

			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->name).'invalid response';
			$email->body = JText::sprintf("Hello,\r\n A Postfinance notification was refused because the response from the Post finance server was invalid")."\r\n\r\n".$order_text;
			$Orderclass = hikashop_get('class.order');
			$order = $Orderclass->get($order_id);
			if($order->order_status != $this->payment_params->invalid_status)
				$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

			if($element->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}
			$app->enqueueMessage('Transaction Failed with the status number : '.$result['STATUS']);
			$app->redirect($cancel_url);
			return false;
		}
	}

	function onPaymentConfiguration(&$element){
		$this->pluginConfig['returnurl'][2] = HIKASHOP_LIVE.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=postfinance&amp;tmpl=component';
		parent::onPaymentConfiguration($element);
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Postfinance';
		$element->payment_description='You can pay by credit card or Postfinance using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,Postfinance';

		$element->payment_params->url='https://e-payment.postfinance.ch/ncol/test/orderstandard.asp';
		$element->payment_params->notification=1;
		$element->payment_params->shop_ID='';
		$element->payment_params->details=0;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->address_override=1;
	}
}
