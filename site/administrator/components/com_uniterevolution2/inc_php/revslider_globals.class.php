<?php

	define("REVSLIDER_TEXTDOMAIN","revslider");

	class GlobalsRevSlider{
		
		const SHOW_DEBUG = false;
		const SLIDER_REVISION = '4.1.3';
		const TABLE_SLIDERS_NAME = "revslider_sliders";
		const TABLE_SLIDES_NAME = "revslider_slides";
		const TABLE_SETTINGS_NAME = "revslider_settings";		
		const TABLE_CSS_NAME = "revslider_css";
		const TABLE_LAYER_ANIMS_NAME = "revslider_layer_animations";

		const FIELDS_SLIDE = "slider_id,slide_order,params,layers";
		const FIELDS_SLIDER = "title,alias,params";

		const YOUTUBE_EXAMPLE_ID = "T8--OggjJKQ";
		const DEFAULT_YOUTUBE_ARGUMENTS = "hd=1&amp;wmode=opaque&amp;controls=1&amp;showinfo=0;rel=0;";
		const DEFAULT_VIMEO_ARGUMENTS = "title=0&amp;byline=0&amp;portrait=0;api=1";
		
		const LINK_HELP_SLIDERS = "http://unitecms.net/joomla-extensions/unite-revolution-slider-responsive/documentation";
		const LINK_HELP_SLIDER = "http://unitecms.net/joomla-extensions/unite-revolution-slider-responsive/documentation/main-settings";
		const LINK_HELP_SLIDE_LIST = "http://unitecms.net/joomla-extensions/unite-revolution-slider-responsive/documentation/slides-editor";
		const LINK_HELP_SLIDE = "http://unitecms.net/joomla-extensions/unite-revolution-slider-responsive/documentation/slide-general-settings";
		
		//joomla additions:
		const PLUGIN_NAME = "com_uniterevolution2";
		
		public static $table_prefix;
		public static $table_sliders;
		public static $table_slides;
		public static $table_settings;
		public static $table_css;
		public static $table_layer_anims;
		public static $filepath_backup;
		public static $filepath_captions;
		public static $filepath_dynamic_captions;
		public static $filepath_static_captions;
		public static $filepath_captions_original;
		public static $url_base;
		public static $url_media;
		public static $url_item_plugin;		
		public static $url_component_client;
		public static $urlCaptionsCSS;
		public static $urlStaticCaptionsCSS;
		public static $urlDynamicCaptionsCSS;
		public static $urlExportZip;
		public static $isNewVersion;
		public static $path_base;
		public static $path_media;
		public static $path_images;
		protected static $path_cache;
		
		/**
		 * 
		 * init globals
		 */
		public static function initGlobals(){
			
			GlobalsRevSlider::$table_prefix = "#__";
			
			//set table names
			GlobalsRevSlider::$table_sliders = self::$table_prefix.GlobalsRevSlider::TABLE_SLIDERS_NAME;
			GlobalsRevSlider::$table_slides = self::$table_prefix.GlobalsRevSlider::TABLE_SLIDES_NAME;
			GlobalsRevSlider::$table_settings = self::$table_prefix.GlobalsRevSlider::TABLE_SETTINGS_NAME;
			GlobalsRevSlider::$table_css = self::$table_prefix.GlobalsRevSlider::TABLE_CSS_NAME;
			GlobalsRevSlider::$table_layer_anims = self::$table_prefix.GlobalsRevSlider::TABLE_LAYER_ANIMS_NAME;
			
			GlobalsRevSlider::$path_media = JPATH_ROOT."/media/".self::PLUGIN_NAME."/";
			self::$path_base = JPATH_ROOT."/";
			self::$path_images = JPATH_ROOT."/images/";			
			self::$path_cache = self::$path_media."cache/";			
			
			$pathMediaAssets = self::$path_media."assets/";
			GlobalsRevSlider::$filepath_dynamic_captions = $pathMediaAssets."rs-plugin/css/dynamic-captions.css";
			GlobalsRevSlider::$filepath_static_captions = $pathMediaAssets."rs-plugin/css/static-captions.css";
			GlobalsRevSlider::$filepath_captions_original = $pathMediaAssets."rs-plugin/css/captions-original.css";
			
			GlobalsRevSlider::$filepath_backup = self::$path_media."backup/";
			GlobalsRevSlider::$filepath_captions = $pathMediaAssets."rs-plugin/css/captions.css";
			
			//set urls
			self::$url_base = JURI::root();			
			self::$url_component_client = self::$url_base."index.php?option=".self::PLUGIN_NAME;
			self::$url_media = self::$url_base."media/".GlobalsRevSlider::PLUGIN_NAME."/";
			self::$url_item_plugin = self::$url_media."assets/rs-plugin/";
			
			GlobalsRevSlider::$urlCaptionsCSS = self::$url_component_client."&action=getcaptions";
			GlobalsRevSlider::$urlDynamicCaptionsCSS = self::$url_item_plugin."css/dynamic-captions.css";
			GlobalsRevSlider::$urlStaticCaptionsCSS = self::$url_item_plugin."css/static-captions.css";
			
			GlobalsRevSlider::$urlExportZip = self::$path_cache."export.zip";
			
		}
		
	}
	
	GlobalsRevSlider::initGlobals();
	
	
?>