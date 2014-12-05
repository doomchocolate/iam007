<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>

<div class="cModule cEvents-PendingInvitations app-box">
	<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_EVENTS_PENDING_INVITATIONS');?></h3>
	
	<div class="app-box-content">
		<ul class="cResetList clrfix">
			<?php
			if( $events )
			{
				
				for( $i = 0; $i < count( $events ); $i++ )
				{
					$event	=&  $events[$i];
			?>
			<li class="jomNameTips" original-title="<?php echo $event->summary ?>">
				<div class="list-right">
				<div class="small"><a href="<?php echo CRoute::_( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ); ?>" class="response"><?php echo JText::_('COM_COMMUNITY_GROUPS_INVITATION_RESPONSE');?></a></div>
				</div>
				<div class="list-left">
				<div class="small"><a href="<?php echo CRoute::_( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ); ?>"><?php echo $this->escape($event->title); ?></a></div>
				<div class="small"><?php echo JText::sprintf((($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT':'COM_COMMUNITY_EVENTS_GUEST_COUNT', $event->confirmedcount);?></div>
				</div>
			</li>
			<?php
				}
			}else{
			?>
			<li class="small"><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_INVITATIONS'); ?></li>
			<?php
			}
			?>
		</ul>
	</div>
	
	<div class="app-box-footer">
		<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=events' ); ?>"><?php echo JText::_('COM_COMMUNITY_FRONTPAGE_VIEW_ALL_EVENTS'); ?></a>
	</div>
</div>