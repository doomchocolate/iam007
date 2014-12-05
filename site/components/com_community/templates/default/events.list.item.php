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
<li>
	<div class="cIndex-Box clearfix">
		
		<a href="<?php echo $event->getLink();?>" class="cIndex-Avatar cFloat-L">
			<img src="<?php echo $event->getThumbAvatar();?>" alt="<?php echo $this->escape($event->title); ?>" class="cAvatar" />
			<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
				<b class="cStatus-Past"><?php echo JText::_('COM_COMMUNITY_EVENTS_PAST'); ?></b>
			<?php } else if(CEventHelper::isToday($event)) { ?>
				<b class="cStatus-OnGoing"><?php echo JText::_('COM_COMMUNITY_EVENTS_ONGOING'); ?></b>
			<?php } ?>
		</a>


		<div class="cIndex-content">
			<h3 class="cIndex-Name cResetH">
				<a href="<?php echo $event->getLink();?>"><strong><?php echo $this->escape($event->title); ?></strong></a>
			</h3>
			<div class="cIndex-Status">
				<div class="cIndex-Date"><b><?php echo CEventHelper::formatStartDate($event, $config->get('eventdateformat') ); ?></b></div>
				<i class="cIndex-Location"><?php echo $this->escape($event->location);?></i>
				<div class="cIndex-Time"><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_DURATION', CTimeHelper::getFormattedTime($event->startdate, $timeFormat), CTimeHelper::getFormattedTime($event->enddate, $timeFormat)); ?></div>
			</div>
			<div class="cIndex-Actions">
				<div class="action">
					<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_COUNT_MANY_PAST':'COM_COMMUNITY_EVENTS_COUNT_PAST', $event->confirmedcount);?></a>
					<?php } else { ?>
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT':'COM_COMMUNITY_EVENTS_GUEST_COUNT', $event->confirmedcount);?></a>
					<?php } ?>
				</div>
				<?php
				if( $isCommunityAdmin && $showFeatured ) {
					if( !in_array($event->id, $featuredList) )
					{
				?>
				<div class="action">
					<a onclick="joms.featured.add('<?php echo $event->id;?>','events');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?></a>
				</div>
				<?php			
					}
				}
				?>
			</div>
		</div>
	</div>
</li>