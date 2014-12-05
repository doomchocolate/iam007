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
class hikashopTableType {
	var $externalValues = null;

	function load($form=false) {
		$this->values = array();
		if(!$form) {
			$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_ALL') );
		}

		$this->values[] = JHTML::_('select.option', 'address', JText::_('ADDRESS'));
		if(hikashop_level(1)){
			$this->values[] = JHTML::_('select.option', 'user', JText::_('HIKA_USER') );
			$this->values[] = JHTML::_('select.option', 'product', JText::_('PRODUCT'));
			$this->values[] = JHTML::_('select.option', 'category', JText::_('CATEGORY'));
			if(hikashop_level(2)){
				$this->values[] = JHTML::_('select.option', 'order', JText::_('HIKASHOP_ORDER'));
				$this->values[] = JHTML::_('select.option', 'item', JText::_('HIKASHOP_ITEM'));
				$this->values[] = JHTML::_('select.option', 'entry', JText::_('HIKASHOP_ENTRY'));
			}

			if($this->externalValues == null) {
				$this->externalValues = array();
				JPluginHelper::importPlugin('hikashop');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onTableFieldsLoad', array( &$this->externalValues ) );
				foreach($this->externalValues as $externalValue) {
					if(!empty($externalValue->table) && substr($externalValue->value, 0, 4) != 'plg.')
						$externalValue->value = 'plg.' . $externalValue->value;
					$this->values[] = JHTML::_('select.option', $externalValue->value, $externalValue->text);
				}
			}
		}
	}
	function display($map, $value, $form=false, $optionsArg=''){
		$this->load($form);
		$options ='class="inputbox" size="1"';
		if(!$form){
			$options.=' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options.$optionsArg, 'value', 'text', $value);
	}
}
