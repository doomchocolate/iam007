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
class plgHikashopRates extends JPlugin
{
	var $message = '';
	var $url = "http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml";
	function plgHikashopRates(&$subject, $config){
		parent::__construct($subject, $config);
		}

		function onHikashopCronTrigger(&$messages){
			$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','rates');
		if(empty($plugin->params['frequency'])){
			$plugin->params['frequency'] = 86400;
		}
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['frequency']>time()){
			return true;
		}
		$plugin->params['last_cron_update']=time();
		$pluginsClass->save($plugin);
		$this->updateRates();
		if(!empty($this->message)){
			$messages[] = $this->message;
		}
		return true;
		}

		function updateRates(){
		$XMLContent= file($this->url);
		if(empty($XMLContent)){
			if(function_exists('curl_init')){
				$ch = curl_init();

				curl_setopt ($ch, CURLOPT_URL, $this->url);
				curl_setopt ($ch, CURLOPT_HEADER, 0);

				ob_start();

				curl_exec ($ch);
				curl_close ($ch);
				$string = ob_get_clean();
				if(!empty($string)){
					$XMLContent = explode("\n",$string);
				}
			}
			if(empty($XMLContent)){
				$app =& JFactory::getApplication();
				$this->message = JText::_( 'NO_DATA_IN_ECB_FEED' );
				$app->enqueueMessage($this->message, 'error' );
				return false;
			}
		}
		$currencies=array();
			foreach($XMLContent as $line){
				if(preg_match('#currency=[\'"]?([A-Z]+)[\'"]?#',$line,$currency_match)&&preg_match('#rate=[\'"]?([0-9\.]+)[\'"]?#',$line,$rate_match)){
					$currencies[$currency_match[1]]=floatval($rate_match[1]);
				}
		}
		if(empty($currencies)){
			$app = JFactory::getApplication();
			$this->message = JText::_( 'No valid data in the currencies rate feed of the European Central Bank' );
			$app->enqueueMessage($this->message, 'error' );
			return false;
		}
		$config = hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$mainCurrencyData = $currencyClass->get($main_currency);
		if($mainCurrencyData->currency_code!='EUR'){
			if(in_array($mainCurrencyData->currency_code,array_keys($currencies))){
				$euroRate = 1.0/$currencies[$mainCurrencyData->currency_code];
				$newCurrencies = array();
				foreach($currencies as $code => $rate){
					if($code!=$mainCurrencyData->currency_code) $newCurrencies[$code]=$euroRate*$rate;
				}
				$newCurrencies[$mainCurrencyData->currency_code]=1;
				$newCurrencies['EUR']=$euroRate;
				$currencies=$newCurrencies;
			}else{
				$app = JFactory::getApplication();
				$this->message = JText::_( 'MAIN_CURRENCY_NOT_SUPPORTED_ECB' );
				$app->enqueueMessage($this->message, 'error' );
				return false;
			}
		}else{
			$currencies['EUR']=1;
		}

		$db = JFactory::getDBO();
		foreach($currencies as $code => $rate){
			$currency = null;
			$query='UPDATE '.hikashop_table('currency').' SET currency_modified='.time().',currency_rate='.$rate.' WHERE currency_code='.$db->Quote($code);
			$db->setQuery($query);
			$db->query();
		}
		$app = JFactory::getApplication();
		$this->message = JText::_( 'RATES_SUCCESSFULLY_UPDATED' );
		$app->enqueueMessage($this->message );
		return true;
		}
}
