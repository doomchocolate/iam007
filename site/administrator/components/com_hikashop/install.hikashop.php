<?php
function com_install(){
	if(!defined('DS'))
		define('DS', DIRECTORY_SEPARATOR);
	include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');
	$lang = JFactory::getLanguage();
	$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
	$installClass = new hikashopInstall();
	$installClass->addPref();
	$installClass->updatePref();
	$installClass->addMenus();
	$installClass->addModules();
	$installClass->updateSQL();
	$installClass->displayInfo();
}
class hikashopInstall{
	var $level = 'Business';
	var $version = '2.3.0';
	var $freshinstall = true;
	var $update = false;
	var $fromLevel = '';
	var $fromVersion = '';
	var $db;
	function hikashopInstall(){
		$this->db = JFactory::getDBO();
		$this->db->setQuery("SELECT COUNT(*) as `count` FROM `#__hikashop_config` WHERE `config_namekey` IN ('version','level') LIMIT 2");
		$results = $this->db->loadObject();
		if(!empty($results) && $results->count == 2)
			$this->freshinstall = false;
		if(empty($results)) {
			$install_sql = file_get_contents(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop'.DIRECTORY_SEPARATOR.'tables.sql');
			$queries = explode(';', $install_sql);
			foreach($queries as $query) {
				$query = trim($query);
				if(!empty($query)) {
					$this->db->setQuery($query);
					$this->db->query();
				}
			}
		}
	}
	function displayInfo(){
		unset($_SESSION['hikashop']['li']);
		echo '<h1>Please wait... </h1><h2>HikaShop will now automatically install the Plugins and the Modules</h2>';
		$url = 'index.php?option=com_hikashop&ctrl=update&task=install&fromversion='.$this->fromVersion.'&update='.(int)$this->update.'&freshinstall='.(int)$this->freshinstall;
		echo '<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>';
		echo "<script language=\"javascript\" type=\"text/javascript\">document.location.href='$url';</script>\n";
	}
	function updatePref(){
		$this->db->setQuery("SELECT `config_namekey`, `config_value` FROM `#__hikashop_config` WHERE `config_namekey` IN ('version','level') LIMIT 2");
		$results = $this->db->loadObjectList('config_namekey');
		if($results['version']->config_value == $this->version && $results['level']->config_value == $this->level)
			return true;
		$this->update = true;
		$this->fromLevel = $results['level']->config_value;
		$this->fromVersion = $results['version']->config_value;

		if(version_compare($this->fromVersion,'1.5.6','<')){
			$config =& hikashop_config();
			$this->db->setQuery("INSERT IGNORE #__hikashop_config (`config_value`,`config_default`,`config_namekey`) VALUES ('0','1','detailed_tax_display'),('0','1','simplified_breadcrumbs'),(".(int)$config->get('thumbnail_x',100).",'100','product_image_x'),(".(int)$config->get('thumbnail_y',100).",'100','product_image_y');");
			$this->db->query();
		}

		$query = "REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`) VALUES ('level',".$this->db->Quote($this->level)."),('version',".$this->db->Quote($this->version)."),('installcomplete','0')";
		$this->db->setQuery($query);
		$this->db->query();
	}
	function updateSQL(){
		if(!$this->update){
			return true;
		}

		if(version_compare($this->fromVersion,'1.0.2','<')){
			$query = 'UPDATE `#__hikashop_user` AS a LEFT JOIN `#__hikashop_user` AS b ON a.user_email=b.user_email SET a.user_email=CONCAT(\'old_\',a.user_email) WHERE a.user_id>b.user_id';
			$this->db->setQuery($query);
			$this->db->query();
			$query = 'ALTER TABLE `#__hikashop_user` ADD UNIQUE (`user_email`)';
			$this->db->setQuery($query);
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.1.2','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_max_per_order` INT UNSIGNED DEFAULT 0");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.3.4','<')){
			$this->db->setQuery("SHOW COLUMNS FROM `#__hikashop_discount`");
			$columns = $this->db->loadObjectList();
			$test = false;
			foreach($columns as $column){
				if($column->Field == 'discount_auto_load'){
					$test = true;
				}
			}
			if(!$test){
				$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_auto_load` TINYINT UNSIGNED DEFAULT 0");
				$this->db->query();
			}
		}

		if(version_compare($this->fromVersion,'1.3.3','>') && version_compare($this->fromVersion,'1.3.6','<')){
			$this->db->setQuery("DELETE FROM `#__modules` WHERE module='HikaShop Content Module' OR  module='HikaShop Cart Module' OR  module='HikaShop Currency Switcher Module'");
			$this->db->query();
		}


		if(version_compare($this->fromVersion,'1.4.1','<')){

			$rand=rand(0,999999999);
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload',`config_default` = 'media/com_hikashop/upload' WHERE `config_namekey` = 'uploadfolder' AND `config_value` LIKE 'components/com_hikashop/upload%' ");
			$this->db->query();
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload/safe',`config_default` = 'media/com_hikashop/upload/safe' WHERE `config_namekey` = 'uploadsecurefolder' AND `config_value` LIKE 'components/com_hikashop/upload/safe%' ");
			$this->db->query();
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'media/com_hikashop/upload/safe/logs/report_".$rand.".log',`config_default` = 'media/com_hikashop/upload/safe/logs/report_".$rand.".log' WHERE `config_namekey` IN ('cron_savepath','payment_log_file') ");
			$this->db->query();

			$updateClass = hikashop_get('helper.update');
			$removeFiles = array();
			$removeFiles[] = HIKASHOP_FRONT.'css'.DS.'backend_default.css';
			$removeFiles[] = HIKASHOP_FRONT.'css'.DS.'frontend_default.css';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'cron_report.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_admin_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_creation_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_creation_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_status_notification.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'order_status_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'user_account.text.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'user_account.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'user_account_admin_notification.html.php';
			$removeFiles[] = HIKASHOP_FRONT.'mail'.DS.'user_account_admin_notification.html.php';
			foreach($removeFiles as $oneFile){
				if(is_file($oneFile)) JFile::delete($oneFile);
			}

			$fromFolders = array();
			$toFolders = array();
			$fromFolders[] = HIKASHOP_FRONT.'css';
			$toFolders[] = HIKASHOP_MEDIA.'css';
			$fromFolders[] = HIKASHOP_FRONT.'mail';
			$toFolders[] = HIKASHOP_MEDIA.'mail';
			$fromFolders[] = HIKASHOP_FRONT.'upload';
			$toFolders[] = HIKASHOP_MEDIA.'upload';

			foreach($fromFolders as $i => $oneFolder){
				if(!is_dir($oneFolder)) continue;
				if(is_dir($toFolders[$i]) || !@rename($oneFolder,$toFolders[$i])) $updateClass->copyFolder($oneFolder,$toFolders[$i]);
			}

			$deleteFolders = array();
			$deleteFolders[] = HIKASHOP_FRONT.'css';
			$deleteFolders[] = HIKASHOP_FRONT.'images';
			$deleteFolders[] = HIKASHOP_FRONT.'js';

			foreach($deleteFolders as $oneFolder){
				if(!is_dir($oneFolder)) continue;
				JFolder::delete($oneFolder);
			}

		}
		if(version_compare($this->fromVersion,'1.4.2','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_price` ADD `price_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_zone` ADD `zone_currency_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			if(version_compare(JVERSION,'1.6.0','<')){
				$query = 'UPDATE `#__plugins` SET `published`=0 WHERE  `element`=\'geolocation\' AND `folder`=\'hikashop\'';
			}else{
				$query = 'UPDATE `#__extensions` SET `enabled`=0 WHERE  `element`=\'geolocation\' AND `folder`=\'hikashop\'';
			}
			$this->db->setQuery($query);
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.4.5','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_group_after_purchase` VARCHAR( 255 ) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_contact` SMALLINT UNSIGNED DEFAULT 0");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.4.6','<')){
			$this->db->setQuery('ALTER TABLE `#__hikashop_product_related` DROP PRIMARY KEY ,
ADD PRIMARY KEY (  `product_id` ,  `product_related_id` ,  `product_related_type` )');
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_min_per_order` INT UNSIGNED DEFAULT 0");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.4.7','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` ADD `payment_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_shipping` ADD `shipping_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.4.8','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_history` ADD `history_user_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_tax_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_shipping_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_discount_tax` decimal(12,5) NOT NULL DEFAULT '0.00000'");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.4.9','<')){

			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_number` VARCHAR( 255 ) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("SELECT order_id,order_created FROM ".hikashop_table('order').' WHERE order_number=\'\'');
			$orders = $this->db->loadObjectList();
			if(!empty($orders)){
				foreach($orders as $k => $order){
					$orders[$k]->order_number = hikashop_encode($order);
				}
				$i = 0;
				$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_order_number` (`order_id` int(10) unsigned NOT NULL DEFAULT '0',`order_number` VARCHAR( 255 ) NOT NULL DEFAULT '') ENGINE=MyISAM ;");
				$this->db->query();
				$inserts = array();
				foreach($orders as $k => $order){
					$i++;
					$inserts[]='('.$order->order_id.','.$this->db->Quote($order->order_number).')';
					if($i >= 500){
						$i=0;
						$this->db->setQuery('INSERT IGNORE INTO `#__hikashop_order_number` (order_id,order_number) VALUES '.implode(',',$inserts));
						$this->db->query();
						$inserts = array();
					}
				}
				$this->db->setQuery('INSERT IGNORE INTO `#__hikashop_order_number` (order_id,order_number) VALUES '.implode(',',$inserts));
				$this->db->query();
				$this->db->setQuery('UPDATE `#__hikashop_order` AS a , `#__hikashop_order_number` AS b SET a.order_number=b.order_number WHERE a.order_id=b.order_id AND a.order_number=\'\'');
				$this->db->query();
				$this->db->setQuery('DROP TABLE IF EXISTS `#__hikashop_order_number`');
				$this->db->query();
			}
		}
		if(version_compare($this->fromVersion,'1.5.0','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();

			if(!HIKASHOP_J30) {
				$columnsTable = $this->db->getTableFields(hikashop_table('product'));
				$columns = reset($columnsTable);
			} else {
				$columns = $this->db->getTableColumns(hikashop_table('product'));
			}
			$found = false;
			foreach($columns as $i => $oneColumn){
				if($oneColumn=="product_contact"){
					$found = true;
				}
			}
			if(!$found){
				$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_min_per_order` INT UNSIGNED DEFAULT 0");
				$this->db->query();
			}

			if(version_compare(JVERSION,'1.6.0','<')){
				$query = 'UPDATE `#__plugins` SET `published`=0 WHERE  `element`=\'hikashop\' AND `folder`=\'user\'';
			}else{
				$query = 'UPDATE `#__extensions` SET `enabled`=0 WHERE  `element`=\'hikashop\' AND `folder`=\'user\'';
			}
			$this->db->setQuery($query);
			$this->db->query();

			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_minimum_products` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_quota_per_user` INT UNSIGNED DEFAULT 0");
			$this->db->query();

		}

		if(version_compare($this->fromVersion,'1.5.2','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_keywords` VARCHAR(255) NOT NULL");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_meta_description` varchar(155) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product_related` ADD `product_related_ordering` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_last_seen_date` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_file` ADD `file_free_download` tinyint(3) unsigned NOT NULL DEFAULT '0'");
			$this->db->query();
			$manufacturer = null;
			$manufacturer->category_type = 'manufacturer';
			$manufacturer->category_name = 'manufacturer';
			$class = hikashop_get('class.category');
			$class->save($manufacturer);
		}

		if(version_compare($this->fromVersion,'1.5.3','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_limit` (
	`limit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`limit_product_id` int(11) NOT NULL DEFAULT '0',
	`limit_category_id` int(11) NOT NULL DEFAULT '0',
	`limit_per_product` tinyint(4) NOT NULL DEFAULT '0',
	`limit_periodicity` varchar(255) NOT NULL DEFAULT '',
	`limit_type` varchar(255) NOT NULL DEFAULT '',
	`limit_value` int(10) NOT NULL DEFAULT '0',
	`limit_unit` varchar(255) DEFAULT NULL,
	`limit_currency_id` int(11) NOT NULL DEFAULT '0',
	`limit_access` varchar(255) NOT NULL DEFAULT '',
	`limit_status` varchar(255) NOT NULL DEFAULT '',
	`limit_published` tinyint(4) NOT NULL DEFAULT '0',
	`limit_created` int(10) DEFAULT NULL,
	`limit_modified` int(10) DEFAULT NULL,
	`limit_start` int(10) DEFAULT NULL,
	`limit_end` int(10) DEFAULT NULL,
	PRIMARY KEY (`limit_id`)
) ENGINE=MyISAM ;");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_zone` ADD INDEX (  `zone_code_3` )");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_sales` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_categories` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_with_sub_categories` TINYINT( 1 ) NOT NULL DEFAULT 0");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE  `#__hikashop_payment` ADD  `payment_shipping_methods` TEXT NOT NULL DEFAULT  ''");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE  `#__hikashop_cart_product` ADD `cart_product_option_parent_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_order_product` ADD `order_product_option_parent_id` INT UNSIGNED DEFAULT 0");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_taxation` ADD `taxation_access` VARCHAR( 255 ) NOT NULL DEFAULT 'all'");
			$this->db->query();

			$class = hikashop_get('class.category');
			$tax = null;
			$tax->category_type = 'tax';
			$tax->category_parent_id = 'tax';
			$class->getMainElement($tax->category_parent_id);
			$tax->category_name = 'Default tax category';
			$tax->category_namekey = 'default_tax';
			$tax->category_depth = 2;
			$class->save($tax);
		}
		if(version_compare($this->fromVersion,'1.5.4','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_filter` (
	`filter_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`filter_name` varchar(250) NOT NULL,
	`filter_namekey` varchar(50) NOT NULL,
	`filter_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`filter_type` varchar(50) DEFAULT NULL,
	`filter_category_id` int(10) unsigned NOT NULL,
	`filter_ordering` smallint(5) unsigned DEFAULT '99',
	`filter_options` text,
	`filter_data` text NOT NULL,
	`filter_access` varchar(250) NOT NULL DEFAULT 'all',
	`filter_direct_application` tinyint(3) NOT NULL DEFAULT '0',
	`filter_value` text NOT NULL,
	`filter_category_childs` tinyint(3) unsigned NOT NULL,
	`filter_height` int(50) unsigned NOT NULL,
	`filter_deletable` tinyint(3) unsigned NOT NULL,
	`filter_dynamic` tinyint(3) unsigned NOT NULL,
	PRIMARY KEY (`filter_id`)
) ENGINE=MyISAM ;");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` ADD `payment_currency` VARCHAR( 255 ) NOT NULL");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.5.5','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_waitlist` (
	`waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
	`product_id` int(11) NOT NULL,
	`date` int NOT NULL,
	`email` varchar(255) NOT NULL,
	`name` varchar(255) DEFAULT NULL,
	`product_item_id` int(11) NOT NULL,
	PRIMARY KEY (`waitlist_id`)
) ENGINE=MyISAM ;");
			$this->db->query();

			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_product` ADD `product_waitlist` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0'");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_discount` ADD `discount_coupon_nodoubling` TINYINT NULL;");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_discount` ADD `discount_coupon_product_only` TINYINT NULL;");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.5.6','<')){
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_taxation` ADD `taxation_cumulative` TINYINT NULL;");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_order` ADD `order_tax_info` text NOT NULL;");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_order_product` ADD `order_product_tax_info` text NOT NULL;");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_category` ADD `category_layout` varchar(255) NOT NULL DEFAULT '';");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_product` ADD `product_layout` varchar(255) NOT NULL DEFAULT ''");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.5.7','<')){
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_characteristic` ADD `characteristic_alias` varchar(255) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_average_score` FLOAT NOT NULL, ADD `product_total_vote` INT NOT NULL DEFAULT '0'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_address` ADD `address_default` TINYINT NOT NULL DEFAULT '0';");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_file` ADD `file_ordering` INT UNSIGNED NOT NULL DEFAULT 0, ADD `file_limit` INT NOT NULL DEFAULT 0;");
			$this->db->query();
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_vote_user` (
	`vote_user_id` int(11) NOT NULL,
	`vote_user_user_id` varchar(26) NOT NULL,
	`vote_user_useful` tinyint(4) NOT NULL
) ENGINE=MyISAM ;");
			$this->db->query();
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_vote` (
	`vote_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`vote_ref_id` int(11) NOT NULL,
	`vote_type` varchar(15) NOT NULL,
	`vote_user_id` varchar(26) NOT NULL,
	`vote_rating` float NOT NULL,
	`vote_comment` varchar(255) NOT NULL,
	`vote_useful` int(11) NOT NULL,
	`vote_pseudo` varchar(25) NOT NULL,
	`vote_ip` varchar(15) NOT NULL,
	`vote_email` varchar(80) NOT NULL,
	`vote_date` int(10) unsigned NOT NULL,
	`vote_published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'1.5.8','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_vote` CHANGE `vote_comment` `vote_comment` TEXT NOT NULL;");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_payment_price` decimal(17,5) NOT NULL DEFAULT '0.00000'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` ADD `payment_price` decimal(17,5) NOT NULL DEFAULT '0.00000'");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.5.9','<')){
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_shipping_price` (
	`shipping_price_id` int(11) NOT NULL AUTO_INCREMENT,
	`shipping_id` int(11) NOT NULL,
	`shipping_price_ref_id` int(11) NOT NULL,
	`shipping_price_ref_type` varchar(255) NOT NULL DEFAULT 'product',
	`shipping_price_min_quantity` int(11) NOT NULL DEFAULT '0',
	`shipping_price_value` decimal(15,7) NOT NULL DEFAULT '0',
	`shipping_fee_value` decimal(15,7) NOT NULL DEFAULT '0',
	PRIMARY KEY (`shipping_price_id`)
) ENGINE=MyISAM;");
			$this->db->query();
			$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = '0',`config_default` = '1' WHERE `config_namekey`='variant_increase_perf';");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_product` ADD `product_page_title` varchar(255) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE `#__hikashop_category` ADD `category_page_title` varchar(255) NOT NULL DEFAULT ''");
			$this->db->query();
			$this->db->setQuery("ALTER IGNORE TABLE  `#__hikashop_characteristic` ADD  `characteristic_ordering` INT( 12 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `characteristic_alias`");
			$this->db->query();

			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_badge` (
	`badge_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`badge_name` varchar(255) NOT NULL DEFAULT '',
	`badge_image` varchar(255) NOT NULL DEFAULT '',
	`badge_start` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_end` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_category_id` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_category_childs` tinyint(4) NOT NULL DEFAULT '0',
	`badge_discount_id` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_size` float(12,2) unsigned NOT NULL,
	`badge_position` varchar(255) NOT NULL DEFAULT 'bottomleft',
	`badge_vertical_distance` int(10) NOT NULL DEFAULT '0',
	`badge_horizontal_distance` int(10) NOT NULL DEFAULT '0',
	`badge_margin` int(10) NOT NULL DEFAULT '0',
	`badge_published` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`badge_id`)
) ENGINE=MyISAM;");
			$this->db->query();

			try{
				$this->db->setQuery("ALTER TABLE `#__hikashop_cart` ADD `cart_type` varchar(25) NOT NULL DEFAULT 'cart', ".
					"ADD `cart_name` varchar(50) NOT NULL, ".
					"ADD `cart_share` varchar(255) NOT NULL DEFAULT 'nobody', ".
					"ADD `cart_current` INT NOT NULL DEFAULT '0'");
				$this->db->query();
			}catch(Exception $e){}

			$this->db->setQuery("ALTER TABLE `#__hikashop_cart_product` ADD `cart_product_wishlist_id` INT NOT NULL DEFAULT '0'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_order_product` ADD `order_product_wishlist_id` INT NOT NULL DEFAULT '0'");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE `#__hikashop_widget` ADD `widget_published` tinyint(4) NOT NULL DEFAULT 1, ".
				"ADD `widget_ordering` int(11) NOT NULL DEFAULT 0, ".
				"ADD `widget_access` varchar(250) NOT NULL DEFAULT 'all'");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE  `#__hikashop_field` CHANGE  `field_value`  `field_value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'1.6.0','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_address` ADD `address_street2` TEXT NOT NULL;");
			$this->db->query();
		}

		if(version_compare($this->fromVersion,'2.0.0','<')){
			$this->db->setQuery("ALTER TABLE  `#__hikashop_order` ADD  `order_invoice_id` INT NOT NULL DEFAULT '0'");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE  `#__hikashop_order` ADD  `order_invoice_number` VARCHAR( 255 ) NOT NULL DEFAULT '';");
			$this->db->query();
			$this->db->setQuery("UPDATE  `#__hikashop_order` SET `order_invoice_number`=`order_number`;");
			$this->db->query();
			$this->db->setQuery("UPDATE  `#__hikashop_order` SET `order_invoice_id`=`order_id`;");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_download` ADD `file_pos` int(10) NOT NULL DEFAULT '1';");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_download` DROP PRIMARY KEY , ADD PRIMARY KEY ( `file_id` , `order_id` , `file_pos` );");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product_category` ADD  `product_parent_id` INT NOT NULL DEFAULT '0'");
			$this->db->query();

			$file = HIKASHOP_BACK.'admin.hikashop.php';
			if(file_exists($file)) JFile::delete($file);
		}

		if(version_compare($this->fromVersion,'2.0.0','=')){
			$this->db->setQuery("SELECT * FROM `#__hikashop_product_category` LIMIT 1");
			$check = $this->db->loadObject();
			if(!isset($check->product_parent_id)) {
				$this->db->setQuery("ALTER TABLE `#__hikashop_product_category` ADD `product_parent_id` INT NOT NULL DEFAULT '0'");
				$this->db->query();
			}
			unset($check);

		}
		if(version_compare($this->fromVersion,'2.1.0','<')){
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_alias` VARCHAR( 255 ) NOT NULL DEFAULT '';");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_alias` VARCHAR( 255 ) NOT NULL DEFAULT '';");
			$this->db->query();

			if($this->level=='starter'){
				$this->db->setQuery("DELETE FROM `#__hikashop_widget` ;");
				$this->db->query();
			}
			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_invoice_created` INT(10) UNSIGNED NOT NULL DEFAULT '0'");
			$this->db->query();
			$this->db->setQuery("UPDATE #__hikashop_order SET `order_invoice_created` = `order_created` WHERE `order_invoice_created`=0 AND `order_invoice_id`>0;");
			$this->db->query();
		}
		if(version_compare($this->fromVersion,'2.1.1','<')){
			$this->db->setQuery("SHOW COLUMNS FROM `#__hikashop_product`");
			$columns = $this->db->loadObjectList();
			$test = false;
			foreach($columns as $column){
				if($column->Field == 'product_price_percentage'){
					$test = true;
				}
			}
			if(!$test){
				$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_price_percentage` decimal(15,7) NOT NULL DEFAULT '0';");
				$this->db->query();
			}

			$this->db->setQuery("SHOW COLUMNS FROM `#__hikashop_discount`");
			$columns = $this->db->loadObjectList();
			$test = false;
			foreach($columns as $column){
				if($column->Field == 'discount_affiliate'){
					$test = true;
				}
			}
			if(!$test){
				$this->db->setQuery("ALTER TABLE `#__hikashop_discount` ADD `discount_affiliate` INT(10) NOT NULL DEFAULT '0';");
				$this->db->query();
			}

			try{
				$this->db->setQuery("ALTER TABLE `#__hikashop_badge` ADD `badge_keep_size` INT(10) NOT NULL DEFAULT '0';");
				$this->db->query();
			}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion,'2.1.2','<')){
			$this->db->setQuery("SHOW COLUMNS FROM `#__hikashop_product`");
			$columns = $this->db->loadObjectList();
			$test = false;
			foreach($columns as $column){
				if($column->Field == 'product_canonical'){
					$test = true;
				}
			}
			if(!$test){
				$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_canonical` VARCHAR( 255 ) NOT NULL DEFAULT '';");
				$this->db->query();
				$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_msrp` decimal(15,7) NULL DEFAULT '0';");
				$this->db->query();
			}

			try{
				$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_site_id` VARCHAR( 255 ) NULL DEFAULT '';");
				$this->db->query();
			}catch(Exception $e){}

			try{
				$this->db->setQuery("ALTER TABLE `#__hikashop_badge` ADD `badge_quantity` VARCHAR( 255 ) NULL DEFAULT '';");
				$this->db->query();
			}catch(Exception $e){}

			try{
				$this->db->setQuery("ALTER TABLE `#__hikashop_category` ADD `category_canonical` VARCHAR( 255 ) NOT NULL DEFAULT '';");
				$this->db->query();
			}catch(Exception $e){}
		}
		if(version_compare($this->fromVersion, '2.2.0', '<')) {
			$this->db->setQuery("ALTER TABLE `#__hikashop_payment` ADD `payment_ordering` int(10) unsigned NOT NULL DEFAULT '0', ".
				" ADD `payment_published` tinyint(4) NOT NULL DEFAULT '1';");
			$this->db->query();

			try{
				$this->db->setQuery("ALTER TABLE `#__hikashop_payment` DROP INDEX payment_type");
				$this->db->query();
			}catch(Exception $e){}

			$this->db->setQuery("ALTER TABLE `#__hikashop_order` ADD `order_shipping_params` text NOT NULL DEFAULT '', ".
				" ADD `order_payment_params` text NOT NULL DEFAULT '';");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE `#__hikashop_order_product` ".
				" ADD `order_product_shipping_id` varchar(255) NOT NULL DEFAULT '', ".
				" ADD `order_product_shipping_method` varchar(255) NOT NULL DEFAULT '', ".
				" ADD `order_product_shipping_price` decimal(17,5) NOT NULL DEFAULT '0.00000', ".
				" ADD `order_product_shipping_tax` decimal(17,5) NOT NULL DEFAULT '0.00000', ".
				" ADD `order_product_shipping_params` varchar(255) NOT NULL DEFAULT '';");
			$this->db->query();

			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_massaction` (
	`massaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`massaction_name` varchar(255) NOT NULL DEFAULT '',
	`massaction_description` text NOT NULL,
	`massaction_table` varchar(255) NOT NULL DEFAULT 'product',
	`massaction_published` tinyint(4) NOT NULL DEFAULT '1',
	`massaction_lasttime` int(10) unsigned NOT NULL DEFAULT '0',
	`massaction_triggers` text NOT NULL,
	`massaction_filters` text NOT NULL,
	`massaction_actions` text NOT NULL,
	`massaction_report` text NOT NULL,
	PRIMARY KEY (`massaction_id`),
	KEY `massaction_table` (`massaction_table`)
) ENGINE=MyISAM;");
			$this->db->query();
		}
		if(version_compare($this->fromVersion, '2.2.1', '<')) {
			$this->db->setQuery("
CREATE TABLE IF NOT EXISTS `#__hikashop_plugin` (
	`plugin_id` INT(10) NOT NULL AUTO_INCREMENT,
	`plugin_type` VARCHAR(255) NOT NULL,
	`plugin_published` INT(4) NOT NULL DEFAULT 0,
	`plugin_name` VARCHAR(255) NOT NULL,
	`plugin_ordering` INT(10) NOT NULL DEFAULT 0,
	`plugin_description` TEXT NOT NULL DEFAULT '',
	`plugin_params` TEXT NOT NULL DEFAULT '',
	`plugin_access` VARCHAR(255) NOT NULL DEFAULT 'all',
	PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM");
			$this->db->query();

			$this->db->setQuery("ALTER TABLE `#__hikashop_field` ADD `field_display` text NOT NULL DEFAULT '';");
			$this->db->query();

			$this->db->setQuery("SHOW COLUMNS FROM `#__hikashop_badge`");
			$columns = $this->db->loadObjectList();
			$test = false;
			foreach($columns as $column){
				if($column->Field == 'badge_url'){
					$test = true;
				}
			}
			if(!$test){
				$this->db->setQuery("ALTER TABLE `#__hikashop_badge` ADD `badge_url` VARCHAR( 255 ) NULL DEFAULT '';");
				$this->db->query();
			}
		}
		if(version_compare($this->fromVersion, '2.2.2', '<')) {
			$this->db->setQuery("ALTER TABLE `#__hikashop_taxation` ADD `taxation_post_code` VARCHAR( 255 ) NULL DEFAULT '';");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_display_quantity_field` SMALLINT DEFAULT 0");
			$this->db->query();

			$lng_override_folder = JLanguage::getLanguagePath(JPATH_ROOT).DS.'overrides';
			if(JFolder::exists($lng_override_folder)) {
				$lngFiles = JFolder::files($lng_override_folder);
				if(!empty($lngFiles)) {
					foreach($lngFiles as $lngfile) {
						$content = JFile::read($lng_override_folder.DS.$lngfile);
						if(!empty($content) && strpos($content, 'PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER="') !== false) {
							$content = preg_replace('#PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER="(.*)"#', 'PLEASE_ACCEPT_TERMS_BEFORE_FINISHING_ORDER="\1"'."\r\n".'PLEASE_ACCEPT_TERMS="\1"', $content);
							JFile::write($lng_override_folder.DS.$lngfile, $content);
							unset($content);
						}
					}
					unset($lngFiles);
				}
			}
		}
		if(version_compare($this->fromVersion, '2.2.3', '<')) {
			$this->db->setQuery("ALTER TABLE `#__hikashop_cart` ADD `cart_params` text NOT NULL DEFAULT ''");
			$this->db->query();
		}
		if(version_compare($this->fromVersion, '2.3.0', '<')) {
			$this->db->setQuery("ALTER TABLE `#__hikashop_taxation` ".
				"ADD `taxation_date_start` int(10) unsigned NOT NULL DEFAULT '0', ".
				"ADD `taxation_date_end` int(10) unsigned NOT NULL DEFAULT '0';");
			$this->db->query();
			$this->db->setQuery("
			CREATE TABLE IF NOT EXISTS `#__hikashop_warehouse` (
				`warehouse_id` INT(10) NOT NULL AUTO_INCREMENT,
				`warehouse_name` VARCHAR(255) NOT NULL DEFAULT '',
				`warehouse_published` tinyint(4) NOT NULL DEFAULT '1',
				`warehouse_description` TEXT NOT NULL,
				`warehouse_ordering` INT(10) NOT NULL DEFAULT 0,
				`warehouse_created` int(10) DEFAULT NULL,
				`warehouse_modified` int(10) DEFAULT NULL,
				PRIMARY KEY (`warehouse_id`)
			) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("ALTER TABLE `#__hikashop_product` ADD `product_warehouse_id` int(10) unsigned NOT NULL DEFAULT '0' ");
			$this->db->query();

			if(file_exists(HIKASHOP_MEDIA.'css'.DS.'frontend_old.css')){
				$this->db->setQuery("UPDATE #__hikashop_config SET `config_value` = 'old',`config_default` = 'old' WHERE `config_namekey` = 'css_frontend' AND `config_value` = 'default' ");
				$this->db->query();
			}

		}
	}

	function addPref(){
		$conf = JFactory::getConfig();
		$this->level = ucfirst($this->level);
		$allPref = array();
		$allPref['level'] =  $this->level;
		$allPref['version'] = $this->version;
		if(version_compare(JVERSION,'3.0','<')){
			$allPref['from_name'] = $conf->getValue('config.fromname');
			$allPref['from_email'] = $conf->getValue('config.mailfrom');
			$allPref['reply_name'] = $conf->getValue('config.fromname');
			$allPref['reply_email'] =  $conf->getValue('config.mailfrom');
		} else {
			$allPref['from_name'] = $conf->get('fromname');
			$allPref['from_email'] = $conf->get('mailfrom');
			$allPref['reply_name'] = $conf->get('fromname');
			$allPref['reply_email'] =  $conf->get('mailfrom');
		}
		$allPref['bounce_email'] =  '';
		$allPref['add_names'] = '1';
		$allPref['encoding_format'] =  'base64';
		$allPref['charset'] = 'UTF-8';
		$allPref['word_wrapping'] = '150';

		$allPref['embed_images'] = '0';
		$allPref['embed_files'] = '1';
		$allPref['multiple_part'] =  '1';
		$allPref['allowedfiles'] = 'zip,doc,docx,pdf,xls,txt,gz,gzip,rar,jpg,gif,tar.gz,xlsx,pps,csv,bmp,epg,ico,odg,odp,ods,odt,png,ppt,swf,xcf,wmv,avi,mkv,mp3,ogg,flac,wma,fla,flv,mp4,wav,aac,mov,epub';
		$allPref['allowedimages'] = 'gif,jpg,jpeg,png';
		$allPref['uploadfolder'] = 'media/com_hikashop/upload/';
		$allPref['uploadsecurefolder'] = 'media/com_hikashop/upload/safe/';
		$allPref['editor'] =  '0';
		$allPref['cron_next'] = '1251990901';
		$allPref['cron_last'] =  '0';
		$allPref['cron_fromip'] = '';
		$allPref['cron_report'] = '';
		$allPref['cron_frequency'] = '900';
		$allPref['cron_sendreport'] =  '2';
		if(version_compare(JVERSION,'3.0','<')){
			$allPref['payment_notification_email'] = $allPref['order_creation_notification_email'] = $allPref['cron_sendto'] = $conf->getValue('config.mailfrom');
		} else {
			$allPref['payment_notification_email'] = $allPref['order_creation_notification_email'] = $allPref['cron_sendto'] = $conf->get('config.mailfrom');
		}
		$allPref['cron_fullreport'] =  '1';
		$allPref['cron_savereport'] =  '2';
		$allPref['cron_savepath'] =  'media/com_hikashop/upload/safe/logs/report_'.rand(0,999999999).'.log';
		$allPref['payment_log_file'] =  'media/com_hikashop/upload/safe/logs/report_'.rand(0,999999999).'.log';
		$allPref['notification_created'] =  '';
		$allPref['notification_accept'] =  '';
		$allPref['notification_refuse'] = '';
		$allPref['bootstrap_design'] = '0';
		$allPref['characteristics_values_sorting']='ordering';


		$descriptions = array('Joomla!™ Shopping Cart Extension','Joomla!™ E-Commerce Extension','Joomla!™ Online Shop System','Joomla!™ Online Store Component');
		$allPref['description_starter'] = $descriptions[rand(0,3)];
		$allPref['description_essential'] = $descriptions[rand(0,3)];
		$allPref['description_business'] = $descriptions[rand(0,3)];

		$allPref['opacity'] = '100';
		$allPref['order_number_format'] = '{automatic_code}';
		$allPref['checkout_cart_delete'] = '1';
		$allPref['variant_default_publish'] = '1';
		$allPref['force_ssl'] = '0';
		$allPref['simplified_registration'] = '0';
		$allPref['tax_zone_type'] = 'billing';
		$allPref['discount_before_tax'] = '0';
		$allPref['default_type'] = 'individual';
		$allPref['main_tax_zone'] = '1375';
		$allPref['main_currency'] = '1';
		$allPref['order_status_for_download'] = 'shipped,confirmed';
		$allPref['download_time_limit'] = '2592000';
		$allPref['click_validity_period'] = '2592000';
		$allPref['click_min_delay'] = '86400';
		$allPref['partner_currency'] = '1';
		$allPref['allow_currency_selection'] = '0';
		$allPref['partner_click_fee'] = '0';
		$allPref['partner_lead_fee'] = '0';
		$allPref['ajax_add_to_cart'] ='0';
		$allPref['partner_percent_fee'] = '0';
		$allPref['partner_flat_fee'] = '0';
		$allPref['affiliate_terms'] = '';
		$allPref['order_created_status'] = 'created';
		$allPref['order_confirmed_status'] = 'confirmed';
		$allPref['download_number_limit'] = '50';
		$allPref['button_style'] = 'normal';
		$allPref['partner_valid_status'] = 'confirmed,shipped';
		$allPref['readmore'] = '0';
		$allPref['menu_style'] = 'title_bottom';
		$allPref['show_cart_image'] = '1';
		$allPref['thumbnail'] = '1';
		$allPref['thumbnail_x'] = '100';
		$allPref['thumbnail_y'] = '100';
		$allPref['product_image_x'] = '100';
		$allPref['product_image_y'] = '100';
		$allPref['image_x'] = '';
		$allPref['image_y'] = '';
		$allPref['max_x_popup'] = '760';
		$allPref['max_y_popup'] = '480';
		$allPref['vat_check'] = '0';
		$allPref['default_translation_publish'] = '0';
		$allPref['multilang_display'] = 'popups';
		$allPref['volume_symbols'] = 'm,dm,cm,mm,in,ft,yd';
		$allPref['weight_symbols'] = 'kg,g,mg,lb,oz,ozt';
		$allPref['store_address'] = "ACME Corporation\nGuildhall\n PO Box 270, London\nUnited Kingdom";
		$allPref['checkout'] = 'login_address_shipping_payment_confirm_coupon_cart_status_fields,end';
		$allPref['display_checkout_bar'] = '0';
		$allPref['show_vote_product'] = '1';
		$allPref['affiliate_advanced_stats'] = '1';
		$allPref['cart_retaining_period'] = '2592000';
		$allPref['default_params'] = '';
		$allPref['default_image'] = 'barcode.png';
		$allPref['characteristic_display'] = 'dropdown';
		$allPref['characteristic_display_text'] = '1';
		$allPref['show_quantity_field'] = '1';
		$allPref['show_cart_price'] = '1';
		$allPref['show_cart_quantity'] = '1';
		$allPref['show_cart_delete'] = '1';
		$allPref['catalogue'] = '0';
		$allPref['redirect_url_after_add_cart'] = 'checkout';
		$allPref['redirect_url_when_cart_is_empty'] = '';
		$allPref['cart_retaining_period_checked'] = '1278664651';
		$allPref['auto_submit_methods'] = '1';
		$allPref['clean_cart_when_order_created'] = 'order_confirmed';

		$border_visible = 1;
		if(version_compare(JVERSION,'3.0','>=')) {
			$border_visible = 2;
		}
		$allPref['default_params'] = base64_encode('a:34:{s:14:"border_visible";s:1:"'.$border_visible.'";s:11:"add_to_cart";s:1:"1";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";s:1:"0";s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";s:14:"display_badges";s:1:"1";}');
		$allPref['category_image'] = 1;//not changeable yet
		$allPref['category_explorer'] = 1;
		$allPref['cancelled_order_status'] = 'cancelled,refunded';
		$allPref['detailed_tax_display']=1;
		$allPref['order_status_notification.subject'] = 'ORDER_STATUS_NOTIFICATION_SUBJECT';
		$allPref['order_creation_notification.subject'] = 'ORDER_CREATION_NOTIFICATION_SUBJECT';
		$allPref['order_notification.subject'] = 'ORDER_NOTIFICATION_SUBJECT';
		$allPref['user_account.subject'] = 'USER_ACCOUNT_SUBJECT';
		$allPref['user_account_admin_notification.subject'] = 'HIKA_USER_ACCOUNT_ADMIN_NOTIFICATION_SUBJECT';
		$allPref['cron_report.subject'] = 'CRON_REPORT_SUBJECT';
		$allPref['order_status_notification.html']=1;
		$allPref['order_status_notification.published']=1;
		$allPref['order_creation_notification.html']=1;
		$allPref['order_creation_notification.published']=1;
		$allPref['order_notification.html']=1;
		$allPref['order_notification.published']=1;
		$allPref['order_admin_notification.html']=1;
		$allPref['order_admin_notification.subject'] = 'ORDER_ADMIN_NOTIFICATION_SUBJECT';
		$allPref['order_admin_notification.published']=1;
		$allPref['new_comment.html']=1;
		$allPref['new_comment.subject']='NEW_COMMENT_NOTIFICATION_SUBJECT';
		$allPref['new_comment.published']=1;
		$allPref['contact_request.html']=1;
		$allPref['contact_request.published']=1;
		$allPref['unfinished_order.published']=1;
		$allPref['user_account.html']=1;
		$allPref['user_account_admin_notification.html']=1;
		$allPref['out_of_stock.html']=1;
		$allPref['out_of_stock.subject']='OUT_OF_STOCK_NOTIFICATION_SUBJECT';
		$allPref['user_account.published']=1;
		$allPref['user_account_admin_notification.published']=1;
		$allPref['cron_report.html']=1;
		$allPref['cron_report.published']=1;
		$allPref['out_of_stock.published']=1;
		$allPref['waitlist_notification.html']=1;
		$allPref['waitlist_notification.subject'] = 'WAITLIST_NOTIFICATION_SUBJECT';
		$allPref['waitlist_notification.published']=1;
		$allPref['order_cancel.html']=1;
		$allPref['order_cancel.subject'] = 'ORDER_CANCEL_SUBJECT';
		$allPref['order_cancel.published']=1;

		$allPref['variant_increase_perf']=1;

		$allPref['show_footer'] = '1';
		$allPref['no_css_header'] = '0';
		$allPref['pathway_sef_name'] = 'category_pathway';
		$allPref['related_sef_name'] = 'related_product';
		$allPref['css_module'] = 'default';
		$allPref['css_frontend'] = 'default';
		$allPref['css_backend'] = 'default';
		$allPref['installcomplete'] = '0';
		$allPref['Starter'] =  '0';
		$allPref['Essential'] =  '1';
		$allPref['Business'] =  '2';
		$allPref['Enterprise'] =  '3';
		$allPref['Unlimited'] =  '9';

		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'3.0','>=') || in_array($app->getTemplate(),array('rt_missioncontrol','aplite'))){
			$allPref['menu_style'] = 'content_top';
		}
		$query = "INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES ";
		foreach($allPref as $namekey => $value){
			$query .= '('.$this->db->Quote($namekey).','.$this->db->Quote($value).','.$this->db->Quote($value).'),';
		}
		$query = rtrim($query,',');
		$this->db->setQuery($query);
		$this->db->query();
	}
	function addModules(){
		if($this->freshinstall){
			$elements = array(new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass());
			$elements[0]->title = JText::_('HIKASHOP_RANDOM_MODULE');
			$elements[1]->title = JText::_('RECENTLY_VIEWED');
			$elements[2]->title = JText::_('HIKASHOP_CATEGORIES_1_MODULE');
			$elements[3]->title = JText::_('HIKASHOP_CATEGORIES_2_MODULE');
			$elements[4]->title = JText::_('HIKASHOP_BEST_SELLERS_MODULE');
			$elements[5]->title = JText::_('HIKASHOP_LATEST_MODULE');
			$elements[6]->title = JText::_('MANUFACTURERS');
			$elements[7]->title = JText::_('HIKASHOP_BEST_RATED_MODULE');
			$elements[8]->title = JText::_('RELATED_PRODUCTS');

			$modulesClass = hikashop_get('class.modules');
			$params = array();
			foreach($elements as $k => $element){
				if(version_compare(JVERSION,'1.6','<')){
					$elements[$k]->position = 'left';
					$elements[$k]->access = 0;
				}else{
					$elements[$k]->position = 'position-7';
					$elements[$k]->language = '*';
					$elements[$k]->access = 1;
				}
				$elements[$k]->published = 0;
				$elements[$k]->module = 'mod_hikashop';
				$elements[$k]->params = '';
				$params[$k] = new stdClass();
				$params[$k]->id = $modulesClass->save($element);
			}
			$query = "INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ";
			foreach($params as $param){
				$query .= '('.$this->db->Quote($param->id).',0),';
			}
			$query = rtrim($query,',');
			$this->db->setQuery($query);
			$this->db->query();

			$categoriesLength = strlen($this->menuid->categories);
			$brandsLength = strlen($this->menuid->brands);
			$id_related_module = $params[8]->id;

			$params[0]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:1:"3";s:6:"random";s:1:"1";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"1";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:15:"add_to_wishlist";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:2:"-1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:14:"border_visible";s:1:"0";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:13:"ul_class_name";s:0:"";}');
			$params[0]->id = 'params_'.$params[0]->id;

			$params[1]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:1:"3";s:6:"random";s:2:"-1";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:7:"inherit";s:19:"product_synchronize";s:1:"4";s:15:"recently_viewed";s:1:"1";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:7:"inherit";s:18:"child_display_type";s:7:"inherit";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:2:"-1";s:18:"number_of_products";s:2:"-1";s:16:"only_if_products";s:2:"-1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";}');
			$params[1]->id = 'params_'.$params[1]->id;

			$params[2]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:8:"category";s:11:"layout_type";s:4:"list";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:15:"add_to_wishlist";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"0";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:14:"border_visible";s:1:"0";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:13:"ul_class_name";s:0:"";}');
			$params[2]->id = 'params_'.$params[2]->id;

			$params[3]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:8:"category";s:11:"layout_type";s:4:"list";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:15:"add_to_wishlist";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"0";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:15:"allchildsexpand";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:7:"#FFFFFF";s:6:"margin";s:2:"10";s:14:"border_visible";s:1:"0";s:15:"rounded_corners";s:1:"1";s:11:"text_center";s:1:"1";s:13:"ul_class_name";s:0:"";}');
			$params[3]->id = 'params_'.$params[3]->id;

			$params[4]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:4:"DESC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:13:"product_sales";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:15:"allchildsexpand";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";}');
			$params[4]->id = 'params_'.$params[4]->id;

			$params[5]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:4:"DESC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:15:"product_created";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:15:"allchildsexpand";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"1";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";}');
			$params[5]->id = 'params_'.$params[5]->id;

			$params[6]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$brandsLength.':"'.$this->menuid->brands.'";s:12:"content_type";s:8:"category";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:2:"10";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:21:"product_average_score";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:11:"category_id";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";}');
			$params[6]->id = 'params_'.$params[6]->id;

			$params[7]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"1";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:4:"DESC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"0";s:13:"product_order";s:21:"product_average_score";s:19:"product_synchronize";s:1:"1";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:11:"category_id";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";}');
			$params[7]->id = 'params_'.$params[7]->id;

			$params[8]->config_value = base64_encode('a:38:{s:6:"itemid";s:'.$categoriesLength.':"'.$this->menuid->categories.'";s:12:"content_type";s:7:"product";s:11:"layout_type";s:3:"div";s:7:"columns";s:1:"3";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:19:"content_synchronize";s:1:"1";s:13:"product_order";s:8:"ordering";s:19:"product_synchronize";s:1:"2";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:2:"-1";s:15:"add_to_wishlist";s:2:"-1";s:20:"link_to_product_page";s:2:"-1";s:17:"show_vote_product";s:2:"-1";s:10:"show_price";s:2:"-1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:2:"-1";s:13:"show_discount";s:1:"3";s:18:"price_display_type";s:7:"inherit";s:14:"category_order";s:11:"category_id";s:18:"child_display_type";s:9:"allchilds";s:11:"child_limit";s:0:"";s:24:"links_on_main_categories";s:1:"1";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:7:"inherit";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";}');
			$params[8]->id = 'params_'.$params[8]->id;

			$query = "INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`) VALUES ";
			foreach($params as $param){
				$query .= '('.$this->db->Quote($param->id).','.$this->db->Quote($param->config_value).'),';
			}
			$query .='(\'product_show_modules\',\''.$id_related_module.'\')';
			$this->db->setQuery($query);
			$this->db->query();
		}else{
			return true;
		}
	}
	function addMenus(){
		if($this->freshinstall){
			$elements = array(new stdClass(),new stdClass(),new stdClass(),new stdClass(),new stdClass());

			$elements[0]->menutype = 'hikashop_default';
			$elements[0]->link = 'index.php?option=com_hikashop&view=category&layout=listing';
			$elements[0]->title = JText::_('COM_HIKASHOP_CATEGORY_VIEW_DEFAULT_TITLE');
			$elements[0]->alias = 'hikashop-menu-for-categories-listing';
			$elements[1]->menutype = 'hikashop_default';
			$elements[1]->link = 'index.php?option=com_hikashop&view=product&layout=listing';
			$elements[1]->title = JText::_('COM_HIKASHOP_PRODUCT_VIEW_DEFAULT_TITLE');
			$elements[1]->alias = 'hikashop-menu-for-products-listing';
			$elements[2]->menutype = 'hikashop_default';
			$elements[2]->link = 'index.php?option=com_hikashop&view=user&layout=cpanel';
			$elements[2]->title = JText::_('COM_HIKASHOP_USER_PANEL_VIEW_DEFAULT_TITLE');
			$elements[2]->alias = 'hikashop-menu-for-user-control-panel';
			$elements[3]->menutype = 'hikashop_default';
			$elements[3]->link = 'index.php?option=com_hikashop&view=user&layout=form';
			$elements[3]->title = JText::_('COM_HIKASHOP_USER_VIEW_DEFAULT_TITLE');
			$elements[3]->alias = 'hikashop-menu-for-hikashop-registration';
			$elements[4]->menutype = 'hikashop_default';
			$elements[4]->link = 'index.php?option=com_hikashop&view=category&layout=listing';
			$elements[4]->title = JText::_('COM_HIKASHOP_BRAND_VIEW_DEFAULT_TITLE');
			$elements[4]->alias = 'hikashop-menu-for-brands-listing';

			foreach($elements as $k => $element){
				$elements[$k]->type = 'component';
				$elements[$k]->published = 1;

				if(version_compare(JVERSION,'1.6','<')){
					$elements[$k]->name = $elements[$k]->title;
					$elements[$k]->parent = 0;
					$elements[$k]->sublevel = 1;
					$elements[$k]->access = 0;
					unset($elements[$k]->title);
				}else{
					$elements[0]->path = 'hikashop-menu-for-categories-listing';
					$elements[1]->path = 'hikashop-menu-for-products-listing';
					$elements[2]->path = 'hikashop-menu-for-user-control-panel';
					$elements[3]->path = 'hikashop-menu-for-hikashop-registration';
					$elements[4]->path = 'hikashop-menu-for-brands-listing';

					$elements[$k]->client_id = 0;
					$elements[$k]->language = '*';
					$elements[$k]->level = 1;
					$elements[$k]->parent_id = 1;
					$elements[$k]->access = 1;
				}
			}

			$this->db->setQuery('SELECT menutype FROM '.hikashop_table('menu_types',false).' WHERE menutype=\'hikashop_default\'');
			$mainMenu = $this->db->loadResult();
			if(empty($mainMenu)){
				$this->db->setQuery('INSERT INTO '.hikashop_table('menu_types',false).' ( `menutype`,`title`,`description` ) VALUES ( \'hikashop_default\',\'HikaShop default menus\',\'This menu is used by HikaShop to store menus configurations\' )');
				$this->db->query();
			}

			$menusClass = hikashop_get('class.menus');
			$this->menuid = new stdClass();
			foreach($elements as $element){
				if(version_compare(JVERSION,'1.6','<')){
					$menusClass->save($element);
					$this->db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$element->alias.'\'');
					$menu_id = $this->db->loadResult();
				}else{
					$this->db->setQuery('SELECT rgt FROM '.hikashop_table('menu',false).' WHERE id=1');
					$root = $this->db->loadResult();
					$element->lft = $root;
					$element->rgt = $root+1;
					$menusClass->save($element);

					$this->db->setQuery('UPDATE '.hikashop_table('menu',false).' SET rgt='.($root+2).' WHERE id=1');
					$this->db->query();

					$this->db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$element->alias.'\'');
					$menu_id = $this->db->loadResult();
				}
				if(!empty($menu_id)){
					if($element->alias == 'hikashop-menu-for-brands-listing'){
						$moduleOtpions = base64_encode('a:37:{s:10:"show_image";s:1:"0";s:16:"show_description";s:1:"1";s:11:"layout_type";s:7:"inherit";s:7:"columns";s:1:"3";s:5:"limit";s:2:"20";s:6:"random";s:1:"0";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:7:"modules";s:0:"";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:15:"recently_viewed";s:1:"0";s:11:"add_to_cart";s:1:"1";s:20:"link_to_product_page";s:1:"1";s:17:"show_vote_product";s:1:"0";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"3";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:18:"number_of_products";s:1:"0";s:16:"only_if_products";s:1:"0";s:11:"image_width";s:0:"";s:12:"image_height";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:11:"pane_height";s:0:"";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:14:"border_visible";s:2:"-1";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:13:"ul_class_name";s:0:"";s:12:"content_type";s:12:"manufacturer";}');
						$query = "UPDATE `#__hikashop_config` SET `config_value`=".$this->db->quote($moduleOtpions)." WHERE `config_namekey`= 'menu_".$menu_id."' ";
						$this->db->setQuery($query);
						$this->db->query();
						$this->menuid->brands = $menu_id;
						$config =& hikashop_config();
						$config->set('menu_'.$menu_id,$moduleOtpions);
						$menusClass->attachAssocModule($menu_id);
					}elseif($element->alias == 'hikashop-menu-for-categories-listing'){
						$moduleOtpions = base64_encode('a:31:{s:12:"content_type";s:7:"product";s:11:"layout_type";s:7:"inherit";s:7:"columns";i:3;s:5:"limit";s:2:"20";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"0";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";i:0;s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";}');
						$query = "UPDATE `#__hikashop_config` SET `config_value`=".$this->db->quote($moduleOtpions)." WHERE `config_namekey`= 'menu_".$menu_id."' ";
						$this->db->setQuery($query);
						$this->db->query();
						$this->menuid->categories = $menu_id;
						$config =& hikashop_config();
						$config->set('menu_'.$menu_id,$moduleOtpions);
						$menusClass->attachAssocModule($menu_id);
					}elseif($element->alias == 'hikashop-menu-for-products-listing'){
						$moduleOtpions = base64_encode('a:31:{s:12:"content_type";s:7:"product";s:11:"layout_type";s:7:"inherit";s:7:"columns";i:3;s:5:"limit";s:2:"20";s:9:"order_dir";s:3:"ASC";s:11:"filter_type";s:1:"1";s:19:"selectparentlisting";s:1:"2";s:15:"moduleclass_sfx";s:0:"";s:7:"modules";s:0:"";s:19:"content_synchronize";s:1:"1";s:15:"use_module_name";s:1:"0";s:13:"product_order";s:8:"ordering";s:6:"random";i:0;s:19:"product_synchronize";s:1:"1";s:10:"show_price";s:1:"1";s:14:"price_with_tax";s:1:"1";s:19:"show_original_price";s:1:"1";s:13:"show_discount";s:1:"1";s:18:"price_display_type";s:8:"cheapest";s:14:"category_order";s:17:"category_ordering";s:18:"child_display_type";s:7:"nochild";s:11:"child_limit";s:0:"";s:20:"div_item_layout_type";s:9:"img_title";s:17:"div_custom_fields";s:0:"";s:6:"height";s:3:"150";s:16:"background_color";s:0:"";s:6:"margin";s:0:"";s:15:"rounded_corners";s:2:"-1";s:11:"text_center";s:2:"-1";s:24:"links_on_main_categories";s:1:"0";s:20:"link_to_product_page";s:1:"1";}');
						$query = "UPDATE `#__hikashop_config` SET `config_value`=".$this->db->quote($moduleOtpions)." WHERE `config_namekey`= 'menu_".$menu_id."' ";
						$this->db->setQuery($query);
						$this->db->query();
						$config =& hikashop_config();
						$config->set('menu_'.$menu_id,$moduleOtpions);
						$menusClass->attachAssocModule($menu_id);
					}
				}
			}
		}else{
			return true;
		}
	}
}

class com_hikashopInstallerScript {
	function install($parent) {
		com_install();
	}

	function update($parent) {
		com_install();
	}

	function uninstall($parent)	{
		$db = JFactory::getDBO();
		$db->setQuery("DELETE FROM `#__hikashop_config` WHERE `config_namekey` = 'li' LIMIT 1");
		$db->query();

		$db->setQuery("DELETE FROM `#__menu` WHERE link LIKE '%com_hikashop%'");
		$db->query();

		$db->setQuery("UPDATE `#__modules` SET `published` = 0 WHERE `module` LIKE '%hikashop%'");
		$db->query();

		$db->setQuery("UPDATE `#__extensions` SET `enabled` = 0 WHERE `type` = 'plugin' AND `element` LIKE '%hikashop%' AND `folder` NOT LIKE '%hikashop%'");
		$db->query();
	}

	function preflight($type, $parent) {
		return true;
	}
	function postflight($type, $parent) {
		return true;
	}
}
