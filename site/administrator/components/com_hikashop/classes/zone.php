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
class hikashopZoneClass extends hikashopClass{

	var $tables = array('zone_link','zone_link','zone');
	var $pkeys = array('','','zone_id');
	var $namekeys = array('zone_parent_namekey','zone_child_namekey','zone_namekey');
	var $deleteToggle = array('zone_link'=>array('zone_parent_namekey','zone_child_namekey'));
	var $toggle = array('zone_published'=>'zone_id');

	function saveForm(){
		$zone = new stdClass();
		$zone->zone_id = hikashop_getCID('zone_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		$status = false;
		if(!empty($formData['zone'])){
			foreach($formData['zone'] as $column => $value){
				hikashop_secureField($column);
				$zone->$column = strip_tags($value);
			}

			$status = $this->save($zone);

			if(!$status){
				JRequest::setVar( 'fail', $zone  );
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JText::_( 'DUPLICATE_ZONE' ), 'error');
			}
		}
		return $status;
	}

	function getZoneParents($zone_id,$already=array()){
		static $level = 0;
		if(!count($already)) $level = 0;
		if($level>10) return array();
		$level++;

		if(!is_array($zone_id)){
			if(is_numeric($zone_id)){
				$zone = $this->get($zone_id);
				if($zone){
					$zone_id = $zone->zone_namekey;
				}
			}
			$zone_id = array($zone_id);
		}
		$quoted = array();
		foreach($zone_id as $zone){
			$quoted[]=$this->database->Quote($zone);
		}
		$query = 'SELECT a.zone_parent_namekey FROM '.hikashop_table('zone_link').' AS a WHERE a.zone_child_namekey IN ('.implode(',',$quoted).');';
		$this->database->setQuery($query);
		if(!HIKASHOP_J25){
			$parents = $this->database->loadResultArray();
		} else {
			$parents = $this->database->loadColumn();
		}
		$results = array();

		foreach($zone_id as $z){
			$results[$z]=$z;
		}

		if(!empty($parents)){
			$getParents = array();
			foreach($parents as $p){
				if(!isset($already[$p])){
					$getParents[]=$p;
				}
				$results[$p]=$p;
			}
			if(!empty($getParents)){
				$grandparents = $this->getZoneParents($getParents,$results);
				foreach($grandparents as $gp){
					$results[$gp]=$gp;
				}
			}
		}

		return $results;
	}


	function getZoneCurrency($zone_id){
		$zone = $this->get($zone_id);
		$already = array($zone->zone_namekey);
		$childs = array($zone->zone_namekey);
		if(empty($zone->zone_currency_id)){
			while(!empty($childs)){
				$quoted = array();
				foreach($childs as $z){
					$quoted[]=$this->database->Quote($z);
				}
				$query = 'SELECT b.* FROM '.hikashop_table('zone_link').' AS a LEFT JOIN '.hikashop_table('zone').' AS b ON a.zone_parent_namekey=b.zone_namekey WHERE a.zone_child_namekey IN ('.implode(',',$quoted).');';
				$this->database->setQuery($query);
				$parents = $this->database->loadObjectList();
				$childs = array();
				if(!empty($parents)){
					foreach($parents as $parent){
						if(in_array($parent->zone_namekey,$already)) continue;
						if(!empty($parent->zone_currency_id)){
							return (int)$parent->zone_currency_id;
						}
						$childs[]=$parent->zone_namekey;
						$already[]=$parent->zone_namekey;
					}
				}
			}
		}
		return (int)$zone->zone_currency_id;
	}


	function getOrderZones(&$order) {
		$field = 'address_country';
		$fieldClass = hikashop_get('class.field');
		$fields = $fieldClass->getData('frontcomp','address');
		if(isset($fields['address_state']) && $fields['address_state']->field_type=='zone' && !empty($order->shipping_address) && !empty($order->shipping_address->address_state) && (!is_array($order->shipping_address->address_state) || count($order->shipping_address->address_state)>1 || !empty($order->shipping_address->address_state[0]))) {
			$field='address_state';
		}
		$type = 'shipping_address';
		if(empty($order->shipping_address) && !empty($order->billing_address)) {
			$type = 'billing_address';
		}
		if(empty($order->$type) || empty($order->$type->$field)) {
			$zones = $this->getZoneParents(hikashop_getZone());
		} else {
			$zones =& $order->$type->$field;
			if(!is_array($zones)) {
				$zones = array($zones);
			}
		}
		return $zones;
	}

	function getNamekey($element){
		return $element->zone_type.'_'.preg_replace('#[^a-z_]#i','',$element->zone_name_english).'_'.rand();
	}

	function getChilds($zone_namekey){
		$database = JFactory::getDBO();
		if(is_numeric($zone_namekey)){
			$zone = $this->get($zone_namekey);
			$zone_namekey = $zone->zone_namekey;
		}
		$query = 'SELECT a.* FROM '.hikashop_table('zone_link').' AS b LEFT JOIN '.hikashop_table('zone').' AS a ON b.zone_child_namekey=a.zone_namekey WHERE b.zone_parent_namekey  = '.$database->Quote($zone_namekey).' ORDER BY a.zone_id';
		$database->setQuery($query);
		return  $database->loadObjectList();
	}

	function addChilds($mainNamekey,$childNamekeys){
		if(empty($mainNamekey)) return null;
		if(empty($childNamekeys)) return null;
		$NamekeysString = '';
		if(is_numeric($mainNamekey)){
			foreach($childNamekeys as $childNamekey){
				$NamekeysString .= $this->database->Quote($childNamekey).',';
			}
			$NamekeysString .= $this->database->Quote($mainNamekey).',';
			$query = 'SELECT zone_id,zone_namekey FROM '.hikashop_table('zone').' WHERE zone_id  IN ('.rtrim($NamekeysString,',').')';
			$this->database->setQuery($query);
			$zones =  $this->database->loadObjectList('zone_id');
			$newChildNamekeys = array();
			foreach($childNamekeys as $childNamekey){
				$newNamekey = $zones[$childNamekey]->zone_namekey;
				$NamekeysString .= $this->database->Quote($newNamekey).',';
				$newChildNamekeys[] = $newNamekey;
			}
			$mainNamekey = $zones[$mainNamekey]->zone_namekey;
			$childNamekeys = $newChildNamekeys;
		}else{
			foreach($childNamekeys as $childNamekey){
				$NamekeysString .= $this->database->Quote($childNamekey).',';
			}
		}

		$query = 'SELECT zone_child_namekey FROM '.hikashop_table('zone_link').' WHERE zone_parent_namekey  = '.$this->database->Quote($mainNamekey).' AND zone_child_namekey IN ('.rtrim($NamekeysString,',').') LIMIT 1';
		$this->database->setQuery($query);
		if(!HIKASHOP_J25){
			$alreadyChild =  $this->database->loadResultArray();
		} else {
			$alreadyChild =  $this->database->loadColumn();
		}
		$toInsertNamekeys = array();
		foreach($childNamekeys as $childNamekey){
			if(!in_array($childNamekey,$alreadyChild))$toInsertNamekeys[]=$childNamekey;
		}
		if(empty($toInsertNamekeys)) return null;
		$query = 'INSERT IGNORE INTO '.hikashop_table('zone_link').' (zone_parent_namekey,zone_child_namekey) VALUES ';
		foreach($toInsertNamekeys as $childNamekey){
			$query.='('.$this->database->Quote($mainNamekey).','.$this->database->Quote($childNamekey).'),';
		}
		$this->database->setQuery(rtrim($query,',').';');
		$this->database->query();
		return $toInsertNamekeys;
	}

}
