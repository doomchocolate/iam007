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
class CharacteristicController extends hikashopController{
	var $type='characteristic';
	function __construct(){
		parent::__construct();
		$this->display[]='selectcharacteristic';
		$this->display[]='usecharacteristic';
		$this->modify_views[]='editpopup';
		$this->modify[]='addcharacteristic';
	}
	function addcharacteristic(){
		$class = hikashop_get('class.characteristic');
		$status = $class->saveForm();
		JRequest::setVar('cid',$status);
		JRequest::setVar( 'layout', 'addcharacteristic'  );
		return parent::display();
	}

	function editpopup(){
		JRequest::setVar( 'layout', 'editpopup'  );
		return parent::display();
	}

	function selectcharacteristic(){
		JRequest::setVar( 'layout', 'selectcharacteristic'  );
		return parent::display();
	}
	function usecharacteristic(){
		JRequest::setVar( 'layout', 'usecharacteristic'  );
		return parent::display();
	}
}
