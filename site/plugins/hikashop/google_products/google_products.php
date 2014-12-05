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
class plgHikashopGoogle_products extends JPlugin{
	function plgHikashopGoogle_products(&$subject, $config){
		parent::__construct($subject, $config);
	}

	function onHikashopCronTrigger(&$messages){
		if(hikashop_level(1)){
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop','google_products');

			if( empty($plugin->params['enable_auto_update']) && empty($plugin->params['local_path'])){
				return true;
			}

			if(empty($plugin->params['frequency'])){
				$plugin->params['frequency'] = 86400;
			}
			if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['frequency']>time()){
				return true;
			}

			$plugin->params['last_cron_update']=time();
			$pluginsClass->save($plugin);
			$pluginsClass->loadParams($plugin);
			$xml=$this->generateXML();

			$app = JFactory::getApplication();
			if(!empty($xml)){
				if(!empty($plugin->params['local_path'])){
					$path=$this->_getRelativePath($plugin->params['local_path']);
					jimport('joomla.filesystem.file');
					if(!JFile::write(JPATH_ROOT.DS.$path,$xml)){
						$message = 'Could not write Google Merchant file to '.JPATH_ROOT.DS.$path;
					}else{
						$message = 'Google Merchant file written to '.JPATH_ROOT.DS.$path;
					}
					$messages[] = $message;
					$app->enqueueMessage($message);
				}

				if( empty($plugin->params['enable_auto_update']) ){
					return true;
				}
				if(!empty($plugin->params['google_password']) && !empty($plugin->params['user_name']) && !empty($plugin->params['file_name'])){
					$pwd=$plugin->params['google_password'];
					$user=$plugin->params['user_name'];
					$name=$plugin->params['file_name'];
				}
				else{
					return true;
				}
				if($this->_connectionToGoogleDB($user,$pwd, $xml, $plugin, $name)){
					$message = 'Products information sent to Google Merchant';
					$messages[] = $message;
					$app->enqueueMessage($message);
				}
			}
		}
	}

	function _getRelativePath($path){
		$relativePath=str_replace(JPATH_ROOT.DS,'',$path);
		return $relativePath;
	}

	function downloadXML(){
		if(hikashop_level(1)){
			$xml=$this->generateXML();
			@ob_clean();
			header("Pragma: public");
			header("Expires: 0"); // set expiration time
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment; filename=Google_data_feed_".time().".xml;");
			header("Content-Transfer-Encoding: binary");
			header('Content-Length: '.strlen($xml));
			echo $xml;
			exit;
		}
	}

	function generateXML(){

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','google_products');
		if(empty($plugin->params['condition'])){
			$plugin->params['condition'] = "new";
		}

		if(@$plugin->params['increase_perf']){
			$memory = '128M';
			$max_execution = '120';
			if($plugin->params['increase_perf']==2){
				$memory = '512M';
				$max_execution = '600';
			}elseif($plugin->params['increase_perf']==3){
				$memory = '1024M';
				$max_execution = '6000';
			}elseif($plugin->params['increase_perf']==10){
				$memory = '4096M';
				$max_execution = '0';
			}
			ini_set('memory_limit',$memory);
			ini_set('max_execution_timeout',$max_execution);
		}

		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_access=\'all\' AND product_published=1 AND product_type=\'main\'';
		if(!empty($plugin->params['in_stock_only'])){
			$query .= ' AND product_quantity!=0';
		}
		$db->setQuery($query);
		$products = $db->loadObjectList();

		if(!empty($products)){
			$ids = array();
			foreach($products as $key => $row){
				$ids[]=$row->product_id;
				$products[$key]->alias = JFilterOutput::stringURLSafe($row->product_name);
			}
			$queryCategoryId='SELECT * FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')';
			$db->setQuery($queryCategoryId);
			$categoriesId = $db->loadObjectList();
			foreach($products as $k=>$row){
			foreach($categoriesId as $catId){
				if($row->product_id==$catId->product_id){
					$products[$k]->categories_id[]=$catId->category_id;
				}
			}
			}
			$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_ordering ASC, file_id ASC';
			$db->setQuery($queryImage);
			$images = $db->loadObjectList();
			$products[$k]->images = array();
			foreach($products as $k=>$row){
				$i=0;
				foreach($images as $image){
					if($row->product_id==$image->file_ref_id){
						$products[$k]->images[$i] = new stdClass();
						foreach(get_object_vars($image) as $key => $name){
							$products[$k]->images[$i]->$key = $name;
						}
					}
					$i++;
				}
			}
			$db->setQuery('SELECT * FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
			$variants = $db->loadObjectList();
			if(!empty($variants)){
				foreach($products as $k => $product){
					foreach($variants as $variant){
						if($product->product_id==$variant->variant_product_id){
							$products[$k]->has_options = true;
							break;
						}
					}
				}
			}
		}
		else{
			return true;
		}


		$zone_id=hikashop_getZone();
		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		if(empty($plugin->params['price_displayed'])) $plugin->params['price_displayed'] = 'cheapest';

		if($plugin->params['price_displayed']=='cheapest'){
			$currencyClass->getListingPrices($products,$zone_id,$main_currency,'cheapest');
		}
		if($plugin->params['price_displayed']=='unit'){
				$currencyClass->getListingPrices($products,$zone_id,$main_currency,'unit');
		}
		if($plugin->params['price_displayed']=='average'){
			$currencyClass->getListingPrices($products,$zone_id,$main_currency,'range');
			$tmpPrice=0;
			$tmpTaxPrice=0;
			foreach($products as $product){
				if(isset($product->prices[0]->price_value)){
					if(count($product->prices)>1){
						for($i=0;$i<count($product->prices);$i++){
							if($product->prices[$i]->price_value>$tmpPrice){
								$tmpPrice+=$product->prices[$i]->price_value;
								$tmpTaxPrice+=@$product->prices[$i]->price_value_with_tax;
							}
						}
						$product->prices[0]->price_value=$tmpPrice/count($product->prices);
						$product->prices[0]->price_value_with_tax=$tmpTaxPrice/count($product->prices);
						for($i=1;$i<count($product->prices);$i++){
							unset($product->prices[$i]);
						}
					}
				}
			}
		}
		if($plugin->params['price_displayed']=='expensive'){
			$currencyClass->getListingPrices($products,$zone_id,$main_currency,'range');
			$tmpPrice=0;
			foreach($products as $product){
				if(isset($product->prices[0]->price_value)){
					if(count($product->prices)>1){
						for($i=0;$i<count($product->prices);$i++){
							if($product->prices[$i]->price_value>$tmpPrice){
								$tmpPrice=$product->prices[$i]->price_value;
								$key=$i;
							}
						}
						$product->prices[0]=$product->prices[$key];
						for($i=1;$i<count($product->prices);$i++){
							unset($product->prices[$i]);
						}
					}
				}
			}
		}

		$usedCat=array();
		$catList="";
		foreach($products as $product){
			if(!empty($product->categories_id)){
				foreach($product->categories_id as $catId){
					if(!isset($usedCat[$catId])){
						$usedCat[$catId]=$catId;
						$catList.=$catId.',';
					}
				}
			}
		}
		$catList=substr($catList,0,-1);

		$parentCatId='product';
		$categoryClass=hikashop_get('class.category');
		$categoryClass->getMainElement($parentCatId);
		$query = 'SELECT DISTINCT b.* FROM '.hikashop_table('category').' AS a LEFT JOIN '.
					hikashop_table('category').' AS b ON a.category_left >= b.category_left WHERE '.
					'b.category_right >= a.category_right AND a.category_id IN ('.$catList.') AND a.category_published=1 AND a.category_type=\'product\' AND b.category_id!='.$parentCatId.' '.
					'ORDER BY b.category_left';
		$db->setQuery($query);
		$categories=$db->loadObjectList();

		$category_path=array();
		foreach($products as $product){
			$path=array();
			foreach($categories as $category){
				if(!empty($product->categories_id)){
					foreach($product->categories_id as $catID){
						if($catID==$category->category_id && !isset($category_path[$catID])){
							$category_path[$catID]=$this->_getCategoryParent($category, $categories, $path, $parentCatId);
						}
					}
				}
			}
		}

		foreach($category_path as $id=>$mainCat){
			$path='';
			for($i=count($mainCat);$i>0;$i--){
				$path.=$mainCat[$i-1]->category_name.' > ';
			}
			$category_path[$id]['path']=substr($path,0,-3);
		}


		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
		$app = JFactory::getApplication();
		$this->thumbnail = $config->get('thumbnail',1);
		$this->thumbnail_x=$config->get('thumbnail_x',100);
		$this->thumbnail_y=$config->get('thumbnail_y',100);
		$this->main_thumbnail_x=$this->thumbnail_x;
		$this->main_thumbnail_y=$this->thumbnail_y;
		$this->main_uploadFolder_url = $this->uploadFolder_url;
		$this->main_uploadFolder = $this->uploadFolder;

		$conf	= JFactory::getConfig();
		if(!HIKASHOP_J30){
			$siteName=$conf->getValue('config.sitename');
			$siteDesc=$conf->getValue('config.MetaDesc');
		}else{
			$siteName=$conf->get('sitename');
			$siteDesc=$conf->get('MetaDesc');
		}
		$siteAddress=JURI::base();
		$siteAddress=str_replace('administrator/','',$siteAddress);
		$xml='<?xml version="1.0" encoding="UTF-8" ?>'."\n".
					'<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n".
					"\t".'<channel>'."\n".
								"\t\t".'<title><![CDATA[ '.$siteName.' ]]></title>'."\n".
								"\t\t".'<description><![CDATA[ '.$siteDesc.' ]]></description>'."\n".
								"\t\t".'<link><![CDATA[ '.$siteAddress.' ]]></link>'."\n"."\n";
		$productClass=hikashop_get('class.product');
		foreach($products as $product) {
			if(isset($product->prices[0]->price_value)){
				$price_name = 'price_value';
				if(!empty($plugin->params['taxed_price'])){
					$price_name='price_value_with_tax';
				}
				if(empty($product->product_min_per_order)){
					$price=round($product->prices[0]->$price_name, 2);
				}
				else{
					$price=round($product->prices[0]->$price_name, 2)*$product->product_min_per_order;
				}
				$currencies=array();
				$currencyClass=hikashop_get('class.currency');
				$ids[$product->prices[0]->price_currency_id]=$product->prices[0]->price_currency_id;
				$currencies=$currencyClass->getCurrencies($ids[$product->prices[0]->price_currency_id],$currencies);
				$currency = reset($currencies);
				$xml.='<item>'."\n";
				$productClass->addAlias($product);
				if($product->product_weight_unit=='mg'){
					$product->product_weight=$product->product_weight*1000;
					$product->product_weight_unit='g';
				}
				$xml.="\t".'<g:id>'.$product->product_id.'</g:id>'."\n";
				$xml.="\t".'<title><![CDATA[ '.$product->product_name.' ]]></title>'."\n";
				$itemID='';

				if(!empty($plugin->params['item_id'])){
					$itemID='&Itemid='.$plugin->params['item_id'];
				}
				if(!empty($product->product_canonical)){
					$xml.="\t".'<g:link><![CDATA[ '.hikashop_cleanURL($product->product_canonical).' ]]></g:link>'."\n";
				}else{
					$xml.="\t".'<g:link><![CDATA[ '.$siteAddress.'index.php?option=com_hikashop&ctrl=product&task=show&cid='.$product->product_id.'&name='.$product->alias.$itemID.' ]]></g:link>'."\n";
				}
				$xml.="\t".'<g:price>'.$price.' '.$currency->currency_code.'</g:price>'."\n";
				if(!empty($product->product_description)){
					if(@$plugin->params['preview']){
						 $xml.="\t".'<g:description><![CDATA[ '.strip_tags(preg_replace('#<hr *id="system-readmore" */>.*#is','',$product->product_description)).']]></g:description>'."\n";
					}
					else{
					$xml.="\t".'<g:description><![CDATA[ '.strip_tags($product->product_description).']]></g:description>'."\n";
					}
				}
				else if(!empty($plugin->params['message'])){
					$xml.="\t".'<g:description><![CDATA[ '.$plugin->params['message'].' ]]></g:description>'."\n";
				}
				else{
					$xml.="\t".'<g:description>No description</g:description>'."\n";
				}
				$xml.=$this->_additionalParameter($product,$plugin,'condition','condition');

				$xml.=$this->_additionalParameter($product,$plugin,'gender','gender');

				$xml.=$this->_additionalParameter($product,$plugin,'age_group','age_group');

				$xml.=$this->_additionalParameter($product,$plugin,'size','size');

				$xml.=$this->_additionalParameter($product,$plugin,'color','color');

				$xml.=$this->_additionalParameter($product,$plugin,'brand','brand');

				$xml.=$this->_additionalParameter($product,$plugin,'category','google_product_category');

				if($plugin->params['add_code']){
					$xml.="\t".'<g:mpn>'.str_replace(array(' ','-'),array('',''),$product->product_code).'</g:mpn>'."\n";
				}

				if(isset($product->images) && count($product->images)){
					$i=0;
					$name = "image_link";
					foreach($product->images as $image){
						if($i<10){
							 $xml.="\t".'<g:'.$name.'>'.htmlspecialchars($siteAddress.$this->main_uploadFolder_url.$image->file_path).'</g:'.$name.'>'."\n";
							 $name = "additional_image_link";
							 $i++;
						}
					}
				}

				 $type='';
				foreach($product->categories_id as $catID){
					foreach($category_path as $id=>$catPath){
						if($id==$catID){
							$type.='"'.$catPath['path'].'",';
						}
					}
				}
				if(!empty($type)){
					$type=substr($type,0,-1);
					$xml.="\t".'<g:product_type><![CDATA[ '.$type.' ]]></g:product_type>'."\n";
				}


				if($product->product_quantity!=-1){
					$xml.="\t".'<g:quantity>'.$product->product_quantity.'</g:quantity>'."\n";
				}
				if($product->product_quantity==0){
					$xml.="\t".'<g:availability>out of stock</g:availability>'."\n";
				}
				else{
					$xml.="\t".'<g:availability>in stock</g:availability>'."\n";
				}
				$xml.="\t".'<g:shipping_weight>'.$product->product_weight.' '.$product->product_weight_unit.'</g:shipping_weight>'."\n";
				$xml.='</item>'."\n";
			}
		}
		$xml.='</channel>'."\n".'</rss>'."\n";
		return $xml;
	}

	function _additionalParameter(&$product,&$plugin,$param,$attribute){
		$xml = '';
		if(!empty($plugin->params[$param])){
			$column = $plugin->params[$param];
			if(isset($product->$column)){
				if(empty($product->$column)) return $xml;

				$text = $product->$column;
			}else{
				$text = $column;
			}
			$xml="\t".'<g:'.$attribute.'><![CDATA[ '.$text.' ]]></g:'.$attribute.'>'."\n";
		}
		return $xml;
	}

	function _connectionToGoogleDB($login, $pwd, $xml, $plugin, $name){
		jimport('joomla.client.ftp');
		$ftp = new JFTP();
		$conn_id=$ftp->connect("uploads.google.com");
		$ftp->login($login, $pwd);
		$ftp->write($name, $xml);
	}

	function _getCategoryParent($theCat, &$categories, $path, $parentCatId){
		if($theCat->category_parent_id==$parentCatId){
			$path[]=$theCat;
			return $path;
		}
		foreach($categories as $category){
			if($category->category_id==$theCat->category_parent_id){
				$path[]=$theCat;
				$path=$this->_getCategoryParent($category,$categories,$path, $parentCatId);
			}
		}
		return $path;
	}

}
