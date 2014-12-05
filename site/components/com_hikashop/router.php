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
function HikashopBuildRoute( &$query )
{
	$segments = array();
	if(!defined('DS'))
		define('DS', DIRECTORY_SEPARATOR);
	if(function_exists('hikashop_config') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
		$config =& hikashop_config();
		if($config->get('activate_sef',1)){
			$categorySef=$config->get('category_sef_name','category');
			$productSef=$config->get('product_sef_name','product');
			$checkoutSef=$config->get('checkout_sef_name','checkout');
			if(empty($categorySef)){
				$categorySef='';
			}
			if(empty($productSef)){
				$productSef='';
			}

			if(isset($query['ctrl']) && isset($query['task'])){
				if($query['ctrl']=='category' && $query['task']=='listing'){
					$segments[] = $categorySef;
					unset( $query['ctrl'] );
					unset( $query['task'] );
				}
				else if($query['ctrl']=='product' && $query['task']=='show'){
					$segments[] = $productSef;
					unset( $query['ctrl'] );
					unset( $query['task'] );
				}
			}
			else if(isset($query['view']) && isset($query['layout'])){
				if($query['view']=='category' && $query['layout']=='listing'){
					$segments[] = $categorySef;
					unset( $query['layout'] );
					unset( $query['view'] );
				}
				else if($query['view']=='product' && $query['layout']=='show'){
					$segments[] = $productSef;
					unset( $query['layout'] );
					unset( $query['view'] );
				}
			}
			if((isset($query['ctrl']) && $query['ctrl']=='checkout' || isset($query['view']) && $query['view']=='checkout') && !empty($query['Itemid']) && (!isset($query['task']) || $query['task']=='step')){
				$menuClass = hikashop_get('class.menus');
				$menu = $menuClass->get($query['Itemid']);
				if(!empty($menu) && !empty($menu->link) && $menu->link =='index.php?option=com_hikashop&view=checkout&layout=step'){
					if(isset($query['ctrl'])) unset($query['ctrl']);
					if(isset($query['view'])) unset($query['view']);
					if(!empty($checkoutSef)) $segments[] = $checkoutSef;
				}
			}
		}
		$pathway_sef_name = $config->get('pathway_sef_name','category_pathway');
		if(isset($query[$pathway_sef_name])&& (empty($query[$pathway_sef_name])) || $config->get('simplified_breadcrumbs',1)){
			unset( $query[$pathway_sef_name] );
		}
		if(isset($query[$pathway_sef_name])){
			$category_pathway = $config->get('category_pathway','category_pathway');
			if($category_pathway!='category_pathway' && !empty($category_pathway)){
				$query[$category_pathway]=$query[$pathway_sef_name];
				unset( $query[$pathway_sef_name] );
			}
		}
		$related_sef_name = $config->get('related_sef_name','related_product');
		if(isset($query[$related_sef_name])&& $config->get('simplified_breadcrumbs',1)){
			unset( $query[$related_sef_name] );
		}
	}

	if (isset($query['ctrl'])) {
		$segments[] = $query['ctrl'];
		unset( $query['ctrl'] );
		if (isset($query['task'])) {
			$segments[] = $query['task'];
			unset( $query['task'] );
		}
	}elseif(isset($query['view'])){
		$segments[] = $query['view'];
		unset( $query['view'] );
		if(isset($query['layout'])){
			$segments[] = $query['layout'];
			unset( $query['layout'] );
		}
	}

	if(isset($query['product_id'])){
		$query['cid'] = $query['product_id'];
		unset($query['product_id']);
	}
	if(isset($query['cid']) && isset($query['name'])){
		if($config->get('sef_remove_id',0) && !empty($query['name'])){
			$segments[] = $query['name'];
		}else{
			if(is_numeric($query['name'])){
				$query['name']=$query['name'].'-';
			}
			$segments[] = $query['cid'].':'.$query['name'];
		}
		unset($query['cid']);
		unset($query['name']);
	}

	if(!empty($query)){
		foreach($query as $name => $value){
			if(!in_array($name,array('option','Itemid','start','format','limitstart'))){
					if(is_array($value)) $value = implode('-',$value);
					$segments[] = $name.':'.$value;
				unset($query[$name]);
			}
		}
	}

	return $segments;
}
function HikashopParseRoute( $segments )
{

	$vars = array();
	$check=false;
	if(!empty($segments)){
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(function_exists('hikashop_config') || include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			$config =& hikashop_config();
			if($config->get('activate_sef',1)){
				$categorySef=$config->get('category_sef_name','category');
				$productSef=$config->get('product_sef_name','product');
				$skip=false;
				if(isset($segments[0])){
					$file = HIKASHOP_CONTROLLER.$segments[0].'.php';
					if(file_exists($file) && isset($segments[1])){
						if(!($segments[0]=='product'&&$segments[1]=='show' || $segments[0]=='category'&&$segments[1]=='listing')){
							$controller = hikashop_get('controller.'.$segments[0],array(),true);
							if($controller->isIn($segments[1],array('display','modify_views','add','modify','delete'))){
								$skip = true;
							}
						}
					}
				}
				if(!$skip){
					$i = 0;
					foreach($segments as $name){

						if(strpos($name,':')){
							if(empty($productSef) && !$check){
								$vars['ctrl']='product';
								$vars['task']='show';
							}
							list($arg,$val) = explode(':',$name,2);
							if($arg=='task'&&$val=='step'){
								$vars['ctrl']='checkout';
							}
							if(is_numeric($arg) && !is_numeric($val)){
								$vars['cid'] = $arg;
								$vars['name'] = $val;
							}elseif(is_numeric($arg)){
								$vars['Itemid'] = $arg;
							}elseif(str_replace(':','-',$name)==$productSef){
								$vars['ctrl']='product';
								$vars['task']='show';
							}else if(str_replace(':','-',$name)==$categorySef){
								$vars['ctrl']='category';
								$vars['task']='listing';
								$check=true;
							}else{
								if(hikashop_retrieve_url_id($vars,$name)) continue;
								$vars[$arg] = $val;
							}
						}else if($name==$productSef){
							$vars['ctrl']='product';
							$vars['task']='show';
						}else if($name==$categorySef){
							$vars['ctrl']='category';
							$vars['task']='listing';
							$check=true;
						}else{
							if(hikashop_retrieve_url_id($vars,$name)) continue;
							$i++;
							if($i == 1) $vars['ctrl'] = $name;
							elseif($i == 2) $vars['task'] = $name;
							$check=true;
						}
					}

					return $vars;
				}
				$i = 0;
				foreach($segments as $name){
					if(strpos($name,':')){
						list($arg,$val) = explode(':',$name,2);
						if(is_numeric($arg) && !is_numeric($val)){
							$vars['cid'] = $arg;
							$vars['name'] = $val;
						}elseif(is_numeric($arg)){
							if(hikashop_retrieve_url_id($vars,$name)) continue;
							$vars['Itemid'] = $arg;
						}else{
							if(hikashop_retrieve_url_id($vars,$name)) continue;
							$vars[$arg] = $val;
						}
					}else{
						if(hikashop_retrieve_url_id($vars,$name)) continue;
						$i++;
						if($i == 1) $vars['ctrl'] = $name;
						elseif($i == 2) $vars['task'] = $name;
					}
				}
				$category_pathway = $config->get('category_pathway','category_pathway');
				if($category_pathway!='category_pathway' && isset($vars[$category_pathway])){
					$vars['category_pathway']=$vars[$category_pathway];
				}
			}else{
				foreach($segments as $name){
					hikashop_retrieve_url_id($vars,$name);
				}
			}

		}
	}
	return $vars;
}

function hikashop_retrieve_url_id(&$vars,$name){
	$config =& hikashop_config();
	if($config->get('sef_remove_id',0) && isset($vars['ctrl']) && isset($vars['task'])){

		if($vars['ctrl']=='category' || ($vars['ctrl']=='product' && $vars['task']=='listing')){
			$type = 'category';
		}elseif($vars['ctrl']=='product' && $vars['task']=='show'){
			$type = 'product';
		}else{
			return false;
		}

		$db = JFactory::getDBO();
		$config =& hikashop_config();

		if($config->get('alias_auto_fill',1)){
			$db->setQuery('SELECT '.$type.'_id FROM '.hikashop_table($type).' WHERE '.$type.'_alias = '.$db->Quote(str_replace(':','-',$name)));
			$retrieved_id = $db->loadResult();
			if($retrieved_id){
				$vars['cid'] = $retrieved_id;
				$vars['name'] = $name;
				return true;
			}
		}

		$name_regex = '^ *'.str_replace(array('-',':'),'.+',$name).' *$';
		$db->setQuery('SELECT * FROM '.hikashop_table($type).' WHERE '.$type.'_alias REGEXP '.$db->Quote($name_regex).' OR '.$type.'_name REGEXP '.$db->Quote($name_regex));
		$retrieved = $db->loadObject();

		if($retrieved){
			$type_id = $type.'_id';
			$vars['cid'] = $retrieved->$type_id;
			$vars['name'] = $name;
			if($config->get('alias_auto_fill',1)){
				if(empty($retrieved->product_alias)){
					$class = hikashop_get('class.'.$type);
					$class->addAlias($retrieved);
					$element = new stdClass();
					$type_alias = $type.'_alias';
					$element->$type_id = $retrieved->$type_id;
					$element->$type_alias = $retrieved->alias;
					$class->save($element);
				}
			}
			return true;
		}
	}
	return false;
}
