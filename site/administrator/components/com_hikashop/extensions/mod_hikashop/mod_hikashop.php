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
$js ='';
$params->set('show_limit',0);
$params->set('from_module',$module->id);
hikashop_initModule();
$config = hikashop_config();
$key_name = 'params_'.$module->id;
$module_options = $config->get($key_name);
if(empty($module_options)){
	$module_options = $config->get('default_params');
}
$type = $module_options['content_type'];
if($type=='manufacturer') $type = 'category';
if(empty($module_options['itemid']) && $type=='category' && !JRequest::getVar('hikashop_front_end_main')){
	$module_options['content_synchronize']=0;
	$menu = hikashop_get('class.menus');
	$menu->createMenu($module_options,$module->id);

	$configData=new stdClass();
	$configData->$key_name = $module_options;
	$config->save($configData);
}
foreach($module_options as $key => $option){
	if($key !='moduleclass_sfx'){
		$params->set($key,$option);
	}
}
$moduleClass = hikashop_get('class.modules');
$moduleClass->loadParams($module);
foreach(get_object_vars($module) as $k => $v){
	if(!is_object($v)){
		$params->set($k,$v);
	}
}
$html = trim(hikashop_getLayout($type,'listing',$params,$js));
if(!empty($html)){ ?>
<div id="hikashop_module_<?php echo $module->id;?>" class="hikashop_module <?php echo @$module->params['moduleclass_sfx']; ?>">
<?php echo $html; ?>
</div>
<?php } ?>
