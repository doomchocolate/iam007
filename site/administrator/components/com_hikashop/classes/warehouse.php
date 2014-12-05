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
class hikashopWarehouseClass extends hikashopClass {
	var $tables = array('warehouse');
	var $pkeys = array('warehouse_id');
	var $toggle = array('warehouse_published'=>'warehouse_id');

	function saveForm(){
		$element = new stdClass();
		$element->warehouse_id = hikashop_getCID('warehouse_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['warehouse'] as $column => $value) {
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$class = hikashop_get('helper.translation');
		$class->getTranslations($element);
		$status = $this->save($element);

		return $status;
	}
	function save(&$element) {
		$isNew = empty($element->warehouse_id);
		$element->warehouse_modified=time();
		if($isNew) {
			$element->warehouse_created=$element->warehouse_modified;
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'warehouse_id';
			$orderClass->table = 'warehouse';
			$orderClass->orderingMap = 'warehouse_ordering';
			$orderClass->reOrder();
		}
		$status = parent::save($element);
		if(!$status) {
			return false;
		}
		return $status;
	}
}
