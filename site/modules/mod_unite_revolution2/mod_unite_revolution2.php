<?php

/**
 * @package Unite Revolution Slider Module for Joomla 1.7-2.5
 * @version 1.0
 * @author UniteCMS.net
 * @copyright (C) 2012- Unite CMS
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined('_JEXEC') or die;

	//include item files
	$pathIncludes = JPATH_ADMINISTRATOR."/components/com_uniterevolution2/includes.php";
	require_once $pathIncludes;
		
	//set active menu link
	$urlBase = JURI::base();
	
	$sliderID = $params->get("sliderid");
		
	$document = JFactory::getDocument();
	$include_jquery = $params->get("include_jquery","true");
	
	if($include_jquery == "true"){
		if(UniteFunctionJoomlaRev::isJqueryIncluded() == false){
			$jsPrefix = "http";
			if(JURI::getInstance()->isSSL() == true)
				$jsPrefix = "https";
				
			$document->addScript("{$jsPrefix}://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js?app=revolution_slider");
			
		}
	}
	
	$loadType = $params->get("js_load_type","head");
	$isJSInBody = ($loadType == "body")?true:false;
	$noConflictMode = ($params->get("no_conflict_mode") == "true")?true:false;
	
	
	//css includes
	$document->addStyleSheet(GlobalsRevSlider::$url_item_plugin."css/settings.css");
	
	if(file_exists(GlobalsRevSlider::$filepath_dynamic_captions) == true)
		$document->addStyleSheet(GlobalsRevSlider::$urlDynamicCaptionsCSS);
	else
		$document->addStyleSheet(GlobalsRevSlider::$urlCaptionsCSS);
		
	$document->addStyleSheet(GlobalsRevSlider::$urlStaticCaptionsCSS);
	
	//include js:
	if($isJSInBody == false){
		$document->addScript(GlobalsRevSlider::$url_item_plugin."js/jquery.themepunch.plugins.min.js");
		$document->addScript(GlobalsRevSlider::$url_item_plugin."js/jquery.themepunch.revolution.min.js");
	}
	
	
	$output = new RevSliderOutput();
	$output->jsToBody = $isJSInBody;
	$output->noConflictMode = $noConflictMode;

	$output->putSliderBase($sliderID);
	
?>	