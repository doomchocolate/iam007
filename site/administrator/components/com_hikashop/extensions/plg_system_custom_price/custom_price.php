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
jimport('joomla.plugin.plugin');
class plgSystemCustom_price extends JPlugin{
}

if(!function_exists('hikashop_product_price_for_quantity_in_cart') && !function_exists('hikashop_product_price_for_quantity_in_order')) {
	function hikashop_product_price_for_quantity_in_cart(&$product){
		$currencyClass = hikashop_get('class.currency');
		$quantity = @$product->cart_product_quantity;


		$plugin = JPluginHelper::getPlugin('system', 'custom_price');
		if(version_compare(JVERSION,'2.5','<')){
			jimport('joomla.html.parameter');
			$params = new JParameter($plugin->params);
		} else {
			$params = new JRegistry($plugin->params);
		}

		$taxes = $params->get('taxes',0);

		if(!empty($product->amount)){
			if(empty($product->prices)){
				$price= new stdClass();
				$price->price_currency_id = hikashop_getCurrency();
				$price->price_min_quantity = 1;
				$product->prices = array($price);
			}
			foreach($product->prices as $k => $price){
				if($taxes && $product->product_type=='variant' && empty($product->product_tax_id)){
					$productClass = hikashop_get('class.product');
					$main = $productClass->get($product->product_parent_id);
					$product->product_tax_id = $main->product_tax_id;
				}
				switch($taxes){
					case 2:
						$product->prices[$k]->price_value = $currencyClass->getUntaxedPrice($product->amount,hikashop_getZone(),$product->product_tax_id);
						$product->prices[$k]->taxes=$currencyClass->taxRates;
						$product->prices[$k]->price_value_with_tax = $product->amount;
						break;
					case 1:
						$product->prices[$k]->price_value = $product->amount;
						$product->prices[$k]->price_value_with_tax = $currencyClass->getTaxedPrice($product->amount,hikashop_getZone(),$product->product_tax_id);
						$product->prices[$k]->taxes=$currencyClass->taxRates;
						break;
					case 0:
					default:
						$product->prices[$k]->price_value = $product->amount;
						$product->prices[$k]->price_value_with_tax = $product->amount;
						break;
				}
			}
		}

		$currencyClass->quantityPrices($product->prices,$quantity,$product->cart_product_total_quantity);
	}
}
