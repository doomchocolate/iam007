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
class plgSystemHikashopanalytics extends JPlugin
{
	function plgSystemHikashopanalytics(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopanalytics');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}
	}

	function onAfterOrderCreate(&$order){
		return $this->onAfterOrderUpdate($order);
	}

	function onAfterOrderUpdate(&$order){
		$config=&hikashop_config();
		$confirmed = $config->get('order_confirmed_status');
		if(!isset($order->order_status)) return true;
		if($order->order_status!=$confirmed){
			return true;
		}
		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.display_ga',1);
		$app->setUserState(HIKASHOP_COMPONENT.'.order_id',$order->order_id);
		$app->setUserState(HIKASHOP_COMPONENT.'.error_display_ga',0);
		return true;
	}

	function onAfterRender(){
		$app = JFactory::getApplication();

		if($app->getUserState('com_hikashop.display_ga')){
			if(!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
			if(!hikashop_level(2)) return true;
			$body = JResponse::getBody();
			$js = $this->getJS();
			if(!empty($js)){
				$body = preg_replace("#<script type=\"text/javascript\">(?:(?!<script).)*'https://ssl' : 'http://www'\) \+ '\.google-analytics\.com.*</script>#siU",'',$body);
				$body=str_replace('</head>', $js.'</head>', $body);
				JResponse::setBody($body);
			}
		}
		return true;
	}

	function getJS(){
		$accounts = array();
		for($i=1;$i<6;$i++){
			$accounts[$i] = new stdClass();
			$accounts[$i]->account_id = $this->params->get('account_'.$i);
			$accounts[$i]->currency = $this->params->get('currency_'.$i);
		}
		if(!empty($accounts)){
			$app=JFactory::getApplication();
			$order_id = JRequest::getInt('order_id');
			if(empty($order_id)){
				$order_id = $app->getUserState( HIKASHOP_COMPONENT.'.order_id');
			}
			if(!empty($order_id)){
				$orderClass = hikashop_get('class.order');
				$order = $orderClass->loadFullOrder($order_id,false,false);
				$currencyClass = hikashop_get('class.currency');
				$currencies[$order->order_currency_id]=$order->order_currency_id;
				$null=array();
				$currencyInfo=reset($currencyClass->getCurrencies($currencies, $null));
				$found=false;
				foreach($accounts as $theAccount){
					if($theAccount->currency==$currencyInfo->currency_code && !empty($theAccount->account_id)){
						$account=$theAccount->account_id;
						$found=true;
						if(!preg_match('/UA-[0-9]{2,12}-[0-9]{1}/',$account)){
							if(!$app->getUserState(HIKASHOP_COMPONENT.'.error_display_ga')){
								$app->setUserState(HIKASHOP_COMPONENT.'.error_display_ga',1);
								$app->enqueueMessage(JText::_('GOOGLE_ACCOUNT_ID_INVALID'));
								$app->redirect(hikashop_currentUrl('',false));
							}
							return '';
						}
						break;
					}
				}
				if(!$found){
					if(!$app->getUserState(HIKASHOP_COMPONENT.'.error_display_ga')){
						$app->setUserState(HIKASHOP_COMPONENT.'.error_display_ga',1);
						$app->enqueueMessage(JText::_('NO_CURRENCY_FOUND_GOOGLE_ANALYTICS'));
						$app->redirect(hikashop_currentUrl('',false));
					}
					return '';
				}
				$conf	= JFactory::getConfig();
				if(HIKASHOP_J30){
					$siteName=$conf->get('sitename');
				}else{
					$siteName=$conf->getValue('config.sitename');
				}

				$tax = $order->order_subtotal_no_vat + $order->order_shipping_tax + $order->order_discount_tax;
				$js="
				 var _gaq = _gaq || [];
					_gaq.push(['_setAccount', '".$account."']);
					_gaq.push(['_trackPageview']);
					_gaq.push(['_addTrans',
						'".$order_id."',           													// order ID - required
						'".str_replace("'",'\\\'',$siteName)."',  									// affiliation or store name
						'".round($order->order_full_price,2)."',          									// total - required
					'".$tax."',           														// tax
					'".round($order->order_shipping_price,2)."',              								// shipping
					'".str_replace("'",'\\\'',@$order->billing_address->address_city)."',		// city
						'".str_replace("'",'\\\'',@$order->billing_address->address_state)."',		// state or province
						'".str_replace("'",'\\\'',@$order->billing_address->address_country)."',	// country
					]);
				";
				foreach($order->products as $product){
					$js.="
					_gaq.push(['_addItem',
						'".$order_id."',           // order ID - required
						'".str_replace("'",'\\\'',$product->order_product_code)."',           		// SKU/code - required
						'".str_replace("'",'\\\'',strip_tags($product->order_product_name))."',     // product name
						'',
						'".($product->order_product_price+$product->order_product_tax)."',          // unit price - required
						'".$product->order_product_quantity."'               						// quantity - required
					]);";
				}

				$file='ga.js';
				if($this->params->get('debug_mode')){
					$file='u/ga_debug.js';
				}


				$js.=	"
					_gaq.push(['_trackTrans']); //submits transaction to the Analytics servers

					(function() {
						var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/".$file."';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
					})();";
				$js = '<script type="text/javascript">'.$js.'</script>';
				return $js;
			}
		}
		return '';
	}
}
