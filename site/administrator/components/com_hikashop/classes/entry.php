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
class hikashopEntryClass extends hikashopClass{
	var $tables = array('entry');
	var $pkeys = array('entry_id');

	function saveForm(){
		$entry = new stdClass();
		$entry->entry_id = hikashop_getCID('entry_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['entry'] as $column => $value){
			hikashop_secureField($column);
			$entry->$column = strip_tags($value);
		}

		$status = $this->save($entry);
		if(JRequest::getVar('tmpl','')=='component'){
			if($status){
				$url = hikashop_completeLink('order&task=edit&cid='.$entry->order_id,false,true);
				echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
				exit;
			}else{
				$app = JFactory::getApplication();
				if(version_compare(JVERSION,'1.6','<')){
					$session =& JFactory::getSession();
					$session->set('application.queue', $app->_messageQueue);
				}
				echo '<html><head><script type="text/javascript">javascript: history.go(-1);</script></head><body></body></html>';
				exit;
			}
		}
		return $status;
	}
}
