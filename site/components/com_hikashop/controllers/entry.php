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
class entryController extends hikashopController{
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display = array('');
		$this->modify_views = array('form', 'edit','newentry','save');
		$this->add = array();
		$this->modify = array();
		$this->delete = array();
		if(!$skip){
			$this->registerDefaultTask('edit');
		}
	}

	function form() {
		return $this->edit();
	}

	function newentry(){
		JRequest::setVar( 'layout', 'newentry'  );
		return $this->display();
	}

	function save(){

		global $Itemid;
		$url = 'checkout';
		if(!empty($Itemid)){
			$url.='&Itemid='.$Itemid;
		}
		$app = JFactory::getApplication();

		$fieldClass = hikashop_get('class.field');
		$null = null;
		$entriesData = $fieldClass->getInput('entry',$null);

		$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields',null);
		$ok = true;

		if(empty($entriesData)){
			$app->redirect( hikashop_completeLink('entry',false,true) );
		}

		$cartClass = hikashop_get('class.cart');
		$fields =& $fieldClass->getData('frontcomp','entry');
		$cartClass->addToCartFromFields($entriesData,$fields);

		$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields',$entriesData);
		$app->redirect( hikashop_completeLink($url,false,true) );
	}
}
