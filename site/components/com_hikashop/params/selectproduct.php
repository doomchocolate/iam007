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
class JElementSelectproduct extends JElement {
	function fetchElement($name, $value, &$node, $control_name) {
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			echo 'HikaShop is required';
			return;
		}

		$productType = hikashop_get('type.productdisplay');

		if(is_array($this->value))
			$this->value = reset($this->value);

		$select = '<div style="height:130px; margin-left:150px;">'.
				$productType->displaySingle('jform[params][product_id]', $this->value, '', 0).
				'</div>';
		return $select;
	}
}
