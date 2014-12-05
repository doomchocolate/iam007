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
class WarehouseController extends hikashopController {
	var $type='warehouse';
	var $pkey = 'warehouse_id';
	var $table = 'warehouse';
	var $orderingMap ='warehouse_ordering';

	function __construct($config = array()) {
		parent::__construct($config);
		$this->display[]='selection';
		$this->modify[]='useselection';
	}
	function selection(){
		JRequest::setVar( 'layout', 'selection'  );
		return parent::display();
	}
	function useselection(){
		JRequest::setVar( 'layout', 'useselection'  );
		return parent::display();
	}
}
?>
