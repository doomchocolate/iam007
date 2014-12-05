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
?>
<?php

class plgSystemHikashopproducttag extends JPlugin {
	function onAfterRender() {
		$option = JRequest::getString('option');
		$ctrl = JRequest::getString('ctrl');
		$task = JRequest::getString('task');

		if ($option!='com_hikashop'||$ctrl!='product'||$task!='show') return;

		$config =& hikashop_config();
		$default_params = $config->get('default_params');

		$body = JResponse::getBody();

		$pattern='/class="hikashop_product_page"/';
		$replacement='class="hikashop_product_page" itemscope itemtype="http://schema.org/Product"';
		$body = preg_replace($pattern,$replacement,$body,1);
		$pattern='/id="hikashop_product_name_main"/';
		$replacement='id="hikashop_product_name_main" itemprop="name"';
		$body = preg_replace($pattern,$replacement,$body,1);
		if($default_params['show_price'] == 1){
			$currency_id = hikashop_getCurrency();
			$null = null;
			$currencyClass = hikashop_get('class.currency');
			$currencies = $currencyClass->getCurrencies($currency_id,$null);
			$data=$currencies[$currency_id];

			$pattern='/<span id="hikashop_product_price_main" class="hikashop_product_price_main">/';
			$replacement= '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer"><span id="hikashop_product_price_main" class="hikashop_product_price_main"><meta itemprop="currency" content="'.$data->currency_code.'" />';
			$body = preg_replace($pattern,$replacement,$body,1);
			$pattern='/<(span|div) id="(hikashop_product_weight_main|hikashop_product_width_main|hikashop_product_length_main|hikashop_product_height_main|hikashop_product_characteristics|hikashop_product_options|hikashop_product_custom_item_info|hikashop_product_price_with_options_main|hikashop_product_quantity_main)"/';
			$replacement='</div> <$1 id="$2"';
			$body = preg_replace($pattern,$replacement,$body,1);
			$pattern='/class="hikashop_product_price_main"(.*)class="hikashop_product_price hikashop_product_price_0/msU';
			$replacement='class="hikashop_product_price_main" $1 itemprop="price" class="hikashop_product_price hikashop_product_price_0';
			$body = preg_replace($pattern,$replacement,$body,1);
		}
		$pattern='/id="hikashop_product_vote_listing"/';
		$replacement='id="hikashop_product_vote_listing" itemprop="reviews" itemscope itemtype="http://schema.org/Review"';
		$body = preg_replace($pattern,$replacement,$body,1);
		$pattern='/class="hikashop_vote_listing_comment"/';
		$replacement='class="hikashop_vote_listing_comment" itemprop="description"';
		$body = preg_replace($pattern,$replacement,$body);
		$pattern='/class="hikashop_vote_listing_username"/';
		$replacement='class="hikashop_vote_listing_username" itemprop="author"';
		$body = preg_replace($pattern,$replacement,$body);
		$pattern='/class="hikashop_product_description_main"/';
		$replacement='class="hikashop_product_description_main" itemprop="description"';
		$body = preg_replace($pattern,$replacement,$body,1);
		$pattern='/class="hikashop_vote_stars"/';
		$replacement='class="hikashop_vote_stars" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"';
		$body = preg_replace($pattern,$replacement,$body);
		$pattern='/id="hikashop_main_image"/';
		$replacement='id="hikashop_main_image" itemprop="image"';
		$body = preg_replace($pattern,$replacement,$body);

		$ratemax=JRequest::getVar("nb_max_star");//nbmax
		$raterounded=JRequest::getVar("rate_rounded");//moy
		$pattern='/(<span\s+class="hikashop_total_vote")/iUs';
		$replacement = '<span style="display:none" itemprop="ratingValue">'.$raterounded.'</span><span style="display:none" itemprop="bestRating">'.$ratemax.'</span>$1 itemprop="reviewCount"';
		$body = preg_replace($pattern,$replacement,$body,1);


		JResponse::setBody($body);
		return;
	}
}
