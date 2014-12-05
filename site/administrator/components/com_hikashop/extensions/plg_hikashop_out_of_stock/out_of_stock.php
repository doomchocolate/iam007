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
class plgHikashopOut_of_stock extends JPlugin
{
	var $message = '';
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}

	function onHikashopCronTrigger(&$messages){
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','out_of_stock');
		if(empty($plugin->params['period'])){
			$plugin->params['period'] = 86400;
		}
		if(empty($plugin->params['stock_limit'])){
			$plugin->params['stock_limit'] = 0;
		}
		$this->stock_limit = $plugin->params['stock_limit'];
		$this->period = $plugin->params['period'];
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['period']>time()){
			return true;
		}
		$plugin->params['last_cron_update']=time();
		$pluginsClass->save($plugin);
		$this->checkProducts();
		if(!empty($this->message)){
			$messages[] = $this->message;
		}
		return true;
	}

	function checkProducts() {
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE '.
			' product_quantity < '.(int)$this->stock_limit.' AND product_published = 1 AND product_quantity != -1 '.
			' AND (product_sale_start = 0 OR product_sale_start < '.time().') AND (product_sale_end = 0 OR product_sale_end > '.time().')';
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if(!empty($products)){
			$mailClass = hikashop_get('class.mail');
			$infos = new stdClass();
			$infos->products =& $products;
			$mail = $mailClass->get('out_of_stock',$infos);
			$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);
			$config =& hikashop_config();
			if(!empty($infos->email)){
				$mail->dst_email = $infos->email;
			}else{
				$mail->dst_email = $config->get('from_email');
			}
			if(!empty($infos->name)){
				$mail->dst_name = $infos->name;
			}else{
				$mail->dst_name = $config->get('from_name');
			}
			$mailClass->sendMail($mail);
		}

		$app = JFactory::getApplication();
		$this->message = 'Products quantity checked';
		$app->enqueueMessage($this->message );
		return true;
	}
}
