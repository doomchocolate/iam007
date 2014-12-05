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

class CMapsHelper
{
	/**
	 *	Returns an object of data containing user's address information
	 *
	 *	@access	static
	 *	@params	int	$userId
	 *	@return stdClass Object
	 **/
	static public function getAddress( $userId )
	{
		$user			= CFactory::getUser( $userId );
		$config			= CFactory::getConfig();

		$obj			= new stdClass();
		$obj->street	= $user->getInfo( $config->get('fieldcodestreet') );
		$obj->city		= $user->getInfo( $config->get('fieldcodecity') );
		$obj->state		= $user->getInfo( $config->get('fieldcodestate') );
		$obj->country	= $user->getInfo( $config->get('fieldcodecountry') );

		return $obj;
	}
}