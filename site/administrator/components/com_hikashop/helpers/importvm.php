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

@include_once(HIKASHOP_ROOT . 'administrator/components/com_virtuemart/virtuemart.cfg.php');

class hikashopImportvmHelper extends hikashopImportHelper
{
	var $vm_version = 0; //TODO : TOCHECK
	var $vm_current_lng = '';
	var $sessionParams = '';
	var $vmprefix;

	function __construct(&$parent)
	{
		parent::__construct();
		$this->importName = 'vm';
		$this->sessionParams = HIKASHOP_COMPONENT.'vm';
		jimport('joomla.filesystem.file');
	}

	function importFromVM()
	{
		@ob_clean();
		echo $this->getHtmlPage();

		$this->token = hikashop_getFormToken();
		$app = JFactory::getApplication();
		flush();

		if( isset($_GET['import']) && $_GET['import'] == '1' )
		{
			$time = microtime(true);
			$this->vmprefix = $app->getUserState($this->sessionParams.'vmPrefix');
			if ($this->vm_version==2)
				$this->vm_current_lng = $app->getUserState($this->sessionParams.'language');
			$processed = $this->doImport();

			if($processed)
			{
				$elasped = microtime(true) - $time;

				if( !$this->refreshPage )
					echo '<p><a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1&time='.time()).'">'.JText::_('HIKA_NEXT').'</a></p>';

				echo '<p style="font-size:0.85em; color:#605F5D;">Elasped time: ' . round($elasped * 1000, 2) . 'ms</p>';
			}
			else
			{
				echo '<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
			}
		}
		else
		{
			echo $this->getStartPage();
		}

		if( $this->refreshPage == true )
		{
			echo "<script type=\"text/javascript\">\r\nr = true; \r\n</script>";
		}
		echo '</body></html>';
	}


	function getStartPage()
	{
		$app = JFactory::getApplication();

		$returnString = '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 0</span></p>';
		$continue=true;

		$this->vmprefix = $app->getUserStateFromRequest($this->sessionParams.'vmPrefix', 'vmPrefix', '', 'string' );
		if (empty($this->vmprefix))
			$this->vmprefix = $this->db->getPrefix();
		elseif ( substr($this->vmprefix, 0, 1)!='_')
				$this->vmprefix .= '_';
		$app->setUserState($this->sessionParams.'vmPrefix',$this->vmprefix);

		if ($this->vm_version==1)
		{
			$this->db->setQuery("SHOW TABLES LIKE '".$this->vmprefix."vm_product'");
			$table = $this->db->loadObjectList();
		}
		elseif ($this->vm_version==2)
		{
			$this->db->setQuery("SHOW TABLES LIKE '".$this->vmprefix."virtuemart_products'");
			$table = $this->db->loadObjectList();
		}

		if (!$table)
		{
			$returnString .= '<p style="color:red; font-size:0.9em;">There is no table with the prefix \''.$this->vmprefix.'\' in your Joomla\'s database.</p>';
			$continue=false;
		}

		if ($this->vm_version==2)
		{
			$this->vm_current_lng = $app->getUserStateFromRequest($this->sessionParams.'language', 'language', '', 'string' );//JRequest::getString('language');
			$this->vm_current_lng = strtolower(str_replace('-','_',$this->vm_current_lng));
			$app->setUserState($this->sessionParams.'language',$this->vm_current_lng);
			$this->db->setQuery("SHOW TABLES LIKE '".$this->db->getPrefix()."virtuemart_products_".$this->vm_current_lng."'");
			$table = $this->db->loadObjectList();
			if (!$table)
			{
				$returnString .= '<p style="color:red; font-size:0.9em;"> There is no table corresponding to the language you selected ('.$this->vm_current_lng.') in your database. Please back and select another language.</p>';
				$continue=false;
			}
		}

		if ($continue)
		{
			$returnString = 'First, make a backup of your database.<br/>'.
			'When ready, click on <a '.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1').'">'.JText::_('HIKA_NEXT').'</a>, otherwise ';
		}
		$returnString .= '<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
		return $returnString;

	}


	function doImport() {
		if( $this->db == null )
			return false;


		$this->loadConfiguration();
		$current = $this->options->current;

		$ret = true;
		$next = false;

		switch( $this->options->state ) {
			case 0:
				$next = $this->createTables();
				break;
			case 1:
				$next = $this->importTaxes();
				break;
			case 2:
				$next = $this->importManufacturers();
				break;
			case 3:
				$next = $this->importCategories();
				break;
			case 4:
				$next = $this->importProducts();
				break;
			case 5:
				$next = $this->importProductPrices();
				break;
			case 6:
				$next = $this->importProductCategory();
				break;
			case 7:
				$next = $this->importUsers();
				break;
			case 8:
				$next = $this->importDiscount();
				break;
			case 9:
				$next = $this->importOrders();
				break;
			case 10:
				$next = $this->importOrderItems();
				break;
			case 11:
				$next = $this->importDownloads();
				break;
			case MAX_IMPORT_ID:
				$next = $this->finishImport();
				$ret = false;
				break;
			case MAX_IMPORT_ID+1:
				$next = false;
				$ret = $this->proposeReImport();
				break;
			default:
				$ret = false;
				break;
		}

		if( $ret && $next ) {
			$sql =  "UPDATE `#__hikashop_config` SET config_value=(config_value+1) WHERE config_namekey = 'vm_import_state'; ";
			$this->db->setQuery($sql);
			$this->db->query();
			$sql = "UPDATE `#__hikashop_config` SET config_value=0 WHERE config_namekey = 'vm_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		} else if( $current != $this->options->current ) {
			$sql =  "UPDATE `#__hikashop_config` SET config_value=".$this->options->current." WHERE config_namekey = 'vm_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}

		return $ret;
	}

	function loadConfiguration() {

		if( $this->db == null )
			return false;

		$this->loadVmConfigs();

		$data = array(
			'uploadfolder',
			'uploadsecurefolder',
			'main_currency',
			'vm_import_state',
			'vm_import_current',
			'vm_import_tax_id',
			'vm_import_main_cat_id',
			'vm_import_max_hk_cat',
			'vm_import_max_hk_prod',
			'vm_import_last_vm_cat',
			'vm_import_last_vm_prod',
			'vm_import_last_vm_user',
			'vm_import_last_vm_order',
			'vm_import_last_vm_pfile',
			'vm_import_last_vm_coupon',
			'vm_import_last_vm_taxrate',
			'vm_import_last_vm_manufacturer'
		);
		$this->db->setQuery('SELECT config_namekey, config_value FROM `#__hikashop_config` WHERE config_namekey IN ('."'".implode("','",$data)."'".');');
		$options = $this->db->loadObjectList();

		$this->options = null;
		if (!empty($options))
		{
			foreach($options as $o) {
				if( substr($o->config_namekey, 0, 10) == 'vm_import_' ) {
					$nk = substr($o->config_namekey, 10);
				} else {
					$nk = $o->config_namekey;
				}

				$this->options->$nk = $o->config_value;
			}
		}

		$this->options->uploadfolder = rtrim(JPath::clean(html_entity_decode($this->options->uploadfolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$this->options->uploadfolder)){
			if(!$this->options->uploadfolder[0]=='/' || !is_dir($this->options->uploadfolder)){
				$this->options->uploadfolder = JPath::clean(HIKASHOP_ROOT.DS.trim($this->options->uploadfolder,DS.' ').DS);
			}
		}

		$this->options->uploadsecurefolder = rtrim(JPath::clean(html_entity_decode($this->options->uploadsecurefolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$this->options->uploadsecurefolder)){
			if(!$this->options->uploadsecurefolder[0]=='/' || !is_dir($this->options->uploadsecurefolder)){
				$this->options->uploadsecurefolder = JPath::clean(HIKASHOP_ROOT.DS.trim($this->options->uploadsecurefolder,DS.' ').DS);
			}
		}

		if( !isset($this->options->state) ) {
			$this->options->state = 0;
			$this->options->current = 0;
			$this->options->tax_id = 0;
			$this->options->last_vm_coupon = 0;
			$this->options->last_vm_pfile = 0;
			$this->options->last_vm_taxrate = 0;
			$this->options->last_vm_manufacturer = 0;

			$element = 'product';
			$categoryClass = hikashop_get('class.category');
			$categoryClass->getMainElement($element);
			$this->options->main_cat_id = $element;

			$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
			$data = $this->db->loadObjectList();
			$this->options->max_hk_cat = (int)($data[0]->max);

			$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
			$data = $this->db->loadObjectList();
			$this->options->max_hk_prod = (int)($data[0]->max);

			$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_cat'),3));
			$this->db->setQuery($query);
			$table = $this->db->loadResult();
			if(!empty($table)){
				$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_cat`;");
				$data = $this->db->loadObjectList();
				if( $data ) {
					$this->options->last_vm_cat = (int)($data[0]->max);
				} else {
					$this->options->last_vm_cat = 0;
				}

				$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_prod`;");
				$data = $this->db->loadObjectList();
				if( $data ) {
					$this->options->last_vm_prod = (int)($data[0]->max);
				} else {
					$this->options->last_vm_prod = 0;
				}
				$this->db->setQuery("SELECT max(order_vm_id) as 'max' FROM `#__hikashop_order`;");
				$data = $this->db->loadObjectList();
				$this->options->last_vm_order = (int)($data[0]->max);
			}else{
				$this->options->last_vm_cat = 0;
				$this->options->last_vm_prod = 0;
				$this->options->last_vm_order = 0;
			}

			$this->options->last_vm_user = 0;

			$sql = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('vm_import_state',".$this->options->state.",".$this->options->state.")".
				",('vm_import_current',".$this->options->current.",".$this->options->current.")".
				",('vm_import_tax_id',".$this->options->tax_id.",".$this->options->tax_id.")".
				",('vm_import_main_cat_id',".$this->options->main_cat_id.",".$this->options->main_cat_id.")".
				",('vm_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('vm_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('vm_import_last_vm_cat',".$this->options->last_vm_cat.",".$this->options->last_vm_cat.")".
				",('vm_import_last_vm_prod',".$this->options->last_vm_prod.",".$this->options->last_vm_prod.")".
				",('vm_import_last_vm_user',".$this->options->last_vm_user.",".$this->options->last_vm_user.")".
				",('vm_import_last_vm_order',".$this->options->last_vm_order.",".$this->options->last_vm_order.")".
				",('vm_import_last_vm_pfile',".$this->options->last_vm_pfile.",".$this->options->last_vm_pfile.")".
				",('vm_import_last_vm_coupon',".$this->options->last_vm_coupon.",".$this->options->last_vm_coupon.")".
				",('vm_import_last_vm_taxrate',".$this->options->last_vm_taxrate.",".$this->options->last_vm_taxrate.")".
				",('vm_import_last_vm_manufacturer',".$this->options->last_vm_manufacturer.",".$this->options->last_vm_manufacturer.")".
				';';
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}


	function loadVmConfigs()
	{
		$configstring = '';
		if ($this->vm_version==2)
		{
			$this->db->setQuery('SELECT config FROM `'.$this->vmprefix.'virtuemart_configs`;');
			$data = $this->db->loadObjectList();
			$configstring = $data[0]->config;
			$paths = $this->parseConfig($configstring);
			foreach ($paths as $key => $value)
			{
				switch ($key)
				{
					case 'media_category_path' :
						$this->copyCatImgDir = HIKASHOP_ROOT.$value;
						break;
					case 'media_product_path' :
						$this->copyImgDir = HIKASHOP_ROOT.$value;
						break;
					case 'media_manufacturer_path' :
						$this->copyManufDir = HIKASHOP_ROOT.$value;
						break;
					default :
						break;
				}
			}

		}
		elseif ($this->vm_version==1)
		{
			if ( defined('IMAGEPATH') )
			{
				$this->copyImgDir = IMAGEPATH. 'product/';
				if ( substr($this->copyImgDir, 0, 1)=='/') $this->copyImgDir = HIKASHOP_ROOT.substr($this->copyImgDir, 1, strlen($this->copyImgDir)-1);
				elseif ( substr($this->copyImgDir, 0, 1)=='\\') $this->copyImgDir = HIKASHOP_ROOT.substr($this->copyImgDir, 1, strlen($this->copyImgDir)-1);

				$this->copyCatImgDir = IMAGEPATH. 'category/';
				if ( substr($this->copyCatImgDir, 0, 1)=='/') $this->copyCatImgDir = HIKASHOP_ROOT.substr($this->copyCatImgDir, 1, strlen($this->copyCatImgDir)-1);
				elseif ( substr($this->copyCatImgDir, 0, 1)=='\\') $this->copyCatImgDir = HIKASHOP_ROOT.substr($this->copyCatImgDir, 1, strlen($this->copyCatImgDir)-1);

				$this->copyManufDir  = IMAGEPATH. 'vendor/';
				if ( substr($this->copyManufDir, 0, 1)=='/') $this->copyManufDir = HIKASHOP_ROOT.substr($this->copyManufDir, 1, strlen($this->copyManufDir)-1);
				elseif ( substr($this->copyManufDir, 0, 1)=='\\') $this->copyManufDir = HIKASHOP_ROOT.substr($this->copyManufDir, 1, strlen($this->copyManufDir)-1);
			}
			else
			{
				$this->copyImgDir = HIKASHOP_ROOT.'components/com_virtuemart/shop_image/product/';
				$this->copyImgDir = HIKASHOP_ROOT.'components/com_virtuemart/shop_image/category/';
				$this->copyImgDir = HIKASHOP_ROOT.'components/com_virtuemart/shop_image/vendor/';
			}

		}
	}

	function parseConfig($string)
	{
		$arraypath = array(
			'media_category_path',
			'media_product_path',
			'media_manufacturer_path'
		);
		$paths =array();

		$firstparse = explode('|', $string);
		foreach ($firstparse as $fp)
		{
			$secondparse = explode('=', $fp);
			if (in_array($secondparse[0],$arraypath))
			{
				$thirdparse = explode('"', $secondparse[1]);
				$paths[$secondparse[0]] = $thirdparse[1];
			}
		}
		return $paths;
	}

	function finishImport() {

		if( $this->db == null )
			return false;
		if ($this->vm_version!=1 && $this->vm_version!=2)
			return false;

		$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_cat`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_prod`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_prod = (int)($data[0]->max);

		if ($this->vm_version==1)
			$this->db->setQuery("SELECT max(user_id) as 'max' FROM `".$this->vmprefix."vm_user_info`;");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT max(virtuemart_user_id) as 'max' FROM `".$this->vmprefix."virtuemart_userinfos`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_user = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(order_vm_id) as 'max' FROM `#__hikashop_order`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_order = (int)($data[0]->max);

		if ($this->vm_version==1)
			$this->db->setQuery("SELECT max(file_id) as 'max' FROM `".$this->vmprefix."vm_product_files`;");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT max(vmpm.virtuemart_media_id) as 'max' FROM `".$this->vmprefix."virtuemart_products` vmp INNER JOIN `".$this->vmprefix."virtuemart_product_medias` vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id;");
		$data = $this->db->loadObject();
		$this->options->last_vm_pfile = (int)($data->max);

		if ($this->vm_version==1)
			$this->db->setQuery("SELECT max(coupon_id) as 'max' FROM `".$this->vmprefix."vm_coupons`;");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT max(virtuemart_coupon_id) as 'max' FROM `".$this->vmprefix."virtuemart_coupons`;");
		$data = $this->db->loadObject();
		$this->options->last_vm_coupon = (int)($data->max);

		if ($this->vm_version==1)
			$this->db->setQuery("SELECT max(tax_rate_id) as 'max' FROM `".$this->vmprefix."vm_tax_rate`;");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT max(virtuemart_calc_id) as 'max' FROM `".$this->vmprefix."virtuemart_calcs`;");
		$data = $this->db->loadObject();
		$this->options->last_vm_taxrate = (int)($data->max);

		if ($this->vm_version==1)
			$this->db->setQuery("SELECT max(manufacturer_id) as 'max' FROM `".$this->vmprefix."vm_manufacturer`;");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT max(virtuemart_manufacturer_id) as 'max' FROM `".$this->vmprefix."virtuemart_manufacturers`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_manufacturer = (int)($data[0]->max);

		$this->options->state = (MAX_IMPORT_ID+1);
		$query = 'REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('vm_import_state',".$this->options->state.",".$this->options->state.")".
				",('vm_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('vm_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('vm_import_last_vm_cat',".$this->options->last_vm_cat.",".$this->options->last_vm_cat.")".
				",('vm_import_last_vm_prod',".$this->options->last_vm_prod.",".$this->options->last_vm_prod.")".
				",('vm_import_last_vm_user',".$this->options->last_vm_user.",".$this->options->last_vm_user.")".
				",('vm_import_last_vm_order',".$this->options->last_vm_order.",".$this->options->last_vm_order.")".
				",('vm_import_last_vm_pfile',".$this->options->last_vm_pfile.",".$this->options->last_vm_pfile.")".
				",('vm_import_last_vm_coupon',".$this->options->last_vm_coupon.",".$this->options->last_vm_coupon.")".
				",('vm_import_last_vm_taxrate',".$this->options->last_vm_taxrate.",".$this->options->last_vm_taxrate.")".
				",('vm_import_last_vm_manufacturer',".$this->options->last_vm_manufacturer.",".$this->options->last_vm_manufacturer.")".
				';';
		$this->db->setQuery($query);
		$this->db->query();

		echo '<p'.$this->titlefont.'>Import finished !</p>';
		$class = hikashop_get('class.plugins');
		$infos = $class->getByName('system','vm_redirect');
		if($infos){
			$pkey = reset($class->pkeys);
			if(!empty($infos->$pkey)){
				if(version_compare(JVERSION,'1.6','<')){
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$infos->$pkey);
				}else{
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$infos->$pkey);
				}
				echo '<p>You can publish the <a'.$this->linkstyle.' href="'.$url.'">VirtueMart Fallback Redirect Plugin</a> so that your old VirtueMart links are automatically redirected to HikaShop pages and thus not loose the ranking of your content on search engines.</p>';
			}
		}
	}

	function createTables() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 1 :</span> Initialization Tables</p>';
		$create = true;

		$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_cat'),3));
		$this->db->setQuery($query);
		$table = $this->db->loadResult();
		if(!empty($table) ) {
			$create = false;
		}

		if( $create ) {
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_vm_prod` (`vm_id` int(10) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`vm_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_vm_cat` (`vm_id` int(10) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`vm_id`)) ENGINE=MyISAM");
			$this->db->query();

			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_address` ADD `address_vm_order_info_id` INT(11) NULL');
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_order` ADD `order_vm_id` INT(11) NULL');
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_order` ADD INDEX ( `order_vm_id` )');
			$this->db->query();
			$this->db->setQuery('ALTER IGNORE TABLE `#__hikashop_taxation` ADD `tax_vm_id` INT(11) NULL');
			$this->db->query();

			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> All table created</p>';

		}
		else
		{
			echo '<p>Tables have been already created.</p>';
		}

		return true;
	}

	function importTaxes() {
		if( $this->db == null )
			return false;

		$ret = false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 2 :</span> Import Taxes<p>';

		if ($this->vm_version==1)
		{
			$buffTable=$this->vmprefix."vm_tax_rate";
			$data = array(
				'tax_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id)",
				'tax_rate' => 'vmtr.tax_rate'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$buffTable.'` AS vmtr '.
			'WHERE vmtr.tax_rate_id > ' . (int)$this->options->last_vm_taxrate;
		}
		elseif ($this->vm_version==2)
		{
			$buffTable=$this->vmprefix."virtuemart_calcs";
			$data = array(
				'tax_namekey' => "CONCAT('VM_TAX_', vmtr.virtuemart_calc_id)",
				'tax_rate' => 'vmtr.calc_value'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$buffTable.'` AS vmtr '.
			'WHERE vmtr.virtuemart_calc_id > ' . (int)$this->options->last_vm_taxrate;
		}
		else
		{
			return false;
		}

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported taxes: ' . $total . '</p>';

		$element = 'tax';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		if ($this->vm_version==1)
		{
			$data = array(
				'category_type' => "'tax'",
				'category_name' => "CONCAT('Tax imported (', vmtr.tax_country,')')",
				'category_published' => '1',
				'category_parent_id' => $element,
				'category_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id,'_',hkz.zone_id)",
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'vm_tax_rate` vmtr '.
			"INNER JOIN `#__hikashop_zone` hkz ON vmtr.tax_country = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE vmtr.tax_rate_id > ' . (int)$this->options->last_vm_taxrate;
		}
		elseif  ($this->vm_version==2)
		{
			$data = array(
				'category_type' => "'tax'",
				'category_name' => "case when vmcs.country_name IS NULL then 'Tax imported (no country)' else CONCAT('Tax imported (', vmcs.country_name,')') end",
				'category_published' => '1',
				'category_parent_id' => $element,
				'category_namekey' => "case when hkz.zone_id IS NULL then CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_0') else CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_',hkz.zone_id) end",
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_calcs` vmtr '.
			"LEFT JOIN `".$this->vmprefix."virtuemart_calc_countries` vmcc ON vmtr.virtuemart_calc_id = vmcc.virtuemart_calc_id " .
			"LEFT JOIN `".$this->vmprefix."virtuemart_countries` vmcs ON vmcc.virtuemart_country_id = vmcs.virtuemart_country_id ".
			"LEFT JOIN `#__hikashop_zone` hkz ON vmcs.country_3_code = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			"WHERE vmtr.virtuemart_calc_id >" . $this->options->last_vm_taxrate;
		}
		else
		{
			return false;
		}

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxes Categories: ' . $total . '</p>';

		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}

		if ($this->vm_version==1)
		{
			$data = array(
				'zone_namekey' => 'hkz.zone_namekey',
				'category_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id,'_',hkz.zone_id)",
				'tax_namekey' => "CONCAT('VM_TAX_', vmtr.tax_rate_id)",
				'taxation_published' => '1',
				'taxation_type' => "''",
				'tax_vm_id' => 'vmtr.tax_rate_id'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'vm_tax_rate` vmtr '.
			"INNER JOIN #__hikashop_zone hkz ON vmtr.tax_country = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE vmtr.tax_rate_id > ' . (int)$this->options->last_vm_taxrate;
		}
		elseif  ($this->vm_version==2)
		{
			$data = array(
				'zone_namekey' => "case when hkz.zone_namekey IS NULL then '' else hkz.zone_namekey end",
				'category_namekey' => "case when hkz.zone_id IS NULL then CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_0') else  CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_',hkz.zone_id) end",
				'tax_namekey' => "CONCAT('VM_TAX_', vmtr.virtuemart_calc_id)",
				'taxation_published' => '1',
				'taxation_type' => "''",
				'tax_vm_id' => 'vmtr.virtuemart_calc_id'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_calcs` vmtr '.
			"LEFT JOIN `".$this->vmprefix."virtuemart_calc_countries` vmcc ON vmtr.virtuemart_calc_id = vmcc.virtuemart_calc_id " .
			"LEFT JOIN `".$this->vmprefix."virtuemart_countries` vmcs ON vmcc.virtuemart_country_id = vmcs.virtuemart_country_id ".
			"LEFT JOIN `".$this->vmprefix."hikashop_zone` hkz ON vmcs.country_3_code = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			"WHERE vmtr.virtuemart_calc_id >" . $this->options->last_vm_taxrate;

		}

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxations: ' . $total . '</p>';

		$ret = true;
		return $ret;
	}

	function importManufacturers() {
		if( $this->db == null )
			return false;
		$ret = false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 3 :</span> Import Manufacturers</p>';

		$element = 'manufacturer';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		if ($this->vm_version==1)
		{
			$buffTable=$this->vmprefix."vm_manufacturer";
			$data = array(
				'category_type' => "'manufacturer'",
				'category_name' => "vmm.mf_name ",
				'category_published' => '1',
				'category_parent_id' => $element,
				'category_namekey' => "CONCAT('VM_MANUFAC_', vmm.manufacturer_id )",
				'category_description' => 'vmm.mf_desc',
				'category_menu' => 'vmm.manufacturer_id'
			);

			$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$buffTable.'` vmm '.
			'WHERE vmm.manufacturer_id > ' . (int)$this->options->last_vm_manufacturer;
		}
		else if ($this->vm_version==2)
		{
			$buffTable=$this->vmprefix."virtuemart_manufacturers_".$this->vm_current_lng;

			$data = array(
				'category_type' => "'manufacturer'",
				'category_name' => "vmm.mf_name ",
				'category_published' => '1',
				'category_parent_id' => $element,
				'category_namekey' => "CONCAT('VM_MANUFAC_', vmm.virtuemart_manufacturer_id )",
				'category_description' => 'vmm.mf_desc',
				'category_menu' => 'vmm.virtuemart_manufacturer_id'
			);

			$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$buffTable.'` vmm '.
			'WHERE vmm.virtuemart_manufacturer_id > ' . (int)$this->options->last_vm_manufacturer;
		}
		else
		{
			return false;
		}

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Manufacturers : ' . $total . '</p>';

		if( $total > 0 )
		{
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}
		$ret = true;
		return $ret;
	}


	function importCategories() {

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 4 :</span> Import General Categories</p>';

		if( $this->db == null )
			return false;

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$rebuild = false;
		$ret = false;
		$offset = 0;
		$count = 100;


		$statuses = array(
			'P' => 'created',
			'C' => 'confirmed',
			'X' => 'cancelled',
			'R'=> 'refunded' ,
			'S' => 'shipped'
		);
		$this->db->setQuery("SELECT category_keywords, category_parent_id FROM `#__hikashop_category` WHERE category_type = 'status' AND category_name = 'confirmed'");
		$data = $this->db->loadObject();
		$status_category = $data->category_parent_id;
		if( $data->category_keywords != 'C' ) {
			foreach($statuses as $k => $v) {
				$this->db->setQuery("UPDATE `#__hikashop_category` SET category_keywords = '".$k."' WHERE category_type = 'status' AND category_name = '".$v."'; ");
				$this->db->query();
			}
		}

		if ($this->vm_version==1)
			$this->db->setQuery("SELECT order_status_code, order_status_name, order_status_description FROM `".$this->vmprefix."vm_order_status` WHERE order_status_name NOT IN ('".implode("','",$statuses)."');");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT order_status_code, order_status_name, order_status_description FROM `".$this->vmprefix."virtuemart_orderstates` WHERE order_status_name NOT IN ('".implode("','",$statuses)."');");
		else
			return false;

		$data = $this->db->loadObjectList();

		if( count($data) > 0 )
		{
			$sql0 = 'INSERT IGNORE INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
				'`category_namekey`,`category_access`,`category_menu`,`category_keywords`) VALUES ';

			$id = $this->options->max_hk_cat + 1;
			$sep = '';
			foreach($data as $c) {
				$d = array(
					$id++,
					$status_category,
					"'status'",
					$this->db->quote( strtolower($c->order_status_name) ),
					$this->db->quote( $c->order_status_description ),
					'1',
					$this->db->quote('status_vm_import_'.strtolower(str_replace(' ','_',$c->order_status_name))),
					"'all'",
					'0',
					$this->db->quote( $c->order_status_code )
				);
				if ($this->vm_version==2)
				{
					$d[3]=$this->db->quote(strtolower(JText::_($c->order_status_name)));
					$d[6]=$this->db->quote('status_vm_import_'.strtolower(str_replace(' ','_',JText::_($c->order_status_name))));
				}
				$sql0 .= $sep.'('.implode(',',$d).')';
				$sep = ',';
			}

			$this->db->setQuery($sql0);
			$this->db->query();
			$total = $this->db->getAffectedRows();

			if( $total > 0 )
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : ' . $total . '</p>';
				$rebuild = true;

				$this->options->max_hk_cat += $total;
				$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
				$this->db->query();
				$sql0 = '';
			}
			else
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : 0</p>';
			}
		}

		if ($this->vm_version==1)
		{
			$this->db->setQuery('SELECT * FROM `'.$this->vmprefix.'vm_category` vmc '.
					'LEFT JOIN `'.$this->vmprefix.'vm_category_xref` vmcx ON vmc.category_id = vmcx.category_child_id '.
					'LEFT JOIN `#__hikashop_vm_cat` hkvm ON vmc.category_id = hkvm.vm_id '.
					'ORDER BY category_parent_id ASC, list_order ASC, category_id ASC;');
		}
		elseif ($this->vm_version==2)
		{
			$buffTable=$this->vmprefix."virtuemart_categories_".$this->vm_current_lng;

			$this->db->setQuery('SELECT * FROM `'.$this->vmprefix.'virtuemart_categories` vmc '.
					"LEFT JOIN `".$buffTable."` vmceg ON vmc.virtuemart_category_id = vmceg.virtuemart_category_id ".
					"LEFT JOIN `".$this->vmprefix."virtuemart_category_medias` vmcm ON vmceg.virtuemart_category_id = vmcm.virtuemart_category_id ".
					"LEFT JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmcm.virtuemart_media_id = vmm.virtuemart_media_id ".
					'LEFT JOIN `'.$this->vmprefix.'virtuemart_category_categories` vmcc ON vmceg.virtuemart_category_id = vmcc.category_child_id '.
					'LEFT JOIN `#__hikashop_vm_cat` hkvm ON vmc.virtuemart_category_id = hkvm.vm_id '.
					'ORDER BY category_parent_id ASC, vmc.ordering ASC, vmc.virtuemart_category_id ASC;');
		}
		else
		{
			return false;
		}
		$data = $this->db->loadObjectList();

		$total = count($data);
		if( $total == 0 ) {
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported category : 0</p>';
			if( $rebuild )
				$this->importRebuildTree();
			return true;
		}


		$sql0 = 'INSERT INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
			'`category_ordering`,`category_namekey`,`category_created`,`category_modified`,`category_access`,`category_menu`) VALUES ';
		$sql1 = 'INSERT INTO `#__hikashop_vm_cat` (`vm_id`,`hk_id`) VALUES ';
		$sql2 = 'INSERT INTO `#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
		$doSql2 = false;
		$doSql1 = false;

		$i = $this->options->max_hk_cat + 1;
		$ids = array( 0 => $this->options->main_cat_id);
		$cpt = 0;
		$sep = '';

		foreach($data as $c)
		{
			if( !empty($c->vm_id) )
			{
				if ($this->vm_version==1)
					$ids[$c->category_id] = $c->hk_id;
				elseif ($this->vm_version==2)
					$ids[$c->virtuemart_category_id] = $c->hk_id;
			}
			else
			{
				$doSql1 = true;
				if ($this->vm_version==1)
				{
					$ids[$c->category_id] = $i;
					$sql1 .= $sep.'('.$c->category_id.','.$i.')';
				}
				elseif ($this->vm_version==2)
				{
					$ids[$c->virtuemart_category_id] = $i;
					$sql1 .= $sep.'('.$c->virtuemart_category_id.','.$i.')';
				}
				$i++;

				$sep = ',';
			}
			$cpt++;
			if( $cpt >= $count )
				break;
		}

		$sql1 .= ';';

		if( $cpt == 0 ) {
			if( $rebuild )
				$this->importRebuildTree();
			return true;
		}

		$cpt = 0;
		$sep = '';
		$sep2 = '';


		foreach($data as $c)
		{
			if( !empty($c->vm_id) )
				continue;

			if ($this->vm_version==1)
				$id = $ids[$c->category_id];
			elseif ($this->vm_version==2)
				$id = $ids[$c->virtuemart_category_id];
			if(!empty($ids[$c->category_parent_id]))
				$pid = (int)$ids[$c->category_parent_id];
			else
				$pid = $ids[0];

			$element = new stdClass();
			$element->category_id = $id;
			$element->category_parent_id = $pid;
			$element->category_name = $c->category_name;
			$nameKey = $categoryClass->getNameKey($element);


			if ($this->vm_version==1)
			{
				$d = array(
					$id,
					$pid,
					"'product'",
					$this->db->quote($c->category_name),
					$this->db->quote($c->category_description),
					'1',
					$c->list_order,
					$this->db->quote($nameKey),
					$c->cdate,
					$c->mdate,
					"'all'",
					'0'
				);
			}
			elseif ($this->vm_version==2)
			{
				$d = array(
					$id,
					$pid,
					"'product'",
					$this->db->quote($c->category_name),
					$this->db->quote($c->category_description),
					'1',
					$c->ordering,
					$this->db->quote($nameKey),
					$this->db->quote($c->created_on),
					$this->db->quote($c->modified_on),
					"'all'",
					'0'
				);
			}

			$sql0 .= $sep.'('.implode(',',$d).')';

			if ($this->vm_version==1)
			{
				if( !empty($c->category_full_image)) {
					$doSql2 = true;

					$sql2 .= $sep2."('','','".$c->category_full_image."','category',".$id.')';
					$sep2 = ',';
					$file_name = str_replace('\\','/',$c->category_full_image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyCatImgDir,$c->category_full_image, $this->options->uploadfolder.$file_name);
				}
			}
			elseif ($this->vm_version==2)
			{
				if( !empty($c->file_title)) {
					$doSql2 = true;
					$sql2 .= $sep2."('','','".$c->file_title."','category',".$id.')';
					$sep2 = ',';
					$file_name = str_replace('\\','/',$c->file_title);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyCatImgDir,$c->file_title, $this->options->uploadfolder.$file_name);
				}
			}
			$sep = ',';

			$cpt++;
			if( $cpt >= $count )
				break;
		}

		if($cpt > 0)
		{
			$sql0 .= ';';
			$this->db->setQuery($sql0);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Categories : ' . $total . '</p>';
		}
		else
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported category : 0</p>';
		}

		if( isset($total) && $total > 0)
		{
			$rebuild = true;
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
		}

		if ($doSql1)
		{
			$this->db->setQuery($sql1);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links : ' . $total . '</p>';
		}
		else
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links : 0</p>';

		if( $doSql2 )
		{
			$sql2 .= ';';
			$this->db->setQuery($sql2);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Categories files : ' . $total . '</p>';
		}
		else
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Categories files : 0</p>';


		if( $rebuild )
			$this->importRebuildTree();

		if( $cpt < $count )
			$ret = true;
		return $ret;
	}


	function importProducts() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 5 :</span> Import Products</p>';

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$ret = false;
		$count = 100;
		$offset = $this->options->current;
		$max = 0;

		if ($this->vm_version==1)
		{
			$this->db->setQuery('SELECT vmp.product_id, vmp.product_full_image '.
							'FROM `'.$this->vmprefix.'vm_product` vmp '.
							'LEFT JOIN `#__hikashop_vm_prod` hkprod ON vmp.product_id = hkprod.vm_id '.
							"WHERE vmp.product_id > ".$offset." AND hkprod.hk_id IS NULL AND (vmp.product_full_image IS NOT NULL) AND vmp.product_full_image <> '' ".
							'ORDER BY product_id ASC LIMIT '.$count.';'
			);

			$data = $this->db->loadObjectList();

			if (!empty($data))
			{
				foreach($data as $c) {
					if( !empty($c->product_full_image) ) {
						$file_name = str_replace('\\','/',$c->product_full_image);
						if( strpos($file_name,'/') !== false ) {
							$file_name = substr($file_name, strrpos($file_name,'/'));
						}
						$this->copyFile($this->copyImgDir,$c->product_full_image, $this->options->uploadfolder.$file_name);
						$max = $c->product_id;
					}
				}
			}
		}
		elseif ($this->vm_version==2)
		{
			$this->db->setQuery('SELECT vmp.virtuemart_product_id, vmm.file_title '.
							'FROM `'.$this->vmprefix.'virtuemart_products` vmp '.
							"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id ".
							"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id ".
							'LEFT JOIN `#__hikashop_vm_prod` hkprod ON vmp.virtuemart_product_id = hkprod.vm_id '.
							"WHERE vmp.virtuemart_product_id > ".$offset." AND hkprod.hk_id IS NULL AND (vmm.file_title IS NOT NULL) AND vmm.file_title <> '' ".
							'ORDER BY vmp.virtuemart_product_id ASC LIMIT '.$count.';'
			);

			$data = $this->db->loadObjectList();

			if (!empty($data))
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying products images... </p>';
				foreach($data as $c) {
					if( !empty($c->file_title) ) {
						$file_name = str_replace('\\','/',$c->file_title);
						if( strpos($file_name,'/') !== false ) {
							$file_name = substr($file_name, strrpos($file_name,'/'));
						}
						$this->copyFile($this->copyImgDir,$c->file_title, $this->options->uploadfolder.$file_name); //???
						$max = $c->virtuemart_product_id;
					}
				}
			}
		}
		else
		{
			return false;
		}

		if( $max > 0 ) {
			echo '<p>Copying files...(last proccessed product id: ' . $max . ')</p>';
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		if ($this->vm_version==1)
		{
			$this->db->setQuery('SELECT config_value FROM `#__hikashop_config` WHERE config_namekey = \'weight_symbols\'');
			$data = $this->db->loadObjectList();
			$wghtunit = explode(',',$data[0]->config_value);

			$this->db->setQuery('SELECT config_value FROM `#__hikashop_config` WHERE config_namekey = \'volume_symbols\'');
			$data = $this->db->loadObjectList();
			$dimunit = explode(',',$data[0]->config_value);

			$data = array(
				'product_name' => 'vmp.product_name',
				'product_description' => "CONCAT(vmp.product_s_desc,'<hr id=\"system-readmore\"/>',vmp.product_desc)",
				'product_quantity' => 'case when vmp.product_in_stock IS NULL or vmp.product_in_stock < 0 then 0 else vmp.product_in_stock end',
				'product_code' => 'vmp.product_sku',
				'product_published' => "case when vmp.product_publish = 'Y' then 1 else 0 end",
				'product_hit' => '0',
				'product_created' => 'vmp.cdate',
				'product_modified' => 'vmp.mdate',
				'product_sale_start' => 'vmp.product_available_date',
				'product_tax_id' => 'hkc.category_id',
				'product_type' => "'main'",
				'product_url' => 'vmp.product_url',
				'product_weight' => 'vmp.product_weight',
				'product_weight_unit' => "case when LOWER(vmp.product_weight_uom) = 'pounds' then 'lb' else '".$wghtunit[0]."' end",
				'product_dimension_unit' => "case when LOWER(vmp.product_lwh_uom) = 'inches' then 'in' else '".$dimunit[0]."' end",
				'product_sales' => 'vmp.product_sales',
				'product_width' => 'vmp.product_width',
				'product_length' => 'vmp.product_length',
				'product_height' => 'vmp.product_height',
			);

			$sql1 = 'INSERT IGNORE INTO `#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_product` AS vmp '.
			'LEFT JOIN `#__hikashop_taxation` hkt ON hkt.tax_vm_id = product_tax_id '.
			'LEFT JOIN `#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
			'LEFT JOIN `#__hikashop_vm_prod` AS hkp ON vmp.product_id = hkp.vm_id '.
			'WHERE hkp.hk_id IS NULL ORDER BY vmp.product_id ASC;';

			$data = array(
				'vm_id' => 'vmp.product_id',
				'hk_id' => 'hkp.product_id'
			);

			$sql2 = 'INSERT IGNORE INTO `#__hikashop_vm_prod` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_product` AS vmp INNER JOIN `#__hikashop_product` AS hkp ON CONVERT(vmp.product_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'LEFT JOIN `#__hikashop_vm_prod` hkvm ON hkvm.vm_id = vmp.product_id '.
			'WHERE hkvm.hk_id IS NULL;';

			$sql3 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `'.$this->vmprefix.'vm_product` AS vmp ON CONVERT(vmp.product_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.product_parent_id = hkvm.vm_id '.
			'SET hkp.product_parent_id = hkvm.hk_id;';

			$data = array(
				'file_name' => "''",
				'file_description' => "''",
				'file_path' => "SUBSTRING_INDEX(vmp.product_full_image,'/',-1)",
				'file_type' => "'product'",
				'file_ref_id' => 'hkvm.hk_id'
			);

			$sql4 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_product` AS vmp '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.product_id = hkvm.vm_id '.
			'WHERE vmp.product_id > '.$this->options->last_vm_prod.' AND (vmp.product_full_image IS NOT NULL) AND (vmp.product_full_image <>'." '');";

			$sql5 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON hkp.product_id = hkvm.hk_id '.
			'INNER JOIN `'.$this->vmprefix.'vm_product_mf_xref` AS vmm ON vmm.product_id = hkvm.vm_id '.
			"INNER JOIN `#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND vmm.manufacturer_id = hkc.category_menu ".
			'SET hkp.product_manufacturer_id = hkc.category_id '.
			'WHERE vmm.manufacturer_id > '.$this->options->last_vm_manufacturer.' OR vmm.product_id > '.$this->options->last_vm_prod.';';

		}

		elseif ($this->vm_version==2) //OK
		{
			$buffTable=$this->vmprefix."virtuemart_products_".$this->vm_current_lng;

			$data = array(
				'product_name' => 'vmpeg.product_name',
				'product_description' => "CONCAT(vmpeg.product_s_desc,'<hr id=\"system-readmore\"/>',vmpeg.product_desc)",
				'product_quantity' => 'case when vmp.product_in_stock IS NULL or vmp.product_in_stock < 0 then 0 else vmp.product_in_stock end',
				'product_code' => 'vmp.product_sku',
				'product_published' => "vmp.published",
				'product_hit' => '0',
				'product_created' => "case when vmp.created_on='0000-00-00 00:00:00' then 0 else 1 end",
				'product_modified' => 'vmp.modified_on',
				'product_sale_start' => 'vmp.product_available_date',
				'product_tax_id' => 'hkc.category_id',
				'product_type' => "'main'",
				'product_url' => 'vmp.product_url',
				'product_weight' => 'vmp.product_weight',
				'product_weight_unit' => "LOWER(vmp.product_weight_uom)",
				'product_dimension_unit' => "LOWER(vmp.product_lwh_uom)",
				'product_sales' => 'vmp.product_sales',
				'product_width' => 'vmp.product_width',
				'product_length' => 'vmp.product_length',
				'product_height' => 'vmp.product_height',
			);

			$sql1 = 'INSERT IGNORE INTO `#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` AS vmp '.
			"INNER JOIN `".$buffTable."` vmpeg ON vmp.virtuemart_product_id = vmpeg.virtuemart_product_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_product_prices` vmpp ON vmpeg.virtuemart_product_id = vmpp.virtuemart_product_id ".
			'LEFT JOIN `#__hikashop_taxation` hkt ON hkt.tax_vm_id = vmpp.product_tax_id '.
			'LEFT JOIN `#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
			'LEFT JOIN `#__hikashop_vm_prod` AS hkp ON vmp.virtuemart_product_id = hkp.vm_id '.
			'WHERE hkp.hk_id IS NULL ORDER BY vmp.virtuemart_product_id ASC;';

			$data = array(
				'vm_id' => 'vmp.virtuemart_product_id',
				'hk_id' => 'hkp.product_id'
			);

			$sql2 = 'INSERT IGNORE INTO `#__hikashop_vm_prod` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` AS vmp '.
			'INNER JOIN `#__hikashop_product` AS hkp ON CONVERT(vmp.product_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'LEFT JOIN `#__hikashop_vm_prod` hkvm ON vmp.virtuemart_product_id = hkvm.vm_id '.
			'WHERE hkvm.hk_id IS NULL;';

			$sql3 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_products` AS vmp ON CONVERT(vmp.product_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.product_parent_id = hkvm.vm_id '.
			'SET hkp.product_parent_id = hkvm.hk_id;';

			$data = array(
				'file_name' => "''",
				'file_description' => "''",
				'file_path' => "SUBSTRING_INDEX(vmm.file_title,'/',-1)",
				'file_type' => "'product'",
				'file_ref_id' => 'hkvm.hk_id'
			);

			$sql4 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` AS vmp '.
			"INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.virtuemart_product_id = hkvm.vm_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` vmpm ON hkvm.vm_id = vmpm.virtuemart_product_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id ".
			'WHERE vmp.virtuemart_product_id > '.$this->options->last_vm_prod.' AND (vmm.file_title <>'." '');";


			$sql5 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON hkp.product_id = hkvm.hk_id '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_product_manufacturers` AS vmpm ON vmpm.virtuemart_product_id = hkvm.vm_id '.
			"INNER JOIN `#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND vmpm.virtuemart_manufacturer_id = hkc.category_menu ".
			'SET hkp.product_manufacturer_id = hkc.category_id '.
			'WHERE vmpm.virtuemart_manufacturer_id > '.$this->options->last_vm_manufacturer.' OR vmpm.virtuemart_product_id > '.$this->options->last_vm_prod.';';

		}

		else
		{
			return false;
		}


		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links: ' . $total . '</p>';

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating products for parent links: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products files: ' . $total . '</p>';

		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating products manufacturers: ' . $total . '</p>';

		$ret = true;

		return $ret;
	}


	function importProductPrices() {

		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 6 :</span> Import Product Prices</p>';

		$ret = false;
		$cpt = 0;

		if ($this->vm_version==1)
		{
			$this->db->setQuery('INSERT IGNORE INTO #__hikashop_price (`price_product_id`,`price_value`,`price_currency_id`,`price_min_quantity`,`price_access`) '
					.'SELECT hkprod.hk_Id, product_price, hkcur.currency_id, price_quantity_start, \'all\' '
					.'FROM `'.$this->vmprefix.'vm_product_price` vm INNER JOIN #__hikashop_vm_prod hkprod ON vm.product_id = hkprod.vm_id '
					.'INNER JOIN #__hikashop_currency hkcur ON CONVERT(vm.product_currency USING utf8) = CONVERT( hkcur.currency_code USING utf8) '
					.'WHERE product_price_vdate < NOW() AND (product_price_edate = 0 OR product_price_edate > NOW() ) '
					.'AND vm.product_id > ' . (int)$this->options->last_vm_prod
			);
		}
		else if ($this->vm_version==2)
		{
			$this->db->setQuery('INSERT IGNORE INTO #__hikashop_price (`price_product_id`,`price_value`,`price_currency_id`,`price_min_quantity`,`price_access`) '
					.'SELECT hkprod.hk_Id, product_price, hkcur.currency_id, price_quantity_start, \'all\' '
					.'FROM '.$this->vmprefix.'virtuemart_product_prices vmpp '
					.'INNER JOIN #__hikashop_vm_prod hkprod ON vmpp.virtuemart_product_id = hkprod.vm_id '
					.'INNER JOIN '.$this->vmprefix.'virtuemart_currencies vmc ON vmpp.product_currency = vmc.virtuemart_currency_id '
					.'INNER JOIN #__hikashop_currency hkcur ON CONVERT(vmc.currency_code_3 USING utf8) = CONVERT( hkcur.currency_code USING utf8) '
					.'WHERE vmpp.virtuemart_product_id > ' . (int)$this->options->last_vm_prod
			);
		}
		else
		{
			return false;
		}

		$ret = $this->db->query();
		$cpt = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Prices imported : ' . $cpt .'</p>';
		return $ret;
	}


	function importProductCategory() {

		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 7 :</span> Import Product Category</p>';

		$data = array(
			'category_id' => 'vmc.hk_id',
			'product_id' => 'vmp.hk_id',
			'ordering' => '`product_list`',
		);

		if ($this->vm_version==1)
		{
			$sql = 'INSERT IGNORE INTO `#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'vm_product_category_xref` vm '.
			'INNER JOIN `#__hikashop_vm_cat` vmc ON vm.category_id = vmc.vm_id '.
			'INNER JOIN `#__hikashop_vm_prod` vmp ON vm.product_id = vmp.vm_id '.
			'WHERE vmp.vm_id > ' . (int)$this->options->last_vm_prod . ' OR vmc.vm_id > ' . (int)$this->options->last_vm_cat;
		}
		else if ($this->vm_version==2)
		{
			$data['ordering'] = '`ordering`';
			$sql = 'INSERT IGNORE INTO `#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_product_categories` vmpc '.
			'INNER JOIN #__hikashop_vm_cat vmc ON vmpc.virtuemart_category_id = vmc.vm_id '.
			'INNER JOIN #__hikashop_vm_prod vmp ON vmpc.virtuemart_product_id = vmp.vm_id '.
			'WHERE vmp.vm_id > ' . (int)$this->options->last_vm_prod . ' OR vmc.vm_id > ' . (int)$this->options->last_vm_cat;
		}
		else
		{
			return false;
		}

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products categories: ' . $total . '</p>';
		return true;
	}


	function importUsers() {

		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 8 :</span> Import Users</p>';

		$ret = false;

		if ($this->vm_version==1)
		{
			$sql0 = 'INSERT IGNORE INTO `#__hikashop_user` (`user_cms_id`,`user_email`) '.
					'SELECT vmui.user_id, vmui.user_email FROM `'.$this->vmprefix.'vm_user_info` AS vmui '.
					'LEFT JOIN `#__hikashop_user` AS hkusr ON vmui.user_id = hkusr.user_cms_id '.
					'WHERE hkusr.user_cms_id IS NULL;';
		}
		else if ($this->vm_version==2)
		{
			$sql0 = 'INSERT IGNORE INTO `#__hikashop_user` (`user_cms_id`,`user_email`) '.
					'SELECT u.id, u.email FROM `'.$this->vmprefix.'virtuemart_userinfos` vmui INNER JOIN `#__users` u ON vmui.virtuemart_user_id = u.id '.
					'LEFT JOIN `#__hikashop_user` AS hkusr ON vmui.virtuemart_user_id = hkusr.user_cms_id '.
					'WHERE hkusr.user_cms_id IS NULL;';
		}
		else
		{
			return false;
		}


		$data = array(
			'address_user_id' => 'hku.user_id',
			'address_firstname' => 'vmui.first_name',
			'address_middle_name' => 'vmui.middle_name',
			'address_lastname' => 'vmui.last_name',
			'address_company' => 'vmui.company',
			'address_street' => 'CONCAT(vmui.address_1,\' \',vmui.address_2)',
			'address_post_code' => 'vmui.zip',
			'address_city' => 'vmui.city',
			'address_telephone' => 'vmui.phone_1',
			'address_telephone2' => 'vmui.phone_2',
			'address_fax' => 'vmui.fax',
			'address_state' => 'vmui.state',
			'address_country' => 'vmui.country',
			'address_published' => 4
		);

		if ($this->vm_version==1)
		{
			$sql1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
					'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_user_info` AS vmui INNER JOIN `#__hikashop_user` AS hku ON vmui.user_id = hku.user_cms_id WHERE vmui.user_id > '.$this->options->last_vm_user.' ORDER BY vmui.user_id ASC';
		}
		elseif ($this->vm_version==2)
		{
			$data['address_state'] = 'vms.state_3_code';
			$data['address_country'] = 'vmc.country_3_code';
			$sql1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
					'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_userinfos` AS vmui '.
					"INNER JOIN `".$this->vmprefix."virtuemart_states` vms ON vmui.virtuemart_state_id = vms.virtuemart_state_id ".
					"INNER JOIN `".$this->vmprefix."virtuemart_countries` vmc ON vmui.virtuemart_country_id = vmc.virtuemart_country_id ".
					'INNER JOIN `#__hikashop_user` AS hku ON vmui.virtuemart_user_id = hku.user_cms_id '.
					'WHERE vmui.virtuemart_user_id > '.$this->options->last_vm_user.' ORDER BY vmui.virtuemart_user_id ASC;';
		}

		$sql2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 3 WHERE address_published = 4;';

		$sql3 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 2 WHERE address_published = 3;';

		$sql4 = "UPDATE `#__hikashop_address` AS a SET a.address_country = '' WHERE address_published > 3;";
		$sql5 = "UPDATE `#__hikashop_address` AS a SET a.address_state = '' WHERE address_published > 2;";
		$sql6 = 'UPDATE `#__hikashop_address` AS a SET a.address_published = 1 WHERE address_published > 1;';

		$this->db->setQuery($sql0);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Users: ' . $total . '</p>';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses countries: ' . $total . '</p>';

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses states: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$this->db->setQuery($sql5);
		$this->db->query();
		$this->db->setQuery($sql6);
		$this->db->query();

		$ret = true;

		return $ret;
	}

	function importOrders() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 10 :</span> Import Orders</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$total = 0;
		$guest = 0;


		if ($this->vm_version==1)
			$this->db->setQuery("SELECT name FROM `".$this->vmprefix."vm_userfield` WHERE type = 'euvatid' AND published = 1");
		elseif ($this->vm_version==2)
			$this->db->setQuery("SELECT name FROM `".$this->vmprefix."virtuemart_userfields` WHERE name = 'tax_exemption_number' AND published = 1");
		else
			return false;

		$vat_cols = $this->db->loadObjectList();
		if( isset($vat_cols) && $vat_cols !== null && is_array($vat_cols) && count($vat_cols)>0)
			$vat_cols = 'vmui.' . $vat_cols[0]->name;
		else
			$vat_cols = "''";

		if ($this->vm_version==1)
		{
			$data = array(
				'order_number' => 'vmo.order_number',
				'order_vm_id' => 'vmo.order_id',
				'order_user_id' => 'case when vmo.user_id < 0 OR hkusr.user_cms_id IS NULL then 0 else hkusr.user_id end ',
				'order_status' => 'hkc.category_name',
				'order_discount_code' => 'vmo.coupon_code',
				'order_discount_price' => 'vmo.coupon_discount',
				'order_created' => 'vmo.cdate',
				'order_ip' => 'vmo.ip_address',
				'order_currency_id' => 'hkcur.currency_id',
				'order_shipping_price' => 'vmo.order_shipping',
				'order_shipping_method' => "'vm import'",
				'order_shipping_id' => '1',
				'order_payment_id' => 0,
				'order_payment_method' => '\'vm import\'',
				'order_full_price' => 'vmo.order_total',
				'order_modified' => 'vmo.mdate',
				'order_partner_id' => 0,
				'order_partner_price' => 0,
				'order_partner_paid' => 0,
				'order_type' => "'sale'",
				'order_partner_currency_id' => 0,
				'order_shipping_tax' => 'vmo.order_shipping_tax',
				'order_discount_tax' => 0
			);

			$sql1 = 'INSERT IGNORE INTO `#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_orders` AS vmo '.
				'JOIN `'.$this->vmprefix.'vm_order_status` AS vmos ON vmo.order_status = vmos.order_status_code '.
				'JOIN `#__hikashop_category` AS hkc ON vmos.order_status_name = hkc.category_name AND hkc.category_type = \'status\' '.
				'JOIN `#__hikashop_currency` AS hkcur ON CONVERT(vmo.order_currency USING utf8) = CONVERT(hkcur.currency_code USING utf8) '.
				'LEFT JOIN `#__hikashop_user` AS hkusr ON vmo.user_id = hkusr.user_cms_id '.
				'WHERE vmo.order_id > ' . (int)$this->options->last_vm_order . ' '.
				'GROUP BY vmo.order_id '.
				'ORDER BY vmo.order_id ASC;';

			$this->db->setQuery('SELECT * FROM `#__hikashop_order` WHERE order_user_id = 0');
			$data = $this->db->loadObjectList();

			if (!empty($data))
			{
				$buffstring = '(';
				$sep = '';
				foreach ($data as $d)
				{
					$buffstring .= $sep.$d->order_vm_id;
					$sep = ',';
				}
				$buffstring .= ')';

				$sql0 = 'SELECT vmou.user_email FROM ``'.$this->vmprefix.'vm_orders` AS vmo '.
						'INNER JOIN ``'.$this->vmprefix.'vm_order_user_info` AS vmou ON vmo.order_id = vmou.order_id '.
						'WHERE vmo.order_id IN '.$buffstring;

				$this->db->setQuery($sql0);
				$buffdata = $this->db->loadObjectList();

				$string = '';
				$sep = '';
				foreach ($buffdata as $bf)
				{
					$string .= $sep."('0','".$bf->user_email."')";
					$sep =  ',';
				}

				$sql0 = 'INSERT IGNORE INTO `#__hikashop_user` (`user_cms_id`,`user_email`) VALUES '.$string;
				$this->db->setQuery($sql0);
				$this->db->query();

				$sql0 = 'UPDATE `#__hikashop_order` AS hko '.
						'INNER JOIN ``'.$this->vmprefix.'vm_orders` AS vmo ON hko.order_vm_id = vmo.order_id '.
						'INNER JOIN ``'.$this->vmprefix.'vm_order_user_info` AS vmou ON vmo.user_id = vmou.user_id '.
						'INNER JOIN `#__hikashop_user` as hku ON vmou.user_email = hku.user_email '.
						'SET hko.order_user_id = hku.user_id '.
						'WHERE hko.order_user_id = 0';

				$this->db->setQuery($sql0);
				$this->db->query();
				$guest = $this->db->getAffectedRows();
			}

			$data = array(
				'address_user_id' => 'vmui.user_id',
				'address_firstname' => 'vmui.first_name',
				'address_middle_name' => 'vmui.middle_name',
				'address_lastname' => 'vmui.last_name',
				'address_company' => 'vmui.company',
				'address_street' => "CONCAT(vmui.address_1,' ',vmui.address_2)",
				'address_post_code' => 'vmui.zip',
				'address_city' => 'vmui.city',
				'address_telephone' => 'vmui.phone_1',
				'address_telephone2' => 'vmui.phone_2',
				'address_fax' => 'vmui.fax',
				'address_state' => 'vmui.state',
				'address_country' => 'vmui.country',
				'address_published' => "case when vmui.address_type = 'BT' then 7 else 8 end",
				'address_vat' => $vat_cols,
				'address_vm_order_info_id' => 'vmui.order_id'
			);

			$sql2_1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_order_user_info` AS vmui WHERE vmui.order_id > '.$this->options->last_vm_order.' ORDER BY vmui.order_info_id ASC';
		}
		elseif ($this->vm_version==2)
		{
			$data = array(
				'order_number' => 'vmo.order_number',
				'order_vm_id' => 'vmo.virtuemart_order_id',
				'order_user_id' => 'hkusr.user_id',
				'order_status' => 'hkc.category_name',
				'order_discount_code' => 'vmo.coupon_code',
				'order_discount_price' => 'vmo.coupon_discount',
				'order_created' => 'vmo.created_on',
				'order_ip' => 'vmo.ip_address',
				'order_currency_id' => 'hkcur.currency_id',
				'order_shipping_price' => 'vmo.order_shipment',
				'order_shipping_method' => "'vm import'",
				'order_shipping_id' => '1',
				'order_payment_id' => 0,
				'order_payment_method' => "'vm import'",
				'order_full_price' => 'vmo.order_total',
				'order_modified' => 'vmo.modified_on',
				'order_partner_id' => 0,
				'order_partner_price' => 0,
				'order_partner_paid' => 0,
				'order_type' => "'sale'",
				'order_partner_currency_id' => 0,
				'order_shipping_tax' => 'vmo.order_shipment_tax',
				'order_discount_tax' => 0
			);

			$sql1 = 'INSERT IGNORE INTO `#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_orders` AS vmo '.
				'INNER JOIN `'.$this->vmprefix.'virtuemart_currencies` vmc ON vmo.order_currency = vmc.virtuemart_currency_id '.
				'INNER JOIN `#__hikashop_currency` hkcur ON CONVERT(vmc.currency_code_3 USING utf8) = CONVERT( hkcur.currency_code USING utf8) '. //needed ?
				'JOIN `'.$this->vmprefix.'virtuemart_orderstates` AS vmos ON vmo.order_status = vmos.order_status_code '.
				'JOIN `#__hikashop_category` AS hkc ON vmos.order_status_name = hkc.category_name AND hkc.category_type = \'status\' '. //No U founded
				'INNER JOIN `#__hikashop_user` AS hkusr ON vmo.virtuemart_user_id = hkusr.user_cms_id '.
				'WHERE vmo.virtuemart_order_id > ' . (int)$this->options->last_vm_order . ' '.
				'GROUP BY vmo.virtuemart_order_id '.
				'ORDER BY vmo.virtuemart_order_id ASC;';

			$data = array(
				'address_user_id' => 'vmui.virtuemart_user_id',
				'address_firstname' => 'vmui.first_name',
				'address_middle_name' => 'vmui.middle_name',
				'address_lastname' => 'vmui.last_name',
				'address_company' => 'vmui.company',
				'address_street' => "CONCAT(vmui.address_1,' ',vmui.address_2)",
				'address_post_code' => 'vmui.zip',
				'address_city' => 'vmui.city',
				'address_telephone' => 'vmui.phone_1',
				'address_telephone2' => 'vmui.phone_2',
				'address_fax' => 'vmui.fax',
				'address_state' => 'vms.state_3_code',
				'address_country' => 'vmc.country_3_code',
				'address_published' => "case when vmui.address_type = 'BT' then 7 else 8 end",
				'address_vat' => $vat_cols,
				'address_vm_order_info_id' => 'vmui.virtuemart_order_id'
			);

			$sql2_1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_order_userinfos` AS vmui '.
				"INNER JOIN `".$this->vmprefix."virtuemart_states` vms ON vmui.virtuemart_state_id = vms.virtuemart_state_id ".
				"INNER JOIN `".$this->vmprefix."virtuemart_countries` vmc ON vmui.virtuemart_country_id = vmc.virtuemart_country_id ".
				'WHERE vmui.virtuemart_order_id > '.$this->options->last_vm_order.' ORDER BY vmui.virtuemart_order_userinfo_id ASC';
		}
		else
		{
			return false;
		}

		$sql2_2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 6 WHERE address_published >= 7;';

		$sql2_3 = 'UPDATE `#__hikashop_address` AS a '. // todo
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 5 WHERE address_published = 6;';

		$sql2_4 = 'UPDATE `#__hikashop_address` AS a '.
				'SET address_published = 0 WHERE address_published > 4;';

		$sql3 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_vm_order_info_id = o.order_vm_id '.
			'SET o.order_billing_address_id = a.address_id, o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_billing_address_id = 0 AND address_published >= 7 ;";

		$sql4 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_vm_order_info_id = o.order_vm_id '.
			'SET o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_shipping_address_id = 0 AND address_published >= 8 ;";

		if ($this->vm_version==1)
		{
			$sql5 = 'UPDATE `#__hikashop_order` AS a '.
					'JOIN `'.$this->vmprefix.'vm_order_payment` AS o ON a.order_vm_id = o.order_id '.
					'JOIN `'.$this->vmprefix.'vm_payment_method` AS p ON o.payment_method_id = p.payment_method_id '.
					"SET a.order_payment_method = CONCAT('vm import: ', p.payment_method_name) ".
					'WHERE a.order_vm_id > ' . (int)$this->options->last_vm_order;
		}
		elseif ($this->vm_version==2)
		{
			$buffTable=$this->vmprefix."virtuemart_paymentmethods_".$this->vm_current_lng;

			$sql5 = 'UPDATE `#__hikashop_order` AS a '.
					'JOIN `'.$this->vmprefix.'virtuemart_orders` AS vmo ON a.order_vm_id = vmo.virtuemart_order_id '.
					'JOIN `'.$buffTable.'` AS vmp ON vmo.virtuemart_paymentmethod_id = vmp.virtuemart_paymentmethod_id '.
					"SET a.order_payment_method = CONCAT('vm import: ', vmp.payment_name) ".
					'WHERE a.order_vm_id > ' . (int)$this->options->last_vm_order;
		}

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported orders: ' . $total . ' (including '.$guest.' guests)</p>';

		$this->db->setQuery($sql2_1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported orders addresses: ' . $total . '</p>';

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating billing addresses: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating shipping addresses: ' . $total . '</p>';

		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating order payments: ' . $total . '</p>';

		$this->db->setQuery($sql2_2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating orders: ' . $total;
		$this->db->setQuery($sql2_3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '/' . $total;
		$this->db->setQuery($sql2_4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '/' . $total . '</p>';

		$ret = true;

		return $ret;
	}

	function importOrderItems() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 11 :</span> Import Order Items</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;

		$data = array(
			'order_id' => 'hko.order_id',
			'product_id' => 'hkp.hk_id',
			'order_product_quantity' => 'vmoi.product_quantity',
			'order_product_name' => 'vmoi.order_item_name',
			'order_product_code' => 'vmoi.order_item_sku',
			'order_product_price' => 'vmoi.product_item_price',
			'order_product_tax' => '(vmoi.product_final_price - vmoi.product_item_price)',
			'order_product_options' => "''"
		);

		if ($this->vm_version==1)
		{
			$sql = 'INSERT IGNORE INTO `#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_order_item` AS vmoi '.
				'INNER JOIN `#__hikashop_order` AS hko ON vmoi.order_id = hko.order_vm_id '.
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmoi.product_id '.
				'WHERE vmoi.order_id > ' . (int)$this->options->last_vm_order . ';';
		}
		elseif ($this->vm_version==2)
		{

			$sql = 'INSERT IGNORE INTO `#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_order_items` AS vmoi '.
				'INNER JOIN `#__hikashop_order` AS hko ON vmoi.virtuemart_order_id = hko.order_vm_id '.
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON vmoi.virtuemart_product_id = hkp.vm_id '.
				'WHERE vmoi.virtuemart_order_id > ' . (int)$this->options->last_vm_order . ';';
		}
		else
		{
			return false;
		}


		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Orders Items imported : '. $total .'</p>';
		$ret = true;

		return $ret;
	}

	function importDownloads() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 12 :</span> Import Downloads</p>';

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');
		$app = JFactory::getApplication();

		$ret = false;
		$count = 100;
		$offset = $this->options->current;

		if( $offset == 0 )
		{
			$offset = $app->getUserState($this->sessionParams.'last_vm_pfile');
			if (!$offset)
				$offset = $this->options->last_vm_pfile;
		}

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'download_number_limit';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$dl_limit = $data[0]->config_value;

		if ($this->vm_version==1)
		{
			$sql = 'SELECT vmf.file_id,vmf.file_name,vmf.file_is_image FROM `'.$this->vmprefix.'vm_product_files` AS vmf WHERE vmf.file_id > '.$offset.' ORDER BY vmf.file_id ASC LIMIT '.$count.';';

			$this->db->setQuery($sql);
			$data = $this->db->loadObjectList();
			$max = 0;
			foreach($data as $c) {
				$file_name = str_replace('\\','/',$c->file_name);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$dstFolder = $this->options->uploadsecurefolder;
				if($c->file_is_image){
					$dstFolder = $this->options->uploadfolder;
				}
				$this->copyFile($this->copyImgDir,$c->file_name, $dstFolder.$file_name);
				$max = $c->file_id;
			}
		}
		elseif ($this->vm_version==2)
		{
			$sql = 'SELECT vmm.virtuemart_media_id,vmm.file_title,vmm.file_is_product_image '.
			'FROM `'.$this->vmprefix.'virtuemart_products` vmp ' .
			"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` AS vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id " .
			"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id " .
			'WHERE vmm.virtuemart_media_id > '.$offset.
			' ORDER BY vmm.virtuemart_media_id ASC LIMIT '.$count.';';

			$this->db->setQuery($sql);
			$data = $this->db->loadObjectList();
			$max = 0;

			foreach($data as $c) {
				$file_name = str_replace('\\','/',$c->file_title);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$dstFolder = $this->options->uploadsecurefolder;
				if($c->file_is_product_image){
					$dstFolder = $this->options->uploadfolder;
				}
				$this->copyFile($this->copyImgDir,$c->file_title, $dstFolder.$file_name);
				$max = $c->virtuemart_media_id;
			}
			$app->setUserState($this->sessionParams.'last_vm_pfile',$max);
		}
		else
		{
			return false;
		}

		if( $max > 0 ) {
			echo '<p>Copying files...<br/>(Last processed file id: ' . $max . ')</p>';
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		if ($this->vm_version==1)
		{
			$data = array(
				'file_name' => 'vmf.file_title',
				'file_description' => 'vmf.file_description',
				'file_path' => "SUBSTRING_INDEX(SUBSTRING_INDEX(vmf.file_name, '/', -1), '\\\\', -1)",
				'file_type' => "case when vmf.file_is_image = 1 then 'product' else 'file' end",
				'file_ref_id' => 'hkp.hk_id'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_product_files` AS vmf '.
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmf.file_product_id '.
				'WHERE vmf.file_id > '.$this->options->last_vm_pfile.';';
		}
		elseif ($this->vm_version==2)
		{
			$data = array(
				'file_name' => 'vmm.file_title',
				'file_description' => 'vmm.file_description',
				'file_path' => "SUBSTRING_INDEX(SUBSTRING_INDEX(vmm.file_title, '/', -1), '\\\\', -1)",
				'file_type' => "case when vmm.file_is_product_image = 1 then 'product' else 'file' end",
				'file_ref_id' => 'hkp.hk_id'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` vmp '.
				"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` AS vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id ".
				"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id " .
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON vmm.virtuemart_media_id = hkp.vm_id '.
				'WHERE vmm.virtuemart_media_id > '.$this->options->last_vm_pfile.';';
		}
		else
		{
			return false;
		}

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable files imported : ' . $total . '</p>';

		if ($this->vm_version==1)
		{
			$data = array(
				'file_id' => 'hkf.file_id',
				'order_id' => 'hko.order_id',
				'download_number' => '(' . $dl_limit . '- vmd.download_max)'
			);
			$sql = 'INSERT IGNORE INTO `#__hikashop_download` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'vm_product_download` AS vmd '.
				'INNER JOIN `#__hikashop_order` AS hko ON hko.order_vm_id = vmd.order_id '.
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmd.product_id '.
				'INNER JOIN `#__hikashop_file` AS hkf ON ( CONVERT(hkf.file_name USING utf8) = CONVERT(vmd.file_name USING utf8) )'.
				"WHERE hkf.file_type = 'file' AND (hkp.hk_id = hkf.file_ref_id) AND (vmd.product_id > ".$this->options->last_vm_prod.' OR vmd.order_id > ' . (int)$this->options->last_vm_order . ');';
		}
		elseif ($this->vm_version==2)
		{

			return true;
		}

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable order files imported : ' . $total . '</p>';

		$ret = true;

		return $ret;
	}

	function importDiscount() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 9 :</span> Import Discount</p>';

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'main_currency';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$main_currency = $data[0]->config_value;

		$data = array(
			'discount_type' => "'coupon'", //coupon or discount
			'discount_published' => '1',
			'discount_code' => '`coupon_code`',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when percent_or_total = 'total' then coupon_value else 0 end",
			'discount_percent_amount' => "case when percent_or_total = 'percent' then coupon_value else 0 end",
			'discount_quota' => "case when coupon_type = 'gift' then 1 else 0 end"
		);

		if ($this->vm_version==1)
		{
			$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM '.$this->vmprefix.'vm_coupons WHERE coupon_id > ' . (int)$this->options->last_vm_coupon;
		}
		elseif ($this->vm_version==2) //OK
		{
			$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM '.$this->vmprefix.'virtuemart_coupons WHERE virtuemart_coupon_id > ' . (int)$this->options->last_vm_coupon;
		}
		else
		{
			return false;
		}
		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount codes / coupons imported : ' . $total . '</p>';

		if ($this->vm_version==1)
		{
			$data = array(
				'discount_type' => "'discount'", //coupon or discount
				'discount_published' => '1',
				'discount_code' => "CONCAT('discount_', vmp.product_sku)",
				'discount_currency_id' => $main_currency,
				'discount_flat_amount' => "case when vmd.is_percent = 0 then vmd.amount else 0 end",
				'discount_percent_amount' => "case when vmd.is_percent = 1 then vmd.amount else 0 end",
				'discount_quota' => "''",
				'discount_product_id' => 'hkp.hk_id',
				'discount_category_id' => '0',
				'discount_start' => "vmd.start_date",
				'discount_end' => "vmd.end_date"
			);

			$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM '.$this->vmprefix.'vm_product vmp '.
				'INNER JOIN `'.$this->vmprefix.'vm_product_discount` vmd ON vmp.product_discount_id = vmd.discount_id '.
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmp.product_id '.
				'WHERE vmp.product_id > ' . (int)$this->options->last_vm_prod;
		}
		elseif ($this->vm_version==2)
		{
			$data = array(
				'discount_type' => "'discount'",
				'discount_published' => '1',
				'discount_code' => "CONCAT('discount_', vmp.product_sku)",
				'discount_currency_id' => $main_currency,
				'discount_flat_amount' => "case when vmc.percent_or_total = 'total' then vmc.coupon_value else 0 end",
				'discount_percent_amount' => "case when vmc.percent_or_total = 'percent' then vmc.coupon_value else 0 end",
				'discount_quota' => "''",
				'discount_product_id' => 'hkp.hk_id',
				'discount_category_id' => '0',
				'discount_start' => "vmc.coupon_start_date",
				'discount_end' => "vmc.coupon_expiry_date"
			);

			$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM '.$this->vmprefix.'virtuemart_products vmp '.
				'INNER JOIN `'.$this->vmprefix.'virtuemart_product_prices` vmpp ON vmp.virtuemart_product_id = vmpp.virtuemart_product_id '.
				'INNER JOIN `'.$this->vmprefix.'virtuemart_coupons` vmc ON vmpp.product_discount_id = vmc.virtuemart_coupon_id '.
				'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmp.virtuemart_product_id '.
				'WHERE vmp.virtuemart_product_id > ' . (int)$this->options->last_vm_prod;
		}

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount product imported : ' . $total . '</p>';

		$ret = true;

		return $ret;
	}
}
