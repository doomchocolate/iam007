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
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgHikashopDatafeed extends JPlugin
{
	var $message = '';


	function plgHikashopData_feed(&$subject, $config){
		parent::__construct($subject, $config);
		}

	function onHikashopCronTrigger(&$messages){
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','datafeed');


		if(empty($plugin->params['frequency'])){
			$plugin->params['frequency'] = 86400;
		}
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['frequency']>time()){
			return true;
		}

		if(!empty($plugin->params['path']))
			$path=$plugin->params['path'];
		else{
			$messages[] = 'No file path specified';
			return true;
		}
		if(!empty($plugin->params['column_name']))
			$column=$plugin->params['column_name'];

		if(!preg_match('#^(https?|ftp)://#',$path)){
			$check=strtolower(substr($path,-3));
			if($check!='csv'){
				$messages[] = 'The file specified is not a CSV file: '.$path;
				return true;
			}

			$path=$this->_getRelativePath($path);
			jimport('joomla.filesystem.file');
			if(!JFile::exists(JPATH_ROOT.DS.$path)){
				$messages[] = 'Could not find the file '.JPATH_ROOT.DS.$path;
				return true;
			}

			$path=JURI::base().$path;
			$path=str_replace('administrator','',$path);

			$os=substr(PHP_OS, 0, 3);
			$os=strtolower($os);
			if($os='win')
				$path=str_replace('\\','/',$path);
		}

		 $helper = hikashop_get('helper.import');
		$tab = null;
		if(isset($column)){
			$tab = array();
			$entries=explode(";",$column);
			$i=0;
			foreach($entries as $entry){
				$var=explode(":",$entry);
				$tab[$var[0]]=$var[1];
				if(function_exists('mb_strtolower')){
					$columnName = mb_strtolower(trim($var[0],'" '));
				}else{
					$columnName = strtolower(trim($var[0],'" '));
				}
				$tab[$columnName]=$var[1];
				$i++;
			}
		}

		if(!empty($plugin->params['charset']))
			$helper->charsetConvert = $plugin->params['charset'];


		$helper->columnNamesConversionTable = $tab;
		$helper->createCategories = true;
		$helper->overwrite = true;
		$helper->header_errors = false;

		$contentFile = file_get_contents($path);
		if(empty($contentFile)){
			$messages[] = 'Could not retrieve the CSV file '. $path;
		}else{
			if(!$helper->handleContent($contentFile)){
				return false;
			}
			if(!empty($plugin->params['delete']) && !empty($helper->products)){
				$ids = array();

				foreach($helper->products as $product){
					$ids[$product->product_id] = $product->product_id;
				}
				$query ='SELECT product_id FROM '.hikashop_table('product').' WHERE product_id NOT IN ('.implode(',',$ids).') AND product_type=\'main\';';
				$db = JFactory::getDBO();
				$db->query($query);
				$prods = $db->loadObjectList('product_id');

				$todelete = array_keys($prods);
				$class = hikashop_get('class.product');
				$class->delete($todelete);
			}
		}
		return true;
	}

	function _getRelativePath($path){
		$app = JFactory::getApplication();
		if(!preg_match('#^([A-Z]:)?/.*#',$path)){
			if(!$path[0]=='/' || !is_dir($path)){
				$pathClean = JPath::clean(HIKASHOP_ROOT.DS.trim($path,DS.' ').DS);
			}
		}


		if(!empty($pathClean)){
			$relativePath=str_replace(JPATH_ROOT.DS,'',$pathClean);
		}
		else{
			$relativePath=str_replace(JPATH_ROOT.DS,'',$path);
		}

		$relativePath=substr($relativePath,0,-1);
		return $relativePath;

	}
}


