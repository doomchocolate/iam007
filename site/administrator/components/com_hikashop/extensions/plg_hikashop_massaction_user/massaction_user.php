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
class plgHikashopMassaction_user extends JPlugin
{
	var $message = '';

	function onMassactionTableLoad(&$externalValues){
		$obj = new stdClass();
		$obj->table ='user';
		$obj->value ='user';
		$obj->text =JText::_('HIKA_USER');
		$externalValues[] = $obj;
	}

	function plgHikashopMassaction_user(&$subject, $config){
		parent::__construct($subject, $config);
		$this->massaction = hikashop_get('class.massaction');
		$this->user = hikashop_get('class.user');
	}

	function onProcessUserMassFilterlimit(&$elements, &$query,$filter,$num){
		$query->start = (int)$filter['start'];
		$query->value = (int)$filter['value'];
	}

	function onProcessUserMassFilteruserColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				$filter['type'] = str_replace('hk_user.','',$filter['type']);
				$filter['type'] = str_replace('joomla_user.','',$filter['type']);
				$in = $this->massaction->checkInElement($element, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['joomla_user'] = hikashop_table('users',false).' as joomla_user ON joomla_user.id = hk_user.user_cms_id';
				$query->where[] = $this->massaction->getRequest($filter);
			}
		}
	}
	function onCountUserMassFilteruserColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilteruserColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}

	function onProcessUserMassFilteraddressColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();

		if(in_array($filter['type'],array('address_state','address_country'))){
			$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_name LIKE '.$db->quote($filter['value']).' OR zone_name_english LIKE '.$db->quote($filter['value']));
			$filter['value'] = $db->loadResult();
		}
		if(count($elements)){
			foreach($elements as $k => $element){
				$db->setQuery('SELECT * FROM '.hikashop_table('address').' WHERE address_user_id = '.(int)$element->user_id.' GROUP BY address_id');
				$results = $db->loadObjectList();
				$del = true;
				foreach($results as $result){
					$in = $this->massaction->checkInElement($result, $filter);
					if($in) $del = false;
				}
				if($del) unset($elements[$k]);
			}
		}else{
			if(!is_null($filter['value']) || (is_null($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->select = ' DISTINCT '.$query->select;
				$query->leftjoin[] = hikashop_table('address').' as hk_address ON hk_address.address_user_id = hk_user.user_id';
				$query->where[] = $this->massaction->getRequest($filter,'hk_address');
			}else{
				$query->leftjoin = '';
				$query->where = array('false');
			}
		 }
	}
	function onCountUserMassFilteraddressColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilteraddressColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}
	function onProcessUserMassFilterdontHave(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->$filter['type']!=$filter['value']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$ids = null;
			switch($filter['type']){
				case 'order':
					$db->setQuery('SELECT order_user_id FROM '.hikashop_table('order').' GROUP BY order_user_id');
					$ids = $db->loadResultArray();
					break;
				case 'order_status':
					$db->setQuery('SELECT order_user_id FROM '.hikashop_table('order').' WHERE order_status = '.$db->quote($filter['order_status']).' GROUP BY order_user_id');
					$ids = $db->loadResultArray();
					break;
				case 'address':
					$db->setQuery('SELECT address_user_id FROM '.hikashop_table('address').' GROUP BY address_user_id');
					$ids = $db->loadResultArray();
					break;
			}
			if($ids == null){
				echo JText::sprintf('SELECTED_PRODUCTS',0); exit;
			}else{
				$query->where[] = 'hk_user.user_id NOT IN ('.implode(',',$ids).')';
			}
		 }
	}
	function onCountUserMassFilterdontHave(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilterdontHave($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}
	function onProcessUserMassFilteraccessLevel(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->$filter['type']!=$filter['value']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$operator = (empty($filter['type']) || $filter['type'] == 'IN') ? ' = ' : ' != ';
			$query->leftjoin['joomla_user'] = hikashop_table('users',false). ' as joomla_user ON joomla_user.id = hk_user.user_cms_id';
			if(!HIKASHOP_J16){
				$query->leftjoin['core_acl_aro_groups'] = hikashop_table('core_acl_aro_groups',false). ' as core_acl_aro_groups ON core_acl_aro_groups.value = joomla_user.usertype';
				$query->where[] = 'core_acl_aro_groups.id'.' '.$operator.' '.(int)$filter['group'];
			}else{
				$query->leftjoin['user_usergroup_map'] = hikashop_table('user_usergroup_map',false). ' as user_usergroup_map ON user_usergroup_map.user_id = joomla_user.id';
				$query->where[] = 'user_usergroup_map.group_id'.' '.$operator.' '.(int)$filter['group'];
			}
		}
	}
	function onCountUserMassFilteraccessLevel(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilteraccessLevel($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}

	function onProcessUserMassActiondisplayResults(&$elements,&$action,$k){
		$params = $this->massaction->_displayResults('user',$elements,$action,$k);
		$params->action_id = $k;
		$js = '';
		$app = JFactory::getApplication();
		if($app->isAdmin() && JRequest::getVar('ctrl','massaction') == 'massaction'){
			echo hikashop_getLayout('massaction','results',$params,$js);
		}
	}
	function onProcessUserMassActionexportCsv(&$elements,&$action,$k){
		$formatExport = $action['formatExport']['format'];
		$path = $action['formatExport']['path'];
		if(empty($path)){
			ob_get_clean();
		}
		$app = JFactory::getApplication();
		if($app->isAdmin() || (!$app->isAdmin() && !empty($path))){
			$params->action['user']['user_id'] = 'user_id';
			unset($action['formatExport']);
			$params = $this->massaction->_displayResults('user',$elements,$action,$k);
			$params->formatExport = $formatExport;
			$params->path = $path;
			$params = $this->massaction->sortResult($params->table,$params);
			$this->massaction->_exportCSV($params);
		}
	}
	function onProcessUserMassActionupdateValues(&$elements,&$action,$k){
		$current = 'user';
		$current_id = $current.'_id';
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->$current_id;
			if(isset($element->$action['type']))
				$element->$action['type'] = $action['value'];

		}
		$action['type'] = strip_tags($action['type']);
		$alias = explode('_',$action['type']);
		$queryTables = array($current);
		$possibleTables = array($current,'joomla_users');
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$value = $this->massaction->updateValuesSecure($action,$possibleTables,$queryTables);
		JArrayHelper::toInteger($ids);
		$db = JFactory::getDBO();




		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
				$queryTables = array_unique($queryTables);
				foreach($queryTables as $queryTable){
					switch($queryTable){
						case 'user':
							if(!in_array('joomla_users',$queryTables)){
								$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
							}
							break;
						case 'joomla_users':
							$action['type'] = str_replace($queryTable.'_','',$action['type']);
							$query .= 'LEFT JOIN '.hikashop_table('users',false).' AS joomla_users ON joomla_users.id = hk_user.user_cms_id ';
							$query .= 'SET '.$queryTable.'.'.$action['type'].' = '.$value.' ';
							break;
					}
				}
				$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$id).')';
				$db->setQuery($query);
				$db->query();
			}
		}else{
			$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
			$queryTables = array_unique($queryTables);
			foreach($queryTables as $queryTable){
				switch($queryTable){
					case 'user':
						if(!in_array('joomla_users',$queryTables)){
							$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
						}
						break;
					case 'joomla_users':
						$action['type'] = str_replace($queryTable.'_','',$action['type']);
						$query .= 'LEFT JOIN '.hikashop_table('users',false).' AS joomla_users ON joomla_users.id = hk_user.user_cms_id ';
						$query .= 'SET '.$queryTable.'.'.$action['type'].' = '.$value.' ';
						break;
				}
			}
			$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$ids).')';
			$db->setQuery($query);
			$db->query();
		}

	}
	function onProcessUserMassActiondeleteElements(&$elements,&$action,$k){
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->user_id;
		}
		$userClass = hikashop_get('class.user');

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$result = $userClass->delete($id);
			}
		}else{
			$result = $userClass->delete($ids);
		}
	}
	function onProcessUserMassActionchangeGroup(&$elements,&$action,$k){
		$user_ids = array();
		$values = array();
		foreach($elements as $element){
			$user_ids[] = $element->user_cms_id;
			$values[] = '('.$element->user_cms_id.','.$action['value'].')';
		}

		$db = JFactory::getDBO();
		if($action['type'] == 'replace'){
			$db->setQuery('DELETE FROM '.hikashop_table('user_usergroup_map',false).' WHERE user_id IN ('.$user_ids.')');
			$db->query();
		}

		$db->setQuery('REPLACE INTO '.hikashop_table('user_usergroup_map',false).' VALUES '.implode(',',$values));
		$db->query();
	}

	function onBeforeUserCreate(&$element,&$do){
		$elements = array($element);
		$this->massaction->trigger('onBeforeUserCreate',$elements);
	}

	function onBeforeUserUpdate(&$element,&$do){
		$getUser = $this->user->get($element->user_id);

		foreach($getUser as $key => $value){
			if(isset($element->$key) && $getUser->$key != $element->$key){
				$getUser->$key = $element->$key;
			}
		}
		$users = array($getUser);
		$this->massaction->trigger('onBeforeUserUpdate',$users);
	}

	function onBeforeUserDelete(&$element,&$do){
		$users = array();
		if(!is_array($element)) $clone = array($element);
		else $clone = $element;
		foreach($clone as $id){
			$users[] = $this->user->get($id);
		}
		$this->deletedUser =& $users;
		$this->massaction->trigger('onBeforeUserDelete',$users);
	}

	function onAfterUserCreate(&$element){
		$getUser = $this->user->get($element->user_id);
		foreach($getUser as $key => $value){
			if(isset($element->$key) && $getUser->$key != $element->$key){
				$getUser->$key = $element->$key;
			}
		}
		$users = array($getUser);
		$this->massaction->trigger('onAfterUserCreate',$users);
	}

	function onAfterUserUpdate(&$element){
		$getUser = $this->user->get($element->user_id);

		foreach($getUser as $key => $value){
			if(isset($element->$key) && $getUser->$key != $element->$key){
				$getUser->$key = $element->$key;
			}
		}
		$users = array($getUser);
		$this->massaction->trigger('onAfterUserUpdate',$users);
	}

	function onAfterUserDelete(&$element){
		$this->massaction->trigger('onAfterUserDelete',$this->deletedUser);
	}

}
