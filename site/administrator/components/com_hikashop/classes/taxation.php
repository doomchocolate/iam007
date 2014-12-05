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
class hikashopTaxationClass extends hikashopClass{
	var $tables = array('taxation');
	var $pkeys = array('taxation_id');
	var $toggle = array('taxation_published'=>'taxation_id');

	function get($id,$default=null){
		$query='SELECT b.*,c.*,d.*,a.* FROM '.hikashop_table('taxation').' AS a LEFT JOIN '.hikashop_table('tax').' AS b ON a.tax_namekey=b.tax_namekey LEFT JOIN '.hikashop_table('category').' AS c ON a.category_namekey=c.category_namekey LEFT JOIN '.hikashop_table('zone').' AS d ON a.zone_namekey=d.zone_namekey WHERE a.taxation_id='.(int)$id.' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}

	function saveForm(){
		$taxation = new stdClass();
		$taxation->taxation_id = hikashop_getCID('taxation_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['taxation'] as $column => $value){
			hikashop_secureField($column);
			$taxation->$column = strip_tags($value);
		}

		if(!empty($taxation->taxation_date_start)){
			$taxation->taxation_date_start=hikashop_getTime($taxation->taxation_date_start);
		}
		if(!empty($taxation->taxation_date_end)){
			$taxation->taxation_date_end=hikashop_getTime($taxation->taxation_date_end);
		}

		return $this->save($taxation);
	}
}
