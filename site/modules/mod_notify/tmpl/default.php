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

if( $my->isOnline() && $my->id != 0 )
{
	$inboxModel			= CFactory::getModel('inbox');
	$notifModel = CFactory::getModel('notification');
	$filter				= array();
	$filter['user_id']	= $my->id;
	$friendModel		= CFactory::getModel ( 'friends' );
	$profileid 			= JRequest::getVar('userid' , 0 , 'GET');
	
	//CFactory::load( 'libraries' , 'toolbar' );
	$toolbar = CToolbarLibrary::getInstance();
	$newMessageCount		= $toolbar->getTotalNotifications( 'inbox' );
	$newEventInviteCount	= $toolbar->getTotalNotifications( 'events' );
	$newFriendInviteCount	= $toolbar->getTotalNotifications( 'friends' );
	$newGroupInviteCount	= $toolbar->getTotalNotifications( 'groups' );
	
	$myParams			=	$my->getParams();
	$newNotificationCount	= $notifModel->getNotificationCount($my->id,'0',$myParams->get('lastnotificationlist',''));
	$newEventInviteCount	= $newEventInviteCount + $newNotificationCount;
	
	//CFactory::load( 'helpers' , 'string');
	
	$config	= CFactory::getConfig();
	$uri	= CRoute::_('index.php?option=com_community' , false );
	$uri	= base64_encode($uri);

	//CFactory::load('helpers' , 'string' );
	
	$show_global_notification 	= $params->get('show_global_notification', 1);
	$show_friend_request 	= $params->get('show_friend_request', 1);
	$enable_private_message 	= $params->get('enable_private_message', 1);

?>

<div id="cModule-Notify" class="cMods cMods-Notify<?php echo $params->get('moduleclass_sfx'); ?>">
	<div class="cMod-Notify">
		<?php if($show_global_notification ) : ?>
		<a href="javascript:joms.notifications.showWindow();" class="" title="<?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS_GLOBAL');?>">
			<i class="tool-icon-notification"></i>
			<span class="notifcount">
			<?php 
			if ($newEventInviteCount) { 
				echo $newEventInviteCount;
			} else { 
				echo "0"; 
			} 
			?>
			</span class="notifcount">
		</a>
		<?php endif; ?>

		<?php if($show_friend_request ) : ?>
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&task=pending');?>" onclick="joms.notifications.showRequest();return false;" class="" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_INVITE_FRIENDS' );?>">
			<i class="tool-icon-friend"></i>
			<span class="notifcount">
			<?php 
				if ($newFriendInviteCount) 
				{ 
					echo $newFriendInviteCount;
				} else { 
					echo "0"; 
				} 
			?>
			</span class="notifcount">
		</a>
		<?php endif; ?>
		<?php if($enable_private_message ) : ?>
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=inbox');?>" class=""  onclick="joms.notifications.showInbox();return false;" title="<?php echo JText::_( 'COM_COMMUNITY_NOTIFICATIONS_INBOX' );?>">
			<i class="tool-icon-inbox"></i>
			<span class="notifcount">
			<?php 
				if ($newMessageCount) 
				{ 
					echo $newMessageCount;
				} else { 
					echo "0"; 
				} 
			?>
			</span class="notifcount">
		</a>
		<?php endif; ?>
	</div>
</div>
<?php
	}
?>