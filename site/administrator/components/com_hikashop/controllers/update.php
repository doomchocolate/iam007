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
class updateController extends HikashopBridgeController {
	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[]='wizard';
		$this->modify[]='wizard_save';
		$this->registerDefaultTask('update');
	}

	function install(){
		hikashop_setTitle('HikaShop','install','update');
		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = hikashop_config();
		$config->save($newConfig);
		$updateHelper = hikashop_get('helper.update');
		$updateHelper->addJoomfishElements();
		$updateHelper->addDefaultData();
		$updateHelper->createUploadFolders();
		$updateHelper->installMenu();
		$updateHelper->addUpdateSite();
		$updateHelper->installExtensions();
		if(!empty($updateHelper->freshinstall)){
			$app = JFactory::getApplication();
			$app->redirect(hikashop_completeLink('update&task=wizard', false, true));
		}
		if (!HIKASHOP_PHP5) {
			$bar =& JToolBar::getInstance('toolbar');
		}else{
			$bar = JToolBar::getInstance('toolbar');
		}
		$bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$this->_iframe(HIKASHOP_UPDATEURL.'install&fromversion='.JRequest::getCmd('fromversion'));
	}

	function update(){
		hikashop_setTitle(JText::_('UPDATE_ABOUT'),'install','update');
		if (!HIKASHOP_PHP5) {
			$bar =& JToolBar::getInstance('toolbar');
		}else{
			$bar = JToolBar::getInstance('toolbar');
		}
		$bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		return $this->_iframe(HIKASHOP_UPDATEURL.'update');
	}
	function _iframe($url){
		$config =& hikashop_config();
		$menu_style = $config->get('menu_style','title_bottom');
		if($menu_style=='content_top'){
			echo hikashop_getMenu('',$menu_style);
		}

		if(hikashop_isSSL())
			$url = str_replace('http://', 'https://', $url);
?>
		<div id="hikashop_div">
			<iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikashop_frame" id="hikashop_frame" src="<?php echo $url.'&level='.$config->get('level').'&component=hikashop&version='.$config->get('version'); ?>"></iframe>
		</div>
<?php
	}
	function wizard(){
		$lang = JFactory::getLanguage();
		$code = $lang->getTag();
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
		jimport('joomla.filesystem.file');
		if(!JFile::exists($path)){
			$url = HIKASHOP_UPDATEURL.'languageload&raw=1&code='.$code;

			$data = '';
			if(function_exists('curl_version')){
				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$data = curl_exec($ch);
				curl_close($ch);
			}else{
				$data = file_get_contents($url);
			}
			if(!empty($data)){
				$result = JFile::write($path, $data);
				if(!$result){
					$updateHelper = hikashop_get('helper.update');
					$updateHelper->installMenu($code);
					hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
				} else {
					$lang->load(HIKASHOP_COMPONENT, JPATH_SITE, $code, true);
				}
			}else{
				hikashop_display(JText::sprintf('CANT_GET_LANGUAGE_FILE_CONTENT',$path),'error');
			}
		}

		JRequest::setVar( 'layout', 'wizard' );
		return parent::display();
	}
	function wizard_save(){
		$layoutType = JRequest::getVar('layout_type');
		$currency = JRequest::getVar('currency');
		$taxName = JRequest::getVar('tax_name');
		$taxRate = JRequest::getVar('tax_rate');
		$addressCountry = JRequest::getVar('address_country');
		$data = JRequest::getVar('data', array(), '', 'array');
		$addressState = (!empty($data['address']['address_state'])) ? ($data['address']['address_state']) : '';
		$shopAddress = JRequest::getVar('shop_address');
		$paypalEmail = JRequest::getVar('paypal_email');
		$productType = JRequest::getVar('product_type');

		$ratePlugin = hikashop_import('hikashop','rates');
		if($ratePlugin){
			$ratePlugin->updateRates();
		}

		$db = JFactory::getDBO();
		foreach($_POST as $key => $data){
			if($data == '0') continue;
			if(preg_match('#menu#',$key)){ // menu
				if(preg_match('#categories#',$key)){
					$alias = 'hikashop-menu-for-categories-listing';
				}else{
					$alias = 'hikashop-menu-for-products-listing';
				}
				$db->setQuery('SELECT * FROM '.hikashop_table('menu',false).' WHERE `alias` = '.$db->quote($alias));
				$data = $db->loadAssoc();
				$db->setQuery('SELECT `menutype` FROM '.hikashop_table('menu',false).' WHERE `home` = 1');
				$menutype = $db->loadResult();
				$data['menutype'] = $menutype;
				$menuTable = JTable::getInstance('Menu', 'JTable', array());
				if(is_object($menuTable)){
					$menuTable->save($data);
					if(method_exists($menuTable,'rebuild')){
						$menuTable->rebuild();
					}
				}
			}elseif(preg_match('#module#',$key)){ // module
				if(preg_match('#categories#',$key)){
					$db->setQuery('UPDATE '.hikashop_table('modules',false).' SET `published` = 1 WHERE `title` = '.$db->quote('Categories on 2 levels'));
					$db->query();
				}
			}
		}

		$db->setQuery('SELECT `config_value` FROM '.hikashop_table('config').' WHERE `config_namekey` = "default_params"');
		$oldDefaultParams = $db->loadResult();
		$oldDefaultParams = unserialize(base64_decode($oldDefaultParams));
		$oldDefaultParams['layout_type'] = preg_replace('#listing_#','',$layoutType);
		$defaultParams = base64_encode(serialize($oldDefaultParams));
		if($addressCountry == 'country_United_States_of_America_223')
			$main_zone = $addressState;
		else
			$main_zone = $addressCountry;
		$zoneClass = hikashop_get('class.zone');
		$zone = $zoneClass->get($main_zone);
		$db->setQuery('REPLACE INTO '.hikashop_table('config').' (config_namekey, config_value) VALUES ("main_tax_zone", '.$db->quote($zone->zone_id).'), ("store_address", '.$db->quote($shopAddress).'), ("main_currency", '.$db->quote($currency).'), ("default_params", '.$db->quote($defaultParams).')');
		$db->query();

		$db->setQuery('UPDATE '.hikashop_table('field').' SET `field_default` = '.$db->quote($addressState).' WHERE field_namekey = "address_state"');
		$db->query();
		$db->setQuery('UPDATE '.hikashop_table('field').' SET `field_default` = '.$db->quote($addressCountry).' WHERE field_namekey = "address_country"');
		$db->query();

		$import_language = JRequest::getVar('import_language');
		if($import_language != '0'){
			if(preg_match('#|#',$import_language)){
				$languages = explode('|',$import_language);
			}else{
				$languages = array($import_language);
			}
			$updateHelper = hikashop_get('helper.update');
			foreach($languages as $code){
				$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_hikashop.ini';
				jimport('joomla.filesystem.file');
				if(!JFile::exists($path)){
					$url = HIKASHOP_UPDATEURL.'languageload&raw=1&code='.$code;
					$data = '';
					if(function_exists('curl_version')){
						$ch = curl_init();
						$timeout = 5;
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
						$data = curl_exec($ch);
						curl_close($ch);
					}else{
						$data = file_get_contents($url);
					}
					if(!empty($data)){
						$result = JFile::write($path, $data);
						if($result){
							$updateHelper->installMenu($code);
							hikashop_display(JText::_('HIKASHOP_SUCC_SAVED'),'success');
						}else{
							hikashop_display(JText::sprintf('FAIL_SAVE',$path),'error');
						}
					}else{
						hikashop_display(JText::sprintf('CANT_GET_LANGUAGE_FILE_CONTENT',$path),'error');
					}
				}
			}
		}

		if(isset($taxRate) && (!empty($taxRate) || $taxRate != '0')){
			$taxRate = (float)$taxRate / 100;
			$db->setQuery('REPLACE INTO '.hikashop_table('tax').' (tax_namekey,tax_rate) VALUES ('.$db->quote($taxName).','.(float)$taxRate.')');
			$db->query();

			$db->setQuery('SELECT `taxation_id` FROM '.hikashop_table('taxation').' ORDER BY `taxation_id` DESC LIMIT 0,1');
			$maxId = $db->loadResult();
			if(is_null($maxId)){
				$maxId = 1;
			}else{
				$maxId = (int)$maxId + 1 ;
			}
			$tax = array();
			$tax['taxation_id'] = $maxId;
			if($addressCountry == 'country_United_States_of_America_223')
				$tax['zone_namekey'] = $addressState;
			else
				$tax['zone_namekey'] = $addressCountry;
			$tax['category_namekey'] = 'default_tax';
			$tax['tax_namekey'] = $taxName;
			$tax['taxation_published'] = 1;
			$tax['taxation_type'] = '';
			$tax['taxation_access'] = 'all';
			$tax['taxation_cumulative'] = 0;
			$db->setQuery('INSERT INTO '.hikashop_table('taxation').' ('.implode(',',array_keys($tax)).') VALUES (\''.implode('\',\'',$tax).'\')');
			$db->query();
		}

		if(isset($paypalEmail) && !empty($paypalEmail)){
			$pluginData = array(
				'payment' => array(
					'payment_name' => 'PayPal',
					'payment_published' => '1',
					'payment_images' => 'MasterCard,VISA,Credit_card,PayPal',
					'payment_price' => '',
					'payment_params' => array(
						'url' => 'https://www.paypal.com/cgi-bin/webscr',
						'email' => $paypalEmail,
					),
					'payment_zone_namekey' => '',
					'payment_access' => 'all',
					'payment_id' => '0',
					'payment_type' => 'paypal',
				),
			);
			JRequest::setVar('name','paypal');
			JRequest::setVar('plugin_type','payment');
			JRequest::setVar('data',$pluginData);

			$pluginsController = hikashop_get('controller.plugins');
			$pluginsController->store(true);
		}
		if(isset($productType) && !empty($productType)){
			if($productType == 'real'){
				$forceShipping = 1;
			}else{
				$forceShipping = 0;
			}
			$db->setQuery('REPLACE INTO '.hikashop_table('config').' (config_namekey, config_value) VALUES ("force_shipping", '.(int)$forceShipping.')');
			$db->query();
		}

		$url = 'index.php?option=com_hikashop&ctrl=product&task=add';
		$this->setRedirect($url);
	}
	function state(){
		JRequest::setVar( 'layout', 'state' );
		return parent::display();
	}
}
