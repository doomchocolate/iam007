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
class DiscountViewDiscount extends hikashopView{
	var $type = '';
	var $ctrl= 'discount';
	var $nameListing = 'DISCOUNTS';
	var $nameForm = 'DISCOUNTS';
	var $icon = 'discount';
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = ' FROM '.hikashop_table('discount').' AS a';
		if(!empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
					break;
			}
		}
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page){
			$productIds = array();
			$categoryIds = array();
			$zoneIds = array();
			foreach($rows as $row){
				if(!empty($row->discount_product_id)) $productIds[] = $row->discount_product_id;
				if(!empty($row->discount_category_id)) $categoryIds[] = $row->discount_category_id;
				if(!empty($row->discount_zone_id)) $zoneIds[] = $row->discount_zone_id;
			}
			if(!empty($productIds)){
				$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$productIds).')';
				$database->setQuery($query);
				$products = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->discount_product_id)){
						$found = false;
						foreach($products as $product){
							if($product->product_id==$row->discount_product_id){
								foreach(get_object_vars($product) as $field => $value){
									$rows[$k]->$field = $product->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->product_name=JText::_('PRODUCT_NOT_FOUND');
						}
					}
				}
			}
			if(!empty($categoryIds)){
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$categoryIds).')';
				$database->setQuery($query);
				$categories = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->discount_category_id)){
						$found = false;
						foreach($categories as $category){
							if($category->category_id==$row->discount_category_id){
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
			if(!empty($zoneIds)){
				$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_id IN ('.implode(',',$zoneIds).')';
				$database->setQuery($query);
				$zones = $database->loadObjectList();
				foreach($rows as $k => $row){
					if(!empty($row->discount_zone_id)){
						$found = false;
						foreach($zones as $zone){
							if($zone->zone_id==$row->discount_zone_id){
								foreach(get_object_vars($zone) as $field => $value){
									$rows[$k]->$field = $zone->$field;
								}
								$found = true;
							}
						}
						if(!$found){
							$rows[$k]->zone_name_english=JText::_('ZONE_NOT_FOUND');
						}
					}
				}
			}
		}

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name' => 'copy','display'=>$manage),
			array('name' => 'addNew','display'=>$manage),
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_discount_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$discountType = hikashop_get('type.discount');
		$this->assignRef('filter_type',$discountType);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	public function selection($tpl = null){
		$this->listing($tpl, true);

		$elemStruct = array(
			'discount_id',
			'discount_code'
		);
		$this->assignRef('elemStruct', $elemStruct);

		$singleSelection = JRequest::getVar('single', false);
		$this->assignRef('singleSelection', $singleSelection);
	}

	public function useselection() {
		$selection = JRequest::getVar('cid', array(), '', 'array');
		$rows = array();
		$data = '';

		$elemStruct = array(
			'discount_id',
			'discount_code'
		);

		if(!empty($selection)) {
			JArrayHelper::toInteger($selection);
			$db = JFactory::getDBO();
			$query = 'SELECT a.* FROM '.hikashop_table('discount').' AS a  WHERE a.discount_id IN ('.implode(',',$selection).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = '{id:'.$v->user_id;
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d .= ','.$s.':"'. str_replace('"', '\"', $v->$s).'"';
					}
					$data[] = $d.'}';
				}
				$data = '['.implode(',', $data).']';
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);

		$confirm = JRequest::getVar('confirm', true, '', 'boolean');
		$this->assignRef('confirm', $confirm);
		if($confirm) {
			$js = 'window.hikashop.ready( function(){window.top.hikashop.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	function form(){
		$discount_id = hikashop_getCID('discount_id',false);
		if(!empty($discount_id)){
			$class = hikashop_get('class.discount');
			$element = $class->get($discount_id);
			$task='edit';
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$app = JFactory::getApplication();
				$type = $app->getUserState( $this->paramBase.".filter_type");
				if(!in_array($type,array('all','nochilds'))){
					$element->discount_type = $type;
				}else{
					$element->discount_type = 'discount';
				}
				$element->discount_published=1;
			}
			$task='add';
		}
		$database = JFactory::getDBO();
		if(!empty($element->discount_product_id)){
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id = '.(int)$element->discount_product_id;
			$database->setQuery($query);
			$product = $database->loadObject();
			if(!empty($product)){
				foreach(get_object_vars($product) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->product_name)){
			$element->product_name = JText::_('PRODUCT_NOT_FOUND');
		}
		if(!empty($element->discount_category_id)){
			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id = '.(int)$element->discount_category_id;
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
		if(!empty($element->discount_zone_id)){
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_id = '.(int)$element->discount_zone_id;
			$database->setQuery($query);
			$zone = $database->loadObject();
			if(!empty($zone)){
				foreach(get_object_vars($zone) as $key => $val){
					$element->$key = $val;
				}
			}
		}
		if(empty($element->zone_name_english)){
			$element->zone_name_english = JText::_('ZONE_NOT_FOUND');
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&discount_id='.$discount_id);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$discountType = hikashop_get('type.discount');
		$this->assignRef('element',$element);
		$this->assignRef('type',$discountType);
		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currency',$currencyType);
		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type='tax';
		$categoryType->field='category_id';
		$this->assignRef('categoryType',$categoryType);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
	}

	function select_coupon(){
		$badge = JRequest::getVar('badge','false');
		$this->assignRef('badge',$badge);
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if($badge!='false'){ $filters[]='a.discount_type="discount"'; }
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$query = ' FROM '.hikashop_table('discount').' AS a';
		if($badge=='false' && !empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
					break;
			}
		}
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements=new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => 'copy', 'task' => 'copy', 'alt' => JText::_('HIKA_COPY'),'display'=>$manage),
			array('name' => 'addNew','display'=>$manage),
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_discount_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		$discountType = hikashop_get('type.discount');
		$this->assignRef('filter_type',$discountType);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	function add_coupon(){
		$discounts = JRequest::getVar( 'cid', array(), '', 'array' );
		$rows = array();
		$filter='';
		$badge = JRequest::getVar( 'badge');
		if(!isset($badge)){ $badge='false'; }
		$this->assignRef('badge',$badge);
		if(!empty($discounts)){
			JArrayHelper::toInteger($discounts);
			$database	= JFactory::getDBO();
			if($badge=='false'){ $filter='AND discount_type="coupon"';}
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE discount_id IN ('.implode(',',$discounts).') '.$filter;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
		}
		$this->assignRef('rows',$rows);
		$document= JFactory::getDocument();
		if($badge=='false'){
			$js = "window.hikashop.ready( function() {
					var dstTable = window.parent.document.getElementById('coupon_listing');
					var srcTable = document.getElementById('result');
					for (var c = 0,m=srcTable.rows.length;c<m;c++){
						var rowData = srcTable.rows[c].cloneNode(true);
						dstTable.appendChild(rowData);
					}
					window.parent.hikashop.closeBox();
			});";
		}else{
			$js = "window.hikashop.ready( function() {
						var field = window.parent.document.getElementById('changeDiscount');
						var result = document.getElementById('result').innerHTML;
						field.innerHTML=result;
						window.parent.hikashop.closeBox();
				});";
		}
		$document->addScriptDeclaration($js);
	}

}
