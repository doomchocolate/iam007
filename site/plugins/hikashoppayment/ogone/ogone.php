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
class plgHikashoppaymentOgone extends hikashopPaymentPlugin
{
	var $debugData = array();
	var $multiple = true;
	var $name = 'ogone';

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order,$methods,$method_id);
		$lang = JFactory::getLanguage();

		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ogone&tmpl=component&lang='.$this->locale.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;

		$language = str_replace('-','_',$lang->get('tag'));
		$language_codes = array(
			'ar_AR',
			'cs_CZ',
			'zh_CN',
			'da_DK',
			'nl_BE',
			'nl_NL',
			'en_GB',
			'en_US',
			'fr_FR',
			'de_DE',
			'el_GR',
			'hu_HU',
			'it_IT',
			'ja_JP',
			'no_NO',
			'pl_PL',
			'pt_PT',
			'ru_RU',
			'sk_SK',
			'es_ES',
			'se_SE',
			'tr_TR',
		);

		if(!in_array($language,$language_codes)){
			$language = 'en_US';
		}
		$vars = array(
			"PSPID" => $this->payment_params->pspid,
			"orderID" => @$order->order_id,
			"amount" => 100 * round(@$order->cart->full_total->prices[0]->price_value_with_tax,2),
			"currency" => $this->currency->currency_code,
			"language" => $language,
			"EMAIL" => $this->user->user_email,
			"accepturl"=>$return_url,
			"declineurl"=>$cancel_url,
			"exceptionurl"=>$cancel_url,
			"cancelurl"=>$cancel_url,
		);

		$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		if(!empty($address)){
			$vars["owneraddress"]=@$order->cart->billing_address->address_street;
			$vars["ownerZIP"]=substr(@$order->cart->billing_address->address_post_code,0,10);
			$vars["ownertown"]=@$order->cart->billing_address->address_city;
			$vars["ownercty"]=@$order->cart->billing_address->address_country->zone_code_2;
			$vars["CN"]=@$order->cart->billing_address->address_firstname." ".@$order->cart->billing_address->address_lastname;
			$vars["ownertelno"]=@$order->cart->billing_address->address_telephone;
		}

		$vars["SHASign"]=$this->generateHash($vars,$this->payment_params->shain_passphrase,$this->payment_params->hash_method);

		if($this->payment_params->environnement=='test'){
			$this->payment_params->url='https://secure.ogone.com/ncol/test/orderstandard_utf8.asp';
		}else{
			$this->payment_params->url='https://secure.ogone.com/ncol/prod/orderstandard_utf8.asp';
		}
		$this->vars = $vars;
		return $this->showPage('end');
	}

	function generateHash($vars,$passphrase,$hash_method,$type='in'){
		uksort($vars, 'strnatcasecmp');
		$key = '';
		foreach($vars as $k => $v){
			if(strlen($v) && !in_array(strtoupper($k),array('SHASIGN','OPTION','CTRL','TASK','NOTIF_PAYMENT','TMPL','ITEMID','HIKASHOP_FRONT_END_MAIN','VIEW','LANG'))){
				if($type=='out' && strtoupper($k)=='LANGUAGE') continue;
				$key.=strtoupper($k).'='.$v.$passphrase;
			}
		}
		return strtoupper(hash($hash_method,$key));
	}

	function onPaymentNotification(&$statuses){
		$vars = array();
		foreach($_REQUEST as $k => $v){
			$vars[strtoupper($k)]=$v;
		}

		$order_id = (int)@$vars['ORDERID'];
		$order_status = '';

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['ORDERID'];
			return false;
		}
		$vars['GENERATEDHASH'] = $this->generateHash($_REQUEST,$this->payment_params->shaout_passphrase,$this->payment_params->hash_method,'out');

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$email = new stdClass();

		$invalid = false;
		$waiting = false;
		switch(substr($vars['STATUS'],0,1)){
			case '0':
			case '1':
			case '2':
			case '4':
			case '6':
			case '7':
			case '8':
				$invalid = true;
				break;
			case '5':
			case '9':
				if(in_array($vars['STATUS'],array('52','92','93'))){
					$invalid = true;
				}
				if(in_array($vars['STATUS'],array('51','55','59','99','91'))){
					$waiting = true;
				}
				break;
		}

		if($invalid || $vars['GENERATEDHASH']!=$vars['SHASIGN'] || empty($vars['SHASIGN'])){

			if($vars['GENERATEDHASH']!=$vars['SHASIGN']){
				$order_text=' The Hashs didn\'t match. Received: '.$vars['SHASIGN']. ' and generated: '.$vars['GENERATEDHASH']."\n\n\n"."\n\n\n".ob_get_clean()."\n\n\n"."\n\n\n".$order_text;
				ob_start();
			}
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Ogone').'invalid transaction';
			$email->body = JText::sprintf("Hello,\r\n An Ogone payment notification was not validated. The status code was :".$vars['STATUS']).$order_text;

			$this->modifyOrder($order_id,$this->payment_params->invalid_status,false,$email);

			if($this->payment_params->debug){
				echo 'invalid transaction'."\n\n\n";
			}

			$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;
			$this->app->redirect($cancel_url);
			return true;
		}

		$history->notified=0;
		$history->data = ob_get_clean();

	 	if(!$waiting){
	 		$order_status = $this->payment_params->verified_status;

	 		if($dbOrder->order_status==$order_status){
	 			return true;
	 		}
	 	}else{
	 		$order_status = $this->payment_params->pending_status;
	 	}
	 	$config =& hikashop_config();
		if($config->get('order_confirmed_status','confirmed')==$order_status){
			$history->notified=1;
		}
	 	$mail_status=$statuses[$order->order_status];
	 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Ogone',$vars['STATUS'],$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Ogone',$vars['STATUS'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$this->app->redirect($return_url);
		return true;
	}

	function onPaymentConfiguration(&$element){
		parent::onPaymentConfiguration($element);
		$lang = JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));

		if(empty($element->payment_params->pspid)){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','Ogone','PSPID','Ogone','http://www.ogone.com/en/sitecore/Content/COM/Web/Solutions/Payment%20Processing/eCommerce.aspx'));
		}

		$element->payment_params->status_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ogone&tmpl=component&lang='.strtolower($locale);
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Ogone';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,American_Express';

		$element->payment_params->notification=1;
		$element->payment_params->details=0;
		$element->payment_params->invalid_status='created';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->address_override=1;
	}

	function onPaymentConfigurationSave(&$element){
		return true;
	}
}
