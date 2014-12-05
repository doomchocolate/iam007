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
class CartController extends hikashopController{
	var $type='cart';
	var $pkey = 'cart_id';
	var $table = 'cart';
	var $orderingMap ='cart_modified';

	function store($new = false){

		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');
		$data = JRequest::getVar('data','0');

		$cart_id = hikashop_getCID('cart_id');
		$cart_type = $data['cart']['cart_type'];
		$cart_name = $data['cart']['cart_name'];
		$cart_user = 0;
		if(!empty($data['user']['user_id']))
			$cart_user = (int)$data['user']['user_id'];

		$cart= new stdClass();
		$cart->cart_id = $cart_id;
		if(!empty($cart_user)){
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($cart_user);
			$cart->user_id = $user->user_cms_id;
		}
		$cart->cart_modified = time();
		$cart->cart_type = $cart_type;
		$cart->cart_name = $cart_name;
		if(isset($data['cart']['cart_coupon']))
			$cart->cart_coupon = $data['cart']['cart_coupon'];
		$status = $cartClass->save($cart);
		if($status && !empty($_POST['data']['cart_product'])){
			JRequest::setVar($cart_type.'_id',$cart_id);
			JRequest::setVar('cart_type',$cart_type);
			foreach($data['cart_product'] as $product_id => $product){
				$cartClass->update((int)$product_id, (int)$product['cart_product_quantity']);
			}
		}

		if($status){
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
			JRequest::setVar( 'cid', $status  );
			JRequest::setVar( 'fail', null  );
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
		return $status;
	}
}
