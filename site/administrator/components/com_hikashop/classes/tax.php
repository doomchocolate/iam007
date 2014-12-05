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
class hikashopTaxClass extends hikashopClass{
	var $tables = array('tax');
	var $namekeys = array('tax_namekey');

	function get($id,$default=null){
		$query='SELECT * FROM '.hikashop_table('tax').' WHERE tax_namekey='.$this->database->Quote($id).' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function delete(&$ids){
		foreach($ids as $k => $id){
			$ids[$k] = $this->database->Quote($id);
		}
		$query='DELETE FROM '.hikashop_table('tax').' WHERE tax_namekey IN ('.implode(',',$ids).')';
		$this->database->setQuery($query);
		return $this->database->query();
	}

	function saveForm(){
		$tax = new stdClass();
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['tax'] as $column => $value){
			hikashop_secureField($column);
			if($column=='tax_rate'){
				$tax->$column = strip_tags(str_replace('"','',$value))/100.0;
			}else{
				$tax->$column = strip_tags($value);
			}
		}
		if(JRequest::getVar('task')!='save2new') JRequest::setVar('tax_namekey',$tax->tax_namekey);
		return $this->save($tax);
	}

	function save(&$element){
		$old = $this->get($element->tax_namekey);
		if(!empty($old)){
			return parent::save($element);
		}else{
			$this->database->setQuery($this->_getInsert($this->getTable(),$element));
			return $this->database->query();
		}
	}
}
