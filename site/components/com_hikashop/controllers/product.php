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
class productController extends hikashopController{
	var $modify = array();
	var $delete = array();
	var $modify_views = array();

	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display[]='updatecart';
		$this->display[]='cart';
		$this->display[]='cleancart';
		$this->display[]='contact';
		$this->display[]='compare';
		$this->display[]='waitlist';
		$this->display[]='send_email';
		$this->display[]='add_waitlist';
		$this->display[]='price';
		$this->display[]='download';
		$this->display[]='printcart';
		$this->display[]='sendcart';
	}
	function authorize($task){
		if($this->isIn($task,array('display'))){
			return true;
		}
		return false;
	}

	function printcart(){
		JRequest::setVar( 'layout', 'printcart' );
		return parent::display();
	}
	function sendcart(){
		JRequest::setVar( 'layout', 'sendcart' );
		return parent::display();
	}

	function contact(){
		JRequest::setVar( 'layout', 'contact' );
		return $this->display();
	}

	function compare(){
		JRequest::setVar( 'layout', 'compare' );
		return $this->display();
	}

	function waitlist(){
		JRequest::setVar( 'layout', 'waitlist' );
		return $this->display();
	}

	function price(){
		JRequest::setVar( 'layout', 'option_price' );
		return $this->display();
	}

	function send_email(){
		JRequest::checkToken('request') || jexit( 'Invalid Token' );
		$element = new stdClass();
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		if(empty($formData['contact'])) $formData['contact'] = @$formData['register'];
		foreach($formData['contact'] as $column => $value) {
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$app = JFactory::getApplication();
		if(empty($element->email)){
			$app->enqueueMessage(JText::_('VALID_EMAIL'));
			return $this->contact();
		}
		$config =& hikashop_config();
		if(!$config->get('product_contact',0)){
			return $this->contact();
		}

		$dispatcher = JDispatcher::getInstance();
		$send = true;
		$dispatcher->trigger( 'onBeforeSendContactRequest', array( & $element,& $send ) );

		if($element->email && !JMailHelper::isEmailAddress($element->email)){
			$app->enqueueMessage(JText::_('EMAIL_INVALID'),'error');
			$send = false;
		}
		if(empty($element->name)){
			$app->enqueueMessage(JText::_('SPECIFY_A_NAME'),'error');
			$send = false;
		}
		if(empty($element->altbody)){
			$app->enqueueMessage(JText::_('PLEASE_FILL_ADDITIONAL_INFO'),'error');
			$send = false;
		}

		if($send){
			$subject = JText::_('CONTACT_REQUEST');
			if(!empty($element->product_id)){
				$productClass = hikashop_get('class.product');
				$product = $productClass->get((int)$element->product_id);
				if(!empty($product)){
					if($product->product_type=='variant'){
						$db = JFactory::getDBO();
						$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$element->product_id.' ORDER BY a.ordering');
						$product->characteristics = $db->loadObjectList();
						$parentProduct = $productClass->get((int)$product->product_parent_id);
						$productClass->checkVariant($product,$parentProduct);
					}
					if(!empty($product->product_name)){
						$subject = JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT',strip_tags($product->product_name));
					}
				}
			}

			$mailClass = hikashop_get('class.mail');
			$infos = new stdClass();
			$infos->element =& $element;
			$infos->product =& $product;
			$mail = $mailClass->get('contact_request',$infos);
			$mail->subject = $subject;
			$mail->from_email = $config->get('from_email');
			$mail->from_name = $config->get('from_name');
			$mail->reply_email = $element->email;
			$mail->dst_email = array($config->get('from_email'));
			$status = $mailClass->sendMail($mail);

			if($status){
				$app->enqueueMessage(JText::_('CONTACT_REQUEST_SENT'));
				if(JRequest::getString('tmpl', '') == 'component') {
					$doc = JFactory::getDocument();
					$doc->addScriptDeclaration('setTimeout(function(){ window.parent.hikashop.closeBox(); }, 4000);');
					return true;
				}
				if(!empty($product->product_id)){
					$url_itemid = '';
					if(!empty($Itemid)){
						$url_itemid = '&Itemid='.(int)$Itemid;
					}
					if(!isset($productClass))
						$productClass = hikashop_get('class.product');
					$productClass->addAlias($product);
					$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_completeLink('product&task=show&cid='.$product->product_id.'&name='.$product->alias.$url_itemid)));
				}
			}
		}else{
			JRequest::setVar('formData',$element);
		}
		$url = JRequest::getVar('redirect_url');
		if($send && !empty($url)) {
			$app->redirect($url);
		} else {
			$this->contact();
		}

	}

	function add_waitlist() {
		JRequest::checkToken('request') || jexit( 'Invalid Token' );
		$element = new stdClass();
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['register'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$user = JFactory::getUser();
		$app= JFactory::getApplication();
		if(empty($element->email) && $user->guest){
			$app->enqueueMessage(JText::_('VALID_EMAIL'));
			return $this->waitlist();
		}
		$config =& hikashop_config();
		if(!$config->get('product_waitlist',0)){
			return $this->waitlist();
		}
		$waitlist_subscribe_limit = $config->get('product_waitlist_sub_limit',10);

		$product_id = 0;
		$itemId = JRequest::getVar('Itemid');
		$url_itemid = '';
		if(!empty($itemId))
			$url_itemid = '&Itemid='.$itemId;
		$alias = '';
		if(!empty($element->product_id)){
			$class = hikashop_get('class.product');
			$product = $class->get((int)$element->product_id);
			if(!empty($product)){
				if($product->product_type=='variant'){
					$db = JFactory::getDBO();
					$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$element->product_id.' ORDER BY a.ordering');
					$product->characteristics = $db->loadObjectList();
					$parentProduct = $class->get((int)$product->product_parent_id);
					$class->checkVariant($product,$parentProduct);
				}
				$product_id = (int)$product->product_id;
				$class->addAlias($product);
				$alias = $product->alias;
			}
		}
		if( $product_id == 0 ) {
			return $this->waitlist();
		}

		$email = (!empty($element->email)) ? $element->email : '';
		$name = (!empty($element->name)) ? $element->name : '';

		$db = JFactory::getDBO();

		$sql = 'SELECT waitlist_id FROM '.hikashop_table('waitlist').' WHERE email='.$db->quote($email).' AND product_id='.(int)$product_id;
		$db->setQuery($sql);
		$subscription = $db->loadResult();
		if(empty($subscription)) {
			$sql = 'SELECT count(*) FROM '.hikashop_table('waitlist').' WHERE product_id='.(int)$product_id;
			$db->setQuery($sql);
			$subscriptions = $db->loadResult();

			if( $subscriptions < $waitlist_subscribe_limit || $waitlist_subscribe_limit <= 0 ) {
				$sql = 'INSERT IGNORE INTO '.hikashop_table('waitlist').' (`product_id`,`date`,`email`,`name`,`product_item_id`) VALUES ('.(int)$product_id.', '.time().', '.$db->quote($email).', '.$db->quote($name).', '.(int)$itemId.');';
				$db->setQuery($sql);
				$db->query();
				$app->enqueueMessage(JText::_('WAITLIST_SUBSCRIBE'));
			} else {
				$app->enqueueMessage(JText::_('WAITLIST_FULL'));
			}
		} else {
			$app->enqueueMessage(JText::_('ALREADY_REGISTER_WAITLIST'));
		}
		$app->enqueueMessage(JText::sprintf('CLICK_HERE_TO_GO_BACK_TO_PRODUCT',hikashop_completeLink('product&task=show&cid='.$product->product_id.'&name='.$alias.$url_itemid)));
		$url = JRequest::getVar('redirect_url');
		if(!empty($url)){
			$app->redirect($url);
		}else{
			$this->waitlist();
		}
	}

	function cleancart(){
		$class = hikashop_get('class.cart');
		if($class->hasCart()){
			$class->delete($class->cart->cart_id);
		}

		$url = JRequest::getVar('return_url','');
		if(empty($url)){
			$url = JRequest::getVar('url','');
			$url = urldecode($url);
		}else{
			$url = base64_decode(urldecode($url));
		}

		if(!empty($url)){
			if(strpos($url,'tmpl=component')!==false || strpos($url,'tmpl-component')!==false){
				if(!empty($_SERVER['HTTP_REFERER'])){
					$app =& JFactory::getApplication();
					$app->redirect($_SERVER['HTTP_REFERER']);
				}else{
					echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
					exit;
				}
			}
			if(hikashop_disallowUrlRedirect($url)) return false;
			$this->setRedirect($url);
		}else{
			echo '<html><head><script type="text/javascript">history.go(-1);</script></head><body></body></html>';
			exit;
		}
	}

	function updatecart(){
		$app = JFactory::getApplication();
		$product_id = (int)JRequest::getCmd('product_id',0);
		$module_id = (int)JRequest::getCmd('module_id',0);

		$cart_type = JRequest::getString('hikashop_cart_type_'.$product_id.'_'.$module_id,'null');

		if($cart_type == 'null')
			$cart_type = JRequest::getString('hikashop_cart_type_'.$module_id,'null');

		if($cart_type == 'null'){
			$cart_type = JRequest::getString('cart_type','cart');
		}

		$cart_type_id = $cart_type.'_id';

		if(JRequest::getInt('cart_id',0,'GET') != 0){
			$cart_id = JRequest::getInt('cart_id',0,'GET');
		}else{
			$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.$cart_type_id,0);
		}

		$addTo = JRequest::getString('add_to','');
		if($addTo != ''){
			$from_id = $cart_id;
			if($addTo == 'cart')
				JRequest::setVar('from_id',$cart_id);
			$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.$addTo.'_id',0);
			$cart_type_id = $addTo.'_id';
			JRequest::setVar('cart_type', $addTo);
		}else{
			JRequest::setVar('cart_type', $cart_type);
		}
		JRequest::setVar($cart_type_id, $cart_id);


		$char = JRequest::getString('characteristic','');
		if(!empty($char)){
			return $this->show();
		}else{
			$tmpl = JRequest::getCmd('tmpl','index');
			$add = JRequest::getCmd('add','');
			if(!empty($add)){
				$add=1;
			}else{
				$add=0;
			}


			if(empty($product_id)){
				$product_id = JRequest::getCmd('cid',0);
			}
			$cart_product_id = JRequest::getCmd('cart_product_id',0);
			$quantity = JRequest::getInt('quantity',1);
			$class = hikashop_get('class.cart');

			if(hikashop_loadUser() != null || $cart_type != 'wishlist'){
				if(!empty($product_id)){
					$type = JRequest::getWord('type','product');
					if($type=='product'){
						$product_id=(int)$product_id;
					}
					$status = $class->update($product_id,$quantity,$add,$type);
				}elseif(!empty($cart_product_id)){
					$status = $class->update($cart_product_id,$quantity,$add,'item');
				}else{
					$formData = JRequest::getVar( 'item', array(), '', 'array' );
					if(!empty($formData)){
						$class->update($formData,0,$add,'item');
					}else{
						$formData = JRequest::getVar( 'data', array(), '', 'array' );
						if(!empty($formData)){

							$class->update($formData,0,$add);
						}
					}
				}
			}

			$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_new', '1');

			if(@$class->errors && $tmpl!='component'){
				if(!empty($_SERVER['HTTP_REFERER'])){
					if(strpos($_SERVER['HTTP_REFERER'],HIKASHOP_LIVE)===false && preg_match('#^https?://.*#',$_SERVER['HTTP_REFERER'])) return false;
					$app->redirect( str_replace('&popup=1','',$_SERVER['HTTP_REFERER']));
				}else{
					echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
					exit;
				}
			}
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method', null);
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id', null);
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data', null);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_method', null);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_id', null);
			$app->setUserState( HIKASHOP_COMPONENT.'.payment_data', null);
			$config =& hikashop_config();
			$checkout = JRequest::getString('checkout','');

			if(!empty($checkout)){
				global $Itemid;
				$url = 'checkout';
				if(!empty($Itemid)){
					$url.='&Itemid='.$Itemid;
				}
				$url = hikashop_completeLink($url,false,true);
				$this->setRedirect($url);
			}
			else if($cart_type == 'wishlist'){
				if(hikashop_loadUser() == null){
					$url = JRequest::getVar('return_url','');
					if(!empty($url)){
						$url=base64_decode(urldecode($url));
					}
					$url = str_replace(array('&popup=1','?popup=1'),'',$url);
					if($config->get('redirect_url_after_add_cart','stay_if_cart') != 'ask_user'){
						$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
					}
					if($tmpl!='component'){
						if(!empty($_SERVER['HTTP_REFERER'])){
							if(strpos($_SERVER['HTTP_REFERER'],HIKASHOP_LIVE)===false && preg_match('#^https?://.*#',$_SERVER['HTTP_REFERER'])) return false;
							$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
							$app->redirect( str_replace('&popup=1','',$_SERVER['HTTP_REFERER']));
						}
					}else{
						echo 'notLogged';
						exit;
					}
				}else{
					$redirectConfig = $config->get('redirect_url_after_add_cart','stay_if_cart');
					$url='';
					$stay = 0;
					switch($redirectConfig){
						case 'ask_user':
							$url = JRequest::getVar('return_url','');
							if(!empty($url)){
								$url=base64_decode(urldecode($url));
							}
							$url = str_replace(array('&popup=1','?popup=1'),'',$url);
							if(JRequest::getInt('popup',0) || JRequest::getInt('quantity',0)){
								if(strpos($url,'?')){
									$url.='&';
								}else{
									$url.='?';
								}
								$url.='popup=1';
								$app->setUserState( HIKASHOP_COMPONENT.'.popup','1');
							}
							JRequest::setVar('cart_type','wishlist');
							$app->setUserState( HIKASHOP_COMPONENT.'.popup_cart_type','wishlist');
							break;
						case 'stay':
							$stay = 1;
							break; //$stay = 1; && $url ='';
						case 'checkout':
							break; //$stay = 0; && $url ='';
						case 'stay_if_cart':
						default:
							$module = JModuleHelper::getModule('hikashop_wishlist',false);
							if($module != null){
								$stay = 1;
							}
							break;
					}
					if($redirectConfig != 'checkout'){
						$module = JModuleHelper::getModule('hikashop_wishlist',false);
						$params = new HikaParameter( @$module->params );
						if(!empty($module)){
							$module_options = $config->get('params_'.$module->id);
						}
						if(empty($module_options)){
							$module_options = $config->get('default_params');
						}
						foreach($module_options as $key => $optionElement){
							$params->set($key,$optionElement);
						}
						if(!empty($module)){
							foreach(get_object_vars($module) as $k => $v){
								if(!is_object($v)){
									$params->set($k,$v);
								}
							}
							$params->set('from','module');
						}
						$params->set('return_url',$url);
						$params->set('cart_type','wishlist');
						$js ='';
						hikashop_getLayout('product','cart',$params,$js);
					}
				}
				if(empty($url)){
					global $Itemid;
					if(isset($from_id))$cart_id = $from_id;
					if(JRequest::getInt('new_'.$cart_type.'_id',0)!= 0 && JRequest::getInt('delete',0) == 0)$cart_id = JRequest::getInt('new_'.$cart_type.'_id',0);
					$cart = $class->get($cart_id,false,$cart_type);
					if(!empty($cart) && (int)$cart_id != 0){
						$url = 'cart&task=showcart&cart_type=wishlist&cart_id='.$cart_id.'&Itemid='.$Itemid;
					}else{
						$url = 'cart&task=showcarts&cart_type=wishlist&Itemid='.$Itemid;
					}
					$url = hikashop_completeLink($url,false,true);
				}
				$stay = JRequest::getInt('stay',0);
				if($stay == 0){
					if(hikashop_disallowUrlRedirect($url)) return false;
					if(JRequest::getVar('from_form',true)){
						JRequest::setVar('cart_type','wishlist');
						$this->setRedirect($url);
						return false;
					}else{
						ob_clean();
						echo 'URL|'.$url;
						exit;
					}
				}else{
					echo '<html><head><script type="text/javascript">history.back();</script></head><body></body></html>';
					exit;
				}
			}else{
				$url = JRequest::getVar('return_url','');
				if(empty($url)){
					$url = JRequest::getVar('url','');
					$url = urldecode($url);
				}else{
					$url = base64_decode(urldecode($url));
				}
				$url = str_replace(array('&popup=1','?popup=1'),'',$url);

				if(empty($url)){
					global $Itemid;
					$url = 'checkout';
					if(!empty($Itemid)){
						$url.='&Itemid='.$Itemid;
					}
					$url = hikashop_completeLink($url,false,true);
				}
				$params = new HikaParameter( @$module->params );
				if($tmpl=='component' && $config->get('redirect_url_after_add_cart','stay_if_cart') != 'checkout'){
					$js ='';
					jimport('joomla.application.module.helper');
					global $Itemid;
					if(isset($Itemid) && empty($Itemid)){
						$Itemid=null;
						JRequest::setVar('Itemid',null);
					}
					$module = JModuleHelper::getModule('hikashop_cart',false);
					$config =& hikashop_config();
					$params = new HikaParameter( @$module->params );
					if(!empty($module)){
						$module_options = $config->get('params_'.$module->id);
					}
					if(empty($module_options)){
						$module_options = $config->get('default_params');
					}
					foreach($module_options as $key => $optionElement){
						$params->set($key,$optionElement);
					}
					if(!empty($module)){
						foreach(get_object_vars($module) as $k => $v){
							if(!is_object($v)){
								$params->set($k,$v);
							}
						}
						$params->set('from','module');
					}
					$params->set('return_url',$url);
					hikashop_getLayout('product','cart',$params,$js);
					return true;
				}else{
					$config =& hikashop_config();
					$url = str_replace(array('&popup=1','?popup=1'),'',$url);
					if(JRequest::getInt('popup',0) || (@JRequest::getInt('quantity',0) && $config->get('redirect_url_after_add_cart','stay_if_cart') == 'ask_user')){
						if(strpos($url,'?')){
							$url.='&';
						}else{
							$url.='?';
						}
						$url.='popup=1';
						$app->setUserState( HIKASHOP_COMPONENT.'.popup','1');
					}
					if(hikashop_disallowUrlRedirect($url)) return false;
					if(JRequest::getInt('hikashop_ajax', 0) == 0) { // $config->get('ajax_add_to_cart','1') == '0'){
						$this->setRedirect($url);
						return false;
					}else{
						ob_clean();
						if($params->get('from','module') != 'module' || $config->get('redirect_url_after_add_cart','stay_if_cart') == 'checkout'){
							echo 'URL|'.$url;
							exit;
						}else{
							$this->setRedirect($url);
							return false;
						}
					}
				}
			}
		}
	}

	function download() {
		$file_id = JRequest::getInt('file_id');
		if(!$file_id){ return false; }
		$fileClass = hikashop_get('class.file');
		$fileClass->download($file_id);
		return true;
	}
}
