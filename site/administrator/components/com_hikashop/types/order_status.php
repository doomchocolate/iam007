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
class hikashopOrder_statusType{
	function __construct() {
		$this->values = array();
	}

	function load() {
		$class = hikashop_get('class.category');
		$rows = $class->loadAllWithTrans('status');
		foreach($rows as $row) {
			if(!empty($row->translation)) {
				$this->values[] = JHTML::_('select.option', $row->category_name, hikashop_orderStatus($row->translation));
			} else {
				$this->values[] = JHTML::_('select.option', $row->category_name, hikashop_orderStatus($row->category_name));
			}
		}
	}

	function display($map, $value, $extra = '', $addAll = false) {
		if(empty($this->values))
			$this->load();
		if($addAll) {
			$values = array_merge(
				array(JHTML::_('select.option', '', JText::_('ALL_STATUSES'))),
				$this->values
			);
		} else {
			$values = $this->values;
		}
		return JHTML::_('select.genericlist', $values, $map, $extra, 'value', 'text', $value);
	}
}
