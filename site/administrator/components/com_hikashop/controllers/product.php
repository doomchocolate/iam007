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
class ProductController extends hikashopController {
	var $toggle = array('product_published' => 'product_id');
	var $type ='product';
	var $pkey = 'product_category_id';
	var $main_pkey = 'product_id';
	var $table = 'product_category';
	var $groupMap = 'category_id';
	var $orderingMap ='ordering';
	var $groupVal = 0;

	function __construct($config = array()){
		parent::__construct($config);
		$this->display=array(
			'unpublish','publish',
			'listing','show','cancel',
			'selectcategory','addcategory',
			'selectrelated','addrelated',
			'getprice','addimage','selectimage','addfile','selectfile',
			'variant','updatecart','export',
			'galleryimage','galleryselect',
			'selection','useselection',
			'getTree','findTree',''
		);
		$this->modify[]='managevariant';
		$this->modify_views[]='edit_translation';
		$this->modify_views[]='priceaccess';
		$this->modify[]='save_translation';
		$this->modify[]='copy';
		$this->modify_views[] = 'unpublish';
		$this->modify_views[] = 'publish';
		if(JRequest::getInt('variant')){
			$this->publish_return_view = 'variant';
		}
	}

	function priceaccess(){
		JRequest::setVar('layout', 'priceaccess');
		return parent::display();
	}

	function edit_translation(){
		JRequest::setVar('layout', 'edit_translation');
		return parent::display();
	}

	function save_translation(){
		$product_id = hikashop_getCID('product_id');
		$class = hikashop_get('class.product');
		$element = $class->get($product_id);
		if(!empty($element->product_id)){
			$class = hikashop_get('helper.translation');
			$class->getTranslations($element);
			$class->handleTranslations('product',$element->product_id,$element);
		}
		$document= JFactory::getDocument();
		$document->addScriptDeclaration('window.top.hikashop.closeBox();');
	}

	function managevariant(){
		$id = $this->store();
		if($id){
			JRequest::setVar('cid',$id);
			$this->variant();
		}else{
			$this->edit();
		}
	}

	function updatecart(){
		echo '<textarea style="width:100%" rows="5"><a class="hikashop_html_add_to_cart_link" href="'.HIKASHOP_LIVE.'index.php?option='.HIKASHOP_COMPONENT.'&ctrl=product&task=updatecart&quantity=1&checkout=1&product_id='.JRequest::getInt('cid').'">'.JText::_('ADD_TO_CART').'</a></textarea>';
	}

	function save(){
		$result = parent::store();
		if(!$result){
			return $this->edit();
		}
		if(JRequest::getBool('variant')){
			JRequest::setVar('cid',JRequest::getInt('parent_id'));
			$this->variant();
		}else{
			$this->listing();
		}
	}

	function copy(){
		$products = JRequest::getVar( 'cid', array(), '', 'array' );
		$result = true;
		if(!empty($products)){
			$helper = hikashop_get('helper.import');
			foreach($products as $product){
				if(!$helper->copyProduct($product)){
					$result=false;
				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
		}
		return $this->listing();
	}

	function variant(){
		JRequest::setVar('layout', 'variant');
		return parent::display();
	}

	function export(){
		JRequest::setVar('layout', 'export');
		return parent::display();
	}

	function orderdown(){
		$this->getGroupVal();
		return parent::orderdown();
	}

	function orderup(){
		$this->getGroupVal();
		return parent::orderup();
	}

	function saveorder(){
		$this->getGroupVal();
		return parent::saveorder();
	}

	function getGroupVal(){
		$app = JFactory::getApplication();
		$this->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.product.filter_id','filter_id',0,'string');
		if(!is_numeric($this->groupVal)){
			$class = hikashop_get('class.category');
			$class->getMainElement($this->groupVal);
		}
	}

	function selectcategory(){
		JRequest::setVar('layout', 'selectcategory');
		return parent::display();
	}

	function addcategory(){
		JRequest::setVar('layout', 'addcategory');
		return parent::display();
	}

	function selectrelated(){
		JRequest::setVar('layout', 'selectrelated');
		return parent::display();
	}

	function addrelated(){
		JRequest::setVar('layout', 'addrelated');
		return parent::display();
	}

	function addimage(){
		$this->_saveFile();
		JRequest::setVar('layout', 'addimage');
		return parent::display();
	}

	function selectimage(){
		JRequest::setVar('layout', 'selectimage');
		return parent::display();
	}

	function addfile(){
		$this->_saveFile();
		JRequest::setVar('layout', 'addfile');
		return parent::display();
	}

	function _saveFile(){
		$file = new stdClass();
		$file->file_id = hikashop_getCID('file_id');
		$formData = JRequest::getVar('data', array(), '', 'array');
		foreach($formData['file'] as $column => $value){
			hikashop_secureField($column);
			$file->$column = strip_tags($value);
		}

		$filemode = 'upload';
		if(!empty($formData['filemode']))
			$filemode = $formData['filemode'];

		$class = hikashop_get('class.file');

		switch($filemode) {
			case 'path':
				$file->file_path = $formData['filepath'];
				break;
			case 'upload':
			default:
				if(empty($file->file_id)){
					$ids = $class->storeFiles($file->file_type,$file->file_ref_id);
					if(is_array($ids)&&!empty($ids)){
						$file->file_id = array_shift($ids);
						if(isset($file->file_path))unset($file->file_path);
					}else{
						return false;
					}
				}
				break;
		}

		if(isset($file->file_ref_id) && empty($file->file_ref_id)){
			unset($file->file_ref_id);
		}

		if(isset($file->file_limit)) {
			$limit = (int)$file->file_limit;
			if($limit == 0 && $file->file_limit !== 0 && $file->file_limit != '0') {
				$file->file_limit = -1;
			} else {
				$file->file_limit = $limit;
			}
		}

		$status = $class->save($file);
		if(empty($file->file_id)) {
			$file->file_id = $status;
		}
		JRequest::setVar('cid',$file->file_id);
		return true;
	}

	function selectfile(){
		JRequest::setVar('layout', 'selectfile');
		return parent::display();
	}

	function galleryimage() {
		JRequest::setVar('layout', 'galleryimage');
		return parent::display();
	}

	function galleryselect(){
		$formData = JRequest::getVar('data', array(), '', 'array' );
		$filesData = JRequest::getVar('files', array(), '', 'array');

		$fileClass = hikashop_get('class.file');
		$file = new stdClass();
		foreach($formData['file'] as $column => $value){
			hikashop_secureField($column);
			$file->$column = strip_tags($value);
		}
		$file->file_path = reset($filesData);
		if(isset($file->file_ref_id) && empty($file->file_ref_id)){
			unset($file->file_ref_id);
		}
		$status = $fileClass->save($file);
		if(empty($file->file_id)) {
			$file->file_id = $status;
		}
		JRequest::setVar('cid', $file->file_id);

		JRequest::setVar('layout', 'addimage');
		return parent::display();
	}

	function getprice(){
		$price = JRequest::getVar('price');
		$productClass = hikashop_get('class.product');
		$price=hikashop_toFloat($price);
		$tax_id = JRequest::getInt('tax_id');
		$conversion = JRequest::getInt('conversion');
		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$main_tax_zone = explode(',',$config->get('main_tax_zone',1346));
		$newprice = $price;
		if(count($main_tax_zone)&&!empty($tax_id)&&!empty($price)&&!empty($main_tax_zone)){
			$function = 'getTaxedPrice';
			if($conversion) {
				$function = 'getUntaxedPrice';
			}
			$newprice = $currencyClass->$function($price,array_shift($main_tax_zone),$tax_id,5);
		}
		echo $newprice;
		exit;
	}

	function remove(){
		$cids = JRequest::getVar('cid', array(), '', 'array');
		$variant = JRequest::getInt( 'variant' );
		$class = hikashop_get('class.'.$this->type);
		$num = $class->delete($cids);
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		if($variant){
			JRequest::setVar('cid',JRequest::getInt('parent_id'));
			return $this->variant();
		}
		return $this->listing();
	}

	function selection() {
		JRequest::setVar('layout', 'selection');
		return parent::display();
	}

	function useselection() {
		JRequest::setVar('layout', 'useselection');
		return parent::display();
	}

	function getTree() {
		$category_id = JRequest::getInt('category_id', 0);
		$displayMode = JRequest::getVar('display', '');
		$productClass = hikashop_get('class.product');
		$elements = $productClass->getTreeList($category_id, 1, true, $displayMode);
		if(!empty($elements) && !empty($elements[0]->data)) {
			$content = $elements[0]->data;
			echo json_encode($content);
			exit;
		}
		echo '[]';
		exit;
	}

	function findTree() {
		$search = JRequest::getVar('search', '');
		$category_id = JRequest::getInt('category_id', 0);
		if(!empty($search)) {
			$productClass = hikashop_get('class.product');
			$elements = $productClass->findTreeList($search, $category_id, true);
			echo json_encode($elements);
			exit;
		}
		echo '[]';
		exit;
	}
}
