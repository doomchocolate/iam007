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

class CPrivacy
{
	/**
	 * Return true if actor have access to target's item
	 * @param type where the privacy setting should be extracted, {user, group, global, custom}
	 * Site super admin waill always have access to all area
	 */
	static public function isAccessAllowed($actorId, $targetId, $type , $userPrivacyParam)
	{
		$actor  = CFactory::getUser($actorId);
		$target = CFactory::getUser($targetId);

		// Load User params
		$params			= $target->getParams();

		// guest
		$relation = 10;

		// site members
		if( $actor->id != 0 )
			$relation = 20;

		// friends
		if( CFriendsHelper::isConnected($actorId, $targetId) )
			 $relation = 30;

		// mine, target and actor is the same person
		if( COwnerHelper::isMine($actor->id, $target->id) )
			 $relation = 40;

		// @todo: respect privacy settings
		// If type is 'custom', then $userPrivacyParam will contain the exact
		// permission level
		$permissionLevel = ($type == 'custom') ? $userPrivacyParam : $params->get($userPrivacyParam);
		if( $relation <  $permissionLevel && !COwnerHelper::isCommunityAdmin($actorId) )
		{
			return false;
		}
		return true;
	}

	static public function getHTML( $nameAttribute , $selectedAccess = 0 , $buttonType = COMMUNITY_PRIVACY_BUTTON_SMALL , $access = array(), $direction = '' )
	{
		$template	= new CTemplate();
		$config		= CFactory::getConfig();

		// Initialize default options to show
		if( empty( $access) )
		{
			$access[ 'public' ]		= true;
			$access[ 'members' ]	= true;
			$access[ 'friends' ]	= true;
			$access[ 'self' ]		= true;
		}
		$classAttribute	= $buttonType == COMMUNITY_PRIVACY_BUTTON_SMALL ? 'js_PriContainer' : 'js_PriContainer js_PriContainerLarge';

		return $template->set( 'classAttribute' , $classAttribute )
						->set( 'access' , $access )
						->set( 'nameAttribute' , $nameAttribute )
						->set( 'selectedAccess' , $selectedAccess )
						->set( 'direction' , $direction )
						->fetch( 'privacy' );
	}

	static public function getAccessLevel($actorId, $targetId)
	{
		$actor  = CFactory::getUser($actorId);
		$target = CFactory::getUser($targetId);

		//CFactory::load( 'helpers' , 'owner' );
		//CFactory::load( 'helpers' , 'friends' );

		// public guest
		$access	= 0;

		// site members
		if($actor->id > 0)
			$access	= 20;

		// they are friends
		if( $target->id > 0 && CFriendsHelper::isConnected($actor->id, $target->id) )
			$access = 30;

		// mine, target and actor is the same person
		if( $target->id > 0 && COwnerHelper::isMine($actor->id, $target->id) )
			$access = 40;

		if( COwnerHelper::isCommunityAdmin() )
			$access = 40;

		return $access;
	}

	/**
	 * Function to get User Privacy setting
	 * @param  [obj] $user       [User Object get form CUser]
	 * @param  [string] $identifier [Paramerter to return]
	 * @return [string]             [User Privacy setting value]
	 */
	static public function getUserPrivacy($user,$identifier)
	{
		$userParam = $user->getParams();
		$privacy = $userParam->get($identifier,0);
		$defaultSelect = 0;

		switch( $privacy ){

			case PRIVACY_PRIVATE:
				$defaultSelect = PRIVACY_PRIVATE;
				break;
			case PRIVACY_FRIENDS:
				$defaultSelect = PRIVACY_FRIENDS;
				break;
			case PRIVACY_MEMBERS:
				$defaultSelect = PRIVACY_MEMBERS;
				break;
			case 0:
				break;
			case 10:
				break;
		}

		return $defaultSelect;
	}
}