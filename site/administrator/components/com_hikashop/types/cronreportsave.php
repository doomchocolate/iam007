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
class hikashopCronreportsaveType{
	function hikashopCronreportsaveType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('HIKASHOP_NO'));
		$this->values[] = JHTML::_('select.option', '1',JText::_('SIMPLIFIED_REPORT'));
		$this->values[] = JHTML::_('select.option', '2',JText::_('DETAILED_REPORT'));
		$js = "function updateCronReportSave(){";
			$js .= "cronsavereport = window.document.getElementById('cronsavereport').value;";
			$js .= "if(cronsavereport != 0) {window.document.getElementById('cronreportsave').style.display = 'block';}else{window.document.getElementById('cronreportsave').style.display = 'none';}";
		$js .= '}';
		$js .='window.hikashop.ready( function(){ updateCronReportSave(); });';
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="updateCronReportSave();"', 'value', 'text', (int) $value ,'cronsavereport');
	}
}
