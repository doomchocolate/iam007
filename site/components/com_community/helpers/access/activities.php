<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

Class CActivitiesAccess implements CAccessInterface{

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
	 * @param : int(activity_id)
	 * This function will get the permission to add for profile/mainstream activity
	 *
	 * @return : bool
	 */
	static public function activitiesCommentAdd($userId, $assetId, $obj=NULL){

		//$obj = func_get_arg(2);
		$params = func_get_args();
		$obj = (!isset($params[2])) ? NULL : $params[2] ;
		$model		= CFactory::getModel('activities');
		$result		= false;
		$config		= CFactory::getConfig();

		// Guest can never leave a comment
		if( $userId == 0){
			return false;
		}

		// If global config allow all members to comment, allow it
		if( $config->get( 'allmemberactivitycomment' ) == '1')
		{
			return true;
		}

		$allow_comment = false;

		// if all activity comment is allowed, return true
		$config			= CFactory::getConfig();
		if($config->get( 'allmemberactivitycomment' ) == '1' && COwnerHelper::isRegisteredUser()){
			$allow_comment = true;
		}

		if($obj instanceof CTableEvent || $obj instanceof CTableGroup){
			//event or group activities only
			if($obj -> isMember($userId)){
				$allow_comment = true;
			}
		}else if($config->get( 'allmemberactivitycomment' ) == '1' && COwnerHelper::isRegisteredUser()){
			// if all activity comment is allowed, return true
			$allow_comment = true;
		}
        if ( !isset($obj->params))
            $params = '{}';
        else
            $params = $obj->params;
		$params = new CParameter($params);
		$commentPermission = $params->get('commentPermission',NULL);

		if( $commentPermission == false  && !is_null($commentPermission))
		{
			$allow_comment = false;
		}

		if($allow_comment || CFriendsHelper::isConnected($assetId, $userId) || COwnerHelper::isCommunityAdmin()){
			$result = true;
		}

		return $result;
	}

	/*
	 * @param : int(activity_id)
	 * This function will get the permission to delete for profile/mainstream activity
	 *
	 * @return : bool
	 */
	static public function activitiesDelete($userId, $assetId){
		$obj = func_get_arg(0);
		$model		  = CFactory::getModel('activities');
		$result = false;

		if($obj instanceof CTableEvent || $obj instanceof CTableGroup){
			//event or group activities only
			$isAppOwner =  $obj->isAdmin($userId);
			if($isAppOwner || COwnerHelper::isCommunityAdmin() || $model->getActivityOwner($assetId) == $userId){
				$result = true;
			}
		}else{
			if($model->getActivityOwner($assetId) == $userId){
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * THis function check permission to hide stream
	 * @param  [int] $userId  [description]
	 * @param  [int] $assetId [description]
	 * @return [bool]          [description]
	 */
	static public function activitiesStreamHide($userId,$actorId){

		if($userId != $actorId){
			return true;
		}

		return false;
	}

	/**
	 * This function to check permission to add Mood in sthe stream
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamAddMood($userId,$actorId,$obj = NULL){

		if(!$obj->params instanceof JRegistry)
			$obj->params = new JRegistry;

		$mood = $obj->params->get('mood',null);

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);
		$isAllowedApp = in_array($obj->app,$allowapp);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && is_null($mood) && $isAllowedApp){
			return true;
		}

		return false;
	}

	/**
	 * This function to check permission for user to edit mood;
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamEditMood($userId,$actorId,$obj = NULL){

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);
		$isAllowedApp = in_array($obj->app,$allowapp);

		$mood = $obj->params->get('mood',null);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && !is_null($mood) && $isAllowedApp){
			return true;
		}
		return false;
	}

	/**
	 * This function to check permission user to edit post
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamEditPost($userId,$actorId,$obj = NULL){
		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall',
                            'profile.status.share'
    					);

		$isAllowedApp = in_array($obj->app,$allowapp);

		if(( ($actorId == $userId) || COwnerHelper::isCommunityAdmin() || ( $obj->target == $userId && $actorId == $userId )) && $isAllowedApp){
			return true;
		}

		return false;
	}

	/**
	 * This Function to check permission for delete post
	 * @param  [string] $userId  [description]
	 * @param  [string] $actorId [description]
	 * @param  [object] $obj     [description]
	 * @return [boolean]          [description]
	 */
	static public function activitiesStreamDeletetPost($userId,$actorId,$obj = NULL){

		if(COwnerHelper::isCommunityAdmin()){
			return true;
		}

		if(COwnerHelper::isCommunityAdmin() && $obj->app == 'users.featured'){
			return true;
		}

        //admin can delete system generated post
        $appType = isset($obj->app) ? explode('.',$obj->app) : false ;
        if(COwnerHelper::isCommunityAdmin() && $appType && $appType[0] == 'system'){
            return true;
        }

        $allowapp = array(	'profile',
            'groups.wall',
            'events.wall',
            'profile.status.share',
            'videos',
            'photos'
        );

        $isAllowedApp = in_array($obj->app,$allowapp);

        if(( ($actorId == $userId) || COwnerHelper::isCommunityAdmin() || ( $obj->target == $userId )) && $isAllowedApp){
            return true;
        }

        return false;
	}

	static public function activitiesStreamAddLocation($userId,$actorId,$obj = NULL){

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);

		$isAllowedApp = in_array($obj->app,$allowapp);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && empty($obj->location) && $isAllowedApp && CFactory::getConfig()->get('streamlocation',0)){
			return true;
		}

		return false;
	}

	static public function activitiesStreamEditLocation($userId,$actorId,$obj = NULL){

		$allowapp = array(	'profile',
							'groups.wall',
    						'events.wall'
    					);
		$isAllowedApp = in_array($obj->app,$allowapp);

		if(($userId == $actorId ||  COwnerHelper::isCommunityAdmin()) && !empty($obj->location) && $isAllowedApp && CFactory::getConfig()->get('streamlocation',0)){
			return true;
		}

		return false;
	}

	static public function activitiesStreamPermission($userId,$actorId,$obj){

		$permission = new StdClass();

		$permission->editPost		= self::activitiesStreamEditPost($userId,$actorId,$obj);
		$permission->deletePost		= self::activitiesStreamDeletetPost($userId,$actorId,$obj);
		$permission->addLocation	= self::activitiesStreamAddLocation($userId,$actorId,$obj);
		$permission->deleteLocation	= self::activitiesStreamEditLocation($userId,$actorId,$obj);
		$permission->addMood		= self::activitiesStreamAddMood($userId,$actorId,$obj);
		$permission->deleteMood		= self::activitiesStreamEditMood($userId,$actorId,$obj);
		$permission->hideStream		= self::activitiesStreamHide($userId,$actorId);
        $permission->ignoreStream   = self::activitiesStreamIgnore($userId,$actorId,$obj);
		$permission->showButton 	= ($permission->editPost || $permission->deletePost || $permission->addLocation ||
										$permission->deleteLocation || $permission->addMood || $permission->deleteMood || $permission->hideStream );

		return $permission;
	}

    static public function activitiesStreamIgnore($userId, $actorId, $obj = NULL){
        if($obj->target == 0){
            return false;
        }

        return true;
    }

	static public function activitiesLikeAdd($userId, $assetId, $obj=NULL){

		// Guest can never leave a comment
		if( $userId == 0 ){
			return false;
		}

		if(isset($obj->params)) {
			$params = new CParameter($obj->params);
			$likesPermission = $params->get('likesPermission',NULL);

			if( $likesPermission == false  && !is_null($likesPermission))
			{
				return false;
			}
		}

		return true;
	}
}