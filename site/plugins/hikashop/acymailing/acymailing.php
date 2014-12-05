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
class plgHikashopAcymailing extends JPlugin
{
	function plgHikashopAcymailing(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('hikashop', 'history');
			jimport('joomla.html.parameter');
			if(version_compare(JVERSION,'2.5','<')){
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}
	}

	function onAfterOrderCreate(&$order,&$send_email){
		return $this->onAfterOrderUpdate($order,$send_email);
	}

	function onAfterOrderUpdate(&$order,&$send_email){
		if(!empty($order->order_id) && !empty($order->order_status)){
			if(empty($order->order_user_id)){
				$class = hikashop_get('class.order');
				$old = $class->get($order->order_id);
				$order->order_user_id = $old->order_user_id;
			}
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($order->order_user_id);
			if(!empty($user)){
				$helper = rtrim(str_replace('/',DS,JPATH_ADMINISTRATOR),DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php';
				if(file_exists($helper)){
					include_once($helper);
					if(function_exists('acymailing_get')){
						$subClass = acymailing_get('class.subscriber');
						$sub = $subClass->get($user->email);
						if(!empty($sub->subid)){
							if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'classes'.DS.'filter.php')){
								$filterClass = acymailing_get('class.filter');
								if($filterClass){
									$filterClass->subid = $sub->subid;
									$filterClass->trigger('hikaorder_'.$order->order_status);
								}
							}
						}
					}
				}
			}
		}
		return true;
	}

	function onAfterUserCreate(&$hikauser){
		$helper = rtrim(str_replace('/',DS,JPATH_ADMINISTRATOR),DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php';
		if(!file_exists($helper) || empty($hikauser->user_email)) return;

		include_once($helper);

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriberClass->geolocRight = true;
		$subid = $subscriberClass->subid($hikauser->user_email);
		if(!empty($subid)) return;

		$acysub = new stdClass();
		$acysub->name = '';
		if(!empty($_REQUEST['data']['address']['address_firstname'])) $acysub->name .= $_REQUEST['data']['address']['address_firstname'].' ';
		if(!empty($_REQUEST['data']['address']['address_middle_name'])) $acysub->name .= $_REQUEST['data']['address']['address_middle_name'].' ';
		if(!empty($_REQUEST['data']['address']['address_lastname'])) $acysub->name .= $_REQUEST['data']['address']['address_lastname'].' ';
		$acysub->name = trim(strip_tags($acysub->name));
		$acysub->email = $hikauser->user_email;
		$acysub->confirmed = 1;
		$acysub->enabled = 1;

		$customValues = JRequest::getVar( 'regacy', array(), '', 'array' );
		$session = JFactory::getSession();
		if(empty($customValues) && $session->get('regacy')){
			$customValues = $session->get('regacy');
			$session->set('regacy',null );
		}
		if(!empty($customValues)){
			$subscriberClass->checkFields($customValues,$acysub);
		}

		$subid = $subscriberClass->save($acysub);

		if(empty($subid)) return;

		$config = acymailing_config();

		$listsToSubscribe = $config->get('autosub','None');
		$currentSubscription = $subscriberClass->getSubscriptionStatus($subid);

		$listsClass = acymailing_get('class.list');
		$allLists = $listsClass->getLists('listid');
		if(acymailing_level(1)){
			$allLists = $listsClass->onlyCurrentLanguage($allLists);
		}

		$visiblelistschecked = JRequest::getVar( 'acysub', array(), '', 'array' );
		$acySubHidden = JRequest::getString( 'acysubhidden');
		if(!empty($acySubHidden)){
			$visiblelistschecked = array_merge($visiblelistschecked,explode(',',$acySubHidden));
		}

		$session = JFactory::getSession();
		if(empty($visiblelistschecked) && $session->get('acysub')){
			$visiblelistschecked = $session->get('acysub');
			$session->set('acysub',null );
		}

		$listsArray = array();
		if(strpos($listsToSubscribe,',') OR is_numeric($listsToSubscribe)){
			$listsArrayParam = explode(',',$listsToSubscribe);
			foreach($allLists as $oneList){
				if($oneList->published AND (in_array($oneList->listid,$visiblelistschecked) || in_array($oneList->listid,$listsArrayParam))){$listsArray[] = $oneList->listid;}
			}
		}elseif(strtolower($listsToSubscribe) == 'all'){
			foreach($allLists as $oneList){
				if($oneList->published){$listsArray[] = $oneList->listid;}
			}
		}elseif(!empty($visiblelistschecked)){
			foreach($allLists as $oneList){
				if($oneList->published AND in_array($oneList->listid,$visiblelistschecked)){$listsArray[] = $oneList->listid;}
			}
		}

		$statusAdd = 1;
		$addlists = array();
		if(!empty($listsArray)){
			foreach($listsArray as $idOneList){
				if(!isset($currentSubscription[$idOneList])){
					$addlists[$statusAdd][] = $idOneList;
				}
			}
		}

		if(!empty($addlists)) {
			$listsubClass = acymailing_get('class.listsub');
			$listsubClass->addSubscription($subid,$addlists);
		}
	}
}
