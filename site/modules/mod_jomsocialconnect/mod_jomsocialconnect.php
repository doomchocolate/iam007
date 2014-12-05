<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php' );
require_once( JPATH_ROOT .'/components/com_community/libraries/window.php' );
CWindow::load();

// Script needs to be here if they are 
//CFactory::load( 'libraries' , 'facebook' );

// Once they reach here, we assume that they are already logged into facebook.
// Since CFacebook library handles the security we don't need to worry about any intercepts here.
$facebook		= new CFacebook( false );
$my				= CFactory::getUser();
$config			= CFactory::getConfig();
$fbUser			= $facebook->getUser();

require(JModuleHelper::getLayoutPath('mod_jomsocialconnect'));
