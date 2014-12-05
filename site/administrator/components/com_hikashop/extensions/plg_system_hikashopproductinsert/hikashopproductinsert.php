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
class plgSystemHikashopproductInsert extends JPlugin {

	var $name = 0;
	var $pricetax = 0;
	var $pricedis = 0;
	var $cart = 0;
	var $quantityfield = 0;
	var $description = 0;
	var $picture = 0;
	var $link = 0;
	var $border = 0;
	var $badge = 0;
	var $price = 0;
	function plgSystemHikashopproductInsert(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopproductinsert');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}
	}

	function escape($str){ return htmlspecialchars($str, ENT_COMPAT, 'UTF-8');}

	function onAfterRender() {
		$app = JFactory::getApplication();

		if ($app->isAdmin()) return true;
		$layout = JRequest::getString('layout');
		if ($layout == 'edit') return true;
		$body = JResponse::getBody();

		if (preg_match_all('#\{product\}(.*)\{\/product\}#Uis', $body, $matches)) {
			if(!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
			$db = JFactory::getDBO();
			$currencyClass = hikashop_get('class.currency');
			$this->image = hikashop_get('helper.image');
			$this->classbadge = hikashop_get('class.badge');
			$para=array();
			$nbtag = count($matches[1]);
			for ($i = 0; $i < $nbtag; $i++) {
				$para[$i] = explode('|', $matches[1][$i]);
			}
			$k = 0;
			$ids=array();
			for ($i = 0; $i<$nbtag; $i++){
				for ($u=0; $u<count($para[$i]);$u++){
					if($para[$i][$u]!='name' &&
					$para[$i][$u]!='pricetax' &&
					$para[$i][$u]!='pricedis' &&
					$para[$i][$u]!='cart' &&
					$para[$i][$u]!='quantityfield' &&
					$para[$i][$u]!='description' &&
					$para[$i][$u]!='link' &&
					$para[$i][$u]!='border' &&
					$para[$i][$u]!='badge' &&
					$para[$i][$u]!='picture'){
						$ids[$k]= (int)$para[$i][$u];
						$k++;
					}
				}
			}

			$product_query = 'SELECT * FROM ' . hikashop_table('product') . ' WHERE product_id IN (' . implode(',', $ids) . ') AND product_access=\'all\' AND product_published=1 AND product_type=\'main\'';
			$db->setQuery($product_query);
			$products = $db->loadObjectList();

			$db->setQuery('SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
			$variants = $db->loadObjectList();
			if(!empty($variants)){
				foreach($products as $k => $product){
					foreach($variants as $variant){
						if($product->product_id==$variant->variant_product_id){
							$products[$k]->has_options = true;
							break;
						}
					}
				}
			}
			foreach($products as $k => $product){
				$this->classbadge->loadBadges($products[$k]);
			}
			$queryImage = 'SELECT * FROM ' . hikashop_table('file') . ' WHERE file_ref_id IN (' . implode(',', $ids) . ') AND file_type=\'product\' ORDER BY file_ordering ASC, file_id ASC';
			$db->setQuery($queryImage);
			$images = $db->loadObjectList();
			$productClass = hikashop_get('class.product');
			foreach ($products as $k => $row) {
				$productClass->addAlias($products[$k]);
				foreach ($images as $j => $image) {
					if ($row->product_id == $image->file_ref_id) {
						foreach (get_object_vars($image) as $key => $name) {
							if(!isset($products[$k]->images)) $products[$k]->images = array();
							if(!isset($products[$k]->images[$j])) $products[$k]->images[$j] = new stdClass();
							$products[$k]->images[$j]->$key = $name;
						}
					}

				}
			}
			$zone_id=hikashop_getZone();
			$currencyClass = hikashop_get('class.currency');

			$currencyClass->getListingPrices($products,$zone_id,hikashop_getCurrency(),'all');
			for ($i = 0; $i < $nbtag; $i++) {
				$nbprodtag = count($para[$i]);
				$this->name = 0;
				$this->pricetax = 0;
				$this->pricedis = 0;
				$this->price = 0;
				$this->cart = 0;
				$this->quantityfield = 0;
				$this->description = 0;
				$this->picture = 0;
				$this->link = 0;
				$this->border = 0;
				$this->badge = 0;
				if (in_array("name", $para[$i])) {
					$this->name = 1;
					$nbprodtag--;
				}
				if (in_array("pricedis1", $para[$i])) {
					$this->pricedis = 1;
					$nbprodtag--;
				}
				if (in_array("pricedis2", $para[$i])) {
					$this->pricedis = 2;
					$nbprodtag--;
				}
				if (in_array("pricedis3", $para[$i])) {
					$this->pricedis = 3;
					$nbprodtag--;
				}
				if (in_array("pricetax1", $para[$i])) {
					$this->pricetax = 1;
					$nbprodtag--;
				}
				if (in_array("pricetax2", $para[$i])) {
					$this->pricetax = 2;
					$nbprodtag--;
				}
				if (in_array("price", $para[$i])) {
					$this->price = 1;
					$nbprodtag--;
				}
				if (in_array("cart", $para[$i])) {
					$this->cart = 1;
					$nbprodtag--;
				}
				if (in_array("quantityfield", $para[$i])) {
					$this->quantityfield = 1;
					$nbprodtag--;
				}
				if (in_array("description", $para[$i])) {
					$this->description = 1;
					$nbprodtag--;
				}
				if (in_array("picture", $para[$i])) {
					$this->picture = 1;
					$nbprodtag--;
				}
				if (in_array("link", $para[$i])) {
					$this->link = 1;
					$nbprodtag--;
				}
				if (in_array("border", $para[$i])) {
					$this->border = 1;
					$nbprodtag--;
				}
				if (in_array("badge", $para[$i])) {
					$this->badge = 1;
					$nbprodtag--;
				}
				$id = array();
				for ($j = 0; $j < $nbprodtag; $j++) {
					$id[$j] = $para[$i][$j];
				}

				if(version_compare(JVERSION,'3.0','<'))
					JHTML::_('behavior.mootools');
				else
					JHTML::_('behavior.framework');
				$name = 'hikashopproductinsert_view.php';
				$path = JPATH_THEMES.DS.$app->getTemplate().DS.'system'.DS.$name;
				if(!file_exists($path)){
					if(version_compare(JVERSION,'1.6','<')){
						$path = JPATH_PLUGINS .DS.'system'.DS.$name;
					}else{
						$path = JPATH_PLUGINS .DS.'system'.DS.'hikashopproductinsert'.DS.$name;
					}
					if(!file_exists($path)){
						return true;
					}
				}

				ob_start();
				require($path);
				$product_view = ob_get_clean();
				$pattern = '#\{product\}(.*)\{\/product\}#Uis';
				$replacement = '';
				$body = JResponse::getBody();
				$body = preg_replace($pattern, str_replace('$','\$',$product_view), $body, 1);

			JResponse::setBody($body);
			}

			return;
		} else {
			return;
		}
	}
}
?>
