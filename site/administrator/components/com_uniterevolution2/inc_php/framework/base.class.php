<?php

	class UniteBaseClassRev{
		
		protected static $wpdb;
		protected static $table_prefix;		
		protected static $mainFile;
		protected static $t;
		
		protected static $dir_plugin;
		protected static $dir_languages;
		public static $url_base;
		public static $url_plugin;
		public static $url_item_plugin;
		protected static $url_ajax;
		public static $url_component;
		public static $url_component_client;
		public static $url_ajax_actions;
		protected static $url_ajax_showimage;				
		protected static $path_settings;
		public static $path_plugin;
		protected static $path_languages;		
		protected static $path_temp;
		protected static $path_views;
		protected static $path_templates;
		protected static $path_cache;
		protected static $is_multisite;
		protected static $debugMode = false;
		
		//joomla addition: 
		public static $path_media;
		public static $url_media;
		
		/**
		 * 
		 * the constructor
		 */
		public function __construct($mainFile,$t){
			global $wpdb;
			
			self::$is_multisite = UniteFunctionsWPRev::isMultisite();
			
			self::$wpdb = $wpdb;
			self::$table_prefix = "#__";
			if(UniteFunctionsWPRev::isMultisite()){
				$blogID = UniteFunctionsWPRev::getBlogID();
				if($blogID != 1){
					self::$table_prefix .= $blogID."_";
				}
			}
			
			self::$mainFile = $mainFile;
			self::$t = $t;
			
			self::$dir_plugin = "uniterevolution";			
			self::$url_base = JURI::root();
			self::$url_plugin = self::$url_base."administrator/components/".GlobalsRevSlider::PLUGIN_NAME."/";
			self::$url_component = self::$url_base."administrator/index.php?option=".GlobalsRevSlider::PLUGIN_NAME;
			self::$url_component_client = self::$url_base."index.php?option=".GlobalsRevSlider::PLUGIN_NAME;
			self::$url_ajax = self::$url_base."administrator/index.php?option=".GlobalsRevSlider::PLUGIN_NAME;
			self::$url_ajax_actions = self::$url_ajax . "&action=".self::$dir_plugin."_ajax_action";
			self::$url_ajax_showimage = self::$url_ajax . "&action=".self::$dir_plugin."_show_image";
			self::$path_plugin = dirname(self::$mainFile)."/";
			
			self::$path_settings = self::$path_plugin."settings/";
			self::$path_temp = self::$path_plugin."temp/";
			
			//set cache path:
			self::setPathCache();
			
			self::$path_views = self::$path_plugin."views/";
			self::$path_templates = self::$path_views."/templates/";
			self::$path_languages = self::$path_plugin."languages/";
			self::$dir_languages = self::$dir_plugin."/languages/";
			
			load_plugin_textdomain(self::$dir_plugin,false,self::$dir_languages);
			
			//update globals oldversion flag
			GlobalsRevSlider::$isNewVersion = false;
			$version = get_bloginfo("version");
			$version = (double)$version;
			if($version >= 3.5)
				GlobalsRevSlider::$isNewVersion = true;
			
				
			//joomla addition:
			self::$url_media = self::$url_base."media/".GlobalsRevSlider::PLUGIN_NAME."/";
			self::$url_item_plugin = self::$url_media."assets/rs-plugin/";
			self::$path_media = JPATH_ROOT."/media/".GlobalsRevSlider::PLUGIN_NAME."/";
			
			self::$path_cache = self::$path_media."cache/";
			
		}
		
		/**
		 * 
		 * set cache path for images. for multisite it will be current blog content folder
		 */
		private static function setPathCache(){
			
		}
		
		/**
		 * 
		 * set debug mode.
		 */
		public static function setDebugMode(){
			self::$debugMode = true;
		}
		
		
		/**
		 * 
		 * add some wordpress action
		 */
		protected static function addAction($action,$eventFunction){
			
			add_action( $action, array(self::$t, $eventFunction) );			
		}
		
		
		/**
		 * 
		 * register script helper function
		 * @param $scriptFilename
		 */
		protected static function addScriptAbsoluteUrl($scriptPath,$handle){
			
			wp_register_script($handle, $scriptPath);
			wp_enqueue_script($handle);
		}
		
		/**
		 * 
		 * register script helper function
		 * @param $scriptFilename
		 */
		protected static function addScriptAbsoluteUrlWaitForOther($scriptPath,$handle,$waitfor = array()){
			wp_enqueue_script($handle, $scriptPath, $waitfor);
		}
		
		/**
		 * 
		 * register script helper function
		 * @param $scriptFilename
		 */
		protected static function addScript($scriptName,$folder="js",$handle=null){
			if($handle == null)
				$handle = self::$dir_plugin."-".$scriptName;
			
			wp_register_script($handle , self::$url_plugin .$folder."/".$scriptName.".js?rev=". GlobalsRevSlider::SLIDER_REVISION );
			wp_enqueue_script($handle);
		}
		
		/**
		 * 
		 * register script helper function
		 * @param $scriptFilename
		 */
		protected static function addScriptWaitFor($scriptName,$folder="js",$handle=null,$waitfor = array()){
			if($handle == null)
				$handle = self::$dir_plugin."-".$scriptName;
			
			wp_enqueue_script($handle, self::$url_plugin .$folder."/".$scriptName.".js?rev=". GlobalsRevSlider::SLIDER_REVISION, $waitfor);
		}
		
		/**
		 * 
		 * register common script helper function
		 * the handle for the common script is coming without plugin name
		 */
		protected static function addScriptCommon($scriptName,$handle=null, $folder="js"){
			if($handle == null)
				$handle = $scriptName;
			
			self::addScript($scriptName,$folder,$handle);
		}
		
		
		/**
		 * 
		 * simple enqueue script
		 */
		protected static function addWPScript($scriptName){
			wp_enqueue_script($scriptName);
		}
		
		
		/**
		 * 
		 * register style helper function
		 * @param $styleFilename
		 */
		protected static function addStyle($styleName,$handle=null,$folder="css"){
			if($handle == null)
				$handle = self::$dir_plugin."-".$styleName;
						
			wp_register_style($handle , self::$url_plugin .$folder."/".$styleName.".css?rev=". GlobalsRevSlider::SLIDER_REVISION);
			wp_enqueue_style($handle);
		}
		
		/**
		 * 
		 * register style helper function
		 * @param $styleFilename
		 */
		protected static function addDynamicStyle($styleName,$handle=null,$folder="css"){
			if($handle == null)
				$handle = self::$dir_plugin."-".$styleName;
			
			wp_register_style($handle , self::$url_plugin .$folder."/".$styleName.".php?rev=". GlobalsRevSlider::SLIDER_REVISION );
			wp_enqueue_style($handle);
		}
		
		/**
		 * 
		 * register common script helper function
		 * the handle for the common script is coming without plugin name
		 */
		protected static function addStyleCommon($styleName,$handle=null,$folder="css"){
			if($handle == null)	
				$handle = $styleName;
			self::addStyle($styleName,$handle,$folder);
			
		}
		
		/**
		 * 
		 * register style absolute url helper function
		 */
		protected static function addStyleAbsoluteUrl($styleUrl,$handle){
			
			wp_register_style($handle , $styleUrl);
			wp_enqueue_style($handle);
		}
		
		
		/**
		 * 
		 * simple enqueue style
		 */
		protected static function addWPStyle($styleName){
			wp_enqueue_style($styleName);
		}
		
		/**
		 * 
		 * get image url to be shown via thumb making script.
		 */
		public static function getImageUrl($filepath, $width=null,$height=null,$exact=false,$effect=null,$effect_param=null){
						
			$urlImage = UniteImageViewRev::getUrlThumb(self::$url_ajax_showimage, $filepath,$width ,$height ,$exact ,$effect ,$effect_param);
			
			return($urlImage);
		}
		
		
		/**
		 * 
		 * on show image ajax event. outputs image with parameters 
		 */
		public static function onShowImage(){
			
			$pathImages = UniteFunctionsWPRev::getPathContent();
			$urlImages = UniteFunctionsWPRev::getUrlContent();
			
			try{
				
				$imageView = new UniteImageViewRev(self::$path_cache,$pathImages,$urlImages);
				$imageView->showImageFromGet();
				
			}catch (Exception $e){
				header("status: 500");
				echo $e->getMessage();
				exit();
			}
		}
		
		
		/**
		 * 
		 * get POST var
		 */
		protected static function getPostVar($key,$defaultValue = ""){
			$val = self::getVar($_POST, $key, $defaultValue);
			return($val);			
		}
				
		/**
		 * 
		 * get GET var
		 */
		protected static function getGetVar($key,$defaultValue = ""){
			$val = self::getVar($_GET, $key, $defaultValue);
			return($val);
		}
		
		
		/**
		 * 
		 * get post or get variable
		 */
		protected static function getPostGetVar($key,$defaultValue = ""){
			
			if(array_key_exists($key, $_POST))
				$val = self::getVar($_POST, $key, $defaultValue);
			else
				$val = self::getVar($_GET, $key, $defaultValue);				
			
			return($val);							
		}
		
		
		/**
		 * 
		 * get some var from array
		 */
		protected static function getVar($arr,$key,$defaultValue = ""){
			$val = $defaultValue;
			if(isset($arr[$key])) $val = $arr[$key];
			return($val);
		}
		
		
		
		/**
		 * 
		 * make config file of all the text in the settings for the .mo and .po creation
		 */
		protected static function updateSettingsText(){
			
			$filelist = UniteFunctionsRev::getFileList(self::$path_settings,"xml");
			foreach($filelist as $file){
				$filepath = self::$path_settings.$file;
				UniteFunctionsWPRev::writeSettingLanguageFile($filepath);
			}
			
		} 
		
	}

?>