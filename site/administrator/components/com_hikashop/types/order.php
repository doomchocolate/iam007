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
class hikashopOrderType {
	function load($type, $value ='', $inherit = true) {
		$filter = false;
		if($type == 'product_filter') {
			$type = 'product';
			$filter = true;
		}

		if(substr($type, 0, 1) != '#')
			$query = 'SELECT * FROM '.hikashop_table($type);
		else
			$query = 'SELECT * FROM '.hikashop_table(substr($type, 2), false);

		$database = JFactory::getDBO();
		$database->setQuery($query, 0, 1);
		$arr = $database->loadAssoc();

		$object = new stdClass();
		if(!empty($arr)) {
			if(!is_array($value) && !isset($arr[$value])) {
				$arr[$value]=$value;
			}
			ksort($arr);
			foreach($arr as $key => $value) {
				if(!empty($key))
					$object->$key = $value;
			}
		}

		$this->values = array();
		if($type == 'product') {
			if(!$filter) {
				$this->values[] = JHTML::_('select.option', 'ordering', JText::_('ORDERING'));
			} else {
				$this->values[] = JHTML::_('select.option', 'all','all');
			}
		}
		if(!empty($object)) {
			foreach(get_object_vars($object) as $key => $val) {
				$this->values[] = JHTML::_('select.option', $key,$key);
			}
			if(JRequest::getCmd('from_display',false) == false && $inherit)
				$this->values[] = JHTML::_('select.option', 'inherit',JText::_('HIKA_INHERIT'));
		}
	}

	function display($map, $value, $type, $options = 'class="inputbox" size="1"', $inherit = true) {
		$this->load($type, $value, $inherit);
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value);
	}
}
