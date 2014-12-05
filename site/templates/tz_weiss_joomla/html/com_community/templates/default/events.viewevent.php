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
CAssets::attach('assets/easytabs/jquery.easytabs.min.js', 'js');
CAssets::attach('assets/ajaxfileupload.pack.js', 'js');
CAssets::attach('assets/joms.jomSelect.js', 'js');
CAssets::attach('assets/imgareaselect/scripts/jquery.imgareaselect.min.js', 'js');
CAssets::attach('assets/imgareaselect/css/imgareaselect-avatar.css', 'css');
CAssets::attach('assets/jqueryui/drag/jquery-ui-drag.js', 'js');
$showStream = ($isEventGuest || $isMine || $isAdmin || $isCommunityAdmin || $handler->manageable());

?>


<div class="js-focus">
	<div class="js-focus-cover">
		<img id='<?php echo $event->id; ?>' data-cover-context="event" class="focusbox-image cover-image" src="<?php echo $event->getCover(); ?>" alt="cover photo" style="top:<?php echo $event->coverPostion; ?>">
		<div class="js-focus-gradient" data-cover-context="event" data-cover-type="cover"></div>

		<?php if( $isMine || COwnerHelper::isCommunityAdmin() || $isAdmin ){?>
			<!-- Change cover button -->
			<div class="js-focus-change-cover" data-cover-type="cover">
				 	<a href="javascript:void(0)" class="btn <?php echo ($event->defaultCover) ? 'hidden' : '' ?>" data-cover-context="event" onclick="joms.cover.reposition(this, <?php echo $event->id; ?>,'<?php echo JText::_("COM_COMMUNITY_CANCEL_BUTTON")?>','<?php echo JText::_("COM_COMMUNITY_SAVE_BUTTON")?>');"><i class="joms-icon-cog"></i><?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER')?></a>
				 	<a href="javascript:void(0)" class="btn ml-6" data-cover-context="event" onclick="joms.cover.select(this, <?php echo $event->id; ?>);" ><i class="joms-icon-picture"></i><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a>
				</div>
		<?php }?>

			<!-- Focus Title , Add as friend button -->
			<div class="js-focus-header">
				<div class="row-fluid">
					<div class="span5 offset3">
						<h3><?php echo $event->title; ?></h3>
					</div>
					<div class="span4 text-right">
						<!-- invite friend button -->
						<?php if( $handler->isAllowed() && !$isPastEvent ) { ?>
								<div class="cEvent-Rsvp btn">
									<select onchange="joms.events.submitRSVP(<?php echo $event->id;?>,this)">
										<?php if($event->getMemberStatus($my->id)==0) { ?><option class="noResponse" selected="selected"><?php echo JText::_('COM_COMMUNITY_GROUPS_INVITATION_RESPONSE')?></option> <?php }?>
										<option class="attend" <?php if($event->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_ATTEND){echo "selected='selected'"; }?> value="<?php echo COMMUNITY_EVENT_STATUS_ATTEND; ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_ATTEND')?></option>
										<option class="notAttend" <?php if($event->getMemberStatus($my->id) >= COMMUNITY_EVENT_STATUS_WONTATTEND ){echo "selected='selected'"; }?> value="<?php echo COMMUNITY_EVENT_STATUS_WONTATTEND; ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_NOT_ATTEND')?></option>
									</select>
								</div>
						<?php }?>
					</div>
				</div>
			</div>

	</div>
	<div class="js-focus-content">
		<div class="row-fluid">
			<div class="span3">
				<div class="thumbnail js-focus-avatar">
						<img src="<?php echo $event->getAvatar( 'avatar' ) . '?_=' . time(); ?>" border="0" alt="<?php echo $this->escape($event->title);?>" />
						<?php if ($isAdmin || $isCommunityAdmin || $isMine) { ?>
						<b class="js-focus-avatar-option">
							<a href="javascript:void(0)" onclick="joms.events.uploadAvatar('event','<?php echo $event->id?>', '<?php echo $event->isRecurring();?>')"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR')?></a>
						</b>
					<?php } ?>
				</div>
			</div>
			<div class="span9">
				<!-- Focus Menu -->
				<div class="js-focus-menu">
					<div class="row-fluid">
						<div class="span12">
							<ul class="inline unstyled">
								<li><?php echo ($event->ticket) ? JText::sprintf('COM_COMMUNITY_EVENTS_TICKET_STATS', $event->ticket, $eventMembersCount, ($event->ticket - $eventMembersCount)) : JText::sprintf('COM_COMMUNITY_EVENTS_UNLIMITED_SEAT'); ?></li>
								<li><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND )?>"><?php echo $eventMembersCount >1 ? JText::sprintf('COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY',$eventMembersCount) : JText::sprintf('COM_COMMUNITY_EVENTS_ATTANDEE_COUNT',$eventMembersCount) ?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- end js-focus-content -->
	<div class="js-focus-actions">

		<div class="navbar">
		  <div class="navbar-inner">
		      <a class="btn btn-navbar js-collapse-btn">
						<span class="caret"></span>
		      </a>
		      <div class="nav-collapse collapse js-collapse">
		        <ul class="nav">
					<?php if ($config->get('enablesharethis')  == 1 ) { ?>
						<li><a href="javascript:void(0);" onclick="joms.bookmarks.show('<?php echo CRoute::getExternalURL( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ); ?>')" ><i class="joms-icon-share"></i><?php echo JText::_('COM_COMMUNITY_SHARE')?></a></li>
					<?php } ?>
					<?php if($isLikeEnabled){?>
						<li id='like-events-<?php echo $event->id; ?>'><a href="javascript:void(0);" onclick="<?php echo ($isUserLiked) > 0 ? 'joms.like.newDislike(this)' : 'joms.like.newLike(this)' ?>;" class="<?php if($isUserLiked > 0){ ?> js-focus-like <?php }?>"><i class="joms-icon-thumbs-up"></i><span><?php echo $totalLikes ?></span> <?php echo JText::_('COM_COMMUNITY_LIKE')?></a></li>
					<?php }?>
					<li><span><i class="joms-icon-eye"></i><?php echo ($event->hits > 1) ? JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY',$event->hits) : JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT',$event->hits); ?></span></li>
					<?php if($config->get('enablereporting') == 1 && ( $config->get('enableguestreporting') !=1 && $my->id !=0)){?>
						<li><a href="javascript:void(0)" onclick="joms.report.emptyMessage = '<?php echo JText::_('COM_COMMUNITY_REPORT_MESSAGE_CANNOT_BE_EMPTY'); ?>';joms.report.showWindow('events,reportEvent','[<?php echo  $event->id  ;?>]');" ><i class="js-icon-warning-sign"></i><?php echo JText::_('COM_COMMUNITY_EVENTS_REPORT'); ?></a></li>
			      	<?php }?>
			      </ul>
		        <ul class="nav pull-right">
		          <li class="dropup">
		            <a href="#" class="js-navbar-options"><?php echo JText::_('COM_COMMUNITY_VIDEOS_OPTIONS')?></a>
		            <ul class="dropdown-menu pull-right">

									<?php if($memberStatus != COMMUNITY_EVENT_STATUS_BLOCKED) { ?>
										<!-- Event Menu List -->
										<?php if( $handler->showPrint() ) { ?>
										<!-- Print Event -->
										<li><a tabindex="-1" href="javascript:void(0)" onclick="window.open('<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=printpopup&eventid='.$event->id); ?>','', 'menubar=no,width=600,height=700,toolbar=no');"><?php echo JText::_('COM_COMMUNITY_EVENTS_PRINT');?></a>
										</li>
										<?php } ?>

										<?php if( $handler->showExport() && $config->get('eventexportical') ) { ?>
										<!-- Export Event -->
										<li><a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=export&format=raw&eventid=' . $event->id); ?>" ><?php echo JText::_('COM_COMMUNITY_EVENTS_EXPORT_ICAL');?></a>
										</li>
										<?php } ?>
										<?php if( (!$isEventGuest) && ($event->permission == COMMUNITY_PRIVATE_EVENT) && (!$waitingApproval)) { ?>
										<!-- Join Event -->
										<li><a tabindex="-1" href="javascript:void(0);" onclick="javascript:joms.events.join('<?php echo $event->id;?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_INVITE_REQUEST'); ?></a>
										</li>
										<?php } ?>
										<?php if( (!$isMine) && !($waitingRespond) && (COwnerHelper::isRegisteredUser()) ) { ?>
										<!-- Leave Event -->
										<li class="important"><a tabindex="-1" href="javascript:void(0);" onclick="joms.events.leave('<?php echo $event->id;?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_IGNORE');?></a>
										</li>
										<?php } ?>
										<li class="divider"></li>
										<!-- Event Menu List -->
									<?php } ?>

									<?php if( $isMine || COwnerHelper::isCommunityAdmin() || $isAdmin ){?>
											<?php if(!$event->defaultCover) {?>
											<li class="hidden-desktop">
												<a href="javascript:void(0)" onclick="joms.events.uploadAvatar('event','<?php echo $event->id?>', '<?php echo $event->isRecurring();?>')"><?php echo JText::_('COM_COMMUNITY_CHANGE_AVATAR')?></a>
											</li>
											<li class="hidden-desktop">
				 								<a href="javascript:void(0)"  data-cover-context="event" onclick="joms.cover.reposition(this, <?php echo $event->id; ?>,'<?php echo JText::_("COM_COMMUNITY_CANCEL_BUTTON")?>','<?php echo JText::_("COM_COMMUNITY_SAVE_BUTTON")?>');"> <?php echo JText::_('COM_COMMUNITY_REPOSITION_COVER')?></a>
											</li>
											<?php }?>
											<li class="hidden-desktop">
				 								<a href="javascript:void(0)" data-cover-context="event" onclick="joms.cover.select(this, <?php echo $event->id; ?>);" ><?php echo JText::_('COM_COMMUNITY_CHANGE_COVER'); ?></a>
											</li>
									<?php } ?>

									<!-- event administration -->
									<?php if($isMine || $isCommunityAdmin || $isAdmin || $handler->manageable()) { ?>
										<?php if( $isMine || $isCommunityAdmin || $isAdmin) {?>
											<!-- Send email to participants -->
											<li>
												<a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=sendmail&eventid=' . $event->id );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_EMAIL_SEND');?></a>
											</li>
											<!-- Edit Event -->
											<li>
												<a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=edit&eventid=' . $event->id );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_EDIT');?></a>
											</li>
										<?php } ?>

										<?php if( ($event->permission != COMMUNITY_PRIVATE_EVENT) && ($isMine || $isCommunityAdmin || $isAdmin) ){ ?>
											<!-- Copy Event -->
											<li>
												<a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=create&eventid=' . $event->id );?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_DUPLICATE');?></a>
											</li>
										<?php } ?>
										<!-- Delete Event -->
										<li class="divider"></li>
										<?php if( $handler->isAdmin() ) { ?>
											<li><a tabindex="-1" class="event-delete" href="javascript:void(0);" onclick="javascript:joms.events.deleteEvent('<?php echo $event->id;?>');"><?php echo JText::_('COM_COMMUNITY_EVENTS_DELETE'); ?></a></li>
										<?php } ?>
									<?php } ?>
									<!-- event administration end -->

									<?php if( ( $isMine || $isAdmin || $isCommunityAdmin) && ( $unapproved > 0 ) ) { ?>
										<li class="divider"></li>
										<li>
											<a tabindex="-1" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&type='.COMMUNITY_EVENT_STATUS_REQUESTINVITE.'&eventid=' . $event->id);?>">
												<?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'COM_COMMUNITY_EVENTS_PENDING_INVITE_MANY'	 :'COM_COMMUNITY_EVENTS_PENDING_INVITE' , $unapproved ); ?>
											</a>
										</li>
									<?php } ?>

			       		</ul>
		          </li>
		        </ul>
		      </div><!-- /.nav-collapse -->
		  </div><!-- /navbar-inner -->
		</div>

	</div>
</div>

	<!-- Event Approval -->
	<?php if( $waitingApproval ) { ?>
	<div class="cAlert alert-info">
		<i class="com-icon-waiting"></i>
		<span><?php echo JText::_('COM_COMMUNITY_EVENTS_APPROVEL_WAITING'); ?></span>
	</div>
	<?php }?>

	<?php if( $isInvited ){ ?>
	<div id="events-invite-<?php echo $event->id; ?>" class="cInvite cAlert alert-info clearfix">
		<div class="cInvite-Content">
			<div class="cInvite-Message">
				<?php echo JText::sprintf( 'COM_COMMUNITY_EVENTS_YOUR_INVITED', $join ); $test = 1; ?>
			</div>
			<?php if ($friendsCount) { ?>
			<div class="cInvite-Relations">
				<?php echo JText::sprintf( (CStringHelper::isPlural($friendsCount)) ? 'COM_COMMUNITY_EVENTS_FRIEND' : 'COM_COMMUNITY_EVENTS_FRIEND_MANY', $friendsCount ); ?>
			</div>
			<?php } ?>
			<div class="cInvite-Actions">
				<?php echo JText::_( 'COM_COMMUNITY_EVENTS_RSVP_NOTIFICATION' ) . JText::_('COM_COMMUNITY_OR'); ?>
				<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxRejectInvitation','<?php echo $event->id; ?>');">
					<?php echo JText::_('COM_COMMUNITY_EVENTS_REJECT'); ?>
				</a>
			</div>
		</div>
	</div>
	<?php } ?>

<div class="row-fluid">

	<div class="span8">

		<!-- begin: .cMain -->
		<div class="cMain">

		<!-- Global Application Tab bar framework -->
		<div class="cTabsBar clearfull">
			<ul class="cPageTabs cResetList cFloatedList clearfix">
				<li <?php if( $showStream ) {echo 'class="cTabCurrent"';} else {echo 'class="cTabDisabled"';} ?>><a href="javascript:void(0)"><?php echo JText::_('COM_COMMUNITY_FRONTPAGE_RECENT_ACTIVITIES');?></a></li>
				<li <?php if(!$isEventGuest && !$showStream) {echo 'class="cTabCurrent"';} ?>><a href="javascript:void(0)"><?php echo JText::_('COM_COMMUNITY_EVENTS_DETAIL');?></a></li>
				<!--li <?php if(!$isEventGuest) {echo 'class="cTabDisabled"';} ?>><a href="javascript:void(0)">Event Program</a></li-->
			</ul>
		</div>
		<!-- END: Global Application Tab bar framework -->

		<!-- START: Global Application Tab bar contents -->
		<div class="cTabsContentWrap">
			<!-- Tab 1: Activity Stream Container -->
			<?php if( $showStream ) { ?>
			<div class="cTabsContent  <?php if($showStream) {echo 'cTabsContentCurrent';} ?>">
				<!-- Stream -->
				 <?php if( $showStream ) { $status->render(); } ?>
				<div class="cActivity cEvent-Activity" id="activity-stream-container">
					<div class="cActivity-LoadLatest joms-latest-activities-container">
						<a id="activity-update-click" class="btn btn-block" href="javascript:void(0);"></a>
					</div>
					<?php echo $streamHTML; ?>
				</div>
				<!-- end: stream -->
			</div>
			<?php } ?>
			<!-- Tab 1: END -->

			<!-- Tab 2: Event Details -->
			<div class="cTabsContent <?php if(!$isEventGuest && !$showStream ) {echo 'cTabsContentCurrent';} ?>">
				<div class="cEvent-Description">
					<?php
					if( !CStringHelper::isHTML($event->description) ) {
						echo CStringHelper::nl2br($event->description);
					}
					else {
						echo $event->description;
					}
					?>
					<!-- Focus Details expand/collapse -->
				<div class="js-focus-details">
					<div class="row-fluid">
						<div class="span12">
							<dl>
								<!-- Event Category -->
								<dt><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?></dt>
								<dd><a href="<?php echo CRoute::_('index.php?option=com_community&view=events&categoryid=' . $event->catid);?>"><?php echo JText::_( $event->getCategoryName() ); ?></a></dd>
								<!-- Event Date & Time -->
								<dt><?php echo JText::_('COM_COMMUNITY_EVENTS_TIME')?></dt>
								<dd>
								<?php echo ($allday) ? JText::sprintf('COM_COMMUNITY_EVENTS_ALLDAY_DATE',$event->startdateHTML) : JText::sprintf('COM_COMMUNITY_EVENTS_DURATION',$event->startdateHTML,$event->enddateHTML); ?>
									<?php if( $config->get('eventshowtimezone') ) { ?>
										<span class="small"><?php echo $timezone; ?></span>
									<?php } ?>
								</dd>
								<!-- Event Location -->
								<dt><?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION');?></dt>
								<dd id="community-event-data-location">
									<a href="http://maps.google.com/?q=<?php echo urlencode($event->location); ?>" target="_blank"><?php echo $event->location; ?></a>
								</dd>
								<!--Event Occurence -->
								<?php if ($event->isRecurring()) { ?>
								<dt><?php echo JText::_('COM_COMMUNITY_EVENTS_OCCURENCE');?></dt>
								<dd><?php echo JText::_('COM_COMMUNITY_EVENTS_REPEAT_' . strtoupper($event->repeat)); ?></dd>
								<?php }?>
								<!--Event Admins-->
								<dt><?php echo JText::_('COM_COMMUNITY_EVENTS_ADMINS')?></dt>
								<dd><?php echo $adminsList;?></dd>
							</dl>


						</div>
					</div>
				</div>
				</div>
			</div>
			<!-- Tab 2: END -->
		</div>
		<!-- END: Global Application Tab bar contents -->
		</div>

	</div>

	<div class="span4">
		<!-- begin: .cSidebar -->
		<div class="cSidebar">

			<?php $this->renderModules( 'js_side_top' ); ?>
			<?php $this->renderModules( 'js_events_side_top' ); ?>

			<?php if( ( $isMine || $isAdmin || $isCommunityAdmin) && ( $unapproved > 0 ) ) { ?>
			<div class="cModule cPage-Approval app-box control-approval">
				<ul class="app-box-list for-menu cResetList">
					<li>
						<i class="com-icon-user-plus"></i>
						<a href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&type='.COMMUNITY_EVENT_STATUS_REQUESTINVITE.'&eventid=' . $event->id);?>">
							<?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'COM_COMMUNITY_EVENTS_PENDING_INVITE_MANY'	 :'COM_COMMUNITY_EVENTS_PENDING_INVITE' , $unapproved ); ?>
						</a>
					</li>
				</ul>
			</div>
			<?php } ?>

			<div id="community-event-members" class="cModule cEvent-Member app-box">

				<h3 class="app-box-header"><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_CONFIRMED_GUESTS'); ?></h3>
				<?php if($eventMembersCount>0){ ?>
					<div class="app-box-content">
						<ul class="cThumbsList cResetList clearfix">
							<?php
							if($eventMembers) {
								foreach($eventMembers as $member) {
							?>
								<li>
									<a href="<?php echo CUrlHelper::userLink($member->id); ?>">
										<img class="cAvatar jomNameTips" src="<?php echo $member->getThumbAvatar(); ?>" title="<?php echo CTooltip::cAvatarTooltip($member);?>" alt="" />
									</a>
								</li>
							<?php
								}
							}
							?>
						</ul>
					</div>
					<div class="app-box-footer">
						<?php if( ( ($isEventGuest && ($event->allowinvite)) || $isMine || $isCommunityAdmin || $isAdmin ) && $handler->hasInvitation() && $handler->isExpired()) { ?>
							<span><?php echo $inviteHTML; ?></span>&nbsp;
						<?php } ?>
						<a href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND );?>">
							<?php echo JText::_('COM_COMMUNITY_VIEW_ALL');?> (<?php echo $eventMembersCount; ?>)
						</a>
					</div>
				<?php }
				else
				echo JText::_('COM_COMMUNITY_EVENTS_NO_USER_ATTENDING_MESSAGE')
				?>
			</div>


			<!-- begin: map -->
			<?php if( $config->get('eventshowmap') && ( $handler->isAllowed() || $event->permission != COMMUNITY_PRIVATE_EVENT ) ) {	?>
				<?php

				if(CMapping::validateAddress($event->location)){
					?>
					<div id="community-event-map" class="cModule cEvent-Map app-box">
						<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_MAP_LOCATION');?></h3>
						<div class="app-box-content event-description">
							<!-- begin: dynamic map -->
							<?php echo CMapping::drawMap('event-map', $event->location); ?>
							<div id="event-map" style="height:210px;width:100%;margin:5px 0;">
								<?php echo JText::_('COM_COMMUNITY_MAPS_LOADING'); ?>
							</div>
							<!-- end: dynamic map -->
							<div class="event-address small"><?php echo CMapping::getFormatedAdd($event->location); ?></div>
						</div>
						<div class="app-box-footer">
							<a href="http://maps.google.com/?q=<?php echo urlencode($event->location); ?>" target="_blank"><?php echo JText::_('COM_COMMUNITY_EVENTS_FULL_MAP'); ?></a>
						</div>
					</div>
				<?php } ?>
			<?php } ?>
			<!-- end: map -->

			<!-- Event in the series -->
			<?php if ($eventSeries && $seriesCount > 1) { ?>
			<div class="cGroup-Events cModule app-box">
				<h3><?php echo JText::_('COM_COMMUNITY_EVENTS_SERIES');?></h3>
				<div class="app-box-content">
					<div id="community-group-container">
						<ul class="unstyled">
						<?php
						$grouplink = '';
						if ($event->contentid > 0) {
							$grouplink = '&groupid=' . $event->contentid;
						}

						foreach( $eventSeries as $series ) {
						?>
							<li class="clearfix">
									<img class="joms-stream-avatar pull-left" src="<?php echo $series->getThumbAvatar();?>" alt="<?php echo $this->escape( $series->title );?>" />
									<div class="event-detail jsDetail">
										<p class="reset-gap"><i class="joms-icon-calendar"></i>	<?php echo CEventHelper::formatStartDate($series, $config->get('eventdateformat') ); ?></p>
										<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&eventid=' . $series->id . $grouplink);?>"><i class="joms-icon-users"></i> <?php echo JText::sprintf((CStringHelper::isPlural($series->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT_MANY':'COM_COMMUNITY_EVENTS_ATTANDEE_COUNT', $series->confirmedcount);?></a>
									</div>
								<div class="clr"></div>
							</li>
						<?php } ?>
						</ul>
					</div>
				</div>

				<div class="app-box-footer">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=events' . $grouplink . '&parent=' . $event->parent);?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_VIEW_SERIES'). '(' . $seriesCount . ')';?></a>
				</div>
			</div>
			<?php } ?>
			<!-- Event in the series -->

		<?php $this->renderModules( 'js_events_side_bottom' ); ?>
		<?php $this->renderModules( 'js_side_bottom' ); ?>
		</div>
		<!-- end: .cSidebar -->
	</div>
</div>

<script type="text/javascript">
      joms.jQuery(function(){
        joms.jQuery("select").jomSelect();
      });
			jomsQuery(".btn-about").click(function () {
				jomsQuery(".cFocus-definition").slideToggle();
			});
</script>

<?php if($editEvent) {?>
<script type="text/javascript">
	joms.events.edit();
</script>
<?php } ?>

<script>

  // override config setting
  joms || (joms = {});
  joms.constants || (joms.constants = {});
  joms.constants.eventid = <?php echo $event->id; ?>;

</script>
