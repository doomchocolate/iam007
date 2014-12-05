<?php
	
	
	global $wpdb;
	$wpdb = new UniteDBRev();
	
	if(!defined("WP_CONTENT_DIR")){
	
		define("WP_CONTENT_DIR",JPATH_BASE."/images/");
	}
	
	/**
	 * 
	 * _e() emulator
	 */
	if(function_exists("_e") == false){
	
		function _e($string, $textdomain = null){
			echo $string;
		}
	}
	
	/**
	 * 
	 * __() emulator
	 */
	 
	if(function_exists("__") == false){
	 
		function __($string, $textdomain = null){
			return($string);
		}
	}
	
	
	/**
	 * 
	 * add_action() emulator
	 */
	if(function_exists("add_action") == false){
		function add_action($action){		
		}
	}

	/**
	 * 
	 * load_plugin_textdomain emulator
	 */
	 
	/**
	 * 
	 * add_action() emulator
	 */
	if(function_exists("load_plugin_textdomain") == false){
		function load_plugin_textdomain($one, $two = null, $three = null, $four= null){		
		}
	}
	
	
	/**
	 * 
	 * ge bloginfo function
	 * @param $item
	 */	
	if(function_exists("get_bloginfo") == false){
		function get_bloginfo($item = null){
			
			$arrResponse = array();
			
			if(empty($item))
				return($arrResponse);
			
			if($item == "version")
				return("3.6");
			else
				return("");
		}	
	}
	
	/**
	 * 
	 * register_activation_hook emulator
	 */

	if(function_exists("register_activation_hook") == false){
		function register_activation_hook($one, $two){}
	}
	
	/**
	 * get_post_types emulator
	 */	 
	if(function_exists("get_post_types") == false){
		function get_post_types(){return(array());}
	}
	 
	 
	 /**
	  * 
	  * get_post_type_object emulator
	  */
	if(function_exists("get_post_type_object") == false){
		 function get_post_type_object($obj){
			
			$strResponse = "";
			if(is_string($obj))
				$strResponse = $obj;
			
			$response = new stdClass();
			$response->labels = new stdClass();
			$response->labels->singular_name = $strResponse;
			
			return($response);
		 }
	}
	 
	 /**
	  * 
	  * wp_create_nonce emulator
	  */
	if(function_exists("wp_create_nonce") == false){
		 function wp_create_nonce($param = null, $param2 = null){
			return("");
		 }
	}
	 
	/**
	 * 
	 * wp_verify_nonce emulator
	 */
	 
	if(function_exists("wp_verify_nonce") == false){
		function wp_verify_nonce($nonce = null){
			return(true);
		}	 
	}
	 
	
	 /**
	  * 
	  * content_url emulator
	  */
	  
	if(function_exists("content_url") == false){
		 function content_url(){
			$urlContent = juri::root()."images/";
			return($urlContent);
		 }
	}

	 
	 /**
	  * 
	  * wp_register_style emulator
	  */
	if(function_exists("wp_register_style") == false){
		 function wp_register_style($handle, $url = null){
			
			if(empty($url))
				UniteFunctionsRev::throwError("wp register style error: empty url. handle: {$handle}");
			
			$document = JFactory::getDocument();
			$document->addStyleSheet($url);
		 }
	}
	
	
	 /**
	  * 
	  * wp_register_script emulator
	  */
	if(function_exists("wp_register_script") == false){
		 function wp_register_script($handle, $url){
			
			if(empty($url))
				UniteFunctionsRev::throwError("wp register script error: empty url. handle: {$handle}");
				
			$document = JFactory::getDocument();
			$document->addScript($url);	
		 }
	}
	 
	 /**
	  * 
	  * wp_enqueue_style emulator
	  */
	  
	if(function_exists("wp_enqueue_style") == false){
		 function wp_enqueue_style($handle, $url = null){
					
			 //wp_register_style($handle, $url);
		 }
	}
	 
	 
	 /**
	  * 
	  * wp enqueue script emulator
	  */
	if(function_exists("wp_enqueue_script") == false){
		 function wp_enqueue_script($handle, $url = null){
			  //wp_register_script($handle, $url);
		 }
	}
	
	 /**
	  * 
	  * get_object_taxonomies emulator
	  */
	if(function_exists("get_object_taxonomies") == false){
	
		function get_object_taxonomies(){
			return array();
		} 
	}
	
	/**
	 * 
	 * is_ssl emulator. return if it's ssl or not
	 */
	if(function_exists("is_ssl") == false){
		function is_ssl(){
			return JURI::getInstance()->isSSL();
		}
	}
	
?>