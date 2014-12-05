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

class COwnerHelper
{
	// Check if the given id is the same and not a guest
	static public function isMine($id1, $id2)
	{
		return ($id1 == $id2) && (($id1 != 0) || ($id2 != 0) );
	}

	static public function isRegisteredUser()
	{
		$my		= JFactory::getUser();
		return (($my->id != 0) && ($my->block !=1));
	}

	/**
	 *  Determines if the currently logged in user is a super administrator
	 **/
	static public function isSuperAdministrator()
	{
		return COwnerHelper::isCommunityAdmin();
	}

	/**
	 * Check if a user can administer the community
	 */
	static public function isCommunityAdmin($userid = null)
	{
        static $resultArr;
        if(isset($resultArr[$userid])){
            return $resultArr[$userid];
        }

		//for Joomla 1.6 afterward checking
		$jUser = JFactory::getUser($userid);
		if(method_exists($jUser,'authorise')){
			if($jUser->authorise('core.login.admin')){
                $resultArr[$userid] = true;
				return true;
			} else {
                $resultArr[$userid] = false;
				return false;
			}
		}

		//for joomla 1.5
		$my	= CFactory::getUser($userid);
		$cacl = CACL::getInstance();
		$usergroup = $cacl->getGroupsByUserId($my->id);
		$admingroups = array (	0 => 'Super Administrator',
								1 => 'Administrator',
								2 => 'Manager',
								3 => 'Super Users'
								);
		return (in_array($usergroup, $admingroups));
		//return ( $my->usertype == 'Super Administrator' || $my->usertype == 'Administrator' || $my->usertype == 'Manager' );
	}

	/**
	 * Sends an email to site administrators
	 *
	 * @param	String	$subject	A string representation of the email subject.
	 * @param	String	$message	A string representation of the email message.
	 **/
	static public function emailCommunityAdmins( $subject , $message )
	{
		$mainframe		= JFactory::getApplication();
		$model			= CFactory::getModel( 'Register' );
		$recipients		= $model->getSuperAdministratorEmail();

		$sitename 		= $mainframe->getCfg( 'sitename' );
		$mailfrom 		= $mainframe->getCfg( 'mailfrom' );
		$fromname 		= $mainframe->getCfg( 'fromname' );
		$subject 		= html_entity_decode( $subject , ENT_QUOTES );

		foreach ( $recipients as $recipient )
		{
			if ($recipient->sendEmail)
			{
				$message	= html_entity_decode( $message , ENT_QUOTES);

				$mail = JFactory::getMailer();
				$mail->sendMail($mailfrom, $fromname, $recipient->email, $subject, $message );
			}
		}

		return true;
	}
}

/**
 * Deprecated since 1.8
 */
function isMine($id1, $id2)
{
	return COwnerHelper::isMine( $id1, $id2 );
}

/**
 * Deprecated since 1.8
 */
function isRegisteredUser()
{
	return COwnerHelper::isRegisteredUser();
}

/**
 * Deprecated since 1.8
 */
function isSuperAdministrator()
{
	return COwnerHelper::isCommunityAdmin();
}

/**
 * Deprecated since 1.8
 */
function isCommunityAdmin($userid = null)
{
	return COwnerHelper::isCommunityAdmin();
}