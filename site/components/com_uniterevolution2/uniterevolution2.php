<?php
/**
 * @package Unite Revolution Slider for Joomla 1.7-2.5
 * @version 1.0
 * @author UniteCMS.net
 * @copyright (C) 2012- Unite CMS
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


require_once JPATH_ADMINISTRATOR."/components/com_uniterevolution2/inc_php/framework/include_framework.php";
require_once JPATH_ADMINISTRATOR."/components/com_uniterevolution2/inc_php/revslider_globals.class.php";

//error_reporting(E_ALL); // debug

/**
 * 
 * get revolution slider captions css
 */
function putRevCssCaptions(){
	$tableCss = "#__".GlobalsRevSlider::TABLE_CSS_NAME;
	
	$db = new UniteDBRev();	
	$arrStyles = $db->fetch($tableCss);
	$cssStyles = UniteCssParserRev::parseDbArrayToCss($arrStyles, "\n");
	
	header('Content-type: text/css');
	echo $cssStyles;
	exit();
}



$action = UniteFunctionsRev::getPostGetVariable("action");

if(empty($action)){
	echo "action not given";
	exit();
}
	
switch($action){
	case "getcaptions":
		putRevCssCaptions();
	break;
	default:
		echo "action not found: $action";
		exit();
	break;
}


?>