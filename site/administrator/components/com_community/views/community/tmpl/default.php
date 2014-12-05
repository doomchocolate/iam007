<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<style>
.demo-container, .demo-container-2 {
box-sizing: border-box;
width: 100%;
height: 150px;
}
.demo-container {margin-bottom:20px;}
.demo-placeholder, .demo-placeholder-2 {
width: 80%;
height: 150px;
float:left;
}

#choices, #choices-2 {
float:left;
width:20%;
}

#js-cpanel #choices label, #js-cpanel #choices-2 label {
font-size:11px !important;
}

#pie-placeholder {
width:100%;
height:280px;
}

.avg-avatar {
	width:56px;
	margin-right:6px;
}

/* User engagement */
.eng-loading {
	margin-left: 10px;
	margin-top: 10px;
}

</style>

<!-- ijoomla logo -->
<a id="ijoomla-logo" href="http://www.ijoomla.com" target="_blank" class="pull-right" style="display:none;">
	<img src="<?php echo COMMUNITY_ASSETS_URL; ?>/images/ijoomla-logo.png" alt="ijoomla" style="margin-bottom:10px;">
</a>

<!-- Infobox -->
<div class="row-fluid">
  <div class="span3">

	<div class="infobox infobox-blue infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-group"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->userCount; ?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_USERS'); ?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-blue infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-envelope"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->messageCount; ?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_MESSAGES')?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-blue infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-comment"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->postCount?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_POSTS')?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-green infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-camera"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->photoCount; ?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_PHOTOS')?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-green infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-film"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->videosCount?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo Jtext::_('COM_COMMUNITY_VIDEOS');?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-orange infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-group"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->groupsCount; ?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_GROUPS')?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-orange infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-calendar"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->eventsCount?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_EVENTS')?></div>
	  </div>
	</div>
  </div>

  <div class="span3">
	<div class="infobox infobox-red infobox-dark">
	  <div class="infobox-icon">
		<i class="js-icon-comments"></i>
	  </div>
	  <div class="infobox-data">
		<div class="infobox-content count"><?php echo $this->discussionCount; ?></div>
	  </div>
	  <div class="infobox-footer">
		<div class="infobox-content"><?php echo JText::_('COM_COMMUNITY_DISCUSSIONS')?></div>
	  </div>
	</div>
  </div>

</div><!--/.row-fluid-->

<!-- Engagement & statistic , most recent stream -->
<div class="row-fluid">
<div class="span14">

<div class="widget-box" id="statistic-box">
	<div class="widget-header widget-header-flat">
	  <h5>
		<i class="js-icon-signal"></i>
		<?php echo JText::_('COM_COMMUNITY_USER_ENGAGEMENT_TITLE')?>
	  </h5>

	  <div class="widget-toolbar no-border">
		<button class="btn btn-minier btn-primary" id="dropdown-time" data-time="week">
		  <span><?php echo JText::_('COM_COMMUNITY_THIS_WEEK')?></span>
		  <i class="js-icon-angle-down js-icon-on-right"></i>
		</button>
		  <ul class="dropdown-menu dropdown-info pull-right dropdown-caret" id="dropdown-time-menu">
			<li class="active">
			  <a href="#time-week"><?php echo JText::_('COM_COMMUNITY_THIS_WEEK')?></a>
			</li>
			<li>
			  <a href="#time-lastweek"><?php echo JText::_('COM_COMMUNITY_LAST_WEEK')?></a>
			</li>
			<li>
			  <a href="#time-month"><?php echo JText::_('COM_COMMUNITY_THIS_MONTH')?></a>
			</li>
			<li>
			  <a href="#time-lastmonth"><?php echo JText::_('COM_COMMUNITY_LAST_MONTH')?></a>
			</li>
		  </ul>
	  </div>
	</div>

	<div class="widget-body">
	  <div class="widget-main">

		<div class="row-fluid">
		  <div class="span24">

		  <!-- Engagement chart -->

		  <div class="widget-box transparent clearfix">
			<div class="widget-header widget-header-flat">
			  <h5 class="lighter">
				<?php echo JText::_('COM_COMMUNITY_DASHBOARD_USER_ENGAGEMENT')?>
			  </h5>

			  <div class="widget-toolbar pull-right the-chev">
				<a href="#" data-action="collapse">
				  <i class="js-icon-chevron-up"></i>
				</a>
			  </div>

				<ul class="nav nav-tabs widget-tabs pull-right" id="engagement-tabs" data-type="message">
				  <li class="active">
					<a href="#eng-message"><?php echo JText::_('COM_COMMUNITY_STREAM')?></a>
				  </li>
				  <li>
					<a href="#eng-photo"><?php echo JText::_('COM_COMMUNITY_PHOTOS')?></a>
				  </li>
				  <li>
					<a href="#eng-video"><?php echo JText::_('COM_COMMUNITY_VIDEOS')?></a>
				  </li>
				  <li>
					<a href="#eng-event"><?php echo JText::_('COM_COMMUNITY_EVENTS')?></a>
				  </li>
				  <li>
					<a href="#eng-event"><?php echo JText::_('COM_COMMUNITY_GROUPS')?></a>
				  </li>
				</ul>
			</div>

			<div class="widget-body">
				<div class="widget-body-inner" style="display: block;">
					<div class="widget-main padding-4">
						<div class="demo-container">
							<div class="tab-content">
								<div class="tab-pane active" id="eng-message">
									<div id="eng-message-plot" class="demo-placeholder"></div>
									<div id="eng-message-choices" class="demo-choices"></div>
								</div>
								<div class="tab-pane" id="eng-photo">
									<div id="eng-photo-plot" class="demo-placeholder"></div>
									<div id="eng-photo-choices" class="demo-choices"></div>
								</div>
								<div class="tab-pane" id="eng-video">
									<div id="eng-video-plot" class="demo-placeholder"></div>
									<div id="eng-video-choices" class="demo-choices"></div>
								</div>
								<div class="tab-pane" id="eng-event">
									<div id="eng-event-plot" class="demo-placeholder"></div>
									<div id="eng-event-choices" class="demo-choices"></div>
								</div>
								<div class="tab-pane" id="eng-group">
									<div id="eng-group-plot" class="demo-placeholder"></div>
									<div id="eng-group-choices" class="demo-choices"></div>
								</div>
							</div>
						</div>
					</div><!--/widget-main-->
				</div>
			</div><!--/widget-body-->
		  </div>

		  <!-- Statistic chart -->
		  <div class="widget-box transparent">
			<div class="widget-header widget-header-flat">
			  <h5 class="lighter">
				 <?php echo JText::_('COM_COMMUNITY_DASHBOARD_DATA_STATISTIC')?>
			  </h5>

			  <div class="widget-toolbar pull-right the-chev">
				<a href="#" data-action="collapse">
				  <i class="js-icon-chevron-up"></i>
				</a>
			  </div>

				<ul class="nav nav-tabs widget-tabs pull-right">
				  <li class="active">
					<a href="#"><?php echo JText::_('COM_COMMUNITY_COUNT')?></a>
				  </li>

				  <!--li>
					<a href="#">Size</a>
				  </li-->

				</ul>
			</div>

			<div class="widget-body"><div class="widget-body-inner" style="display: block;">
			  <div class="widget-main padding-4">
			  <div class="demo-container-2">
				<div id="placeholder-2" class="demo-placeholder-2"></div>
				<div id="choices-2" ></div>
			  </div>

			  </div><!--/widget-main-->
			</div></div><!--/widget-body-->
		  </div>

		  </div>
		</div>

	  </div><!--/widget-main-->
	</div><!--/widget-body-->
	</div>
</div>

<!-- Most Recent Stream -->
<div class="span10">

  <div class="widget-box" id="recent-box">
	<div class="widget-header widget-header-flat">
	  <h5>
		<i class="js-icon-th-list"></i>
		<?php echo JText::_('COM_COMMUNITY_MOST_RECENT')?>
	  </h5>

	  <div class="widget-toolbar no-border">
		<ul class="nav nav-tabs" id="recent-tab">
		  <li class="active">
			<a data-toggle="tab" href="#posts-tab"><?php echo JText::_('COM_COMMUNITY_POSTS')?></a>
		  </li>

		  <li class="">
			<a data-toggle="tab" href="#comments-tab"><?php echo JText::_('COM_COMMUNITY_COMMENTS')?></a>
		  </li>

		  <li class="">
			<a data-toggle="tab" href="#members-tab"><?php echo JText::_('COM_COMMUNITY_MEMBERS')?></a>
		  </li>
		</ul>
	  </div>
	</div>

		<div class="widget-body clearfix">
		  <div class="widget-main padding-4">
			<div class="tab-content padding-8 overflow-visible clearfix">
			  <!-- Posts tab -->
			  <div id="posts-tab" class="tab-pane active">
				<div class="dialogs">
				  <!-- Contain start here-->
				  <?php if(!empty($this->statusData)) {?>
					  <?php foreach($this->statusData as $data) { ?>
						<div class="itemdiv dialogdiv">
						  <div class="user">
							<img alt="<?php echo $data->user->getDisplayName(); ?>" src="<?php echo $data->user->getThumbAvatar()?>"/>
						  </div>
						  <div class="body">
							<div class="time">
							  <i class="icon-time"></i>
							  <span class="green"><?php echo  CTimeHelper::timeLapse(JFactory::getDate($data->created)); ?></span>
							</div>
							<div class="name">
							  <?php if(!empty($data->user->id)){?>
							  		<a href="<?php echo JURI::root() . '/administrator/index.php?option=com_community&view=users&layout=edit&id=' . $data->user->id; ?>"><?php echo $data->user->getDisplayName(); ?></a>
								<?php }?>
							</div>
							<div class="text"><?php echo JHtml::_('string.truncate', ucfirst(trim($data->title)), 750); ?></div>
						  </div>
						</div>
					  <?php }?>
					<?php } else {?>
							<?php echo JText::_('COM_COMMUNITY_DASHBOARD_NO_POST')?>
					<?php }?>
				   <!-- Contain End here-->
				  <div class="center cta-full">
					<a href="<?php echo JURI::root() . '/administrator/index.php?option=com_community&view=activities'; ?>">
					  <?echo JText::_('COM_COMMUNITY_DASHBOARD_SEE_ACTIVITIES')?> &nbsp;
					  <i class="js-icon-arrow-right"></i>
					</a>
				  </div>
				</div>
			  </div>
			  <!-- Comments tab -->
			  <div id="comments-tab" class="tab-pane">

				<div class="dialogs">
				  <!-- Contain start here-->
				  <?php if(!empty($this->comments)){?>
					  <?php foreach($this->comments as $comment) {?>
						<div class="itemdiv dialogdiv">
						  <div class="user">
							<img alt="<?php echo $comment->user->getDisplayName() ?>" src="<?php echo $comment->user->getThumbAvatar() ?>" />
						  </div>
						  <div class="body">
							<div class="time">
							  <i class="icon-time"></i>
							  <span class="green"><?php echo  CTimeHelper::timeLapse(JFactory::getDate($comment->date)); ?></span>
							</div>
							<div class="name">
							  <a href="<?php echo JURI::root() . '/administrator/index.php?option=com_community&view=users&layout=edit&id=' . $comment->user->id; ?>"><?php echo $comment->user->getDisplayName() ?></a>
							</div>

							<div class="text">
							  <i class="js-icon-quote-left"></i>
								<?php echo JHtml::_('string.truncate', $comment->comment, 220); ?>
							</div>
						  </div>
						</div>
					  <?php }?>
				 <?php } else {?>
				 		<?php echo JText::_('COM_COMMUNITY_DASHBOARD_NO_COMMENT')?>
				 <?php }?>
				  <!-- Contain End here-->

				  <!--div class="center cta-full">
					<a href="<?php echo JURI::root() . '/administrator/index.php?option=com_community&view=activities'; ?>">
					  See all Activities &nbsp;
					  <i class="js-icon-arrow-right"></i>
					</a>
				  </div-->
				 </div>
			  </div>
			  <!-- Members tab -->
			  <div id="members-tab" class="tab-pane clearfix">
				<?php foreach($this->latestMembers as $member ) { ?>
				  <div id="member-<?php echo $member->id?>"class="itemdiv memberdiv clearfix">
					<div class="user">
					  <img alt="<?php echo $member->getDisplayName()?>" src="<?php echo $member->getThumbAvatar()?>" />
					</div>

					<div class="body">
					  <div class="name">
						<a href="<?php echo JURI::root() . '/administrator/index.php?option=com_community&view=users&layout=edit&id=' . $member->id; ?>"><?php echo $member->getDisplayName()?></a>
					  </div>

					  <div class="time">
						<i class="js-icon-time"></i>
						<span class="green"><?php echo CTimeHelper::timeLapse(JFactory::getDate($member->registerDate)); ?></span>
					  </div>

					  <div id="member-label-<?php echo $member->id?>">
						<span class="label <?php echo $this->getLabelCss($member->memberstatus)?>"><?php echo $member->memberstatus?></span>
						<?php if($member->memberstatus !=='approved') {?>
						  <div class="inline position-relative">
							<button class="btn btn-minier bigger btn-yellow dropdown-toggle">
							  <i class="js-icon-angle-down js-icon-only bigger-120"></i>
							</button>

							<ul class="dropdown-menu dropdown-icon-only dropdown-yellow pull-right dropdown-caret dropdown-close">
							  <li>
								<a href="javascript:void(0);" class="tooltip-success" onClick= "azcommunity.toggleStatus('<?php echo $member->id?>',1)" data-rel="tooltip" title="Approve">
								  <span class="green">
									<i class="js-icon-ok bigger-110"></i>
								  </span>
								</a>
							  </li>

							  <li>
								<a href="javascript:void(0);" class="tooltip-warning" onClick= "azcommunity.toggleStatus('<?php echo $member->id?>',0)" data-rel="tooltip" title="Reject">
								  <span class="orange">
									<i class="js-icon-remove bigger-110"></i>
								  </span>
								</a>
							  </li>

							  <li>
								<a href="javascript:void(0);" class="tooltip-error" onClick= "azcommunity.toggleStatus('<?php echo $member->id?>',2)" data-rel="tooltip" title="Delete">
								  <span class="red">
									<i class="js-icon-trash bigger-110"></i>
								  </span>
								</a>
							  </li>
							</ul>
						  </div>
						<?php }?>

					  </div>

					</div>
				  </div>
				  <?php }?>

				  <div class="clearfix"></div>

				  <div class="center cta-full">
					<a href="<?php echo JURI::root() . '/administrator/index.php?option=com_community&view=users'; ?>">
					  <?php echo JText::_('COM_COMMUNITY_DASHBOARD_ALL_MEMBERS')?> &nbsp;
					  <i class="js-icon-arrow-right"></i>
					</a>
				  </div>

			  </div> <!-- members tab end -->
			</div>
		  </div><!--/widget-main-->
		</div><!--/widget-body-->
	  </div>
</div>
</div>

<!-- Demographic & Member Locations -->
<div class="row-fluid">
<!-- Demographic -->
<div class="span8">
	  <div class="widget-box" id="demographic-box">
		<div class="widget-header widget-header-flat">

		  <h5><i class="js-icon-globe"></i> <?php echo JText::_('COM_COMMUNITY_DEMOGRAPHIC')?></h5>
		</div>

		<div class="widget-body">
		  <div class="widget-main">
			<div id="pie-placeholder"></div>

			<div>
			<div class="hr hr-8 hr-double"></div>
			<h4 class="text-center"><?php echo JText::sprintf('COM_COMMUNITY_DASHBOARD_AVARAGE_AGE', $this->age->average)?></h4>
			<div class="hr hr-8 hr-double"></div>
				<div class="row-fluid">
				  <div class="span12 text-center">
					<img src="<?php echo COMMUNITY_BASE_ASSETS_URL . "/user-Male.png" ?>" alt="" class="inline avg-avatar img-circle">
					<h3 class="inline"><?php echo $this->age->MaleAverage?></h3>
					<span class="block"><?php echo JText::_('COM_COMMUNITY_DASHBOARD_MALE')?></span>
				  </div>
				  <div class="span12 text-center">
					<img src="<?php echo COMMUNITY_BASE_ASSETS_URL . "/user-Female.png" ?>" alt="" class="inline avg-avatar img-circle">
					<h3 class="inline"><?php echo $this->age->FemaleAverage?></h3>
					<span class="block"><?php echo Jtext::_('COM_COMMUNITY_DASHBOARD_FEMALE')?></span>
				  </div>
				</div>
			</div>
		  </div>
		</div>
	  </div>
</div>
<!-- Member Location -->
<div class="span16">
	<div class="widget-box" id="location-box">
	  <div class="widget-header widget-header-flat">
		<h5><i class="js-icon-map-marker"></i><?php echo JText::_('COM_COMMUNITY_DASHBOARD_MEMBER_LOCATION')?></h5>
	  </div>
	  <div class="widget-body">
		<div class="widget-main">
		  <div class="row-fluid reset-gap">
			<div class="span24">
			  <!-- Map -->
			  <div id="map_canvas"></div>
			</div>
		  </div>
		  <div class="row-fluid reset-gap">
			<div class="span12">
			  <table class="table table-bordered reset-gap">
				<thead>
				  <tr>
					<th><?php echo JText::_('COM_COMMUNITY_DASHBOARD_COUNTRY') ?></th>
					<th><?php echo JText::_('COM_COMMUNITY_DASHBOARD_FAN') ?></th>
				  </tr>
				</thead>
				<tbody>
					<?php if(!empty($this->userCountry)) {?>
					  <?php foreach($this->userCountry as $userCountry){?>
					  <tr>
						<td><?php echo JText::_($userCountry->country) ?></td>
						<td><?php echo $userCountry->count ?></td>
					  </tr>
					  <?php }?>
					<?php } else {?>
						<tr>
							<td colspan=2> <?php echo JText::_('COM_COMMUNITY_DASHBOARD_NO_COUNTRY')?> </td>
						</tr>
					<?php }?>
				</tbody>
			  </table>
			</div>
			<div class="span12">
			  <table class="table table-bordered reset-gap">
				<thead>
				  <tr>
					<th><?php echo JText::_('COM_COMMUNITY_DASHBOARD_CITY')?></th>
					<th><?php echo Jtext::_('COM_COMMUNITY_DASHBOARD_FAN')?></th>
				  </tr>
				</thead>
				<tbody>
				  <?php if(!empty($this->userCity)) {?>
					  <?php foreach($this->userCity as $city){?>
						  <?php if ( trim($city->city) != '' ) { ?>
							  <tr>
								<td><?php echo $this->escape($city->city)?></td>
								<td><?php echo $city->count?></td>
							  </tr>
						  <?php }?>
					  <?php }?>
				 <?php } else {?>
				 		<tr>
				 			<td colspan=2><?php echo JText::_('COM_COMMUNITY_DASHBOARD_NO_CITY')?></td>
				 		</tr>
				 <?php }?>
				</tbody>
			  </table>
			</div>
		  </div>
		</div>
	  </div>
	</div>
</div>
</div>

<div class="row-fluid">
<div class="span12">
  <!-- Calendar -->
  <div class="widget-box" id="event-box">
	<div class="widget-header widget-header-flat">

	  <h5>
		<i class="js-icon-calendar"></i>
		<?php echo JText::_('COM_COMMUNITY_DASHBOARD_EVENT_SCHEDULE')?>
	  </h5>
	</div>
	<div class="widget-body">
	  <div class="widget-main">
		<div id="calendar"></div>
	  </div>
	</div>
  </div>
</div>
<div class="span12">
  <!-- News Feed -->
	   <div class="widget-box" id="news-feed-box">
		<div class="widget-header widget-header-flat">
		  <h5>
			<i class="js-icon-rss orange"></i>
			<?php JText::_('COM_COMMUNITY_DASHBOARD_NEWS_FEED')?>
		  </h5>

		  <div class="widget-toolbar no-border">
			<ul class="nav nav-tabs" id="recent-tab">
			  <li class="active">
				<a data-toggle="tab" href="#news-tab"><?php echo JText::_('COM_COMMUNITY_DASHBOARD_NEWS'); ?></a>
			  </li>

			  <li class="">
				<a data-toggle="tab" href="#addon-tab"><?php echo JText::_('COM_COMMUNITY_DASHBOARD_FEED_ADDONS'); ?></a>
			  </li>

			  <li class="">
				<a data-toggle="tab" href="#ijoomla-tab"><?php echo JText::_('COM_COMMUNITY_DASHBOARD_FEED_IJOOMLA'); ?></a>
			  </li>

			</ul>
		  </div>
		</div>

		<div class="widget-body">
		  <div class="widget-main padding-4">
			<div class="tab-content padding-8 overflow-visible">
			  <div id="news-tab" class="tab-pane active"> </div>

			  <div id="addon-tab" class="tab-pane"> </div>

			  <div id="ijoomla-tab" class="tab-pane"> </div>
			</div>
		  </div><!--/widget-main-->
		</div><!--/widget-body-->
	  </div>

</div>
</div>

<script src="<?php echo COMMUNITY_ASSETS_URL; ?>/js/jquery.flot.min.js"></script>
<script src="<?php echo COMMUNITY_ASSETS_URL; ?>/js/jquery.flot.categories.min.js"></script>
<script src="<?php echo COMMUNITY_ASSETS_URL; ?>/js/jquery.flot.pie.min.js"></script>
<script src="<?php echo COMMUNITY_ASSETS_URL; ?>/js/fullcalendar.min.js"></script>
<script src="<?php echo COMMUNITY_ASSETS_URL; ?>/js/jquery.slimscroll.min.js"></script>

<!-- User engagement and statistics -->
<script type="text/javascript">
var dashboard = {
	init: function()
	{
		var $engTabs = $('#engagement-tabs');

		// Graph tabs
		$engTabs.find('a').click(function (e) {
			e.preventDefault();

			var $self = $(this);
			var type = $self.attr('href').replace('#eng-', '');

			// Set type
			$engTabs.data('type', type);

			dashboard.getEngagementGraph();

			$self.tab('show');
		});

		// Initialize the default graphs
		dashboard.getEngagementGraph();
		dashboard.getStatisticGraph();
	},
	getEngagementGraph: function()
	{
		// Load the graph
		var time = $('#dropdown-time').data('time');
		var type = $('#engagement-tabs').data('type');

		$('.demo-placeholder, .demo-choices').html('');
		dashboard.showEngagementLoading();

		jax.call('community','admin,community,getEngagementGraph', type, time);
	},
	getStatisticGraph: function()
	{
		var time = $('#dropdown-time').data('time');
		var type = $('#engagement-tabs').data('type');

		$('#placeholder-2, #choices-2').html('');
		dashboard.showStatisticLoading();

		jax.call('community','admin,community,getStatisticGraph', time);
	},
	showEngagementLoading: function()
	{
		$('<img src="<?php echo JURI::root() . 'components/com_community/assets/window/wait.gif'; ?>" class="eng-loading"/>').prependTo('.demo-placeholder');
	},
	showStatisticLoading: function()
	{
		$('<img src="<?php echo JURI::root() . 'components/com_community/assets/window/wait.gif'; ?>" class="eng-loading"/>').prependTo('#placeholder-2');
	}
}

$(function() {
	dashboard.init();

	// move the ijoomla logo
	jQuery("#ijoomla-logo").css('display','block');
	jQuery("#ijoomla-logo").addClass("pull-right no-margin").prependTo(".page-header");
	jQuery("#toolbar").remove();

	// Time dropdown
	$('#dropdown-time').unbind().dropdown();
	$('#dropdown-time-menu a').click(function (e) {
		e.preventDefault();

		var $self = $(this);

		// Active selection
		$self.closest('ul').find('li').removeClass('active');
		$self.parent('li').addClass('active');
		$('#dropdown-time').find('span:first').text($self.text());

		// Set time
		var time = $self.attr('href').replace('#time-', '');
		$('#dropdown-time').data('time', time);

		// Reload graph
		dashboard.getEngagementGraph();
		dashboard.getStatisticGraph();
	});
});
</script>

<!-- Dummy content pie chart -->
<script type="text/javascript">
	function labelFormatter(label, series) {
	return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + Math.round(series.percent) + "%</div>";
	}

	jQuery(function() {

	// Example Data

	var data = [
	 { label: "<?php echo JText::_('COM_COMMUNITY_DASHBOARD_MALE')?>",  data: <?php echo $this->gender->Male?>},
	 { label: "<?php echo JText::_('COM_COMMUNITY_DASHBOARD_FEMALE')?>",  data: <?php echo $this->gender->Female?>}
	];

	var placeholder = jQuery("#pie-placeholder");
	  $.plot(placeholder, data, {
		series: {
		  pie: {
			innerRadius: 0.2,
			show: true,
			label: {
			  show: true,
			  radius: 2/4,
			  formatter: labelFormatter,
			  background: {
				opacity: 0.5,
				color: "#000"
			  }
			}
		  }
		}
	  });

	});
</script>

<!-- Add google maps -->
<style>
  #map_canvas {
	width: 100%;
	height: 200px;
	margin-bottom:10px !important;
  }
</style>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script>
	var geocoder;
	var map;
	function initialize() {
	geocoder = new google.maps.Geocoder();
	var latlng = new google.maps.LatLng(44.5403, -78.5463);
	var map_canvas = document.getElementById('map_canvas');
	var map_options = {
	  center: latlng,
	  zoom: 2,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	//map = new google.maps.Map(map_canvas, map_options);
	map = new google.maps.Map(document.getElementById('map_canvas'), map_options);

	<?php
	if(! empty($this->userCountry)) {
		foreach($this->userCountry as $userCountry) {
		?>
		codeAddress('<?php echo JText::_($userCountry->country) ?>');
		<?php
		}
	}
	?>

	}

	function codeAddress(address) {
	geocoder.geocode( { 'address': address}, function(results, status) {
	  if (status == google.maps.GeocoderStatus.OK) {
		map.setCenter(results[0].geometry.location);
		var marker = new google.maps.Marker({
			map: map,
			title:address,
			position: results[0].geometry.location
		});
	  } else {
		alert('Geocode was not successful for the following reason: ' + status);
	  }
	});
	}
	google.maps.event.addDomListener(window, 'load', initialize);
</script>


<script type="text/javascript">

jQuery(function() {

  /* initialize the calendar
  -----------------------------------------------------------------*/

  var date = new Date();
  var d = date.getDate();
  var m = date.getMonth();
  var y = date.getFullYear();

    var months = [  "<?php echo JText::_('COM_COMMUNITY_MONTH_JANUARY');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_FEBRUARY');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_MARCH');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_APRIL');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_MAY');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_JUNE');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_JULY');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_AUGUST');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_SEPTEMBER');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_OCTOBER');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_NOVEMBER');?>",
                    "<?php echo JText::_('COM_COMMUNITY_MONTH_DECEMBER');?>"
                ];
    var days = ["<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_1');?>",
                "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_2');?>",
                "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_3');?>",
                "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_4');?>",
                "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_5');?>",
                "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_6');?>",
                "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY_7');?>"];


  var calendar = jQuery('#calendar').fullCalendar({
	 buttonText: {
	  prev: '<i class="js-icon-chevron-left"></i>',
	  next: '<i class="js-icon-chevron-right"></i>'
	},
      monthNames: months,
      monthNamesShort: months,
      dayNames: days,
      dayNamesShort: days,
      buttonText: {
          prev: "&nbsp;&#9668;&nbsp;",
          next: "&nbsp;&#9658;&nbsp;",
          prevYear: "&nbsp;&lt;&lt;&nbsp;",
          nextYear: "&nbsp;&gt;&gt;&nbsp;",
          today: "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_CURRENT');?>",
          month: "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_MONTH');?>",
          week: "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_WEEK');?>",
          day: "<?php echo JText::_('COM_COMMUNITY_DATEPICKER_DAY');?>"},

	header: {
	  left: 'prev,next today',
	  center: 'title',
	  right: ''
	},
	events: <?php echo $this->eventcal;?>
	,
	editable: false,
	droppable: false, // this allows things to be dropped onto the calendar !!!
	drop: function(date, allDay) { // this function is called when something is dropped

	  // retrieve the dropped element's stored Event Object
	  var originalEventObject = jQuery(this).data('eventObject');
	  var $extraEventClass = jQuery(this).attr('data-class');


	  // we need to copy it, so that multiple events don't have a reference to the same object
	  var copiedEventObject = $.extend({}, originalEventObject);

	  // assign it the date that was reported
	  copiedEventObject.start = date;
	  copiedEventObject.allDay = allDay;
	  if($extraEventClass) copiedEventObject['className'] = [$extraEventClass];

	  // render the event on the calendar
	  // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
	  jQuery('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

	  // is the "remove after drop" checkbox checked?
	  if (jQuery('#drop-remove').is(':checked')) {
		// if so, remove the element from the "Draggable Events" list
		jQuery(this).remove();
	  }

	}
	,
	selectable: true,
	selectHelper: true,
	select: function(start, end, allDay) {
	}
	,
	eventClick: function(calEvent, jsEvent, view) {

	}

  });

});

jax.call( 'community' , 'admin,community,getRssFeed','http://feeds.feedburner.com/JomsocialBlog','news-tab');
jax.call( 'community' , 'admin,community,getRssFeed','http://feeds.feedburner.com/new-addons','addon-tab');
jax.call( 'community' , 'admin,community,getRssFeed','http://www.ijoomla.com/blog/feed/','ijoomla-tab');

</script>

<script>
	// jquery slimscroll
	jQuery("#news-feed-box .widget-body").slimScroll({
		height: '460px'
	});
	jQuery('#recent-box > .widget-body').slimScroll({
		height: '500px'
	});

	jQuery('#statistic-box > .widget-body').height('500px');
	jQuery('#event-box > .widget-body').css({ minHeight: 460 });
	jQuery('#demographic-box > .widget-body,#location-box > .widget-body').height('464px');

	joms.jQuery('div.inline.position-relative > button').dropdown();
</script>