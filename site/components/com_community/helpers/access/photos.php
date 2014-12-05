<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

Class CPhotosAccess implements CAccessInterface
{
    /**
	 * Method to check if a user is authorised to perform an action in this class
	 *
	 * @param	integer	$userId	Id of the user for which to check authorisation.
	 * @param	string	$action	The name of the action to authorise.
	 * @param	mixed	$asset	Name of the asset as a string.
	 *
	 * @return	boolean	True if authorised.
	 * @since	Jomsocial 2.4
	 */
	static public function authorise()
	{
		$args      = func_get_args();
		$assetName = array_shift ( $args );

		if (method_exists(__CLASS__,$assetName)) {
			return call_user_func_array(array(__CLASS__, $assetName), $args);
		} else {
			return null;
		}
	}

	/*
	 * @param : $asset = group id, $album_obj = album object, $userid = userid
	 * @return : boolean / int
	 */
	static public function photosGroupAlbumView($userid, $asset, $album_obj){
		$group	= JTable::getInstance( 'Group' , 'CTable' );
		$group->load($asset);

		$params	= $group->getParams();
		$photopermission = $params->get('photopermission', GROUP_PHOTO_PERMISSION_ADMINS);

		if( $photopermission == GROUP_PHOTO_PERMISSION_MEMBERS && $group->isMember($userid) ){
			return ($userid == $album_obj->creator || $group->isAdmin($userid ));
		}else if( ($photopermission == GROUP_PHOTO_PERMISSION_ADMINS && $group->isAdmin($userid) ) || COwnerHelper::isCommunityAdmin() ){
			return true;
		}else{
			return false;
		}
	}

	/*
	 * @param : $asset = null, $wall_obj = wall object, $userid = userid
	 * @return : boolean / int
	 */
	static public function photosWallEdit($userid, $asset, $wall_obj){
		// @rule: We only allow editing of wall in 15 minutes
		$viewer		= CFactory::getUser($userid);
		$now		= JFactory::getDate();
		$interval	= CTimeHelper::timeIntervalDifference( $wall_obj->date , $now->toSql() );
		$interval	= abs( $interval );

		// Only owner and site admin can edit
		if( ( COwnerHelper::isCommunityAdmin() || $viewer->id == $wall_obj->post_by ) && ( COMMUNITY_WALLS_EDIT_INTERVAL > $interval ) )
		{
			return true;
		}
		return false;
	}

	/*
	 *	@param - asset as photo id
	 */
	static public function photosTagRemove($userid, $asset,$taggedUser){
		//condition: only owner can remove the tag
		$photo	= JTable::getInstance( 'Photo' , 'CTable' );
		$photo->load( $asset );
		if($userid == $photo->creator || $userid == $taggedUser->id){
			return true;
		}else{
			return false;
		}
	}

	/*
	 * @param - asset as album id
	 * @param - group_obj as group object
	 *
	 */

	static public function photosGroupAlbumManage($userid, $asset, $group_obj){
		$album	= JTable::getInstance( 'Album' , 'CTable' );
		$album->load( $asset );

		//condition: only owner of the album or community admin or album owner
		return ( COwnerHelper::isCommunityAdmin() || $group_obj->isAdmin( $userid ) || $album->creator == $userid );
	}

	/*
	 * @param - asset as album id
	 *
	 */
	static public function photosUserAlbumManage($userid, $asset){
		$album	= JTable::getInstance( 'Album' , 'CTable' );
		$album->load( $asset );

		//condition: only owner of the album or community admin
		return ($album->creator == $userid || COwnerHelper::isCommunityAdmin());
	}

}