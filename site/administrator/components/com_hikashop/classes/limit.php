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
class hikashopLimitClass extends hikashopClass{
	var $tables = array('limit');
	var $pkeys = array('limit_id');
	var $toggle = array('limit_published'=>'limit_id');

	function get($id,$default=null){
		$result = parent::get($id);
		$result->limit_status = explode(',', $result->limit_status);
		return $result;
	}

	function saveForm(){
		$limit = new stdClass();
		$limit->limit_id = hikashop_getCID('limit_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['limit'] as $column => $value){
			hikashop_secureField($column);
			if(is_array($value)){
				$value = implode(',',$value);
			}
			$limit->$column = strip_tags($value);
		}
		if(!empty($limit->limit_start)){
			$limit->limit_start=hikashop_getTime($limit->limit_start);
		}
		if(!empty($limit->limit_end)){
			$limit->limit_end=hikashop_getTime($limit->limit_end);
		}
		if(empty($limit->limit_id)){
			$limit->limit_created = time();
		}
		$limit->limit_modified = time();

		$status = $this->save($limit);

		return $status;
	}

	function save(&$limit){
		if(empty($limit->limit_type) || $limit->limit_type != 'weight' ) {
			$limit->limit_unit = '';
		}
		if(!empty($limit->limit_status) && is_array($limit->limit_status)){
			$limit->limit_status = implode(',',$limit->limit_status);
		}
		$status = parent::save($limit);
		return $status;
	}
}
