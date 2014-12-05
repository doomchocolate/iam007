<?php
/**
 * @package Unite Revolution Slider for Joomla 1.7-2.5
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/


// No direct access.
defined('_JEXEC') or die;

	class UniteFunctionJoomlaRev{
		
		private static $arrControls;
		public static $componentName;	//current component name. have to be set on include.
		public static $app;
		
		
		/**
		 * 
		 * return if current joomla version is joomla 3
		 */
		public static function isJoomla3(){
			
			if(defined("JVERSION")){
				$version = JVERSION;
				$version = (int)$version;
				return($version == 3);
			}
			
			if(class_exists("JVersion")){
				$jversion = new JVersion;
				$version = $jversion->getShortVersion();
				$version = (int)$version;
				return($version == 3);
			}			
			
			return(!defined("DS"));
		}
		
		
		/**
		 * 
		 * set controls array
		 */
		public static function setControls($arrControls){
			self::$arrControls = $arrControls;
		}
		
		
		/**
		 * 
		 * put the label and the input of some form field
		 */
		public static function putFormField($form,$name,$group=null){
			echo $form->getLabel($name,$group);
			echo $form->getInput($name,$group);
		}
		
		/**
		 * 
		 * print fieldset box
		 */
		public static function putHtmlFieldsetBox($form,$name,$boxTitle="Settings"){

			if(empty($form))
				UniteFunctionsRev::throwError("Form not found!!!");
				
			?>
			
				<fieldset class="adminform unite-adminform">
					<legend><?php echo $boxTitle?></legend>
					<ul class="adminformlist unite-adminformlist">
					<?php
						$fieldset = $form->getFieldset($name);
						if(empty($fieldset))
							UniteFunctionsRev::throwError("fieldset with name: $name not found.");
						
						foreach($fieldset as $key=>$field){
							?>
								<li><?php echo $field->label; ?>
								<?php echo $field->input; ?></li>
							<?php 
						}
					?>
					</ul>
				</fieldset>
			<?php
		}
		
		/**
		 * 
		 * add script declaration wrapper
		 */
		public static function addScriptDeclaration($script){
			$document = JFactory::getDocument();
			$document->addScriptDeclaration($script);
		}
		
		/**
		 * 
		 * add script wrapper
		 */
		public static function addScript($urlScript){
			$document = JFactory::getDocument();
			$document->addScript($urlScript);
		}

		/**
		 * 
		 * add style wrapper
		 */
		public static function addStyle($urlStyle,$id=null){
			$document = JFactory::getDocument();
			$attribs = array();
			if(!empty($id))
				$attribs["id"]=$id;
			
			$document->addStyleSheet($urlStyle,"text/css",null,$attribs);
		}
		
		/**
		 * 
		 * encode array to registry (json) for saving, in some array of items
		 * 
		 */		
		public static function encodeArrayToRegistry($arr,$field){
 
			if(!isset($arr[$field]))
				return("");
				
			if(!is_array($arr[$field]))
				return($arr[$field]);
			
			$registry = new JRegistry();
			$registry->loadArray($arr[$field]);
			$value = $registry->toString('JSON');
			
			return($value);
		}
		
		/**
		 * 
		 * decode some array item to registry
		 */
		public static function decodeRegistryToArray($arr,$field){
			
			$output = array();
			if(!isset($arr[$field]))
				return($output);
				
			$value = $arr[$field];	
			if(is_array($value))
				return($value);
			
			$registry = new JRegistry();
			$registry->loadString($value,'JSON');
			$output = $registry->toArray();
			
			return($output);
		}
		
		/**
		 * 
		 * get form field value by types.
		 */
		public static function getFormFieldValue($form,$field,$group=null){
			$objField = $form->getField($field,$group);
			
			$value = $objField->value;
			$type = strtolower($objField->type);
			
			switch($type){
				case "mycheckbox":						
					$value = $objField->isChecked();
				break;
			}
			
			return($value);
		}
		
		
		/**
		 * 
		 * hide some form field
		 * @param $form
		 */
		public static function hideFormField(JForm $form,$field, $group=""){
			$class = $form->getFieldAttribute($field, "class","",$group);
			if(!empty($class))
				$class .= " hidden";
			else
				$class == "hidden";
			
			$form->setFieldAttribute($field, "hidden", "true",$group);
			$form->setFieldAttribute($field, "class", $class,$group);
		}
		
		
		/**
		 * 
		 * set alias from title
		 */
		public static function normalizeAlias($alias){
			$alias = JFilterOutput::stringURLSafe($alias);
			
			if(trim(str_replace('-','',$alias)) == '')
				$alias = JFactory::getDate()->format("Y-m-d-H-i-s");
			
			return($alias);				
		}	
		
		/**
		 * 
		 * put multiple html option boxes
		 * exceptions - come delimited fieldset names. Those won't be shown
		 */
		public static function putHtmlFieldsetBoxes($form,$name,$exceptions=""){
			
			$arrExceptions = explode(",",$exceptions);
			
			$arrfieldsets = $form->getFieldsets($name);
		
			foreach($arrfieldsets as $name=>$arrFieldset){
				
				if(in_array($name, $arrExceptions))
					continue;
				
				self::putHtmlFieldsetBox($form,$arrFieldset->name,$arrFieldset->label);				
			}
		}
		
		
		/**
		 * 
		 * disable mootools include
		 */
		public static function disableMootools(){
			JHtml::_('behavior.framework', true);
			JHtml::_('behavior.modal');
									
			$doc = JFactory::getDocument();
				
			if(!method_exists($doc,'getHeadData')){ 
				return;
			}
			
			//	looking for scripts
			$headers = $doc->getHeadData();
			
			$scripts = isset($headers['scripts']) ? $headers['scripts'] : array();
			$headers['scripts'] = array();
			
			foreach($scripts as $url=>$type){
				if (strpos($url, 'mootools') === false && 
					strpos($url, 'js/core.js') === false && 
					strpos($url, 'js/modal.js') === false && 
				    strpos($url, 'js/caption.js') === false){
					$headers['scripts'][$url] = $type;
				}
			}
			
			$doc->setHeadData($headers); 			
		}
		
		
		/**
		 * 
		 * give joomla order to hide main menu. 
		 * this must be used on view.html.php
		 */
		public static function hideMainMenu(){
			JRequest::setVar('hidemainmenu', true);
		}
		
		/**
		 * 
		 * get current component
		 */
		public static function getCurrentComponent(){
			$component = JRequest::getCmd("option");
			return($component);
		}
		
		/**
		 * 
		 * get component url - site side
		 * 
		 */
		public static function getUrlComponent($args,$component=""){
			if(empty($component))
				$component = self::$componentName;
			$url = juri::root()."index.php?option=".$component."&".$args;
			
			return($url);
		}
		
		
		/**
		 * 
		 * get view url (admin side)
		 */
		public static function getViewUrl($view,$layout="default",$args="",$component=""){
			
			if(empty($component))
				$component = self::$componentName;
				
			$url = "index.php?option=".$component;
			$url .= "&view=$view";
			
			//add layout
			if(!empty($layout))
				$url .= "&layout=".$layout; 
			
			//add additional arguments
			if(!empty($args))
				$url .= "&".$args;
			
			//$url = JURI::root().$url;
			
			$url = JRoute::_($url,false);
			
			return($url);
		}
		
		/**
		 * Get url of image for output
		 */
		public static function getImageOutputUrl($filename,$width=0,$height=0,$exact=false,$encode=true){
			
			//exact validation:
			if(($exact == "true" || $exact == true) && (empty($width) || empty($height) ))
				UniteFunctionsRev::throwError("Exact must have both - width and height");
						
			if($encode == true)
				$filename = base64_encode($filename);
			
			$url = "index.php?option=".self::$componentName."&task=showimage&img=$filename";
			
			if(!empty($width))
				$url .= "&w=".$width;
		
			if(!empty($height))
				$url .= "&h=".$height;
		
			if($exact == true)
				$url .= "&t=exact";
		
			if($encode == false)
				$url .= "&noencode=true";
			
			return($url);
		}
		
		
		/**
		 * 
		 * get image url from filename
		 */
		public static function getImageUrl($filename){
			$filenameLower = strtolower($filename);
			
			if(strpos($filenameLower, "http://") !== false)
				return($filename);
				
			if(strpos($filenameLower, "https://") !== false)
				return($filename);
				
			if(strpos($filenameLower, "www.") === 0)
				return($filename);
			
			$urlImage = JURI::root().$filename;
			
			if(JURI::getInstance()->isSSL() == true)
				$urlImage = str_replace("http://","https://",$urlImage);
			
			return($urlImage);
		}
		
		
		/**
		 * 
		 * get filename out of image
		 */
		public static function getImageFilename($urlImage){

			$prefix = JURI::root();
			if(strpos($prefix,"http://www."))
				$prefix2 = str_replace("http://www.","http://",$prefix);
			else
				$prefix2 = str_replace("http://","http://www.",$prefix);
			
			$filename = str_replace($prefix, "", $urlImage);
			if($filename == $urlImage)
				$filename = str_replace($prefix2, "", $urlImage);
			
			return($filename);
		}
		
		/**
		 * 
		 * get image filepath from filename
		 * @param $filename
		 */
		public static function getImageFilepath($filename){
			$pathImage = JPATH_SITE."/".$filename;
			return($pathImage);
		}
		
		/**
		 * 
		 * get image filepath from image url (images folder)
		 */
		public static function getImageFilepathFromUrl($urlImage){
			$urlBase = GlobalsRevSlider::$url_base;
			$pathImage = str_replace($urlBase, "", $urlImage);
			
			$filepathImage = GlobalsRevSlider::$path_base.$pathImage;
			$filepathImage = realpath($filepathImage);
			
			return($filepathImage);
		}
		
		
		/**
		 * get image path from url
		 */
		public static function getPathImageFromUrl($urlImage){
			$urlBase = GlobalsRevSlider::$url_base;
			$pathImage = str_replace($urlBase, "", $urlImage);
			
			return($pathImage);
		}
		
		/**
		 * get cache path. if not exists - try to crate it
		 */
		private static function getPathCache(){
			
			//set cache path
			$component = self::$componentName;
			
			$pathCache = JPATH_SITE."/cache/".$component."/";
			if(is_dir($pathCache))
				return($pathCache);
			
			@mkdir($pathCache);
			
			if(is_dir($pathCache))
				return($pathCache);
			
			//make media cache path
			$pathCache = JPATH_SITE."/media/".$component."/cache/";
			if(is_dir($pathCache))
				return($pathCache);
			
			@mkdir($pathCache);
			
			if(is_dir($pathCache))
				return($pathCache);
			
			//make component cache path
			$pathCache = JPATH_COMPONENT_SITE."/cache/";
			
			return($pathCache);
		}
		
		
		/**
		 * show image from request
		 */
		public static function showImageFromRequest(){
			
			$pathCache = self::getPathCache();
			$pathImages = JPATH_SITE."/";
			$urlImages = JURI::root();
			$pathEmptyImage = JPATH_COMPONENT_ADMINISTRATOR."/assets/resizer/empty_image.jpg";
			
			$imageView = new UniteImageViewRev($pathCache, $pathImages, $urlImages, $pathEmptyImage);
			$imageView->showImageFromGet();
			exit();
		}		

		/**
		 * 
		 * get post or get application
		 */
		public static function getPostGetVar($name,$default="",$filter="STRING"){
			if(empty(self::$app))
				self::$app = JFactory::getApplication();
			
			$jinput = self::$app->input;
			$var = $jinput->get($name,$default,$filter);
			return($var);
		}
		
		
		/**
		 * 
		 * clear params array from client side
		 */
		public static function clearParamsArray($arrParams){
			$arrNew = array();
			foreach($arrParams as $key=>$value){
				$key = str_replace("jform_params_", "", $key);
				$key = str_replace("jform_", "", $key);
				$arrNew[$key] = $value;
			}
			return($arrNew);
		}
		
		/**
		 * 
		 * check if jquery script included
		 */
		public static function isJqueryIncluded(){
			$document = JFactory::getDocument();
			$arrScripts = $document->_scripts;
			foreach($arrScripts as $filepath=>$arrDetails){
				$filename = basename($filepath);
				$filename = strtolower($filename);
				switch($filename){
					case "jquery.min.js":
						return(true);
					break;
				}
			}
			return(false);
		}
		
	}

?>