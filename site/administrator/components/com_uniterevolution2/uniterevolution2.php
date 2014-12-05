<?php
/**
 * @package Unite Revolution Slider for Joomla 1.7-2.5
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$currentDir = dirname(__FILE__)."/";

$currentFile = __FILE__;
$currentFolder = dirname($currentFile);

require_once $currentFolder."/includes.php";
require_once $currentFolder."/revslider_admin.php";


	//add tiny box dropdown menu
	$tinybox = new RevSlider_TinyBox();
	$productAdmin = new RevSliderAdmin($currentFile);
		
	UniteFunctionJoomlaRev::disableMootools();
	
//set global title
$title = JText::_('COM_UNITEREVOLUTION2');
JToolBarHelper::title($title , 'generic.png');


?>