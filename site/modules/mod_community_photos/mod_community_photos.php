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

include_once(JPATH_BASE.'/components/com_community/defines.community.php');
require_once(JPATH_BASE .'/components/com_community/libraries/core.php');

$document = JFactory::getDocument();
$document->addStyleSheet(rtrim(JURI::root(), '/').'/components/com_community/assets/modules/module.css');

$model 	= CFactory::getModel('photos');
$default = $params->get('default');

$latestPhotos	    = $model->getAllPhotos( null , PHOTOS_USER_TYPE, $default, 0 , COMMUNITY_ORDER_BY_DESC , COMMUNITY_ORDERING_BY_CREATED );

if( $latestPhotos )
{
	shuffle( $latestPhotos );
	// Make sure it is all photo object
	foreach( $latestPhotos as $row )
	{
		$photo	= JTable::getInstance( 'Photo' , 'CTable' );
		$photo->bind($row);
		$row = $photo;
	}
}

if( !empty($latestPhotos) )
{
	for( $i = 0; $i < count( $latestPhotos ); $i++ )
	{
		$row	    =	$latestPhotos[$i];
		$row->user  =	CFactory::getUser( $row->creator );
	}
}

require( JModuleHelper::getLayoutPath('mod_community_photos') );
