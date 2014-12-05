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
class CartViewCart extends hikashopView {
	var $ctrl= 'cart';
	var $nameListing = 'HIKASHOP_CHECKOUT_CART';
	var $nameForm = 'HIKASHOP_CHECKOUT_CART';
	var $icon = 'cart';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function setName(){
		$cart_type = JRequest::getString('cart_type','cart');
		if($cart_type!='cart'){
			$this->nameListing = 'WISHLIST';
			$this->nameForm = 'WISHLIST';
			$this->icon = 'wishlist';
		}
	}

	function listing(){
		$this->setName();
		$config = hikashop_config();
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.cart_modified','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );

		$pageInfo->manageUser = hikashop_isAllowed($config->get('acl_user_manage','all'));
		$popup = (JRequest::getString('tmpl') === 'component');
		$database	= JFactory::getDBO();
		if(JRequest::getString('cart_type', 'cart') == 'cart')
			$filters = array('a.cart_type=\'cart\'');
		else
			$filters = array('a.cart_type=\'wishlist\'');
		$searchMap = array('a.cart_id','a.user_id','a.cart_name','a.cart_coupon','a.cart_type');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$groupBy = 'GROUP BY a.cart_id';
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE '. implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$from = 'FROM '.hikashop_table('cart').' AS a';
		$cartProduct = 'JOIN '.hikashop_table('cart_product').' AS b ON a.cart_id=b.cart_id';
		$query = $from.' '.$cartProduct.' '.$filters.' '.$groupBy.' '.$order;
		$database->setQuery('SELECT a.*, b.* '.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'cart_id');
		}
		$database->setQuery('SELECT COUNT(*) '.$from.' '.$cartProduct.' '.$filters.' '.$groupBy);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);



		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$class = hikashop_get('class.cart');
		$productClass = hikashop_get('class.product');
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();
		if($config->get('tax_zone_type','shipping')=='billing'){
			$zone_id = hikashop_getZone('billing');
		}else{
			$zone_id = hikashop_getZone('shipping');
		}
		$discount_before_tax = (int)$config->get('discount_before_tax',0);

		$cids = array();
		foreach($rows as $row){
			if($row->cart_id != null)
				$cids[] = $row->cart_id;
		}
		if(!empty($cids))
		$filters = array('a.cart_id IN('.implode(",",$cids).')');
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY cart_id ASC';
		}
		$products = null;
		if(!empty($cids)){
			$product = ' LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id';
			$query = 'FROM '.hikashop_table('cart_product').' AS a '.$product.' WHERE ('.implode(') AND (',$filters).') '.$order;
			$database->setQuery('SELECT a.*,b.* '.$query);
			$products = $database->loadObjectList();
		}
		if(!empty($products)){
			$ids = array();
			foreach($products as $row){
				$ids[] = $row->product_id;
			}
			$row_1 = 0;
			$previous = 0;
			foreach($products as $k => $row){
				$currencyClass->getPrices($row,$ids,$currency_id,$main_currency,$zone_id,$discount_before_tax);
				if(!isset($row->prices[0]) && isset($row_1->prices[0])){
					$row->prices[0] = $row_1->prices[0];
				}
				$products[$k]->hide = 0;
				if($row->product_type == 'variant'){
					$products[$previous]->hide = 1;
				}
				$row_1 = $row;
				$previous = $k;
			}
			$currentId = 0;
			$values = array();
			$currency = hikashop_getCurrency();
			foreach($products as $product){
				if(isset($product->cart_id)){
					if($product->cart_id != $currentId){
						$price = 0;
						$quantity = 0;
						$currentId = $product->cart_id;
						if(isset($product->prices[0]))
							$currency = $product->prices[0]->price_currency_id;
					}
					if(isset($product->prices[0])){
						$price += $product->cart_product_quantity * $product->prices[0]->price_value;
						$quantity += $product->cart_product_quantity;
					}
					if(!isset($values[$currentId])) $values[$currentId] = new stdClass();
					$values[$currentId]->price = $price;
					$values[$currentId]->quantity = $quantity;
					$values[$currentId]->currency = $currency;
				}
			}
		}
		foreach($rows as $k => $row){
			if($values[$row->cart_id] != null){
				$rows[$k]->price = $values[$row->cart_id]->price;
				$rows[$k]->quantity = $values[$row->cart_id]->quantity;
				$rows[$k]->currency = $values[$row->cart_id]->currency;
			}
		}
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$this->assignRef('carts',$rows);
		$this->assignRef('popup',$popup);
		$pageInfo->elements->total = count($rows);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_wishlist_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_wishlist_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}

	function form(){
		$this->setName();
		$cart_id = hikashop_getCID('cart_id',false)?hikashop_getCID('cart_id',false):0;
		if($cart_id == 0)$cart_id = JRequest::getInt('cart_id',0);
		$database	= JFactory::getDBO();
		$searchMap = array('a.cart_id','a.product_id');
		$filters = array('a.cart_id ='.(int)$cart_id);
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY cart_product_modified ASC';
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			$filters[] =  $filter;
		}

		$query = 'FROM '.hikashop_table('cart_product').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id WHERE ('.implode(') AND (',$filters).') '.$order;
		if((int)$cart_id != 0){

			$database->setQuery('SELECT a.*, b.* '.$query);
			$rows = $database->loadObjectList();
		}else{
			$rows = null;
		}
		if(!empty($rows[0]->cart_id)){
			$database->setQuery('SELECT a.* FROM '.hikashop_table('cart').' AS a WHERE a.cart_id = '.$rows[0]->cart_id);
			$cart = $database->loadObject();
		}else{
			$cart = new stdClass();
			$cart->cart_id = '0';
			$cart->cart_name = '';

			$cart->cart_type = JRequest::getString('cart_type','cart');
			$cart->cart_modified = time();

			$cartClass = hikashop_get('class.cart');
			$cart->cart_id = $cartClass->save($cart);

			$cart->user_id = 0;
			$cart->cart_coupon = '';
			$rows = null;
		}

		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$class = hikashop_get('class.cart');
		$productClass = hikashop_get('class.product');
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();

		if($config->get('tax_zone_type','shipping')=='billing'){
			$zone_id = hikashop_getZone('billing');
		}else{
			$zone_id = hikashop_getZone('shipping');
		}
		$discount_before_tax = (int)$config->get('discount_before_tax',0);

		if(!empty($rows)){
			$ids = array();
			foreach($rows as $row){
				$ids[] = $row->product_id;
			}
			$row_1 = 0;
			$previous = 0;
			foreach($rows as $k => $row){

				$currencyClass->getPrices($row,$ids,$currency_id,$main_currency,$zone_id,$discount_before_tax);

				if(!isset($row->prices[0]) && isset($row_1->prices[0])){
					$row->prices[0] = $row_1->prices[0];
					if($row->product_name == ''){
						$row->product_name = $row_1->product_name;
					}
				}
				$rows[$k]->hide = 0;
				if($row->product_type == 'variant'){
					$rows[$previous]->hide = 1;
				}
				$row_1 = $row;
				$previous = $k;
			}
		}
		$this->assignRef('rows',$rows);
		$class = hikashop_get('class.cart');
		if(!empty($cart_id)){
			$element = $class->get($cart_id,true,$cart->cart_type);
			$task='edit';
		}else{
			$element = new stdClass();
			$task='add';
		}
		$user = null;
		if($cart->user_id != 0){
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($cart->user_id,'cms');
		}
		$this->assignRef('user',$user);
		$this->assignRef('cart',$cart);
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&cart_id='.$cart_id);
		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);

		$this->assignRef('element',$element);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
	}
	function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics){
		$element->characteristics = $mainCharacteristics[$element->product_id][0];
		if(!empty($element->characteristics) && is_array($element->characteristics)){
			foreach($element->characteristics as $k => $characteristic){
				if(!empty($mainCharacteristics[$element->product_id][$k])){
					$element->characteristics[$k]->default=end($mainCharacteristics[$element->product_id][$k]);
				}else{
					$app =& JFactory::getApplication();
					$app->enqueueMessage('The default value of one of the characteristics of that product isn\'t available as a variant. Please check the characteristics and variants of that product');
				}
			}
		}

		if(!empty($element->variants)){
			foreach($characteristics as $characteristic){
				foreach($element->variants as $k => $variant){
					if($variant->product_id==$characteristic->variant_product_id){
						$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id]=$characteristic;
						$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id]=$characteristic;
						if($this->selected_variant_id && $variant->product_id==$this->selected_variant_id){
							$element->characteristics[$characteristic->characteristic_parent_id]->default=$characteristic;
						}
					}
				}
			}
			if(isset($_REQUEST['hikashop_product_characteristic'])){
				if(is_array($_REQUEST['hikashop_product_characteristic'])){
					JArrayHelper::toInteger($_REQUEST['hikashop_product_characteristic']);
					$chars = $_REQUEST['hikashop_product_characteristic'];
				}else{
					$chars = JRequest::getCmd('hikashop_product_characteristic','');
					$chars = explode('_',$chars);
				}
				if(!empty($chars)){
					foreach($element->variants as $k => $variant){
						$chars = array();
						foreach($variant->characteristics as $val){
							$i = 0;
							$ordering = @$element->characteristics[$val->characteristic_parent_id]->ordering;
							while(isset($chars[$ordering])&& $i < 30){
								$i++;
								$ordering++;
							}
							$chars[$ordering] = $val;
						}
						ksort($chars);
						$element->variants[$k]->characteristics=$chars;
						$variant->characteristics=$chars;

						$choosed = true;
						foreach($variant->characteristics as $characteristic){
							$ok = false;
							foreach($chars as $k => $char){
								if(!empty($char)){
									if($characteristic->characteristic_id==$char){
										$ok = true;
										break;
									}
								}
							}
							if(!$ok){
								$choosed=false;
							}else{
								$element->characteristics[$characteristic->characteristic_parent_id]->default=$characteristic;
							}
						}
						if($choosed){
							break;
						}
					}
				}
			}
			foreach($element->variants as $k => $variant){
				$temp=array();
				foreach($element->characteristics as $k2 => $characteristic2){
					if(!empty($variant->characteristics)){
						foreach($variant->characteristics as $k3 => $characteristic3){
							if($k2==$k3){
								$temp[$k3]=$characteristic3;
								break;
							}
						}
					}
				}
				$element->variants[$k]->characteristics=$temp;
			}
		}
	}
}
