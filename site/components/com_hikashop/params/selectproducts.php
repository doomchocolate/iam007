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
class JElementSelectproducts extends JElement {

	function fetchElement($name, $value, &$node, $control_name) {
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			echo 'HikaShop is required';
			return;
		}

		$class = hikashop_get('class.product');

		$products = array();
		if(!empty($this->value)){
			$database = JFactory::getDBO();
			if(!is_array($this->value))
				$this->value = array($this->value);
			JArrayHelper::toInteger($this->value);
			$query = 'SELECT product_id,product_name FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$this->value).')';
			$database->setQuery($query);
			$products = $database->loadObjectList('product_id');
		}
		$productType = hikashop_get('type.productdisplay');
		$select = '<div style="height:130px; margin-left:150px;">';
		$select .= $productType->displayMultiple('jform[params][product_id]', $products, '', 0);
		$select .= '</div>';
		return $select;
	}
}
