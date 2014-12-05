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
class hikashopAddressClass extends hikashopClass{
	var $tables = array('address');
	var $pkeys = array('address_id');

	function getByUser($user_id){
		$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE a.address_user_id='.(int)$user_id.' and a.address_published=1 ORDER BY a.address_default DESC, a.address_id DESC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList('address_id');
	}

	function loadZone(&$addresses,$type='name',$display='frontcomp'){
		$fieldClass = hikashop_get('class.field');
		$fields = $fieldClass->getData($display,'address');
		$this->fields =& $fields;

		if(!empty($fields)){
			$namekeys = array();
			foreach($fields as $field){
				if($field->field_type=='zone'){
					$namekeys[$field->field_namekey] = $field->field_namekey;
				}
			}
			if(!empty($namekeys)){
				$zones=array();
				foreach($addresses as $address){
					foreach($namekeys as $namekey){
						if(!empty($address->$namekey)){
							$zones[$address->$namekey]=$address->$namekey;
						}
					}
				}

				if(!empty($zones)){
					if(in_array($type,array('name','object'))){
						$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN (\''.implode('\',\'',$zones).'\');';
						$this->database->setQuery($query);
						$zones = $this->database->loadObjectList('zone_namekey');
						if(!empty($zones)){
							foreach($addresses as $k => $address){
								foreach($namekeys as $namekey){
									if(!empty($address->$namekey) && !empty($zones[$address->$namekey])){
										if($type=='name'){
											if(is_numeric($zones[$address->$namekey]->zone_name_english)){
												$addresses[$k]->$namekey = $zones[$address->$namekey]->zone_name;
											}else{
												$addresses[$k]->$namekey=$zones[$address->$namekey]->zone_name_english;
											}
										}else{
											$addresses[$k]->$namekey=$zones[$address->$namekey];
										}
									}
								}
							}
						}
					}else{
						$this->_getParents($zones,$addresses,$namekeys);
					}
				}
			}
		}
	}

	function loadUserAddresses($user_id){
		static $addresses = array();
		if(!isset($addresses[$user_id])){
			$query = 'SELECT a.* FROM '.hikashop_table('address').' AS a WHERE a.address_user_id='.(int)$user_id.' and a.address_published=1 ORDER BY a.address_default DESC, a.address_id DESC';
			$this->database->setQuery($query);
			$addresses[$user_id] = $this->database->loadObjectList('address_id');
		}
		return $addresses[$user_id];
	}

	function _getParents(&$zones,&$addresses,&$fields){
		$namekeys = array();
		foreach($zones as $zone){
			$namekeys[]=$this->database->Quote($zone);
		}
		$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS a WHERE a.zone_child_namekey IN ('.implode(',',$namekeys).');';
		$this->database->setQuery($query);
		$parents = $this->database->loadObjectList();
		if(!empty($parents)){
			$childs = array();
			foreach($parents as $parent){
				foreach($addresses as $k => $address){
					foreach($fields as $field){
						if(!is_array($addresses[$k]->$field)){
							$addresses[$k]->$field = array($addresses[$k]->$field);
						}
						foreach($addresses[$k]->$field as $value){
							if($value == $parent->zone_child_namekey && !in_array($parent->zone_parent_namekey,$addresses[$k]->$field)){
								$values =& $addresses[$k]->$field;
								$values[]=$parent->zone_parent_namekey;
								$childs[$parent->zone_parent_namekey]=$parent->zone_parent_namekey;
							}
						}
					}
				}
			}
			if(!empty($childs)){
				$this->_getParents($childs,$addresses,$fields);
			}
		}

	}

	function save(&$addressData,$order_id=0,$type='shipping'){
		$new = true;
		if(!empty($addressData->address_id)){
			$new = false;
			$oldData = $this->get($addressData->address_id);

			if(!empty($addressData->address_vat) && $oldData->address_vat != $addressData->address_vat){
				if(!$this->_checkVat($addressData)){
					return false;
				}
			}

			$app = JFactory::getApplication();
			if(!$app->isAdmin()){
				$user_id = hikashop_loadUser();
				if($user_id!=$oldData->address_user_id || !$oldData->address_published){
					unset($addressData->address_id);
					$new = true;
				}
			}

			$orderClass = hikashop_get('class.order');

			if(!empty($addressData->address_id) && ($oldData->address_published!=0||$order_id) && $orderClass->addressUsed($addressData->address_id,$order_id,$type)){
				unset($addressData->address_id);
				$new = true;
				$oldData->address_published=0;
				parent::save($oldData);
			}
		}elseif(!empty($addressData->address_vat)){
			if(!$this->_checkVat($addressData)){
				return false;
			}
		}

		if(empty($addressData->address_id) && empty($addressData->address_user_id) && empty($order_id))
			return false;

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if($new){
			if(!empty($addressData->address_user_id)) {
				$query = 'SELECT count(*) as cpt FROM '.hikashop_table('address').' WHERE address_user_id = '.(int)$addressData->address_user_id.' AND address_published = 1 AND address_default = 1';
				$this->database->setQuery($query);
				$ret = $this->database->loadObject();
				if($ret->cpt == 0) {
					$addressData->address_default = 1;
				}
			}

			$dispatcher->trigger( 'onBeforeAddressCreate', array( & $addressData, & $do) );
		}else{
			$dispatcher->trigger( 'onBeforeAddressUpdate', array( & $addressData, & $do) );
		}
		if(!$do){
			return false;
		}
		$status = parent::save($addressData);
		if(!$status){
			return false;
		}
		if(!empty($addressData->address_default) && !empty($oldData->address_id)){
			$query = 'UPDATE '.hikashop_table('address').' SET address_default=0 WHERE address_user_id = '.(int)$oldData->address_user_id.' AND address_id != '.(int)$oldData->address_id;
			$this->database->setQuery($query);
			$this->database->query();
		}
		if($new){
			$dispatcher->trigger( 'onAfterAddressCreate', array( & $addressData ) );
		}else{
			$dispatcher->trigger( 'onAfterAddressUpdate', array( & $addressData ) );
		}
		return $status;
	}

	function frontSaveForm($task = '') {
		$fieldsClass = hikashop_get('class.field');
		$data = JRequest::getVar('data', array(), '', 'array');
		$ret = array();

		$user_id = hikashop_loadUser(false);

		$currentTask = 'billing_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask])) {
			$oldAddress = null;
			$billing_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($billing_address)) {
				$billing_address->address_user_id = $user_id;
				$id = (int)@$billing_address->address_id;

				$result = $this->save($billing_address, 0, 'billing');
				if($result) {
					$r = new stdClass();
					$r->id = $result;
					$r->previous_id = $id;
					$ret[$currentTask] = $r;
				}
			}
		}

		$currentTask = 'shipping_address';
		if( (empty($task) || $task == $currentTask) && !empty($data[$currentTask])) {
			$oldAddress = null;
			$shipping_address = $fieldsClass->getInput(array($currentTask, 'address'), $oldAddress);

			if(!empty($shipping_address)) {
				$shipping_address->address_user_id = $user_id;
				$id = (int)@$shipping_address->address_id;

				$result = $this->save($shipping_address, 0, 'shipping');
				if($result) {
					$r = new stdClass();
					$r->id = $result;
					$r->previous_id = $id;
					$ret[$currentTask] = $r;
				}
			}
		}

		return $ret;
	}

	function delete(&$elements,$order=false){
		$elements = (int)$elements;

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do=true;
		$dispatcher->trigger( 'onBeforeAddressDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}
		$orderClass = hikashop_get('class.order');
		$status = true;
		if($orderClass->addressUsed($elements)){
			if(!$order){
				$address=new stdClass();
				$address->address_id = $elements;
				$address->address_published=0;
				$status = parent::save($address);
				$app = JFactory::getApplication();
				if($app->isAdmin()){
					$app->enqueueMessage(JText::_('ADDRESS_UNPUBLISHED_CAUSE_USED_IN_ORDER'));
				}
			}
		}else{
			$data = $this->get($elements);
			if(!$order || (isset($data->address_published) && !$data->address_published)){
				$status = parent::delete($elements);
			}
		}
		if($status){
			$dispatcher->trigger( 'onAfterAddressDelete', array( & $elements ) );
		}
		return $status;
	}

	function _checkVat(&$vatData){
		$vat = hikashop_get('helper.vat');
		if(!$vat->isValid($vatData)){
			$this->message = @$vat->message;
			return false;
		}
		return true;
	}

	function miniFormat($address) {
		$config = hikashop_config();
		$ret = $config->get('mini_address_format', '');
		if(empty($ret))
			$ret = '{address_lastname} {address_firstname} - {address_street}, {address_state} ({address_country})';
		foreach($address as $k => $v) {
			if(is_string($v))
				$ret = str_replace('{' . $k . '}', $v, $ret);
		}
		$ret = preg_replace('#{[-_a-zA-Z0-9]+}#iU', '', $ret);
		return $ret;
	}
}
