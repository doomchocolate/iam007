<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class CLimitsLibrary
{
	static public function exceedDaily( $view , $userId = null )
	{
		$my		= CFactory::getUser( $userId );

		// Guests shouldn't be even allowed here.
		if( $my->id == 0 )
		{
			return true;
		}

		$view		= JString::strtolower( $view );

		// We need to include the model first before using ReflectionClass so that the model file is included.
		$model		= CFactory::getModel( $view );

		// Since the model will always return a CCachingModel which is a proxy,
		// for the real model, we can't really test what type of object it is.
		$modelClass	= 'CommunityModel' . ucfirst( $view );

		$reflection	= new ReflectionClass( $modelClass );
		if( !$reflection->implementsInterface( 'CLimitsInterface' ) )
		{
			return false;
		}


		$config		= CFactory::getConfig();
		$total		= $model->getTotalToday( $my->id );
		$max		= $config->getInt( 'limit_' . $view . '_perday' );

		return ( $total >= $max && $max != 0 );
	}
}