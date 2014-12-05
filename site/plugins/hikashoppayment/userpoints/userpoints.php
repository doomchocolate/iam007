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
class plgHikashoppaymentUserpoints extends hikashopPaymentPlugin {
	var $multiple = true;
	var $name = 'userpoints';
	var $accepted_currencies = array();

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->config = hikashop_config();
	}

	function onPaymentDisplay(&$order, &$methods, &$usable_methods) {
		$user = JFactory::getUser();
		if(empty($user->id))
			return false;

		$ordered_methods = array();
		foreach($methods as &$method) {
			if($method->payment_type != $this->name || !$method->enabled || !$method->payment_published)
				continue;
			if(!empty($method->payment_params->virtual_coupon))
				$ordered_methods[] =& $method;
		}
		foreach($methods as &$method) {
			if($method->payment_type != $this->name || !$method->enabled || !$method->payment_published)
				continue;
			if(empty($method->payment_params->virtual_coupon))
				$ordered_methods[] =& $method;
		}

		if(empty($ordered_methods))
			return;

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency', 1);
		$this->currency_id = hikashop_getCurrency();
		$this->points = $this->getUserPoints(null, 'all');
		$this->virtualpoints = $this->getVirtualPoints($order, 'all');
		$this->virtual_coupon_used = false;

		parent::onPaymentDisplay($order, $ordered_methods, $usable_methods);
	}

	function checkPaymentDisplay(&$method, &$order) {
		$this->payment_params = $method->payment_params;
		$this->plugin_params = $method->payment_params;

		if(!empty($method->payment_params->virtual_coupon)) {
			if($this->payment_params->partialpayment == 0)
				return false;
			if($this->virtual_coupon_used)
				return false;

			$app = JFactory::getApplication();
			$no_virtual_coupon = (int)$app->getUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', 0);
			if(!empty($no_virtual_coupon)) {
				unset($order->additional['userpoints']);
				unset($order->additional['userpoints_points']);
				return false;
			}

			$userPoints = 0;
			if($this->checkRules($order, $userPoints) == false)
				return false;
			$check = $this->checkPoints($order);
			$this->points[$this->payment_params->points_mode] -= $check;

			$old_discount = 0;
			if(!empty($order->additional['userpoints']))
				$old_discount = $order->additional['userpoints']->price_value;
			$new_discount = -$check * $this->payment_params->value;

			if($old_discount != $new_discount) {
				if(!isset($order->additional['userpoints']))
					$order->additional['userpoints'] = new stdClass();
				$order->additional['userpoints']->price_value = $new_discount;
				$order->additional['userpoints']->price_value_with_tax = $new_discount;

				$pts = -$check;
				if($pts < 0) $pts = 0;
				if(!isset($order->additional['userpoints_points']))
					$order->additional['userpoints_points'] = new stdClass();
				$order->additional['userpoints_points']->value = $pts.' '.JText::_('USERPOINTS_POINTS');

				$diff = $new_discount - $old_discount;
				if(!empty($order->full_total->prices)) {
					foreach($order->full_total->prices as $k => $price) {
						$order->full_total->prices[$k]->price_value += $diff;
						if($order->full_total->prices[$k]->price_value < 0)
							$order->full_total->prices[$k]->price_value = 0;
						$order->full_total->prices[$k]->price_value_with_tax += $diff;
						if($order->full_total->prices[$k]->price_value_with_tax < 0)
							$order->full_total->prices[$k]->price_value_with_tax = 0;
					}
				}
			}

			$this->virtual_coupon_used = true;
			$this->virtual_coupon_value = $check;
			return false;
		}

		$userPoints = 0;
		if($this->checkRules($order, $userPoints) == false)
			return false;

		if(!$method->payment_params->virtual_coupon && !empty($order->coupon->discount_code)) {
			if(preg_match('#^POINTS_[a-zA-Z0-9]{30}$#', $order->coupon->discount_code))
				return false;
		}

		$currencyClass = hikashop_get('class.currency');
		if($this->main_currency != $this->currency_id)
			$method->payment_params->value = $currencyClass->convertUniquePrice($method->payment_params->value, $this->main_currency, $this->currency_id);

		if(isset($order->order_currency_id))
			$curr = $order->order_currency_id;
		else
			$curr = hikashop_getCurrency();

		$price = $currencyClass->format($this->pointsToCurrency($userPoints, $method, $order), $curr);

		$method->payment_description .= JText::sprintf('YOU_HAVE', $userPoints, $price);

		$fullOrderPoints = $this->finalPriceToPoints($order, $userPoints, $method);

		if($method->payment_params->partialpayment == 0 ) {
			if( $method->payment_params->allowshipping == 1 ) {
				$method->payment_description .= JText::sprintf('PAY_FULL_ORDER_POINTS', $fullOrderPoints);
			} else {
				$method->payment_description .= JText::sprintf('PAY_FULL_ORDER_NO_SHIPPING', $fullOrderPoints);
				$method->payment_description .= JText::sprintf('COUPON_GENERATE');
				$method->payment_description .= JText::sprintf('CAUTION_POINTS');
			}
		} else {
			$check = $this->checkPoints($order);
			if( $check >= $fullOrderPoints ) {
				$method->payment_description .= JText::sprintf('PAY_FULL_ORDER_POINTS', $fullOrderPoints);
			} else {
				$coupon = $check * $method->payment_params->value;
				$price = $currencyClass->format($coupon, $this->currency_id);
				$method->payment_description .= JText::sprintf('COUPON_GENERATE_PARTIAL', $price);
				$method->payment_description .= JText::sprintf('CAUTION_POINTS');
			}
		}
		return true;
	}

	function onAfterCartProductsLoad(&$cart) {
	}

	function onAfterCartShippingLoad(&$cart) {
		$app = JFactory::getApplication();
		$no_virtual_coupon = (int)$app->getUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', 0);
		if(!empty($no_virtual_coupon)) {
			unset($cart->additional['userpoints']);
			unset($cart->additional['userpoints_points']);
			return;
		}

		if(isset($cart->additional['userpoints']))
			return;

		$ret = $this->getCartUsedPoints($cart);
		if(!empty($ret)) {
			$pointsToLoose = $ret['points'];
			$coupon = $ret['value'];

			if(isset($cart->order_currency_id))
				$currency_id = $cart->order_currency_id;
			else
				$currency_id = hikashop_getCurrency();

			$userpoints = new stdClass();
			$userpoints->name = 'USERPOINTS_DISCOUNT';
			$userpoints->value = '';
			$userpoints->price_currency_id = $currency_id;
			$userpoints->price_value = -$coupon;
			$userpoints->price_value_with_tax = -$coupon;
			$cart->additional['userpoints'] = $userpoints;

			$userpoints_points = new stdClass();
			$userpoints_points->name = 'USERPOINTS_USE_POINTS';
			$userpoints_points->value = $pointsToLoose.' '.JText::_('USERPOINTS_POINTS');
			$userpoints_points->price_currency_id = 0;
			$userpoints_points->price_value = 0;
			$userpoints_points->price_value_with_tax = 0;

			$cart->additional['userpoints_points'] = $userpoints_points;
		}
	}

	function getCartUsedPoints(&$cart) {
		$check = 0;
		if(!empty($this->virtual_coupon_used))
			$check = $this->virtual_coupon_value;

		$ids = array();
		$currency = hikashop_getCurrency();
		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);

		$this->virtualpoints = $this->getVirtualPoints($cart, 'all');

		parent::listPlugins($this->name, $ids, false);
		foreach($ids as $id) {
			parent::pluginParams($id);
			$this->payment_params =& $this->plugin_params;

			if(hikashop_level(2) && !hikashop_isAllowed($this->plugin_data->payment_access))
				continue;
			if(!@$this->payment_params->virtual_coupon)
				continue;
			if($this->payment_params->partialpayment == 0)
				continue;

			$userPoints = 0;
			if(empty($check) && $this->checkRules($cart, $userPoints) == false)
				continue;

			if($this->main_currency != $currency)
				$this->payment_params->value = $currencyClass->convertUniquePrice($this->payment_params->value, $this->main_currency, $currency);

			if(empty($check))
				$check = $this->checkPoints($cart);
			if($check !== false && $check > 0) {
				if(isset($cart->order_currency_id))
					$currency_id = $cart->order_currency_id;
				else
					$currency_id = hikashop_getCurrency();

				$coupon = $check * $this->payment_params->value;
				$pointsToLoose = $check;
				$virtual_points = $this->getVirtualPoints($cart, $this->payment_params->points_mode);
				if(!empty($virtual_points)) {
					if($virtual_points <= $check)
						$pointsToLoose = $check - $virtual_points;
					else
						$pointsToLoose = 0;
				}
				return array(
					'points' => $pointsToLoose,
					'value' => $coupon,
					'mode' => $this->payment_params->points_mode
				);
			}
		}
		unset($this->payment_params);

		return null;
	}

	function onAfterOrderConfirm(&$order, &$methods,$method_id) {
		$this->removeCart = true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;

		if(empty($order->order_payment_params))
			$order->order_payment_params = new stdClass();
		if(empty($order->order_payment_params->userpoints))
			$order->order_payment_params->userpoints = new stdClass();
		if(empty($order->order_payment_params->userpoints->use_points))
			$order->order_payment_params->userpoints->use_points = 0;
		if(empty($order->order_payment_params->userpoints->earn_points))
			$order->order_payment_params->userpoints->earn_points = array();

		$earnPoints = $this->getPointsEarned($order, 'all');

		if(!empty($earnPoints)) {
			foreach($earnPoints as $mode => $pts) {
				if(empty($order->order_payment_params->userpoints->earn_points[$mode]))
					$order->order_payment_params->userpoints->earn_points[$mode] = 0;
				$order->order_payment_params->userpoints->earn_points[$mode] += $pts;
			}
		}

		if((empty($order->order_payment_method) || $order->order_payment_method != $this->name) && !empty($order->cart->additional)) {
			$ids = array();
			parent::listPlugins($this->name, $ids, false);
			foreach($ids as $id) {
				parent::pluginParams($id);
				if($this->payment_params->virtual_coupon) {
					$checkPoints = $points = $this->checkPoints($order);
					$usePts = -1;
					foreach($order->cart->additional as $additional) {
						if($additional->name != 'USERPOINTS_USE_POINTS')
							continue;
						$matches = array();
						if(preg_match('#-([0-9]+)#', $additional->value, $matches)) {
							$usePts = (int)$matches[1];
						} else {
							$usePts = substr($additional->value, 0, strpos($additional->value, ' '));
							$usePts = (int)trim(str_replace('-','',$usePts));
						}
						break;
					}

					if($checkPoints > $usePts) {
						$order->order_payment_params->userpoints->earn_points[$this->plugin_params->points_mode] += ($usePts - $checkPoints);
						$points = $usePts;
					}

					if($usePts > 0)
						$points = $usePts;
					if($points !== false && $points > 0) {
						$order->order_payment_params->userpoints->use_points += $points;
						$order->order_payment_params->userpoints->use_mode = $this->plugin_params->points_mode;
					}
					break;
				}
			}

			return true;
		}

		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!empty($order->cart->coupon->discount_code) && (preg_match('#^POINTS_[a-zA-Z0-9]{30}$#', $order->cart->coupon->discount_code) || preg_match('#^POINTS_([-a-zA-Z0-9]+)_[a-zA-Z0-9]{25}$#', $order->cart->coupon->discount_code))) {
			if(@$this->payment_params->partialpayment === 0 && $order->cart->full_total->prices[0]->price_value_without_discount != $order->cart->coupon->discount_value) {
				$do = false;
				echo JText::_('ERROR_POINTS');
				return true;
			}
		}

		$check = $this->checkPoints($order);
		$userPoints = $this->getUserPoints(null, $this->payment_params->points_mode);
		$fullOrderPoints = $this->finalPriceToPoints($order, $userPoints);

		if(($this->payment_params->partialpayment == 1 || $this->payment_params->allowshipping == 0) && ($check !== false && $check > 0) && ($check < $fullOrderPoints) && $userPoints) {
			$discountClass = hikashop_get('class.discount');
			$cartClass = hikashop_get('class.cart');
			$config =& hikashop_config();
	 		$currency = hikashop_getCurrency();

			$app = JFactory::getApplication();
			$newCoupon = new stdClass();
			$newCoupon->discount_type='coupon';
			$newCoupon->discount_currency_id = $currency;

			$newCoupon->discount_flat_amount = $check * $this->payment_params->value;
			$newCoupon->discount_quota = 1;
			jimport('joomla.user.helper');
			if(!empty($this->payment_params->givebackpoints)) {
				$newCoupon->discount_code = 'POINTS_' . $this->payment_params->points_mode.'_';
				$newCoupon->discount_code .= JUserHelper::genRandomPassword(25);
			} else {
				$newCoupon->discount_code = 'POINTS_';
				$newCoupon->discount_code .= JUserHelper::genRandomPassword(30);
			}
			$newCoupon->discount_published = 1;
			$discountClass->save($newCoupon);
			$coupon = $newCoupon;
			if(!empty($coupon)){
				$cartClass->update($coupon->discount_code, 1, 0, 'coupon');
				$cartClass->loadCart(0,true);
			}
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', null);
			$do = false;
			if(empty($order->customer)) {
				$userClass = hikashop_get('class.user');
				$order->customer = $userClass->get($order->order_user_id);
			}
			$this->addPoints(-$check, $order, JText::_('HIKASHOP_COUPON').' '.$coupon->discount_code);
		}
	}

	function onAfterOrderCreate(&$order, &$send_email) {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return true;

		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;

		if(empty($order->order_payment_method) || $order->order_payment_method != $this->name) {
			return true;
		}

		$this->loadOrderData($order);
		$this->loadPaymentParams($order);
		if(empty($this->payment_params)) {
			$do = false;
			return true;
		}

		$points = $this->checkpoints($order);
		if($points !== false && $points > 0) {
			$this->addPoints(-$points, $order);

			$orderClass = hikashop_get('class.order');
			$config =& hikashop_config();
			$orderObj = new stdClass();
			$orderObj->order_status = $config->get('order_confirmed_status');
			$orderObj->order_id = $order->order_id;
			$orderClass->save($orderObj);
		} else {
			return false;
		}
	}

	function addPoints($points, $order, $data = null) {
		$plugin = hikashop_import('hikashop', 'userpoints');
		return $plugin->addPoints($points, $order, $data, $this->plugin_params->points_mode);
	}

	function getUserPoints($cms_user_id = null, $mode = 'all') {
		$plugin = hikashop_import('hikashop', 'userpoints');
		return $plugin->getUserPoints($cms_user_id, $mode);
	}

	function getPointsEarned($order, $mode = 'all') {
		$plugin = hikashop_import('hikashop', 'userpoints');
		$points = 0;
		if($mode == 'all')
			$points = array();
		$plugin->onGetUserPointsEarned($order, $points, $mode);
		return $points;
	}

	function getVirtualPoints($order, $mode = 'all') {
		$plugin = hikashop_import('hikashop', 'userpoints');
		$points = 0;
		if($mode == 'all')
			$points = array();
		$plugin->onGetUserPointsEarned($order, $points, $mode, true);
		return $points;
	}

	function giveAndGiveBack(&$order) {
		$plugin = hikashop_import('hikashop', 'userpoints');
		return $plugin->giveAndGiveBack($order);
	}

	function checkRules($order, &$userPoints) {
		if(empty($this->plugin_params))
			return false;

		if(isset($this->points[$this->plugin_params->points_mode]))
			$userPoints = $this->points[$this->plugin_params->points_mode];
		else
			$userPoints = $this->getUserPoints(null, $this->plugin_params->points_mode);

		$check = $this->checkPoints($order, true);

		$virtualPoints = 0;
		if($userPoints == 0 && !empty($this->virtualpoints) && !empty($this->virtualpoints[$this->plugin_params->points_mode])) {
			$virtualPoints = $this->virtualpoints[$this->plugin_params->points_mode];
		}

		if($check === false || $check == 0 || ($userPoints == 0 && $virtualPoints == 0))
			return false;

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
		if(!isset($this->currency_id))
			$this->currency_id = hikashop_getCurrency();
		if($this->main_currency != $this->currency_id)
			$this->plugin_params->minimumcost = $currencyClass->convertUniquePrice($this->plugin_params->minimumcost, $this->main_currency, $this->currency_id);

		if($this->plugin_params->minimumcost > $total)
			return false;

		if($this->plugin_params->allowshipping == 1)
			$calculatedPrice = $total;
		else
			$calculatedPrice = $total_without_shipping;

		$neededpoints = ($this->plugin_params->percent / 100) * $calculatedPrice;
		$useablePoints = $this->pointsToCurrency($userPoints);
		if($useablePoints < $neededpoints)
			return false;

		if($this->plugin_params->partialpayment == 0)
			$this->plugin_params->percentmax = 100;

		if($this->plugin_params->percentmax <= 0)
			return false;

		return true;
	}

	function pointsToCurrency($userPoints) {
		if(empty($this->plugin_params))
			return false;
		$coupon = $userPoints * hikashop_toFloat($this->plugin_params->value);
		return $coupon;
	}

	function checkPoints(&$order, $showWarning = false) {
		static $displayed = false;

		if(empty($this->plugin_params))
			return false;

		if(isset($this->points[$this->plugin_params->points_mode]))
			$userPoints = $this->points[$this->plugin_params->points_mode];
		else
			$userPoints = $this->getUserPoints(null, $this->plugin_params->points_mode);
		if(empty($userPoints))
			$userPoints = 0;
		if(isset($this->virtualpoints[$this->plugin_params->points_mode]))
			$userPoints += $this->virtualpoints[$this->plugin_params->points_mode];
		else
			$userPoints += $this->getVirtualPoints($order, $this->plugin_params->points_mode);

		$fullOrderPoints = $this->finalPriceToPoints($order, $userPoints);
		$points = $fullOrderPoints;

		if($this->plugin_params->partialpayment == 0) {
			if((int)$userPoints >= $fullOrderPoints)
				return $fullOrderPoints;
			return 0;
		}

		if(!empty($this->plugin_params->percentmax) && ((int)$this->plugin_params->percentmax > 0) && ((int)$this->plugin_params->percentmax <= 100))
			$points = $points * ( (int)$this->plugin_params->percentmax / 100 );

		if((int)$userPoints < $points)
			$points = (int)$userPoints;

		if(isset($this->plugin_params->grouppoints) && ((int)$this->plugin_params->grouppoints > 1)) {
			if($showWarning && !$displayed) {
				$this->plugin_params->grouppoints = (int)$this->plugin_params->grouppoints;
				if(isset($this->plugin_params->grouppoints_warning_lvl) && ((int)$this->plugin_params->grouppoints_warning_lvl >= 1) ) {
					if($points < $this->plugin_params->grouppoints && ($points + (int)$this->plugin_params->grouppoints_warning_lvl) >= $this->plugin_params->grouppoints) {
						$app = JFactory::getApplication();
						$currencyClass = hikashop_get('class.currency');

						if(isset($cart->order_currency_id)) {
							$currency_id = $cart->order_currency_id;
						} else {
							$currency_id = hikashop_getCurrency();
						}
						$possible_coupon = $this->plugin_params->grouppoints * $this->plugin_params->value;
						$price = $currencyClass->format($possible_coupon, $currency_id);

						$app->enqueueMessage(JText::sprintf('MISSING_X_POINTS_TO_REDUCTION', $this->plugin_params->grouppoints - $points, $price));
						$displayed = true;
					}
				}
			}
			$points -= ($points % $this->plugin_params->grouppoints);
		}

		if(isset($this->plugin_params->maxpoints) && ((int)$this->plugin_params->maxpoints > 0) && $points > (int)$this->plugin_params->maxpoints) {
			$points = (int)$this->plugin_params->maxpoints;

			if(isset($this->plugin_params->grouppoints) && ((int)$this->plugin_params->grouppoints > 1) ) {
				$points -= ($points % (int)$this->plugin_params->grouppoints);
			}
		}

		if($points < (int)$userPoints)
			return (int)$points;
		return (int)$userPoints;
	}

	function finalPriceToPoints(&$order, &$userPoints) {
		if(empty($this->plugin_params))
			return 0;
		if(empty($this->plugin_params->value) || bccomp($this->plugin_params->value, 0, 5) < 1)
			return 0;
		if(isset($order->order_subtotal) && isset($order->order_shipping_price)) {
			if($this->plugin_params->allowshipping == 1) {
				$final_price = @$order->order_subtotal + $order->order_shipping_price;
			} else {
				$final_price = @$order->order_subtotal;
			}
		} else if(empty($order->cart->full_total->prices[0]->price_value_with_tax)) {
			if($this->plugin_params->allowshipping == 1) {
				$final_price = @$order->full_total->prices[0]->price_value_with_tax;
			}else{
				$final_price = @$order->total->prices[0]->price_value_with_tax;
			}
		} else {
			if($this->plugin_params->allowshipping == 1) {
				$final_price = @$order->cart->full_total->prices[0]->price_value_with_tax;
			} else {
				$final_price = @$order->cart->total->prices[0]->price_value_with_tax;
			}
		}

		$pointsDecrease = $final_price * ( 1 / $this->plugin_params->value );

		$rounding = 0;
		if(!empty($this->plugin_params->rounding))
			$rounding = $this->plugin_params->rounding;

		return round($pointsDecrease, $rounding);
	}

	function _makeLevel(&$productData, $level, &$idparentcats) {
		foreach($productData[$level] as $cat) {
			if(!empty($idparentcats[$cat]->category_parent_id) && !empty($idparentcats[$idparentcats[$cat]->category_parent_id])) {
				$productData[$level+1][] = $idparentcats[$cat]->category_parent_id;
			}
		}
		if(!empty($productData[$level+1])) {
			$this->_makeLevel($productData,$level+1,$idparentcats);
		}
	}

	function loadFullOrder($order_id) {
		if(empty($this->fullOrder) || $this->fullOrder->order_id != $order_id) {
			$classOrder =& hikashop_get('class.order');
			$this->fullOrder = $classOrder->loadFullOrder($order_id, false, false);
			if(empty($this->fullOrder->customer)){
				if(empty($userClass))
					$userClass = hikashop_get('class.user');
				$this->fullOrder->customer = $userClass->get($this->fullOrder->order_user_id);
			}
		}
	}

	function getAUP($warning = false) {
		static $aup = null;
		if(!isset($aup)) {
			$aup = false;
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
			if(file_exists($api_AUP)) {
				require_once ($api_AUP);
				if(class_exists('AlphaUserPointsHelper'))
					$aup = true;
			}
			if(!$aup && $warning) {
				$app = JFactory::getApplication();
				if($app->isAdmin())
					$app->enqueueMessage('The HikaShop UserPoints plugin requires the component AlphaUserPoints to be installed. If you want to use it, please install the component or use another mode.');
			}
		}
		return $aup;
	}

	function onPaymentConfiguration(&$element) {
		parent::onPaymentConfiguration($element);

		$this->modes = array();
		if($this->getAUP())
			$this->modes[] = JHTML::_('select.option', 'aup', 'ALPHA_USER_POINTS');
		$this->modes[] = JHTML::_('select.option', 'hk', 'HIKASHOP_USER_POINTS');

		$this->address = hikashop_get('type.address');
		if(!empty($element->payment_params->categories))
			$this->categories = unserialize($element->payment_params->categories);

		$ids = array();
		if(!empty($this->categories)) {
			foreach($this->categories as $cat) {
				$ids[] = $cat->category_id;
			}
			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$ids).')');
			$cats = $db->loadObjectList('category_id');
			foreach($this->categories as $k => $cat) {
				if(!empty($cats[$cat->category_id])) {
					$this->categories[$k]->category_name = $cats[$cat->category_id]->category_name;
				} else {
					$this->categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
				}
			}
		}

		$acl = JFactory::getACL();
		if(!HIKASHOP_J16) {
			$this->groups = $acl->get_group_children_tree(null, 'USERS', false);
		} else {
			$db = JFactory::getDBO();
			$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
			$this->groups = $db->loadObjectList('id');
			foreach($this->groups as $id => $group) {
				if(isset($this->groups[$group->parent_id])) {
					$this->groups[$id]->level = intval(@$this->groups[$group->parent_id]->level) + 1;
					$this->groups[$id]->text = str_repeat('- - ',$this->groups[$id]->level).$this->groups[$id]->text;
				}
			}
		}

		if(!empty($element->payment_params->groups)) {
			$element->payment_params->groups = unserialize($element->payment_params->groups);
			foreach($this->groups as $id => $group) {
				$this->groups[$id]->points = (int)@$element->payment_params->groups[$group->value];
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

	function onPaymentConfigurationSave(&$element) {
		$categories = JRequest::getVar('category', array(), '', 'array');
		JArrayHelper::toInteger($categories);
		$cats = array();
		if(!empty($categories)) {
			$category_points = JRequest::getVar('category_points', array(), '', 'array');
			foreach($categories as $id => $category) {
				if((int)@$category_points[$id] != 0) {
					$obj = new stdClass();
					$obj->category_id = $category;
					$obj->category_points = (int)@$category_points[$id];
					$cats[] = $obj;
				}
			}
		}

		$element->payment_params->categories = serialize($cats);
		$groups = JRequest::getVar('groups', array(), '', 'array');
		JArrayHelper::toInteger($groups);
		$element->payment_params->groups = serialize($groups);

		if($element->payment_params->virtual_coupon && $element->payment_params->partialpayment == 0) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('The Virtual coupon mode cannot be used for partial payment with points. Either deactivate the Virtual coupon mode or the partial payment. Otherwise, you won\'t see any payment with points on the checkout');
		}

		if($element->payment_params->points_mode == 'aup') {
			if($this->getAUP(true)) {
				$db = JFactory::getDBO();
				$query = 'SELECT id FROM '.hikashop_table('alpha_userpoints_rules',false).' WHERE rule_name="Order_validation"';
				$db->setQuery($query);
				$exist=$db->loadResult();
				if(empty($exist)){
					$query='INSERT INTO '.hikashop_table('alpha_userpoints_rules',false).' (rule_name, rule_description, rule_plugin, plugin_function, access, points, published, system, autoapproved)' .
							'VALUES ("Order_validation", "Give points to customer when the order is validate", "com_hikashop", "plgaup_orderValidation", 1, 0, 1, 0,1)';
					$db->setQuery($query);
					$db->query();
				}
			} else {
				$element->payment_params->points_mode = 'hk';
			}
		}
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'Pay with Points';
		$element->payment_description='You can pay with points using this payment method';
		$element->payment_images = '';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->valid_order_status = 'confirmed,shipped';
		$element->payment_params->percentmax = 100;
		$element->payment_params->virtual_coupon = true;
	}
}
