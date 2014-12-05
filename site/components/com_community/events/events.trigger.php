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

class CEventsTrigger
{
	public function onEventCreate( $event )
	{
		$config		= CFactory::getConfig();

		// Send an email notification to the site admin's when there is a new group created
		if( $config->get( 'event_moderation' ) )
		{
			$userModel	= CFactory::getModel( 'User' );
			$my			= CFactory::getUser();
			$admins		= $userModel->getSuperAdmins();

			//Send notification email to administrators
			foreach( $admins as $row )
			{


				if($event->type == CEventHelper::GROUP_TYPE && $event->contentid != 0){
					$event_url = 'index.php?option=com_community&view=events&task=viewevent&groupid='.$event->contentid.'&eventid='.$event->id ;
				} else {
					$event_url = 'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id ;
				}
				$params	= new CParameter( '' );
				$params->set('url' , JURI::root() . 'administrator/index.php?option=com_community&view=events' );
				$params->set('title' , $event->title );
				$params->set('event' , $event->title );
				$params->set('event_url' , JURI::root() . 'administrator/index.php?option=com_community&view=events' );

				CNotificationLibrary::add( 'events_notify_admin' , $my->id , $row->id , JText::sprintf( 'COM_COMMUNITY_EVENT_CREATION_MODERATION_EMAIL_SUBJECT' ) , '' , 'events.notifyadmin' , $params );
			}
		}
	}
}