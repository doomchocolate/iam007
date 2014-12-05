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
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};
$config =& hikashop_config();
if(!$config->get('enable_wishlist')){
	echo 'This module can not work, wishlists are not enabled';
	return;
}
$js ='';
hikashop_initModule();


$module_options = $config->get('params_'.$module->id);
if(empty($module_options)){
	$module_options = $config->get('default_params');
}
foreach($module_options as $key => $option){
	if($key !='moduleclass_sfx'){
		$params->set($key,$option);
	}
}
foreach(get_object_vars($module) as $k => $v){
	if(!is_object($v)){
		$params->set($k,$v);
	}
}

$params->set('cart_type','wishlist');
$params->set('from','module');
$html = trim(hikashop_getLayout('product','cart',$params,$js));

if(!empty($html)){
?>
<div class="hikashop_wishlist_module" id="hikashop_wishlist_module">
<?php echo $html; ?>
</div>
<?php }
