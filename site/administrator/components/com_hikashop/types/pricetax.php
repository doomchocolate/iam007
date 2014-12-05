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
class hikashopPricetaxType{
	function load($inherit=false){
		$this->values = array();
		if($inherit){
			$this->values[] = JHTML::_('select.option', 3,JText::_('HIKA_INHERIT') );
		}
		$this->values[] = JHTML::_('select.option', 0,JText::_('NO_TAX') );
		$this->values[] = JHTML::_('select.option', 1,JText::_('WITH_TAX'));
		$this->values[] = JHTML::_('select.option', 2,JText::_('DISPLAY_BOTH_TAXES'));
	}
	function display($map,$value,$inherit=false){
		$this->load($inherit);
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}
