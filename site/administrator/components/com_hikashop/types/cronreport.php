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
class hikashopCronreportType{
	function hikashopCronreportType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('HIKA_NONE'));
		$this->values[] = JHTML::_('select.option', '1',JText::_('EACH_TIME'));
		$this->values[] = JHTML::_('select.option', '2',JText::_('ONLY_ACTION'));
		$js = "function updateCronReport(){";
			$js .= "cronsendreport = window.document.getElementById('cronsendreport').value;";
			$js .= "if(cronsendreport != 0) {window.document.getElementById('cronreportdetail').style.display = 'block';}else{window.document.getElementById('cronreportdetail').style.display = 'none';}";
		$js .= '}';
		$js .='window.hikashop.ready( function(){ updateCronReport(); });';
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="updateCronReport();"', 'value', 'text', (int) $value ,'cronsendreport');
	}
}
