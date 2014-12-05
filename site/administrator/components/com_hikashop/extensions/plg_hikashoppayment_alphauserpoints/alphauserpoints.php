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
class plgHikashoppaymentAlphauserpoints extends JPlugin {
	var $multiple = false;
	var $name = 'alphauserpoints';

	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
		if(!(file_exists($api_AUP)))
			return true;
		$user = Jfactory::getUser();
		if(empty($user->id)) {
			return true;
		}
		if(!empty($methods)) {
			foreach($methods as $i => $method) {
				if($method->payment_type != 'alphauserpoints' || !$method->enabled) {
					continue;
				}

				if(@$method->payment_params->virtual_coupon && $method->payment_params->partialpayment != 0) {
					continue;
				}

				$userInfo = null;
				if($this->checkRules($method, $order, $userInfo) == false)
					return true;

				if(!$method->payment_params->virtual_coupon && !empty($order->coupon->discount_code)){
					if(preg_match('#^POINTS_[a-zA-Z0-9]{30}$#',$order->coupon->discount_code)){
						return true;
					}
				}

				$currency = hikashop_getCurrency();
				$currencyClass = hikashop_get('class.currency');
				if($this->main_currency != $currency) {
					$method->payment_params->value = $currencyClass->convertUniquePrice($method->payment_params->value, $this->main_currency, $currency);
				}

				if(isset($order->order_currency_id)) {
					$curr = $order->order_currency_id;
				} else {
					$curr = hikashop_getCurrency();
				}
				$price = $currencyClass->format($this->pointsToCurrency($userInfo, $methods[$i], $order), $curr);

				$method->payment_description .= JText::sprintf('YOU_HAVE', $userInfo->points, $price);

				$fullOrderPoints = $this->finalPriceToPoints($order, $userInfo, $methods[$i]);

				if($method->payment_params->partialpayment == 0 ) {
					if( $method->payment_params->allowshipping == 1 ) {
						$method->payment_description.=JText::sprintf( 'PAY_FULL_ORDER_POINTS', $fullOrderPoints);
					} else {
						$method->payment_description.=JText::sprintf( 'PAY_FULL_ORDER_NO_SHIPPING', $fullOrderPoints);
						$method->payment_description.=JText::sprintf( 'COUPON_GENERATE' );
						$method->payment_description.=JText::sprintf( 'CAUTION_POINTS' );
					}
				} else {
					$check = $this->checkPoints($order);
					if( $check >= $fullOrderPoints ) {
						$method->payment_description .= JText::sprintf( 'PAY_FULL_ORDER_POINTS', $fullOrderPoints);
					} else {
						$coupon = $check * $methods[$i]->payment_params->value;
						$price = $currencyClass->format($coupon, $this->currency->currency_id);
						$method->payment_description .= JText::sprintf( 'COUPON_GENERATE_PARTIAL', $price );
						$method->payment_description .= JText::sprintf( 'CAUTION_POINTS' );
					}
				}

				$usable_methods[$method->ordering]=$method;
			}
		}
		return true;
	}

	function onAfterCartProductsLoad(&$cart) {
		$method = $this->getMethod();
		if(@$method->payment_params->virtual_coupon) {
			if(hikashop_level(2) && !hikashop_isAllowed($method->payment_access))
				return;

			if(isset($cart->additional['aup']))
				return;

			if($method->payment_params->partialpayment == 0){
				return;
			}

			$userInfo = null;
			if($this->checkRules($method, $cart, $userInfo) == false)
				return true;

			$currency = hikashop_getCurrency();
			$currencyClass = hikashop_get('class.currency');
			if($this->main_currency != $currency) {
				$method->payment_params->value = $currencyClass->convertUniquePrice($method->payment_params->value, $this->main_currency, $currency);
			}

			$check = $this->checkPoints($cart);
			if($check !== false && $check > 0) {

				if(isset($cart->order_currency_id)) {
					$currency_id = $cart->order_currency_id;
				} else {
					$currency_id = hikashop_getCurrency();
				}

				$coupon = $check * $method->payment_params->value;

				$aup = new stdClass();
				$aup->name = 'AUP_DISCOUNT';
				$aup->value = '';
				$aup->price_currency_id = $currency_id;
				$aup->price_value = -$coupon;
				$aup->price_value_with_tax = -$coupon;
				$cart->additional['aup'] = $aup;

				$pointsToLoose = -$check;
				if($method->payment_params->virtualpoints) {
					$newPoints = $this->getPointsEarned($cart);

					if($newPoints <= $check) {
						$pointsToLoose = $newPoints - $check;
					} else {
						$pointsToLoose = 0;
					}
				}

				if($method->payment_params->virtualpoints || true) {
					$aup_points = new stdClass();
					$aup_points->name = 'AUP_USE_POINTS';
					$aup_points->value = $pointsToLoose.' '.JText::_('AUP_POINTS');
					$aup_points->price_currency_id = 0;
					$aup_points->price_value = 0;
					$aup_points->price_value_with_tax = 0;

					$cart->additional['aup_points'] = $aup_points;
				}
			}
		}
	}

	function checkRules($method, $order, &$userInfo) {

		$user =& Jfactory::getUser();
		if(empty($user->id))
			return false;

		if(!$this->getAUP())
			return false;
		$userInfo = AlphaUserPointsHelper::getUserInfo('', $user->id);

		$check = $this->checkPoints($order, true);
		if(@$method->payment_params->virtualpoints) {
			$newPoints = $this->getPointsEarned($order);
			$userInfo->points += $newPoints;
		}
		if($check === false || $check == 0 || $userInfo->points == 0){
			return false;
		}

		if(!isset($order->full_total)) {
			$total = $order->order_full_price;
			$total_without_shipping = $total-$order->order_shipping_price;
		} else {
			$total = $order->full_total->prices[0]->price_value_with_tax;
			$total_without_shipping = $order->total->prices[0]->price_value_with_tax;
		}

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_getCurrency();
		$this->currency = $currencyClass->get($this->main_currency);
		if($this->main_currency != $currency) {
			$method->payment_params->minimumcost = $currencyClass->convertUniquePrice($method->payment_params->minimumcost, $this->main_currency, $currency);
		}
		if($method->payment_params->minimumcost > $total) {
			return false;
		}

		if($method->payment_params->allowshipping == 1) {
			$calculatedPrice = $total;
		} else {
			$calculatedPrice = $total_without_shipping;
		}

		$neededpoints = ($method->payment_params->percent / 100) * $calculatedPrice;
		$useablePoints = $this->pointsToCurrency($userInfo, $method);
		if($useablePoints < $neededpoints) {
			return false;
		}

		if($method->payment_params->partialpayment == 0) {
			$method->payment_params->percentmax = 100;
		}
		if($method->payment_params->percentmax <= 0) {
			return false;
		}

		if(!empty($method->payment_zone_namekey)) {
			$zoneClass = hikashop_get('class.zone');
			$zones = $zoneClass->getOrderZones($order);
			if(!in_array($method->payment_zone_namekey,$zones)) {
				return false;
			}
		}

		return true;
	}

	function pointsToCurrency(&$userInfo, &$method){
		$coupon = @$userInfo->points * @$method->payment_params->value;
		return $coupon;
	}

	function checkPoints(&$order, $showWarning = false) {
		static $displayed = false;

		$method = $this->getMethod();
		$user =& Jfactory::getUser();
		if(empty($user->id)) {
			return false;
		}

		if($this->getAUP()) {
			$userInfo = AlphaUserPointsHelper::getUserInfo('', $user->id);
		} else {
			return false;
		}

		if(empty($userInfo))
			$userInfo = new stdClass();
		if(empty($userInfo->points))
			$userInfo->points = 0;

		if(@$method->payment_params->virtualpoints) {
			$userInfo->points += $this->getPointsEarned($order);
		}
		$fullOrderPoints = $this->finalPriceToPoints($order, $userInfo, $method);
		$points = $fullOrderPoints;

		if( $method->payment_params->partialpayment == 0 ) {
			if( (int)$userInfo->points >= $fullOrderPoints )
				return $fullOrderPoints;
			return 0;
		}

		if( !empty($method->payment_params->percentmax) && ((int)$method->payment_params->percentmax > 0) && ((int)$method->payment_params->percentmax <= 100)) {
			$points = $points * ( (int)$method->payment_params->percentmax / 100 );
		}

		if( (int)$userInfo->points < $points ) {
			$points = (int)$userInfo->points;
		}

		if( isset($method->payment_params->grouppoints) && ((int)$method->payment_params->grouppoints > 1) ) {
			if($showWarning && !$displayed) {
				$method->payment_params->grouppoints = (int)$method->payment_params->grouppoints;
				if(isset($method->payment_params->grouppoints_warning_lvl) && ((int)$method->payment_params->grouppoints_warning_lvl >= 1) ) {
					if($points < $method->payment_params->grouppoints && ($points + (int)$method->payment_params->grouppoints_warning_lvl) >= $method->payment_params->grouppoints) {

						$app =& JFactory::getApplication();
						$currencyClass = hikashop_get('class.currency');

						if(isset($cart->order_currency_id)) {
							$currency_id = $cart->order_currency_id;
						} else {
							$currency_id = hikashop_getCurrency();
						}
						$possible_coupon = $method->payment_params->grouppoints * $method->payment_params->value;
						$price = $currencyClass->format($possible_coupon, $currency_id);

						$app->enqueueMessage(JText::sprintf('MISSING_X_POINTS_TO_REDUCTION', $method->payment_params->grouppoints - $points, $price));
						$displayed = true;
					}
				}
			}
			$points -= ($points % $method->payment_params->grouppoints);
		}

		if(isset($method->payment_params->maxpoints) && ((int)$method->payment_params->maxpoints > 0) && $points > (int)$method->payment_params->maxpoints) {
			$points = (int)$method->payment_params->maxpoints;

			if( isset($method->payment_params->grouppoints) && ((int)$method->payment_params->grouppoints > 1) ) {
				$points -= ($points % (int)$method->payment_params->grouppoints);
			}
		}

		if( $points < (int)$userInfo->points )
			return (int)$points;
		return (int)$userInfo->points;
	}

	function onPaymentSave(&$cart,&$rates,&$payment_id){
		$usable = array();
		$this->onPaymentDisplay($cart,$rates,$usable);
		$payment_id = (int) $payment_id;
		foreach($usable as $usable_method){
			if($usable_method->payment_id==$payment_id){
				return $usable_method;
			}
		}
		return false;
	}

	function finalPriceToPoints(&$order, &$userInfo, &$method){
		if(empty($method->payment_params->value) || bccomp($method->payment_params->value,0,5)<1) return 0;
		if(isset($order->order_subtotal) && isset($order->order_shipping_price)) {
			if($method->payment_params->allowshipping == 1){
				$final_price = @$order->order_subtotal + $order->order_shipping_price;
			}else{
				$final_price = @$order->order_subtotal;
			}
		} else if(empty($order->cart->full_total->prices[0]->price_value_with_tax)){
			if($method->payment_params->allowshipping == 1){
				$final_price = @$order->full_total->prices[0]->price_value_with_tax;
			}else{
				$final_price = @$order->total->prices[0]->price_value_with_tax;
			}
		} else {
			if($method->payment_params->allowshipping == 1){
				$final_price = @$order->cart->full_total->prices[0]->price_value_with_tax;
			}else{
				$final_price = @$order->cart->total->prices[0]->price_value_with_tax;
			}
		}

		$pointsDecrease = $final_price * ( 1 / $method->payment_params->value );
		return round($pointsDecrease, 0);
	}

	function getPointsEarned($order){
		$method = $this->getMethod();
		$points = 0;
		$database = JFactory::getDBO();

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$this->currency = $currencyClass->get($this->main_currency);

		if(isset($order->order_currency_id)) {
			$order_currency_id = $order->order_currency_id;
		} else {
			$order_currency_id = hikashop_getCurrency();
		}
		if($this->main_currency != $order_currency_id) {
			$method->payment_params->value = $currencyClass->convertUniquePrice($method->payment_params->value, $this->main_currency, $order_currency_id);
			$method->payment_params->minimumcost = $currencyClass->convertUniquePrice($method->payment_params->minimumcost, $this->main_currency, $order_currency_id);
			$method->payment_params->currency_rate = $currencyClass->convertUniquePrice($method->payment_params->currency_rate, $this->main_currency, $order_currency_id);
		}

		$categories = unserialize($method->payment_params->categories);
		if(!empty($categories)){
			if(!empty($order->cart->products)) {
				$products =& $order->cart->products;
			} else {
				$products =& $order->products;
			}
			foreach($products as $product) {
				$ids[$product->product_id] = $product->product_id;
			}
			$queryP = 'SELECT product_parent_id FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$ids).')';
			$database->setQuery($queryP);
			$pids = $database->loadObjectList();
			if(!empty($pids)) {
				foreach($pids as $pid) {
					$ids[$pid->product_parent_id] = $pid->product_parent_id;
				}
			}

			$query = 'SELECT * FROM '.hikashop_table('product_category').' prod LEFT JOIN '.hikashop_table('category').' cat ON prod.category_id=cat.category_id ' .
					'WHERE prod.product_id IN ('.implode(',',$ids).')';
			$database->setQuery($query);
			$idcats = $database->loadObjectList();
			if(!empty($idcats)) {
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND ';
				$conditions = array();
				foreach($idcats as $idcat) {
					$conditions[] = '(category_left <= '.$idcat->category_left.' AND category_right >= '.$idcat->category_right.')';
					$idfinalcats[$idcat->product_id][0][] = $idcat->category_id;
				}
				$query .= implode(' OR ',$conditions);
			}
			$database->setQuery($query);
			$idparentcats = $database->loadObjectList('category_id');
			foreach($idcats as $id) {
				$this->_makeLevel($idfinalcats[$id->product_id], 0, $idparentcats);
			}

			$maxPoints = 0;
			$tempCatId = null;
			foreach($idfinalcats as $product){
				foreach($product as $levels){
					foreach($categories as $category){
						foreach ($levels as $categoryid){
							if($categoryid==$category->category_id && $category->category_points>$maxPoints){
								$maxPoints=$category->category_points;
								$tempCatId=$category->category_id;
							}
						}
						$points+=$maxPoints;
						if(!empty($tempCatId)){
							foreach ($categories as $category){
								if($category->category_id==$tempCatId && $method->payment_params->limitecategory==1){
									$category->category_points=0;
								}
							}
							$maxPoints=0;
							$tempCatId=0;
							break 2;
						}
						$maxPoints=0;
					}
				}
			}
		}

		if(hikashop_level(2)) {
			$groups=unserialize($method->payment_params->groups);
			if(version_compare(JVERSION,'1.6.0','<')) {
					$my =& JFactory::getUser();
					$gid = @$my->gid;
				if(empty($gid)) {
					$userGroups = array(29); // default group in J1.5
				} else {
					$userGroups = array($gid);
				}
			} else {
				if(!empty($order->customer->user_cms_id)) {
					$user_id = $order->customer->user_cms_id;
				} else {
					$my =& JFactory::getUser();
					$user_id = @$my->id;
				}
				jimport('joomla.access.access');
				$userGroups = JAccess::getGroupsByUser($user_id, true);//$my->authorisedLevels();
			}

			foreach($userGroups as $groupid) {
				if(!empty($groups[$groupid])) {
					$points += $groups[$groupid];
				}
				if(!empty($groups[$groupid]) && $method->payment_params->limitegroup == 1) {
					break;
				}
			}
		}

		$cart = null;
		if(isset($order->cart)) {
			$cart =& $order->cart;
		} else {
			$cart =& $order;
		}
		if(!empty($cart->full_total->prices[0]->price_value_with_tax)) {
			if($method->payment_params->shippingpoints==1) {
				$calculatedPrice = $cart->full_total->prices[0]->price_value_with_tax - @$cart->coupon->discount_value;
			} else {
				$calculatedPrice = $cart->total->prices[0]->price_value_with_tax - @$cart->coupon->discount_value;
			}
		} else {
			if($method->payment_params->shippingpoints==1) {
				$calculatedPrice = $order->order_full_price;
			} else {
				$calculatedPrice = $order->order_full_price - $order->order_shipping_price;
			}
		}
		unset($cart);

		if($method->payment_params->currency_rate!=0) {
			$points += $calculatedPrice/$method->payment_params->currency_rate;
		}

		if(!empty($order->cart->products)) {
			$products = &$order->cart->products;
		} else {
			$products = &$order->products;
		}

		if($method->payment_params->limitetype == 1) {
			foreach ($products as $product) {
				$points += $method->payment_params->productpoints;
			}
		} elseif($method->payment_params->limitetype == 0) {
			foreach ($products as $product) {
				if(isset($product->order_product_quantity)) {
					$points += $method->payment_params->productpoints * $product->order_product_quantity;
				} else {
					$points += $method->payment_params->productpoints * $product->cart_product_quantity;
				}
			}
		}
		return round($points, 0);
	}

	function _makeLevel(&$productData,$level,&$idparentcats){
		foreach($productData[$level] as $cat){
			if(!empty($idparentcats[$cat]->category_parent_id) && !empty($idparentcats[$idparentcats[$cat]->category_parent_id])){
				$productData[$level+1][] = $idparentcats[$cat]->category_parent_id;
			}
		}
		if(!empty($productData[$level+1])){
			$this->_makeLevel($productData,$level+1,$idparentcats);
		}
	}

	function getAUP(){
		static $aup=null;
		if(!isset($aup)){
			$aup=false;
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
			if(file_exists($api_AUP)){
				require_once ($api_AUP);
				if(class_exists('AlphaUserPointsHelper')){
					$aup=true;
				}
			}
			if(!$aup){
				$app = JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage('The HikaShop AlphaUserPoints plugin requires the component AlphaUserPoints to be installed. If you want to use it, please install the component.');
				}
			}
		}
		return $aup;
	}

	function onPaymentConfiguration(&$element){
	 	$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
		if ( !(file_exists($api_AUP))){
			$app = JFactory::getApplication();
			$app->enqueueMessage('You have to install the AlphaUserPoint component in order to use this payment method', 'error');
			$link=hikashop::completeLink("plugins&plugin_type=payment",false, true);
			$app->redirect($link);
			return true;
		}
		$db = &JFactory::getDBO();
		$query='SELECT id FROM '.hikashop::table('alpha_userpoints_rules',false).' WHERE rule_name="Order_validation"';
		$db->setQuery($query);
		$exist=$db->loadResult();
		if(empty($exist)){
			$query='INSERT INTO '.hikashop::table('alpha_userpoints_rules',false).' (rule_name, rule_description, rule_plugin, plugin_function, access, points, published, system, autoapproved)' .
					'VALUES ("Order_validation", "Give points to customer when the order is validate", "com_hikashop", "plgaup_orderValidation", 1, 0, 1, 0,1)';
			$db->setQuery($query);
			$db->query();
		}
		$subtask = JRequest::getCmd('subtask','');
		$this->alphauserpoints = JRequest::getCmd('name','alphauserpoints');
		$this->getAUP();
		if(empty($element)){
			$element = new stdClass();
			$element->payment_name='Pay with Points';
			$element->payment_description='You can pay with points using this payment method';
			$element->payment_images='';
			$element->payment_type=$this->alphauserpoints;
			$element->payment_params=new stdClass();
			$element->payment_params->invalid_status='cancelled';
			$element->payment_params->pending_status='created';
			$element->payment_params->verified_status='confirmed';
			$element->payment_params->valid_order_status='confirmed,shipped';
			$element->payment_params->percentmax=100;
			$element->payment_params->virtual_coupon = true;
			$element = array($element);
		}
		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' =>'payment-alphauserpoints-form')
		);

		hikashop_setTitle('AlphaUserPoints','plugin','plugins&plugin_type=payment&task=edit&name='.$this->alphauserpoints);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
		$this->categories = array();
		$key = key($element);
		if(!empty($element[$key]->payment_params->categories)){
			$this->categories = unserialize($element[$key]->payment_params->categories);
		}
		$ids = array();
		if(!empty($this->categories)){
			foreach($this->categories as $cat){
				$ids[]=$cat->category_id;
			}
			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$ids).')');
			$cats = $db->loadObjectList('category_id');
			foreach($this->categories as $k => $cat){
				if(!empty($cats[$cat->category_id])){
					$this->categories[$k]->category_name = $cats[$cat->category_id]->category_name;
				}else{
					$this->categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
				}
			}
		}

		$acl = JFactory::getACL();
		if(version_compare(JVERSION,'1.6.0','<')){
			$this->groups = $acl->get_group_children_tree( null, 'USERS', false );
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
			$this->groups = $db->loadObjectList('id');
			foreach($this->groups as $id => $group){
				if(isset($this->groups[$group->parent_id])){
					$this->groups[$id]->level = intval(@$this->groups[$group->parent_id]->level) + 1;
					$this->groups[$id]->text = str_repeat('- - ',$this->groups[$id]->level).$this->groups[$id]->text;
				}
			}
		}

		if(!empty($element[$key]->payment_params->groups)){
			$element[$key]->payment_params->groups = unserialize($element[$key]->payment_params->groups);
			foreach($this->groups as $id => $group){
				$this->groups[$id]->points = (int)@$element[$key]->payment_params->groups[$group->value];
			}
	 	}

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currency = hikashop_get('class.currency');
		$this->currency = $currency->get($this->main_currency);

		$js='
function setVisible(value){
	value = (parseInt(value) == 1) ? "" : "none";
	document.getElementById("opt").style.display = value;
	document.getElementById("opt2").style.display = value;
}
';
		if(!HIKASHOP_PHP5)
			$doc =& JFactory::getDocument();
		else
			$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	function onPaymentConfigurationSave(&$element){
		$categories = JRequest::getVar( 'category', array(), '', 'array' );
		JArrayHelper::toInteger($categories);
		$cats = array();
		if(!empty($categories)){
			foreach($categories as $id => $category){
				if((int)@$_REQUEST['category_points'][$id]!=0){
					$obj = new stdClass();
					$obj->category_id = $category;
					$obj->category_points = (int)@$_REQUEST['category_points'][$id];
					$cats[]=$obj;
				}
			}
		}
		$element->payment_params->categories = serialize($cats);
		$groups = JRequest::getVar( 'groups', array(), '', 'array' );
		JArrayHelper::toInteger($groups);
		$element->payment_params->groups = serialize($groups);

		if($method->payment_params->virtual_coupon && $method->payment_params->partialpayment == 0){
			$app = JFactory::getApplication();
			$app->enqueueMessage('The Virtual coupon mode cannot be used for partial payment with points. Either deactivate the Virtual coupon mode or the partial payment. Otherwise, you won\'t see any payment with points on the checkout');
		}
	}

	function getMethod(){
		static $paymentData = null;

		if($paymentData == null) {
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type=\'alphauserpoints\'';
			$db->setQuery($query);
			$paymentData = $db->loadObject();
			$paymentData->payment_params = unserialize($paymentData->payment_params);
		}
		return $paymentData;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$this->removeCart = true;
	}

	function onBeforeOrderCreate(&$order,&$do){
		$method=$this->getMethod();
		if(!empty($order->cart->coupon->discount_code)){
			if(preg_match('#^POINTS_[a-zA-Z0-9]{30}$#',$order->cart->coupon->discount_code)){
				if($method->payment_params->partialpayment==0){
					if($order->cart->full_total->prices[0]->price_value_without_discount!=$order->cart->coupon->discount_value){
						$do=false;
						echo JText::sprintf( 'ERROR_POINTS' );
						return true;
					}
				}
			}
		}
		if(empty($order->order_payment_method) || $order->order_payment_method != 'alphauserpoints'){
			return true;
		}
		if(!$this->getAUP()) {
			return true;
		}

		$check = $this->checkPoints($order);
		$user = Jfactory::getUser();

		$userInfo = AlphaUserPointsHelper::getUserInfo( '', $user->id);
		$fullOrderPoints = $this->finalPriceToPoints($order, $userInfo, $method);

		if($method->payment_type == 'alphauserpoints' && ($method->payment_params->partialpayment == 1 || $method->payment_params->allowshipping == 0) && ($check !== false && $check > 0) && ($check < $fullOrderPoints) && $userInfo->points) {
			$discountClass = hikashop_get('class.discount');
			$obj=&hikashop_get('class.cart');
			$config =& hikashop_config();
	 		$currency = hikashop_getCurrency();

			$app = JFactory::getApplication();
			$newCoupon = new stdClass();
			$newCoupon->discount_type='coupon';
			$newCoupon->discount_currency_id = $currency;

			$newCoupon->discount_flat_amount = $check * $method->payment_params->value;
			$newCoupon->discount_quota = 1;
			jimport('joomla.user.helper');
			$newCoupon->discount_code = 'POINTS_';
			$newCoupon->discount_code .= JUserHelper::genRandomPassword(30);
			$newCoupon->discount_published = 1;
			$discountClass->save($newCoupon);
			$coupon = $newCoupon;
			if(!empty($coupon)){
				$obj->update($coupon->discount_code,1,0,'coupon');
				$obj->loadCart(0,true);
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method','');
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',0);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data',0);
			$do = false;
			if($this->getAUP()){
				if(empty($order->customer)){
					$userClass = hikashop_get('class.user');
					$order->customer = $userClass->get($order->order_user_id);
				}
				$aupid = AlphaUserPointsHelper::getAnyUserReferreID($order->customer->user_cms_id);
				AlphaUserPointsHelper::newpoints( 'plgaup_orderValidation', $aupid, '', JText::_('HIKASHOP_COUPON').' '.$coupon->discount_code, -$check);
			}
		}
	}

	function onAfterOrderCreate(&$order,&$send_email){
		$app =& JFactory::getApplication();
		if($app->isAdmin()) {
			return true;
		}
		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;

		$method = $order->order_payment_method;
		if($method=='alphauserpoints'){
			$points = $this->checkpoints($order);
			if($points !== false && $points > 0) {
				if($this->getAUP()) {
					if(empty($order->customer)){
						$userClass = hikashop_get('class.user');
						$order->customer = $userClass->get($order->order_user_id);
					}
					$aupid=AlphaUserPointsHelper::getAnyUserReferreID($order->customer->user_cms_id);
					AlphaUserPointsHelper::newpoints( 'plgaup_orderValidation', $aupid, '', $this->getDataReference($order), -$points);
				}
				$orderObj = new stdClass();
				$config =& hikashop_config();
				$orderObj->order_status = $config->get('order_confirmed_status');
				$orderObj->order_id = $order->order_id;
				$orderClass = hikashop_get('class.order');
				$orderClass->save($orderObj);
			}else{
				echo JText::sprintf( 'NOT_ENOUGH_POINTS' );
				$do=false;
				return false;
			}
		} else if( !empty($order->cart->additional) ) {
			$method = $this->getMethod();
			if($method->payment_params->virtual_coupon) {
				$points = $this->checkPoints($order);

				$usePts = -1;
				foreach($order->cart->additional as $additional) {
					if($additional->name == 'AUP_USE_POINTS') {
						$usePts = substr($additional->value, 0, strpos($additional->value, ' '));
						$usePts = (int)trim(str_replace('-','',$usePts));
						break;
					}
				}
				if($usePts > 0) {
					$points = $usePts;
				} else if($method->payment_params->virtualpoints) {
					$points = 0;
				}

				if($points !== false && $points > 0) {
					if(!$this->getAUP())
						return;
					if($points > 0) {
						if(empty($order->customer)){
							$userClass = hikashop_get('class.user');
							$order->customer = $userClass->get($order->order_user_id);
						}
						$aupid = AlphaUserPointsHelper::getAnyUserReferreID($order->customer->user_cms_id);
						AlphaUserPointsHelper::newpoints('plgaup_orderValidation', $aupid, '', $this->getDataReference($order), -$points);
					}
				}
			}
			$this->previousOrderStatus = '';
			$this->_giveAndGiveBack($order, $method, 0);
		}
	}

	function onBeforeOrderUpdate(&$order,&$do){
		$previousOrder = &hikashop_get('class.order');
		$previousOrder = $previousOrder->get($order->order_id);
		$this->previousOrderStatus = $previousOrder->order_status;
	}

	function onAfterOrderUpdate(&$order,&$send_email){
		$method = $this->getMethod();
		if(!isset($order->order_status))
			return true;
		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;
		$this->_giveAndGiveBack($order, $method, 0);
	}

	function getDataReference(&$order){
		if(!empty($order->order_number)){
			$number = $order->order_number;
		}
		elseif(!empty($order->old->order_number)){
			$number = $order->old->order_number;
		}
		elseif(!empty($order->order_id)){
			$class = hikashop_get('class.order');
			$data = $class->get($order->order_id);
			$number = $data->order_number;
		}else{
			return '';
		}
		return  JText::_('ORDER_NUMBER').' : '.$number;
	}

	function _giveAndGiveBack(&$order, &$method, $currentValue){

		$config =& hikashop_config();
		if(!empty($method->payment_params->valid_order_status)) {
			$confirmed = explode(',', $method->payment_params->valid_order_status);
		} else {
			$confirmed = array($config->get('order_confirmed_status'));
		}
		$created = $config->get('order_created_status');

		$fullOrder = null;

		if( (in_array($order->order_status, $confirmed) || (empty($order->order_status) && in_array($created,$confirmed))) && (empty($this->previousOrderStatus) || !in_array($this->previousOrderStatus, $confirmed)) ) {

			if($fullOrder == null){
				$classOrder =& hikashop_get('class.order');
				$fullOrder = $classOrder->loadFullOrder($order->order_id, false, false);
				if(empty($fullOrder->customer)){
					if(empty($userClass))
						$userClass = hikashop_get('class.user');
					$fullOrder->customer = $userClass->get($fullOrder->order_user_id);
				}
			}

			$points = $this->getPointsEarned($fullOrder);
			$userid = (int)$fullOrder->customer->user_cms_id;

			if(@$method->payment_params->virtualpoints && !empty($fullOrder->additional)) {
				$usePts = -1;
				foreach($fullOrder->additional as $additional) {
					if($additional->order_product_name == 'AUP_USE_POINTS') {
						$usePts = substr($additional->order_product_options, 0, strpos($additional->order_product_options, ' '));
						$usePts = (int)trim(str_replace('-','',$usePts));
						break;
					}
				}
				if($usePts > 0) {
					$points -= $usePts;
				} else {
					$points -= $this->checkPoints($fullOrder);
				}
			}

			if($this->getAUP() && $points > 0){
				$aupid = AlphaUserPointsHelper::getAnyUserReferreID($userid);
				AlphaUserPointsHelper::newpoints( 'plgaup_orderValidation', $aupid, '', $this->getDataReference($fullOrder), $points);
			}
		}

		if( !empty($order->order_status) &&
			$order->order_status != $this->previousOrderStatus &&
			!in_array($order->order_status, $confirmed) &&
			in_array($this->previousOrderStatus, $confirmed)
		){
			if($fullOrder == null){
				$classOrder =& hikashop_get('class.order');
				$fullOrder = $classOrder->loadFullOrder($order->order_id, false, false);
				if(empty($fullOrder->customer)){
					if(empty($userClass))
						$userClass = hikashop_get('class.user');
					$fullOrder->customer = $userClass->get($fullOrder->order_user_id);
				}
			}

			if($fullOrder->order_payment_method == 'alphauserpoints' && $method->payment_params->givebackpoints == 1){
				$userid = (int) $fullOrder->customer->user_cms_id;
				if($this->getAUP() && !empty($method->payment_params->value)){
					$aupid = AlphaUserPointsHelper::getAnyUserReferreID($userid);
					AlphaUserPointsHelper::newpoints( 'plgaup_orderValidation', $aupid, '', $this->getDataReference($fullOrder), round($fullOrder->order_full_price / $method->payment_params->value,0));
				}
				return true;
			}
			if(!empty($fullOrder->order_discount_code)){
				if(preg_match('#^POINTS_[a-zA-Z0-9]{30}$#',$fullOrder->order_discount_code) && $method->payment_params->givebackpoints==1){
					$userid = (int)$fullOrder->customer->user_cms_id;
					if($this->getAUP() && !empty($method->payment_params->value)){
						$aupid = AlphaUserPointsHelper::getAnyUserReferreID($userid);
						AlphaUserPointsHelper::newpoints( 'plgaup_orderValidation', $aupid, '', $this->getDataReference($fullOrder), round($fullOrder->order_discount_price / $method->payment_params->value,0));
					}
				}
			}
			$points = 0;
			if(in_array($this->previousOrderStatus, $confirmed))
				$points = $this->getPointsEarned($fullOrder);
			$userid = (int)$fullOrder->customer->user_cms_id;

			if($method->payment_params->virtual_coupon) {

				if(!empty($fullOrder->additional)) {
					$usePts = -1;
					foreach($fullOrder->additional as $additional) {
						if($additional->order_product_name == 'AUP_USE_POINTS') {
							$usePts = substr($additional->order_product_options, 0, strpos($additional->order_product_options, ' '));
							$usePts = (int)trim(str_replace('-','',$usePts));
							break;
						}
					}
					if($usePts > 0) {
						$points -= $usePts;
					} else {
						$points -= $this->checkPoints($fullOrder);
					}
				}
			}

			if($this->getAUP() && $points != 0){
				$aupid = AlphaUserPointsHelper::getAnyUserReferreID($userid);
				AlphaUserPointsHelper::newpoints( 'plgaup_orderValidation', $aupid, '', $this->getDataReference($fullOrder), -$points);
			}
		}
	}
}
