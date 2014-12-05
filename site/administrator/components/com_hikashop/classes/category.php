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
class hikashopCategoryClass extends hikashopClass{
	var $tables = array('taxation','product_category','category');
	var $pkeys = array('','category_id','category_id');
	var $namekeys = array('category_namekey','','');
	var $parent = 'category_parent_id';
	var $toggle = array('category_published'=>'category_id');
	var $type = 'product';
	var $query = '';
	var $parentObject = '';

	function setType($type){
		$this->type = $type;
	}

	function get($element,$withimage=false){
		if(in_array($element,array('product','status','tax','manufacturer'))){
			$this->getMainElement($element);
		}
		if($withimage){
			$query = 'SELECT a.*,b.* FROM '.hikashop_table(end($this->tables)).' AS a LEFT JOIN '.hikashop_table('file').' AS b ON a.category_id=b.file_ref_id AND b.file_type=\'category\' WHERE a.category_id = '.(int)$element.' LIMIT 1';
			$this->database->setQuery($query);
			return $this->database->loadObject();
		}

		return parent::get($element);
	}

	function addAlias(&$element){
		if(empty($element->category_alias)){
			$element->alias = $element->category_name;
		}else{
			$element->alias = $element->category_alias;
		}
		$config = JFactory::getConfig();
		if(!$config->get('unicodeslugs')){
			$lang = JFactory::getLanguage();
			$element->alias = $lang->transliterate($element->alias);
		}
		$app = JFactory::getApplication();
		if(method_exists($app,'stringURLSafe')){
			$element->alias = $app->stringURLSafe(strip_tags($element->alias));
		}else{
			$element->alias = JFilterOutput::stringURLSafe(strip_tags($element->alias));
		}
	}

	function saveForm(){

		$category_id = hikashop_getCID('category_id');
		if($category_id){
			$oldCategory = $this->get($category_id);
		}
		$fieldsClass = hikashop_get('class.field');
		$element = $fieldsClass->getInput('category',$oldCategory);
		if(empty($element)){
			return false;
		}

		$main = JRequest::getVar( 'main_category', 0, '', 'int' );
		if($main){
			$element->category_parent_id = 0;
		}else{
			$element->category_type='';
		}
		$category_description = JRequest::getVar('category_description','','','string',JREQUEST_ALLOWRAW);
		$element->category_description = $category_description;
		$class = hikashop_get('helper.translation');
		$class->getTranslations($element);

		$config =& hikashop_config();
		if($config->get('alias_auto_fill',1) && empty($element->category_alias)){
			$this->addAlias($element);
			$element->category_alias = $element->alias;
			unset($element->alias);
		}

		$status = $this->save($element);

		if(!$status){
			JRequest::setVar( 'fail', $element  );
		}else{
			$class->handleTranslations('category',$status,$element);
			$class = hikashop_get('class.file');
			$class->storeFiles('category',$status);
		}
		return $status;
	}

	function save(&$element,$ordering=true){
		$pkey = end($this->pkeys);
		$table = hikashop_table(end($this->tables));
		$recalculate = false;
		$new = true;
		if(!empty($element->$pkey)){
			$new = false;
			$old = $this->get($element->$pkey);

			if(isset($element->category_parent_id)){
				$newParentElement = $this->get($element->category_parent_id);
				if( $old->category_parent_id != $element->category_parent_id){

					if($element->category_parent_id == $element->$pkey){
						return false;
					}

					if(($newParentElement->category_left > $old->category_left) && ($newParentElement->category_right < $old->category_right)){
						return false;
					}
					$recalculate = true;
				}
				if(!empty($newParentElement->category_type)&&$newParentElement->category_type!='root'){
					$element->category_type = $newParentElement->category_type;
				}
				if(!empty($element->category_site_id) && $newParentElement->category_type=='root'){
					$element->category_site_id = '';
				}
			}
			if(empty($element->category_type)){
				if(!empty($old->category_type)){
					if($old->category_type=='root'){

						$element->category_type = $this->type;
					}else{
						$element->category_type = $old->category_type;
					}
				}else{
					$element->category_type = $this->type;
				}
			}
			$element->category_modified=time();
		}else{
			if(empty($element->category_parent_id)){
				$query='SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' LIMIT 1;';
				$this->database->setQuery($query);
				$element->category_parent_id=$this->database->loadResult();
				$element->category_namekey=$element->category_type;
				$element->category_depth = 1;
			}
			$newParentElement = $this->get($element->category_parent_id);
			if(empty($element->category_type) && $newParentElement->category_type!='root'){
				$element->category_type = $newParentElement->category_type;
			}

			if(empty($element->category_type)){
				$element->category_type = $this->type;
			}

			if(empty($element->category_site_id) && $newParentElement->category_type!='root'){
				$element->category_site_id = $newParentElement->category_site_id;
			}

			$element->category_created=$element->category_modified=time();
			if(empty($element->category_namekey)){
				$element->category_namekey=$newParentElement->category_type.'_'.$element->category_created.'_'.rand();
			}
			if(!isset($element->category_published)){
				$element->category_published = 1;
			}

		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if($new){
			$dispatcher->trigger( 'onBeforeCategoryCreate', array( & $element, & $do) );
		}else{
			$dispatcher->trigger( 'onBeforeCategoryUpdate', array( & $element, & $do) );
		}
		if(!$do){
			return false;
		}

		$status = parent::save($element);

		if(!$status){
			return false;
		}
		if($new){
			$dispatcher->trigger( 'onAfterCategoryCreate', array( & $element ) );
		}else{
			$dispatcher->trigger( 'onAfterCategoryUpdate', array( & $element, ) );
		}

		if(empty($element->$pkey)){
			$element->$pkey = $status;
			if($ordering){
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'category_id';
				$orderClass->table = 'category';
				$orderClass->groupMap = 'category_parent_id';
				$orderClass->groupVal = $element->category_parent_id;
				$orderClass->orderingMap = 'category_ordering';
				$orderClass->reOrder();
			}
		}
		$filter = '';
		if($new){
			$query = 'UPDATE '.$table.' SET category_right = category_right + 2 WHERE category_right >= '.$newParentElement->category_right.$filter;
			$this->database->setQuery($query);
			$this->database->query();
			$query = 'UPDATE '.$table.' SET category_left = category_left + 2 WHERE category_left >= '.$newParentElement->category_right.$filter;
			$this->database->setQuery($query);
			$this->database->query();
			$query = 'UPDATE '.$table.' SET category_left = '.$newParentElement->category_right.', category_right = '.($newParentElement->category_right+1).', category_depth = '.($newParentElement->category_depth+1).' WHERE '.$pkey.' = '.$status.' LIMIT 1';
			$this->database->setQuery($query);
			$this->database->query();
		}elseif($recalculate){
			$query = 'SELECT category_left,category_right,category_depth,'.$pkey.',category_parent_id FROM '.$table.$filter.' ORDER BY category_left ASC';
			$this->database->setQuery($query);
			$categories = $this->database->loadObjectList();
			$root = null;
			$this->categories = array();
			foreach($categories as $cat){
				$this->categories[$cat->category_parent_id][]=$cat;
				if(empty($cat->category_parent_id)){
					$root = $cat;
				}
			}

			$this->rebuildTree($root,0,1);

			if($element->category_type=='status' && !empty($old->category_type)){
				$this->database->setQuery('UPDATE '.hikashop_table('config').' SET config_value = REPLACE(config_value,'.$this->database->Quote($old->category_type).','.$this->database->Quote($element->category_type).')');
				$this->database->query();
				$this->database->setQuery('UPDATE '.hikashop_table('payment').' SET payment_params = REPLACE(payment_params,'.$this->database->Quote(strlen($old->category_type).':"'.$old->category_type).','.$this->database->Quote(strlen($element->category_type).':"'.$element->category_type).')');
				$this->database->query();
			}

		}
		return $status;
	}

	function delete(&$elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}
		JArrayHelper::toInteger($elements);
		$status = true;
		$pkey = end($this->pkeys);
		$table = hikashop_table(end($this->tables));
		$parent = $this->parent;

		$parentIds = array();

		$ids = array();

		$products=array();

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do=true;
		$dispatcher->trigger( 'onBeforeCategoryDelete', array( & $elements, & $do) );
		if(!$do){
			return false;
		}

		foreach($elements as $element){
			if(!$status) continue;
			$data = $this->get($element);
			if(empty($data)) continue;
			if(in_array($data->category_namekey,array('root','product','tax','status','created','confirmed','cancelled','refunded','shipped','manufacturer'))){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('DEFAULT_CATEGORIES_DELETE_ERROR'),'error');
				$status=false;
				continue;
			}

			$ids[]=$element;
			$parentIds[$data->category_parent_id]=$data->category_parent_id;

			if($data->category_type=='product'){
				$query = 'SELECT product_id FROM '.hikashop_table('product_category').' WHERE category_id='.$element;
				$this->database->setQuery($query);
				if(!HIKASHOP_J25){
					$products = array_merge($products,$this->database->loadResultArray());
				} else {
					$products = array_merge($products,$this->database->loadColumn());
				}
			}

			if(!empty($data->category_type)){
				$this->type = $data->category_type;
			}
			$filter = '';

			if($data->category_right - $data->category_left != 1 ){
				$query = 'UPDATE '.$table.' SET '.$parent.' = '.$data->$parent.' WHERE '.$parent.' = '.$element;
				$this->database->setQuery($query);
				$status = $status && $this->database->query();

				$query = 'UPDATE '.$table.' SET category_depth = category_depth-1, category_left=category_left-1, category_right=category_right-1 WHERE category_left > '.$data->category_left.' AND category_right < '.$data->category_right.$filter;
				$this->database->setQuery($query);
				$status = $status && $this->database->query();
			}//endif

			$query = 'UPDATE '.$table.' SET category_right=category_right-2 WHERE category_right > '.$data->category_right.$filter;
			$this->database->setQuery($query);
			$status = $status && $this->database->query();

			$query = 'UPDATE '.$table.' SET category_left=category_left-2 WHERE category_left > '.$data->category_right.$filter;
			$this->database->setQuery($query);
			$status = $status && $this->database->query();

			$status = $status && parent::delete($element);
		}

		if($status){
			$dispatcher->trigger( 'onAfterCategoryDelete', array( & $elements ) );

			if(!empty($parentIds)){
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'category_id';
				$orderClass->table = 'category';
				$orderClass->groupMap = 'category_parent_id';
				$orderClass->orderingMap = 'category_ordering';
				foreach($parentIds as $parentId){
					$orderClass->groupVal = $parentId;
					$orderClass->reOrder();
				}
			}

			if(!empty($products)){
				$query='SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$products).')';
				$this->database->setQuery($query);
				$entries=$this->database->loadObjectList();
				foreach($entries as $entry){
					if(in_array($entry->product_id,$products)){
						$key = array_search($entry->product_id,$products);
						unset($products[$key]);
					}
				}
				if(!empty($products)){
					$root = 'product';
					$this->getMainElement($root);
					$insert = array();
					foreach($products as $new){
						$insert[]='('.(int)$root.','.$new.')';
					}
					$query = 'INSERT IGNORE INTO '.hikashop_table('product_category').' (category_id,product_id) VALUES '.implode(',',$insert).';';
					$this->database->setQuery($query);
					$this->database->query();
					$orderClass = hikashop_get('helper.order');
					$orderClass->pkey = 'product_category_id';
					$orderClass->table = 'product_category';
					$orderClass->groupMap = 'category_id';
					$orderClass->orderingMap = 'ordering';
					$orderClass->groupVal = $root;
					$orderClass->reOrder();
				}
			}

			$class = hikashop_get('class.file');
			$class->deleteFiles('category',$elements);
			$class = hikashop_get('helper.translation');
			$class->deleteTranslations('category',$elements);
		}
		return $status;
	}

	function getRoot(){
		static $id = 0;
		if(empty($id)){
			$this->database->setQuery('SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' LIMIT 1');
			$id = $this->database->loadResult();
		}
		return $id;
	}

	function getMainElement(&$element){
		$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_parent_id='.$this->getRoot().' AND category_type='.$this->database->Quote($element).' LIMIT 1';
		$this->database->setQuery($query);
		$element = (int)$this->database->loadResult();
	}

	function getChilds($element, $all=false,$additionalFilters=array(),$order='',$start=0,$value=20,$category_image=false,$select = 'a.*'){
		return $this->getChildren($element,$all,$additionalFilters,$order,$start,$value,$category_image,$select);
	}

	function getChildren($element, $all=false,$additionalFilters=array(),$order='',$start=0,$value=20,$category_image=false,$select = 'a.*'){
		$filters = array();
		$this->category_used = null;

		if(empty($element)){
			$element=$this->getRoot();
		}

		if(is_array($element)){

			if(count($element)>1){
				if($all){
					$this->database->setQuery('SELECT category_left,category_right FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$element).')');
					$leafs = $this->database->loadObjectList();
					$conditions = array();
					foreach($leafs as $v){
						$conditions[] = '(a.category_left > '.$v->category_left.' AND a.category_right < '.$v->category_right.')';
					}
					$filters[] = '('.implode(' OR ',$conditions).')';
					$this->type=0;
				}else{
					foreach($element as $k => $v){
						$element[$k]=(int)$v;
					}

					$filters[] = 'a.category_parent_id IN ('.implode(',',$element).')';
					$this->type=0;
				}
			}else{
				$element = (int) array_pop($element);
			}
		}elseif(!is_numeric($element)){
			$this->getMainElement($element);
		}
		if(is_numeric($element)){
			if($all){
				$data = $this->get($element);
				if(!empty($data)){
					if ( ($data->category_left +1) == $data->category_right ) return array();
					$filters[] = 'a.category_left > '.$data->category_left;
					$filters[] = 'a.category_right < '.$data->category_right;
					if(!empty($data->category_type) && $data->category_type != 'root'){
						$this->type = $data->category_type;
					}
				}
			}else{
				$filters[] = 'a.category_parent_id = '.$element;
				$this->type=0;
			}
		}elseif(!is_array($element)){
			$this->type = $element;
		}
		if(is_numeric($element)){
			$this->category_used = $element;
		}elseif(is_array($element)){
			$this->category_used = (int) array_pop($element);
		}

		if(!empty($this->type)){
			if($this->type == 'product')
				$filters[] = '(a.category_type = '.$this->database->Quote($this->type).' OR a.category_type = '.$this->database->Quote('vendor').')';
			else
				$filters[] = 'a.category_type = '.$this->database->Quote($this->type);
		}
		if(!empty($additionalFilters)){
			$filters = array_merge($filters,$additionalFilters);
		}
		$leftjoin = '';


		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$filters[]='a.category_published=1';
			hikashop_addACLFilters($filters,'category_access','a');
		}

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeCategoryListingLoad', array( & $filters, &$order, &$this->parentObject) );
		if(!empty($filters)){
			$filters = ' WHERE '.implode(' AND ',$filters);
		}else{
			$filters = '';
		}

		$this->query = ' FROM '.hikashop_table(end($this->tables)).' AS a'.$filters;
		$this->database->setQuery('SELECT '.$select.' FROM '.hikashop_table(end($this->tables)).' AS a'.$leftjoin.$filters.$order,(int)$start,(int)$value);
		$rows = $this->database->loadObjectList();
		if($category_image && !empty($rows)){
			$ids = array();
			foreach($rows as $row){
				$ids[]=$row->category_id;
			}
			$this->database->setQuery('SELECT * FROM '.hikashop_table('file').' WHERE file_type=\'category\' AND file_ref_id IN ('.implode(',',$ids).')');
			$images = $this->database->loadObjectList();

			foreach($rows as $k => $cat){
				if(!empty($images)){
					foreach($images as $img){
						if($img->file_ref_id==$cat->category_id){
							foreach(get_object_vars($img) as $key => $val){
								$rows[$k]->$key = $val;
							}
							break;
						}
					}
				}
				if(!isset($rows[$k]->file_name)){
					$rows[$k]->file_name = $row->category_name;
				}
			}
		}
		return $rows;
	}

	function loadAllWithTrans($type='',$all=false,$filters=array(),$order=' ORDER BY category_ordering ASC',$start=0,$value=500,$category_image=false){
		static $data = array();
		static $queries = array();
		if(is_array($type)) $typeQ = implode('_',$type);
		else $typeQ = $type;
		$key = $typeQ.'_'.(int)$all.'_'.$order.'_'.implode('_',$filters).'_'.$start.'_'.$value.'_'.(int)$category_image;
		if(!isset($data[$key])){
			$rows = $this->getChilds($type,$all,$filters,$order,$start,$value,$category_image);
			$queries[$key]=$this->query;
			if(!empty($rows)){
				$ids = array();
				foreach($rows as $id => $oneRow){
					$ids[]=$oneRow->category_id;
				}
				$translationHelper = hikashop_get('helper.translation');
				if($translationHelper->isMulti()){
					$user = JFactory::getUser();
					$locale = $user->getParam('language');
					if(empty($locale)){
						$config = JFactory::getConfig();
						if(!HIKASHOP_J16){
							$locale = $config->getValue('config.language');
						} else {
							$locale = $config->get('language');
							if($locale === null)
								$locale = $config->get('config.language');
						}
					}
					$lgid = $translationHelper->getId($locale);
					$trans_table = 'jf_content';
					if($translationHelper->falang){
						$trans_table = 'falang_content';
					}
					$query = 'SELECT * FROM '.hikashop_table($trans_table,false).' AS b WHERE b.reference_id IN ('.implode(',',$ids).') AND b.reference_table=\'hikashop_category\' AND b.reference_field=\'category_name\' AND b.published=1 AND b.language_id='.$lgid;
					$this->database->setQuery($query);
					$translations = $this->database->loadObjectList();
					if(!empty($translations)){
						foreach($translations as $translation){
							foreach($rows as $k => $row){
								if($row->category_id==$translation->reference_id){
									$rows[$k]->translation = $translation->value;
									break;
								}
							}
						}
					}
				}
				foreach($rows as $k => $category){
					if(!isset($category->translation)){
						$val = str_replace(array(' ',','),'_',strtoupper($category->category_name));
						$rows[$k]->translation = JText::_($val);
						if($val==$rows[$k]->translation){
							$rows[$k]->translation = $category->category_name;
						}
					}
				}

			}
			$data[$key] =& $rows;
		}else{
			$this->query=$queries[$key];
		}
		return $data[$key];
	}

	function getParents( $element ,$exclude=0){
		if(empty($element)) return array();
		static $results = array();
		$key = sha1(serialize($element).'_'.$exclude);
		if(!isset($results[$key])){
			$where='';
			if($exclude){
				$el = $this->get($exclude);
				if($el){
					$where=' AND b.category_left>='.$el->category_left.' AND b.category_right<='.$el->category_right;
				}else{
					$where=' AND b.category_id!='.(int)$exclude;
				}
			}
			if(is_array($element)){
				$and='AND a.category_id IN (';
				foreach($element as $cat){
					if(is_object($cat)){
						$and.=(int)$cat->category_id.',';
					}else{
						$and.=(int)$cat.',';
					}
				}
				$and=substr($and,0,-1);
				$and.=')';
			}else{
				$and='AND a.category_id='.(int)$element;
			}
			$query = 'SELECT b.* FROM '.hikashop_table(end($this->tables)).' AS a LEFT JOIN '.
			hikashop_table(end($this->tables)).' AS b ON a.category_left >= b.category_left WHERE '.
			'b.category_right >= a.category_right '.$and.$where.' GROUP BY b.category_id ORDER BY b.category_left';
			$this->database->setQuery($query);
			$results[$key] = $this->database->loadObjectList();
		}
		return $results[$key];
	}

	function getNamekey($element){
		return $element->category_parent_id.'_'.preg_replace('#[^a-z0-9]#i','',$element->category_name).'_'.rand();
	}

	function rebuildTree($element,$depth,$left){
		$currentLeft = $left;
		$currentDepth = $depth;
		$pkey = end($this->pkeys);
		if(!empty($this->categories[$element->$pkey])){
			$depth++;
			foreach($this->categories[$element->$pkey] as $child){
				$left++;
				list($depth,$left) = $this->rebuildTree($child,$depth,$left);
			}
			$depth--;
		}
		$left++;
		if($currentLeft != $element->category_right || $currentLeft != $element->category_left || $currentDepth!=$element->category_depth){
			$query = 'UPDATE '.hikashop_table(end($this->tables)). ' SET category_left='.$currentLeft.', category_right='.$left.', category_depth='.$currentDepth.' WHERE '.$pkey.' = '.$element->$pkey.' LIMIT 1';
			$this->database->setQuery($query);
			$this->database->query();
		}
		return array($depth,$left);
	}

	function &getProductsIn($category_id, &$products, $subcategories = true) {
		$ret = array();
		if(empty($products))
			return $ret;

		$products_ids = array();
		foreach($products as $product) {
			if(isset($product->product_id))
				$products_ids[] = (int)$product->product_id;
			else
				$products_ids[] = (int)$product;
		}

		$db = JFactory::getDBO();
		if($subcategories) {
			$category = $this->get($category_id);
			if(empty($category))
				return $ret;

			$query = 'SELECT product.product_id '.
				' FROM '.hikashop_table('product').' AS product '.
				' INNER JOIN '.hikashop_table('product_category').' AS product_category ON (product.product_id = product_category.product_id) OR (product.product_parent_id = product_category.product_id) '.
				' INNER JOIN '.hikashop_table('category').' AS category ON category.category_id = product_category.category_id '.
				' WHERE category.category_left >= ' . (int)$category->category_left . ' AND category.category_right <= ' . (int)$category->category_right . ' AND product.product_id IN ('.implode(',',$products_ids).')';
		} else {
			$query = 'SELECT product.product_id '.
				' FROM '.hikashop_table('product').' AS product '.
				' INNER JOIN '.hikashop_table('product_category').' AS product_category ON (product.product_id = product_category.product_id) OR (product.product_parent_id = product_category.product_id) '.
				' WHERE product_category.category_id = ' . (int)$category_id . ' AND product.product_id IN ('.implode(',',$products_ids).')';
		}

		$db->setQuery($query);
		if(!HIKASHOP_J25)
			$category_products = $db->loadResultArray();
		else
			$category_products = $db->loadColumn();

		return $category_products;
	}

	public function &getList($type = 'product', $root = 0, $getRoot = true) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$select = 'SELECT a.*';
		$table = ' FROM '.hikashop_table('category').' AS a ';
		$where = array();
		if(!empty($type)) {
			if(is_array($type)) {
				if($getRoot && !in_array('root', $type))
					$type[] = 'root';
				$types = array();
				foreach($type as $t) {
					$types[] = $db->Quote($t);
				}
				$where[] = 'a.category_type IN ('.implode(',',$types).')';
			} else {
				if($getRoot) {
					$where[] = 'a.category_type IN ('.$db->Quote($type).',\'root\')';
				} else {
					$where[] = 'a.category_type = '.$db->Quote($type);
				}
			}
		}

		if((int)$root > 0) {
			$table .= ' INNER JOIN '.hikashop_table('category').' AS b On b.category_id = ' . (int)$root . ' ';
			$where[] = 'a.category_left >= b.category_left AND a.category_right <= b.category_right';
		}

		if(!empty($where)) {
			$where = ' WHERE (' . implode($where,') AND ('). ')';
		} else {
			$where = '';
		}
		$db->setQuery($select . $table . $where . ' ORDER BY a.category_left ASC');
		$elements = $db->loadObjectList();

		foreach($elements as &$element) {
			if(empty($element->value)){
				$val = str_replace(' ', '_', strtoupper($element->category_name));
				$element->value = JText::_($val);
				if($val == $element->value) {
					$element->value = $element->category_name;
				}
			}
			$element->category_name = $element->value;

			if($element->category_namekey == 'root') {
				$element->category_parent_id = -1;
			}
			unset($element);
		}
		return $elements;
	}
}
