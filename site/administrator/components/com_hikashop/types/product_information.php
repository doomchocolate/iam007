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
class hikashopProduct_informationType{
	function load($fields,$id, $sort){

		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'weight',JText::_('PRODUCT_WEIGHT'));
		$this->values[] = JHTML::_('select.option', 'volume',JText::_('PRODUCT_VOLUME'));
		$this->values[] = JHTML::_('select.option', 'height',JText::_('PRODUCT_HEIGHT'));
		$this->values[] = JHTML::_('select.option', 'length',JText::_('PRODUCT_LENGTH'));
		$this->values[] = JHTML::_('select.option', 'width',JText::_('PRODUCT_WIDTH'));
		$this->values[] = JHTML::_('select.option', 'surface',JText::_('PRODUCT_SURFACE'));
		if(empty($id) || $id=='datafilterfilter_data_cursor'){
			$this->values[] = JHTML::_('select.option', 'b.product_name',JText::_('PRODUCT_NAME'));
			$this->values[] = JHTML::_('select.option', 'price',JText::_('PRICE'));
			$this->values[] = JHTML::_('select.option', 'b.product_average_score',JText::_('RATING'));
		}
		if($sort){
			$this->values[] = JHTML::_('select.option', 'b.product_created',JText::_('CREATION_DATE'));
			$this->values[] = JHTML::_('select.option', 'b.product_sales',JText::_('SALES'));
			$this->values[] = JHTML::_('select.option', 'b.product_modified',JText::_('MODIFICATION_DATE'));
			$this->values[] = JHTML::_('select.option', 'b.product_hit',JText::_('CLICKS'));
		}

		$fieldClass=hikashop_get('class.field');
		if(!empty($fields) && is_array($fields)){
			foreach($fields as $field){
				$this->values[] = JHTML::_('select.option', $field->field_namekey, $field->field_realname);
			}
		}
	}

	function display($map,$value, $fields='', $option='size="1" ',$id=null, $sort=false){
		$this->load($fields,$id, $sort);
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" '.$option, 'value', 'text', $value, $id );
	}

}
