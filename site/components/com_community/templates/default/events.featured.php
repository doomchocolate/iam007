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

$mainframe	= JFactory::getApplication();
$jinput 	= $mainframe->input;

if ($events && $showFeatured) {

?>
	<!-- Slider Kit compatibility -->
		<!--[if IE 6]><?php CAssets::attach('assets/featuredslider/sliderkit-ie6.css', 'css'); ?><![endif]-->
		<!--[if IE 7]><?php CAssets::attach('assets/featuredslider/sliderkit-ie7.css', 'css'); ?><![endif]-->
		<!--[if IE 8]><?php CAssets::attach('assets/featuredslider/sliderkit-ie8.css', 'css'); ?><![endif]-->

		<!-- Slider Kit scripts -->
		<?php
			CAssets::attach('assets/featuredslider/sliderkit/jquery.sliderkit.1.8.js', 'js');
			CAssets::attach('assets/joms.jomSelect.js', 'js');
		?>

		<!-- Slider Kit launch -->
		<script type="text/javascript">
			joms.jQuery(window).load(function(){

				<?php if($jinput->get('limitstart')!="" || $jinput->get('sort')!="" || $jinput->get('categoryid') != ""){?>
					if(joms.jQuery("#lists").length){
						var target_offset = joms.jQuery("#lists").offset();
						var target_top = target_offset.top;
						joms.jQuery('html, body').animate({scrollTop:target_top}, 200);
					}
				<?php } ?>

				jax.call('community' , 'events,ajaxShowEventFeatured' , <?php echo $events[0]->id; ?>, '<?php echo $allday; ?>' );

				joms.jQuery(".featured-event").sliderkit({
					shownavitems:3,
					scroll:<?php echo $config->get('featuredeventscroll'); ?>,
					// set auto to true to autoscroll
					auto:false,
					mousewheel:true,
					circular:true,
					scrollspeed:500,
					autospeed:10000,
					start:0
				});
				joms.jQuery('.cBoxPad').click(function(){
					var event_id = joms.jQuery(this).parent().attr('id');
					event_id = event_id.split("cPhoto");
					event_id = event_id[1];
					jax.call('community' , 'events,ajaxShowEventFeatured' , event_id, '<?php echo $allday; ?>' );
				});

			});

			function updateEvent(eventId, title, categoryName, likes, avatar, eventDate, location, summary, eventLink,rsvp, eventUnfeature){
			joms.jQuery('#like-container').html(likes);
			joms.jQuery('#event-title').html(title);
			joms.jQuery('#event-date').html(eventDate);
			joms.jQuery('#event-data-location').html(location);
			joms.jQuery('#event-summary').html(summary);
			joms.jQuery('.cSlider-selected').removeClass('cSlider-selected');
			joms.jQuery('#cPhoto'+eventId).addClass('cSlider-selected');
			if(rsvp !=""){
				joms.jQuery('#rsvp-wrapper').html(rsvp);
			} else {
			   joms.jQuery('#rsvp').html(rsvp);
			}
			joms.jQuery('.album-actions').html(eventUnfeature);
			joms.jQuery('#community-event-data-category').html(categoryName);
			joms.jQuery('#event-avatar').attr('src',avatar);
			eventLink = eventLink.replace(/\&amp;/g,'&');
			joms.jQuery('.event-link').attr('href',eventLink);
			}

		</script>






<div id="cFeatured" class="cFeatured-Events">

	<div class="cFeaturedTop">

		<div class="clearfull">
			<div id="rsvp-container" class="cFeatured-Rsvp">
				<div id="community-event-rsvp" class="cEvent-Rsvp">
					<p><?php echo JText::_('COM_COMMUNITY_EVENTS_ATTENDING_QUESTION'); ?>&nbsp;&nbsp;</p>
					<div class="btn pull-right">
                                            <div id="rsvp-wrapper">
						<select onchange="joms.events.submitRSVP(<?php echo $events[0]->id;?>,this)">
							<?php if($events[0]->getMemberStatus($my->id)==0) { ?><option class="noResponse" selected="selected"><?php echo JText::_('COM_COMMUNITY_GROUPS_INVITATION_RESPONSE')?></option> <?php }?>
							<option class="attend" <?php if($events[0]->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_ATTEND){echo "selected='selected'"; }?> value="<?php echo COMMUNITY_EVENT_STATUS_ATTEND; ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_ATTEND')?></option>
							<option class="notAttend" <?php if($events[0]->getMemberStatus($my->id) >= COMMUNITY_EVENT_STATUS_WONTATTEND ){echo "selected='selected'"; }?> value="<?php echo COMMUNITY_EVENT_STATUS_WONTATTEND; ?>"><?php echo JText::_('COM_COMMUNITY_EVENTS_RSVP_NOT_ATTEND')?></option>
						</select>
                                            </div>
					</div>
				</div>
			</div><!--.rvsp-->

			<div id="community-event-avatar" class="cFeatured-PageCover cFeaturedThumb cFloat-L">
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $events[0]->id );?>" class="event-link">
					<img id="event-avatar" src="<?php echo $events[0]->getAvatar( 'avatar' ); ?>" alt="<?php echo $this->escape($events[0]->title);?>" />
				</a>
				<?php if( $isCommunityAdmin ){?>
				<b>
					<a class="album-action remove-featured" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $events[0]->id;?>','events');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a>
				</b>
				<?php } ?>

				<div id="like-container" class="cFeaturedLike"></div>
			</div><!--.event-vatar -->

			<!-- Event Information -->
			<div class="cFeaturedInfo Page">
				<!-- Title -->
				<div class="cFeaturedTitle">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $events[0]->id );?>" class="event-link"><span id="event-title"><?php echo $events[0]->title; ?></span></a>
				</div>

				<ul class="cFeaturedMeta cFloatedList cResetList clearfull">
					<!-- Event Time -->
					<li class="event-created">
						<span><?php echo JText::_('COM_COMMUNITY_EVENTS_TIME')?>:</span>
						<b id="event-date"></b>
					</li>
				</ul>

				<!--Event Summary-->
				<div class="event-summary">
					<!-- <span><?php echo JText::_('COM_COMMUNITY_EVENTS_VIEW_SUMMARY');?></span> -->
					<div id="event-summary"></div>
				</div>

				<div class="cFeaturedExtra">
					<ul class="cFeaturedMeta cFloatedList cResetList clearfull">
						<li class="event-category">
							<span><?php echo JText::_('COM_COMMUNITY_EVENTS_CATEGORY'); ?>:</span>
							<b id="community-event-data-category">
								<?php echo JText::_( $events[0]->getCategoryName() ); ?>
							</b>
						</li><!--.event-category-->

						<!-- Location info -->
						<li class="event-location">
							<span><?php echo JText::_('COM_COMMUNITY_EVENTS_LOCATION');?>:</span>
							<b id="event-data-location">
								<a href="http://maps.google.com/?q=<?php echo urlencode($events[0]->location); ?>" target="_blank">
									<?php echo $events[0]->location; ?>
								</a>
							</b>
						</li>
					</ul>
				</div>
			</div><!--.event-info -->
		</div>
	</div><!--.event-main-->

	<!-- navigation container -->
	<div class="cFeaturedBottom">
		<!--#####SLIDER#####-->
		<div class="cSlider featured-event">
			<div class="cSlider-Wrap cSlider-nav">
				<div class="cSlider-Clip cSlider-nav-clip">
					<ul class="cSlider-List Events cFloatedList cResetList clearfix">

						<?php foreach($events as $event) { ?>
						<li id="cPhoto<?php echo $event->id; ?>">
							 <div id="<?php echo $event->id; ?>" class="cBoxPad">
								<a href="javascript:void(0);">
								<b class="cThumb-Calendar">
									<b><?php echo CEventHelper::formatStartDate($event, JText::_('M') ); ?></b>
									<b><?php echo CEventHelper::formatStartDate($event, JText::_('d') ); ?></b>
								</b>
								<div class="cFeaturedTitle"><b><?php echo $event->title;?></b></div>
								<div class="cFeaturedMeta"><?php echo $event->location; ?></div>
								</a>
							</div>
						</li>
						<?php
							} // end foreach
						?>
					</ul>
				</div>
				<div class="cSlider-btn cSlider-nav-btn cSlider-nav-prev"><a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_PREVIOUS_BUTTON');?>"><span>Previous</span></a></div>
				<div class="cSlider-btn cSlider-nav-btn cSlider-nav-next"><a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_NEXT_BUTTON');?>"><span>Next</span></a></div>
			</div>
		</div><!--.cSlider-->
	</div>

	<script type="text/javascript">
	  joms.jQuery(function(){
		joms.jQuery("select").jomSelect();
	  });
	</script>

</div><!--#cFeatured-->
<?php } ?>
