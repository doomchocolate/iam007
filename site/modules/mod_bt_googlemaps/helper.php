<?php
/**
 * @package 	mod_bt_googlemaps - BT Google Maps
 * @version		2.0.5
 * @created		Jun 2012

 * @author		BowThemes
 * @email		support@bowthems.com
 * @website		http://bowthemes.com
 * @support		Forum - http://bowthemes.com/forum/
 * @copyright	Copyright (C) 2012 Bowthemes. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


class modbt_googlemapsHelper
{	
	public static function fetchHead($params, $module){
		$document	= JFactory::getDocument();
		$mainframe = JFactory::getApplication();
		$template = $mainframe->getTemplate();
		JHTML::_('behavior.framework');
		
		$language = JFactory::getLanguage();
		$mapApi= '//maps.google.com/maps/api/js?sensor=true&language='.$language->getTag();
		if($params->get('weather')) $mapApi .= '&libraries=weather';
		$document->addScript($mapApi);
		
		if(file_exists(JPATH_BASE.'/templates/'.$template.'/html/mod_bt_googlemaps/js/default.js'))
		{
			$document->addScript(JURI::root().'templates/'.$template.'/html/mod_bt_googlemaps/js/default.js');	
		}
		else{
			$document->addScript(JURI::root().'modules/mod_bt_googlemaps/tmpl/js/btbase64.min.js');
			if($params->get('enable-custom-infobox')){
				$document->addScript(JURI::root().'modules/mod_bt_googlemaps/tmpl/js/infobox.js');		
			}
			$document->addScript(JURI::root().'modules/mod_bt_googlemaps/tmpl/js/default.js');	
			
		}
	if(file_exists(JPATH_BASE.'/templates/'.$template.'/html/mod_bt_googlemaps/css/styles.css'))
		{
			$document->addStyleSheet(JURI::root().'templates/'.$template.'/html/mod_bt_googlemaps/css/style.css');	
		}
		else{
			$document->addStyleSheet(JURI::root().'modules/mod_bt_googlemaps/tmpl/css/style.css');
		}
	
	}
}