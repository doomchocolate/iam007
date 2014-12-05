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
require_once( JPATH_ROOT .'/components/com_community/libraries/karma.php' );

class CUserPoints {

	/**
	 * return the path to karma image
	 * @param	user	CUser object
	 */
	static public function getPointsImage( $user ) {
		$CKarma = new CKarma();
		return $CKarma->getKarmaImage($user);
	}


	/**
	 * add points to user based on the action.
	 */
	static public function assignPoint( $action, $userId=null)
	{
		//get the rule points
		//must use the JFactory::getUser to get the aid
		$juser	= JFactory::getUser($userId);

		if( $juser->id != 0 )
		{
			if (!method_exists($juser,'getAuthorisedViewLevels')) {
				$aid    = $juser->aid;
				// if the aid is null, means this is not the current logged-in user.
				// so we need to manually get this aid for this user.
				if(is_null($aid))
				{
					$aid = 0; //defautl to 0
					// Get an ACL object
					$acl 	= JFactory::getACL();
					$grp 	= $acl->getAroGroup($juser->id);
					$group	= 'USERS';

					if($acl->is_group_child_of( $grp->name, $group))
					{
						$aid	= 1;
						// Fudge Authors, Editors, Publishers and Super Administrators into the special access group
						if ($acl->is_group_child_of($grp->name, 'Registered') ||
							$acl->is_group_child_of($grp->name, 'Public Backend'))    {
							$aid	= 2;
						}
					}
				}
			} else {
				//joomla 1.6
				$aid    = $juser->getAuthorisedViewLevels();
			}

			$points	= CUserPoints::_getActionPoint($action, $aid);

			$user	= CFactory::getUser($userId);
			$points	+= $user->getKarmaPoint();
			$user->_points = $points;

			$profile = JTable::getInstance('Profile','CTable');
			$profile->load($user->id);

			$user->_thumb = $profile->thumb;
			$user->_avatar = $profile->avatar;

			$user->save();

			//Event trigger
			$appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();

            $params = array('action'=>$action,'points'=>$points,'userId'=>$user->id);

            $appsLib->triggerEvent('onAfterAssignPoint', $params);
		}
	}


	/**
	 * Private method. DO NOT call this method directly.
	 * Return points for various actions. Return value should be configurable from the backend.
	 */
	static public function _getActionPoint( $action, $aid = 0) {

		include_once(JPATH_ROOT.'/components/com_community/models/userpoints.php');

		$userPoint = '';
		if( class_exists('CFactory') ){
			$userPoint = CFactory::getModel('userpoints');
		} else {
			$userPoint = new CommunityModelUserPoints();
		}

		$point	= 0;
		$upObj	= $userPoint->getPointData( $action );

		if(! empty($upObj))
		{
			$published	= $upObj->published;
			$access		= $upObj->access;

			if (is_array($aid)) {
				//joomla 1.6
				if(in_array($access,$aid)) {
					$point = $upObj->points;
				}
			} else {
				//joomla 1.5 and older
				$userAccess	= (empty($aid)) ? 0 : $aid;
				if($access <= $userAccess) {
					$point = $upObj->points;
				}
			}
			if ($published == '0')
				$point = 0;
		}

		return $point;

	}//end _getActionPoint


}