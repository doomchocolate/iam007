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
class plgHikashopUserpoints extends hikashopPlugin {
	var $multiple = true;
	var $name = 'userpoints';

	function onPluginConfiguration(&$element) {
		$this->pluginConfiguration($element);

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currency = hikashop_get('class.currency');
		$this->currency = $currency->get($this->main_currency);

		$this->modes = array();
		if($this->getAUP())
			$this->modes[] = JHTML::_('select.option', 'aup', JText::_('ALPHA_USER_POINTS'));
		$this->modes[] = JHTML::_('select.option', 'hk', JText::_('HIKASHOP_USER_POINTS'));

		$acl = JFactory::getACL();
		if(!HIKASHOP_J16) {
			$this->groups = $acl->get_group_children_tree(null, 'USERS', false);
		} else {
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
	}

	function onAfterOrderCreate(&$order, &$send_email) {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return true;
		if(!isset($order->order_status))
			return true;

		if( !empty($order->order_type) && $order->order_type != 'sale' )
			return true;
		if( !empty($order->userpoints_process->updated) )
			return true;
		$this->giveAndGiveBack($order);
		unset($this->fullOrder);
		return true;
	}

	function onAfterOrderUpdate(&$order, &$send_email) {
		if(!isset($order->order_status))
			return true;

		if( (!empty($order->order_type) && $order->order_type != 'sale') || $order->old->order_type != 'sale' )
			return true;
		if( !empty($order->userpoints_process->updated) )
			return true;
		$this->giveAndGiveBack($order);
		unset($this->fullOrder);
		return true;
	}

	function onGetUserPoints($cms_user_id = null, $mode = 'all') {
		$points = $this->getUserPoints($cms_user_id, $mode);
	}

	function onGetUserPointsEarned(&$order, &$points, $mode = 'all', $onlyVirtual = false) {
		$ids = array();
		$user_id = false;
		if(!empty($order->old->order_user_id))
			$user_id = (int)$order->old->order_user_id;
		if(!empty($order->order_user_id))
			$user_id = (int)$order->order_user_id;
		parent::listPlugins($this->name, $ids, false, $user_id);
		foreach($ids as $id) {
			parent::pluginParams($id);
			if(
				(!$onlyVirtual || !empty($this->plugin_params->virtualpoints)) &&
				($mode == 'all' || @$this->plugin_params->points_mode == $mode)
			) {
				$pts = $this->getPointsEarned($order);

				if(empty($pts))
					continue;

				$myMode = $this->plugin_params->points_mode;
				if($mode == 'all') {
					if(empty($points[$myMode]))
						$points[$myMode] = 0;
					$points[$myMode] += $pts;
				} else
					$points += $pts;
			}
		}
	}

	function onGetVirtualUserPoints(&$order, &$points, $mode = 'all') {
		$this->onGetUserPointsEarned($order, $points, $mode, true);
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

	function getDataReference(&$order) {
		if(!empty($order->order_number)) {
			$number = $order->order_number;
		} elseif(!empty($order->old->order_number)) {
			$number = $order->old->order_number;
		} elseif(!empty($order->order_id)) {
			$class = hikashop_get('class.order');
			$data = $class->get($order->order_id);
			$number = $data->order_number;
		} else {
			return '';
		}
		return  JText::_('ORDER_NUMBER').' : '.$number;
	}

	function getUserPoints($cms_user_id = null, $mode = 'all') {
		$ret = 0;
		if($mode == 'all') {
			$ret = array();
		}

		if($mode == 'aup' || ($mode == 'all' && $this->getAUP())) {
			if($mode == 'aup' && !$this->getAUP(true))
				return false;
			if($cms_user_id === null) {
				$user = Jfactory::getUser();
				$cms_user_id = $user->id;
			}
			$userInfo = AlphaUserPointsHelper::getUserInfo('', $cms_user_id);
			if($mode == 'aup')
				return (int)$userInfo->points;
			$ret['aup'] = (int)$userInfo->points;
		}

		if($cms_user_id === null) {
			$user = hikashop_loadUser(true);
		} else {
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($cms_user_id, 'cms');
		}

		if($mode == 'hk') {
			if(isset($user->user_points))
				return (int)$user->user_points;
			return false;
		}
		if(isset($user->user_points)) {
			if($mode == 'all')
				$ret['hk'] = (int)$user->user_points;
			else
				$ret = (int)$user->user_points;
		}
		return $ret;
	}

	function addPoints($points, $order, $data = null, $forceMode = null) {
		if(empty($this->plugin_params) && $forceMode === null)
			return false;

		if($points === 0)
			return true;

		$points_mode = @$this->plugin_params->points_mode;
		if($forceMode !== null)
			$points_mode = $forceMode;

		if($points_mode == 'aup') {
			if(empty($order->customer)) {
				$userClass = hikashop_get('class.user');
				$order->customer = $userClass->get($order->order_user_id);
			}
			if($this->getAUP(true)) {
				if($data === null)
					$data = $this->getDataReference($order);
				$aupid = AlphaUserPointsHelper::getAnyUserReferreID($order->customer->user_cms_id);
				AlphaUserPointsHelper::newpoints('plgaup_orderValidation', $aupid, '', $data, $points);
				return true;
			}
			return false;
		}

		$ret = true;
		$userClass = hikashop_get('class.user');
		$oldUser = $userClass->get($order->order_user_id);
		if(!isset($oldUser->user_points))
			return false;
		if(empty($oldUser->user_points))
			$oldUser->user_points = 0;

		$user = new stdClass();
		$user->user_id = $oldUser->user_id;
		$user->user_points = (int)$oldUser->user_points + $points;
		if($user->user_points < 0) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('HIKAPOINTS_USE_X_POINTS', -$points), 'error');
			$points = -$oldUser->user_points;
			$user->user_points = 0;
			$ret = false;
		}
		$userClass->save($user);

		$app = JFactory::getApplication();
		if(!$app->isAdmin() && $points !== 0) {
			$user = hikashop_loadUser(true);
			if($user->user_id == $order->order_user_id) {
				if($points < 0)
					$app->enqueueMessage(JText::sprintf('HIKAPOINTS_USE_X_POINTS', -$points));
				else
					$app->enqueueMessage(JText::sprintf('HIKAPOINTS_EARN_X_POINTS', $points));
			}
		}

		return $ret;
	}

	function getPointsEarned($order) {
		if(empty($this->plugin_params))
			return 0;
		if(!empty($this->plugin_params->notgivewhenuse) && !empty($order->additional['userpoints']))
			return 0;

		$points = 0;
		$db = JFactory::getDBO();

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
			if(!empty($this->plugin_params->value))
				$this->plugin_params->value = $currencyClass->convertUniquePrice($this->plugin_params->value, $this->main_currency, $order_currency_id);
			else
				$this->plugin_params->value = 0;

			if(!empty($this->plugin_params->minimumcost))
				$this->plugin_params->minimumcost = $currencyClass->convertUniquePrice($this->plugin_params->minimumcost, $this->main_currency, $order_currency_id);
			else
				$this->plugin_params->minimumcost = 0;

			if(!empty($this->plugin_params->currency_rate))
				$this->plugin_params->currency_rate = $currencyClass->convertUniquePrice($this->plugin_params->currency_rate, $this->main_currency, $order_currency_id);
			else
				$this->plugin_params->currency_rate = 0;
		}

		$categories = array();
		if(!empty($this->plugin_params->categories))
			$categories = unserialize($this->plugin_params->categories);
		if(!empty($categories)) {
			if(!empty($order->cart->products)) {
				$products =& $order->cart->products;
			} else {
				$products =& $order->products;
			}
			foreach($products as $product) {
				$ids[$product->product_id] = $product->product_id;
			}
			$queryP = 'SELECT product_parent_id FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$ids).')';
			$db->setQuery($queryP);
			$pids = $db->loadObjectList();
			if(!empty($pids)) {
				foreach($pids as $pid) {
					$ids[$pid->product_parent_id] = $pid->product_parent_id;
				}
			}

			$query = 'SELECT * FROM '.hikashop_table('product_category').' prod LEFT JOIN '.hikashop_table('category').' cat ON prod.category_id=cat.category_id ' .
					'WHERE prod.product_id IN ('.implode(',',$ids).')';
			$db->setQuery($query);
			$idcats = $db->loadObjectList();
			if(!empty($idcats)) {
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND ';
				$conditions = array();
				foreach($idcats as $idcat) {
					$conditions[] = '(category_left <= '.$idcat->category_left.' AND category_right >= '.$idcat->category_right.')';
					$idfinalcats[$idcat->product_id][0][] = $idcat->category_id;
				}
				$query .= implode(' OR ',$conditions);
			}
			$db->setQuery($query);
			$idparentcats = $db->loadObjectList('category_id');
			foreach($idcats as $id) {
				$this->_makeLevel($idfinalcats[$id->product_id], 0, $idparentcats);
			}

			$maxPoints = 0;
			$tempCatId = null;
			foreach($idfinalcats as $product) {
				foreach($product as $levels) {
					foreach($categories as $category) {
						foreach($levels as $categoryid) {
							if($categoryid == $category->category_id && $category->category_points > $maxPoints) {
								$maxPoints = $category->category_points;
								$tempCatId = $category->category_id;
							}
						}
						$points += $maxPoints;
						if(!empty($tempCatId)) {
							foreach ($categories as $category) {
								if($category->category_id == $tempCatId && $this->plugin_params->limitecategory == 1) {
									$category->category_points = 0;
								}
							}
							$maxPoints = 0;
							$tempCatId = 0;
							break 2;
						}
						$maxPoints = 0;
					}
				}
			}
		}

		if(hikashop_level(2)) {
			$groups = array();
			if(!empty($this->plugin_params->groups))
				$groups = unserialize($this->plugin_params->groups);
			if(!HIKASHOP_J16) {
				$my = JFactory::getUser();
				$gid = $my->gid;
				if(empty($gid))
					$userGroups = array(29); // default group in J1.5
				else
					$userGroups = array($gid);
			} else {
				if(!empty($order->customer->user_cms_id)) {
					$user_id = $order->customer->user_cms_id;
				} else {
					$my = JFactory::getUser();
					$user_id = $my->id;
				}
				jimport('joomla.access.access');
				$userGroups = JAccess::getGroupsByUser($user_id, true);//$my->authorisedLevels();
			}

			foreach($userGroups as $groupid) {
				if(!empty($groups[$groupid]))
					$points += $groups[$groupid];
				if(!empty($groups[$groupid]) && $this->plugin_params->limitegroup == 1)
					break;
			}
		}

		$cart = null;
		if(isset($order->cart)) {
			$cart =& $order->cart;
		} else {
			$cart =& $order;
		}
		if(!empty($cart->full_total->prices[0]->price_value_with_tax)) {
			if($this->plugin_params->shippingpoints == 1) {
				$calculatedPrice = $cart->full_total->prices[0]->price_value_with_tax - @$cart->coupon->discount_value;
			} else {
				$calculatedPrice = $cart->total->prices[0]->price_value_with_tax - @$cart->coupon->discount_value;
			}
		} else {
			if($this->plugin_params->shippingpoints == 1) {
				$calculatedPrice = @$order->order_full_price;
			} else {
				$calculatedPrice = @$order->order_full_price - @$order->order_shipping_price;
			}
		}
		unset($cart);

		if($this->plugin_params->currency_rate != 0) {
			$points += $calculatedPrice / $this->plugin_params->currency_rate;
		}

		$products = array();
		if(!empty($order->cart->products)) {
			$products = &$order->cart->products;
		} elseif(!empty($order->products)) {
			$products = &$order->products;
		}

		if($this->plugin_params->limittype == 1) {
			foreach($products as $product) {
				$points += $this->plugin_params->productpoints;
			}
		} elseif($this->plugin_params->limittype == 0) {
			foreach($products as $product) {
				if(isset($product->order_product_quantity)) {
					$points += $this->plugin_params->productpoints * $product->order_product_quantity;
				} else {
					$points += $this->plugin_params->productpoints * $product->cart_product_quantity;
				}
			}
		}
		return round($points, 0);
	}

	function loadFullOrder($order_id) {
		if(empty($this->fullOrder) || $this->fullOrder->order_id != $order_id) {
			$classOrder = hikashop_get('class.order');
			$this->fullOrder = $classOrder->loadFullOrder($order_id, false, false);
			if(empty($this->fullOrder->customer)){
				if(empty($userClass))
					$userClass = hikashop_get('class.user');
				$this->fullOrder->customer = $userClass->get($this->fullOrder->order_user_id);
			}
		}
	}

	function giveAndGiveBack(&$order) {

		$this->config = hikashop_config();
		$confirmed = null;
		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'userpoints');
			$confirmed = explode(',', @$plugin->params['order_status']);
		} else if($this->params->get('order_status', '') != '') {
			$confirmed = explode(',', $this->params->get('order_status', ''));
		}
		if(empty($confirmed))
			$confirmed = explode(',', $this->config->get('invoice_order_statuses'));
		$created = $this->config->get('order_created_status');

		$points = array();

		$this->loadFullOrder($order->order_id);


		$creation = empty($order->old->order_status);
		$changed = !empty($order->old->order_status) && !empty($order->order_status) && $order->old->order_status != $order->order_status;
		$old_confirmed = !empty($order->old->order_status) && in_array($order->old->order_status, $confirmed);
		$old_created = !empty($order->old->order_status) && ($order->old->order_status == $created);
		$new_confirmed = !empty($order->order_status) && in_array($order->order_status, $confirmed);
		$new_created = !empty($order->order_status) && ($order->order_status == $created);

		if($changed && $old_confirmed && !$new_confirmed && !empty($this->fullOrder->order_payment_params->userpoints->earn_points)) {
			foreach($this->fullOrder->order_payment_params->userpoints->earn_points as $mode => $p) {
				if(empty($points[$mode]))
					$points[$mode] = 0;
				$points[$mode] -= $p;
			}
		}

		if(($creation || ($changed && !$old_confirmed)) && $new_confirmed && !empty($this->fullOrder->order_payment_params->userpoints->earn_points)) {
			foreach($this->fullOrder->order_payment_params->userpoints->earn_points as $mode => $p) {
				if(empty($points[$mode]))
					$points[$mode] = 0;
				$points[$mode] += $p;
			}
		}

		if(($creation || ($changed && !$old_confirmed && !$old_created)) && ($new_confirmed || $new_created)) {
			if(!empty($this->fullOrder->order_payment_params->userpoints->use_points)) {
				$m = $this->fullOrder->order_payment_params->userpoints->use_mode;
				if(empty($points[ $m ]))
					$points[ $m ] = 0;
				$points[ $m ] -= $this->fullOrder->order_payment_params->userpoints->use_points;
			}

			if(!$creation && $this->fullOrder->order_payment_method == 'userpoints') {
				if($this->pluginParams($this->fullOrder->order_payment_id) && $this->plugin_params->givebackpoints == 1 && !empty($this->plugin_params->value)) {
					$p = round($this->fullOrder->order_full_price / $this->plugin_params->value, 0);
					$m = $this->plugin_params->points_mode;
					if(empty($points[ $m ]))
						$points[ $m ] = 0;
					$points[ $m ] -= $p;
				}
			}

			if(!$creation && !empty($this->fullOrder->order_discount_code)) {

				$matches = array();
				if(preg_match('#^POINTS_([-a-zA-Z0-9])+_[a-zA-Z0-9]{25}$#', $this->fullOrder->order_discount_code, $matches) && !empty($this->plugin_params->value)) {
					$p = -round($this->fullOrder->order_discount_price / $this->plugin_params->value, 0);
					$this->addPoints($p, $this->fullOrder, $matches[1]);
				}
			}
		}

		if($changed && ($old_confirmed || $old_created) && !$new_confirmed && !$new_created) {
			if(!empty($this->fullOrder->order_payment_params->userpoints->use_points)) {
				$m = $this->fullOrder->order_payment_params->userpoints->use_mode;
				if(empty($points[ $m ]))
					$points[ $m ] = 0;
				$points[ $m ] += $this->fullOrder->order_payment_params->userpoints->use_points;
			}

			if($this->fullOrder->order_payment_method == 'userpoints') {
				if($this->pluginParams($this->fullOrder->order_payment_id) && $this->plugin_params->givebackpoints == 1 && !empty($this->plugin_params->value)) {
					$p = round($this->fullOrder->order_full_price / $this->plugin_params->value, 0);
					$m = $this->plugin_params->points_mode;
					if(empty($points[ $m ]))
						$points[ $m ] = 0;
					$points[ $m ] += $p;
				}
			}

			if(!empty($this->fullOrder->order_discount_code)) {

				$matches = array();
				if(preg_match('#^POINTS_([-a-zA-Z0-9])+_[a-zA-Z0-9]{25}$#', $this->fullOrder->order_discount_code, $matches) && !empty($this->plugin_params->value)) {
					$p = round($this->fullOrder->order_discount_price / $this->plugin_params->value, 0);
					$this->addPoints($p, $this->fullOrder, $matches[1]);
				}
			}
		}

		if(!empty($points)) {
			foreach($points as $mode => $p) {
				if(!empty($p))
					$this->addPoints($p, $this->fullOrder, null, $mode);
			}
		}
	}

	function _readOptions() {
		if(!empty($this->plugin_options))
			return;

		$this->plugin_options = array();

		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'userpoints');

			$this->plugin_options['checkout_step'] = true;
			if(isset($plugin->params['checkout_step']))
				$this->plugin_options['checkout_step'] = (int)$plugin->params['checkout_step'];

			$this->plugin_options['hide_when_no_points'] = true;
			if(isset($plugin->params['hide_when_no_points']))
				$this->plugin_options['hide_when_no_points'] = (int)$plugin->params['hide_when_no_points'];

			$this->plugin_options['ask_no_coupon'] = true;
			if(isset($plugin->params['ask_no_coupon']))
				$this->plugin_options['ask_no_coupon'] = (int)$plugin->params['ask_no_coupon'];

			$this->plugin_options['show_points'] = 'hk';
			if(isset($plugin->params['show_points']))
				$this->plugin_options['show_points'] = $plugin->params['hide_when_no_points'];

		} else {
			$this->plugin_options['checkout_step'] = $this->params->get('checkout_step', '1');
			$this->plugin_options['hide_when_no_points'] = $this->params->get('hide_when_no_points', '1');
			$this->plugin_options['ask_no_coupon'] = $this->params->get('ask_no_coupon', '1');
			$this->plugin_options['show_points'] = $this->params->get('show_points', 'hk');
		}

		if(!in_array($this->plugin_options['show_points'], array('hk','aup','*')))
			$this->plugin_options['show_points'] = 'hk';
	}

	function onCheckoutStepList(&$list) {
		$this->_readOptions();
		if(!empty($this->plugin_options['checkout_step']))
			$list['plg.shop.userpoints'] = JText::_('HIKASHOP_USER_POINTS');
	}

	function onCheckoutStepDisplay($layoutName, &$html, &$view) {
		if($layoutName == 'plg.shop.userpoints') {
			$this->_readOptions();
			if(empty($this->plugin_options['checkout_step']))
				return;

			switch($this->plugin_options['show_points']) {
				case 'aup':
					$points = $this->getUserPoints(null, 'aup');
					break;
				case 'hk':
				default:
					$points = $this->getUserPoints(null, 'hk');
					break;
			}

			if($points === false)
				return;

			if(!empty($this->plugin_options['hide_when_no_points']) && empty($points))
				return;

			$app = JFactory::getApplication();
			$currencyClass = hikashop_get('class.currency');
			$consume = null;
			$discount = '';
			$use_coupon = 1 - (int)$app->getUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', 0);

			$paymentUserPoints = hikashop_import('hikashoppayment', 'userpoints');
			if(!empty($paymentUserPoints)) {
				$cart = $view->initCart();
				$consume = $paymentUserPoints->getCartUsedPoints($cart);

				if(!empty($consume) && $consume['mode'] != $this->plugin_options['show_points']) {
					$consume = null;
				}

				if(!empty($consume)) {
					if(isset($cart->order_currency_id))
						$currency_id = $cart->order_currency_id;
					else
						$currency_id = hikashop_getCurrency();

					$discount = $currencyClass->format($consume['value'], $currency_id);
				}
			}

			$app = JFactory::getApplication();
			$path = JPATH_THEMES.DS.$app->getTemplate().DS.'plg_hikashop_userpoints'.DS.'checkout.php';
			if(!file_exists($path)) {
				if(version_compare(JVERSION,'1.6','<'))
					$path = JPATH_PLUGINS.DS.'hikashop'.DS.'userpoints_checkout.php';
				else
					$path = JPATH_PLUGINS.DS.'hikashop'.DS.'userpoints'.DS.'userpoints_checkout.php';
			}
			if(!file_exists($path))
				return false;
			require($path);
		}
	}

	function onBeforeCheckoutStep($controllerName, &$go_back, $original_go_back, &$controller) {
	}

	function onAfterCheckoutStep($controllerName, &$go_back, $original_go_back, &$controller) {
		if($controllerName == 'plg.shop.userpoints') {
			$app = JFactory::getApplication();
			$this->_readOptions();
			if(empty($this->plugin_options['checkout_step'])) {
				$app->setUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', 0);
				return;
			}

			if(!empty($this->plugin_options['ask_no_coupon'])) {
				$no_virtual_coupon = (int)$app->getUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', 0);

				$use_coupon_opt = JRequest::getString('userpoints_use_coupon', '');
				if($use_coupon_opt !== '') {
					$no_coupon =  1 - (int)$use_coupon_opt;
					if($no_coupon != $no_virtual_coupon) {
						$go_back = true;
						$app->setUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', $no_coupon);
					}
				}
			} else {
				$app->setUserState(HIKASHOP_COMPONENT.'.userpoints_no_virtual_coupon', 0);
			}
		}
	}
}
