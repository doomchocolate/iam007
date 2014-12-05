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
class BadgeViewBadge extends hikashopView {
	var $ctrl= 'badge';
	var $nameListing = 'HIKA_BADGES';
	var $nameForm = 'HIKA_BADGES';
	var $icon = 'badge';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.badge_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$database	= JFactory::getDBO();
		$filters = array();
		$searchMap = array('a.badge_id','a.badge_name','a.badge_position');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('badge').' AS a'.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'badge_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->elements->page){
			$discountIds = array();
			$categoryIds = array();

			foreach($rows as $row){
				if(!empty($row->badge_discount_id)) $discountIds[] = $row->badge_discount_id;
				if(!empty($row->badge_category_id)) $categoryIds[] = $row->badge_category_id;
			}
			if(!empty($discountIds)){
				$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE discount_id IN ('.implode(',',$discountIds).')';
				$database->setQuery($query);
				$discounts = $database->loadObjectList();

				foreach($rows as $k => $row){
					if(!empty($row->badge_discount_id)){
						$found = false;
						foreach($discounts as $discount){
							if($discount->discount_id==$row->badge_discount_id){
								foreach(get_object_vars($discount) as $field => $value){
									$rows[$k]->$field = $discount->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->discount_code=JText::_('DISCOUNT_NOT_FOUND');
						}
					}
				}
			}
			if(!empty($categoryIds)){
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$categoryIds).')';
				$database->setQuery($query);
				$categories = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->badge_category_id)){
						$found = false;
						foreach($categories as $category){
							if($category->category_id==$row->badge_category_id){
								foreach(get_object_vars($category) as $field => $value){
									$rows[$k]->$field = $category->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->category_name=JText::_('CATEGORY_NOT_FOUND');
						}
					}
				}
			}
		}

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$order = new stdClass();
		$order->ordering = true;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.badge_ordering'){
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_badge_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'check' => JText::_('HIKA_VALIDDELETEITEMS'), 'display' => hikashop_isAllowed($config->get('acl_badge_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}
	function form(){
		$badge_id = hikashop_getCID('badge_id');
		$class = hikashop_get('class.badge');
		if(!empty($badge_id)){
			$element = $class->get($badge_id,true);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->banner_published = 1;
			$task='add';
		}
		$database = JFactory::getDBO();
		if(!empty($element->badge_discount_id)){
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE discount_id = '.(int)$element->badge_discount_id;
			$database->setQuery($query);
			$discount = $database->loadObject();
			if(!empty($discount)){
				foreach(get_object_vars($discount) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->discount_code)){
			$element->discount_code = JText::_('DISCOUNT_NOT_FOUND');
		}
		if(!empty($element->badge_category_id)){
			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id = '.(int)$element->badge_category_id;
			$database->setQuery($query);
			$category = $database->loadObject();
			if(!empty($category)){
				foreach(get_object_vars($category) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->category_name)){
			$element->category_name = JText::_('CATEGORY_NOT_FOUND');
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&badge_id='.$badge_id);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);

		$js = "
		function hikashopSizeUpdate(keep_size){
			if(keep_size>0){
			 displayStatus ='none';
			}else{
			 displayStatus = '';
			}
			var el = document.getElementById('field_size');
			if(el){ el.style.display=displayStatus; }
		}
		window.hikashop.ready( function(){ hikashopSizeUpdate(".(int)@$element->badge_keep_size."); });
		";
		$document= JFactory::getDocument();
		$document->addScriptDeclaration($js);

		$this->assignRef('element',$element);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_badge',@$element->badge_id,$element);
			jimport('joomla.html.pane');
			$config =& hikashop_config();
			$multilang_display=$config->get('multilang_display','tabs');
			if($multilang_display=='popups') $multilang_display = 'tabs';
			$tabs = hikashop_get('helper.tabs');
			$this->assignRef('tabs',$tabs);
			$this->assignRef('transHelper',$transHelper);
		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$badge=hikashop_get('type.badge');
		$this->assignRef('badge',$badge);
		$this->assignRef('translation',$translation);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);
	}
}
