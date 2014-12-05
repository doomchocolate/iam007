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
class PluginsController extends hikashopController {
	var $type = 'plugins';
	var $listing = true;
	var $toggle = array(
		'payment_published' => 'payment_id',
		'shipping_published' => 'shipping_id'
	);

	function __construct($config = array()){
		parent::__construct($config);

		$this->display[]='selectimages';
		$this->display[]='selectnew';
		$this->modify_views[]='edit_translation';
		$this->modify_views[] = 'unpublish';
		$this->modify_views[] = 'publish';
		$this->modify[]='save_translation';
		$this->modify[]='trigger';
		$this->modify[]='cancel';
		$this->modify[]='copy';
	}

	function _getToggle() {
		$this->type = JRequest::getCmd('plugin_type');
		if($this->type == 'payment') {
			$this->toggle = array('payment_published' => 'payment_id');
		} else {
			$this->toggle = array('shipping_published' => 'shipping_id');
		}
	}

	function publish(){
		$this->_getToggle();
		return parent::publish();
	}

	function unpublish(){
		$this->_getToggle();
		return parent::unpublish();
	}

	function trigger(){
		$cid= JRequest::getInt('cid', 0);
		$function = JRequest::getString('function', '');
		if(empty($cid) || empty($function)){
			return false;
		}
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->get($cid);
		if(empty($plugin)){
			return false;
		}
		$plugin = hikashop_import($plugin->folder, $plugin->element);
		if(method_exists($plugin, $function))
			return $plugin->$function();
		return false;
	}

	function copy(){
		$plugins = JRequest::getVar( 'cid', array(), '', 'array' );
		$result = true;
		if(!empty($plugins)){
			$type = JRequest::getCMD('plugin_type');
			if(!in_array($type,array('payment','shipping'))){
				$this->listing();
				return false;
			}
			$pluginsClass = hikashop_get('class.'.$type);
			foreach($plugins as $plugin){
				$data = $pluginsClass->get($plugin);
				if($data){
					$key = $type.'_id';
					unset($data->$key);
					if(!$pluginsClass->save($data)){
						$result=false;
					}
				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
			return $this->listing();
		}
		return $this->listing();
	}

	function edit_translation(){
		JRequest::setVar( 'layout', 'edit_translation');
		return parent::display();
	}

	function save_translation(){
		$cid= JRequest::getInt('cid');
		$type = JRequest::getString('type');
		$id_field = $type.'_id';
		$pluginClass = hikashop_get('class.'.$type);
		$element = $pluginClass->get($cid);
		if(!empty($element->$id_field)){
			$class = hikashop_get('helper.translation');
			$class->getTranslations($element);
			$class->handleTranslations($type,$element->$id_field,$element);
		}
		$document= JFactory::getDocument();
		$document->addScriptDeclaration('window.top.hikashop.closeBox();');
	}

	function orderdown(){
		$this->setOptions();
		return parent::orderdown();
	}

	function orderup(){
		$this->setOptions();
		return parent::orderup();
	}

	function saveorder(){
		$this->setOptions();
		$this->listing = false;
		JRequest::setVar('subtask', '');
		return parent::saveorder();
	}

	function cancel(){
		$type = JRequest::getVar( 'plugin_type','shipping').'_edit';
		if(JRequest::getVar('subtask','') == $type) {
			JRequest::setVar('subtask', '');
			return $this->edit();
		}
		return $this->listing();
	}

	function add(){
		JRequest::setVar('layout', 'selectnew');
		return parent::display();
	}

	function listing(){
		JRequest::setVar('layout', 'listing');
		return parent::display();
	}

	function selectimages(){
		JRequest::setVar( 'layout', 'selectimages'  );
		return parent::display();
	}

	function setOptions(){
		$app = JFactory::getApplication();
		$this->listing = false;
		$this->groupVal = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.'.$this->type.'_plugin_type', $this->type.'_plugin_type', 'manual');
		$this->type = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.plugin_type', 'plugin_type', 'shipping');
		$this->pkey = $this->type.'_id';
		$this->table = $this->type;
		$this->groupMap = ''; // No more group map
		$this->orderingMap = $this->type.'_ordering';
	}

	function save() {
		$status = $this->store();
		$subtask = JRequest::getVar('subtask');
		if(!empty($subtask)){
			JRequest::setVar('subtask','');
		}
		return $this->listing();
	}

	function store($new = false) {
		$this->plugin = JRequest::getCmd('name','manual');
		$this->plugin_type = JRequest::getCmd('plugin_type','shipping');
		if(!in_array($this->plugin_type,array('shipping','payment','plugin'))) {
			return false;
		}
		if($this->plugin_type == 'plugin')
			$data = hikashop_import('hikashop',$this->plugin);
		else
			$data = hikashop_import('hikashop'.$this->plugin_type, $this->plugin);

		$element = new stdClass();
		$id = hikashop_getCID($this->plugin_type.'_id');
		$formData = JRequest::getVar('data', array(), '', 'array');

		$params_name = $this->plugin_type.'_params';
		if(!empty($formData[$this->plugin_type])) {
			$plugin_id = $this->plugin_type.'_id';
			$element->$plugin_id = $id;
			foreach($formData[$this->plugin_type] as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					if($column == $params_name){
						$element->$params_name = new stdClass();
						foreach($formData[$this->plugin_type][$column] as $key=>$val){
							hikashop_secureField($key);
							if(is_array($val))
								$element->$params_name->$key = $val;
							else
								$element->$params_name->$key = strip_tags($val);
						}
					}elseif($column=='payment_shipping_methods' || $column=='payment_currency'){
						$element->$column = array();
						foreach($formData[$this->plugin_type][$column] as $key=>$val){
							$element->{$column}[(int)$key] = strip_tags($val);
						}
					}
				}else{
					$element->$column = strip_tags($value);
				}
			}
			if($this->plugin_type=='payment') {
				if(!isset($element->payment_shipping_methods)) $element->payment_shipping_methods = array();
				if(!isset($element->payment_currency)) $element->payment_currency = array();
			}

			$plugin_description = $this->plugin_type.'_description';
			$plugin_description_data = JRequest::getVar($plugin_description,'','','string',JREQUEST_ALLOWRAW);
			$element->$plugin_description = $plugin_description_data;
			$translationHelper = hikashop_get('helper.translation');
			$translationHelper->getTranslations($element);
		}
		$function = 'on'.ucfirst($this->plugin_type).'ConfigurationSave';
		if(method_exists($data,$function)){
			$data->$function($element);
		}

		if(!empty($element)) {
			$pluginClass = hikashop_get('class.'.$this->plugin_type);
			$status = $pluginClass->save($element);

			if(!$status) {
				JRequest::setVar('fail', $element);
			} else {
				$translationHelper->handleTranslations($this->plugin_type, $status, $element);
				$app = JFactory::getApplication();
				if(!HIKASHOP_J30)
					$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
				else
					$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
				if(empty($id)) {
					JRequest::setVar($this->plugin_type.'_id',$status);
				}
			}
		}
	}

	function edit(){
		if(JRequest::getInt('fromjoomla')){
			$app = JFactory::getApplication();
			$context = 'com_plugins.edit.plugin';
			$id = hikashop_getCID('id');
			if($id){
				$values = (array) $app->getUserState($context . '.id');
				$index = array_search((int) $id, $values, true);
				if (is_int($index)){
					unset($values[$index]);
					$app->setUserState($context . '.id', $values);
				}
			}
		}
		return parent::edit();
	}
}
