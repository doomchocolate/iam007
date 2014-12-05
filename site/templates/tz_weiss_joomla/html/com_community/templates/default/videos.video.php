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
?>

<script language="javascript">
joms.jQuery(function() {

descHeight = joms.jQuery(".video-description").css('height');
descHeight = descHeight.split("px");
descHeight = parseInt(descHeight[0]);

if(descHeight>120){
    joms.jQuery(".video-description").css('height','120px').css('overflow','hidden');
} else {
    joms.jQuery(".more-text").hide();
}

joms.jQuery(".more-text").click(function()
{

if(joms.jQuery(".video-description").css('overflow')=="hidden"){
    joms.jQuery(".video-description").css('height','auto').css('overflow','auto');
    joms.jQuery(".more-text").html('<?php echo JText::_("COM_COMMUNITY_HIDE_ACTIVITY") ?>');
} else {
    joms.jQuery(".video-description").css('height','120px').css('overflow','hidden');
    joms.jQuery(".more-text").html('<?php echo JText::_("COM_COMMUNITY_MORE") ?>');
}

});

// joms.jQuery(".video-player").children('iframe').attr('src',function() {
// 	return this.src + "?wmode=opaque";
// });

});
</script>


<div class="video-full" id="<?php echo "video-" . $video->getId() ?>">


	<div class="cPageActions clearfix">
		<div class="cPageAction cFloat-R">
			<?php echo $reportHTML;?>
			<?php echo $bookmarksHTML;?>
		</div>
	</div><!--action-button-->

	<!--VIDEO PLAYER-->
    <div class="cVideo-Screen video-player">
    	<div class="cVideo-Wrapper" style="margin: 0 auto; position: relative;">
			<?php echo $video->getPlayerHTML(); ?>
		</div>
    </div>
    <div class="cMedia-Option">
    	<ul class="cMedia-Options cResetList cFloatedList clearfix">

			<li title="<?php echo JText::_('COM_COMMUNITY_VIDEOS_HITS') ?>">
				<i class="com-icon-chart"></i>
				<span>
					<?php
					if(CStringHelper::isPlural($video->getHits())) {
						echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT_MANY', $video->getHits());
					} else {
						echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT', $video->getHits());
					}
					?>
				</span>
			</li>
			<li class="cFloat-R">
				<div id="like-container" class="cMedia-Like"><?php echo $likesHTML; ?></div>
			</li>
		</ul>
    </div>
    <!--end: VIDEO PLAYER-->

	<div class="cLayout">

		<div class="row-fluid">

			<div class="span8">
				<div class="cMain">
					<div class="cMedia-About">
						<div class="cMedia-Author">
							<?php echo JText::_('COM_COMMUNITY_VIDEOS_UPLOADED_BY');?>
							<strong>
								<a href="<?php echo CUrlHelper::userLink($user->id);?>">
									<?php echo $user->getDisplayName(); ?>
								</a>
							</strong>.

							<?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY');?>:
							<strong>
								<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=display&catid='.$video->category_id);?>">
									<?php echo JText::_($video->getCategoryName()); ?>
								</a>
							</strong>.

							<?php echo JText::_('COM_COMMUNITY_VIDEOS_CREATED') ?>
							<strong><?php echo JHTML::_('date', $video->created, JText::_('DATE_FORMAT_LC3')); ?></strong>.

							<?php if (!empty($video->location) && $videoMapsDefault==1):?>
								<?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION') ?>
								<a class="album-map-link" onclick="joms.jQuery('#video-map').toggle();" title="<?php echo JText::_('COM_COMMUNITY_VIEW_LOCATION_TIPS');?>" href="javascript: void(0)"><?php echo $video->location; ?></a>
							<?php endif; ?>
						</div>

						<div class="cMedia-Tag videoTextTags small app-box"><?php echo JText::_('COM_COMMUNITY_VIDEOS_IN_THIS_VIDEO'); ?></div>

						<?php
						if (COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator)) {
						?>
						<div class="cMedia-TagOptions video-tagging">
							<a class="btn btn-small" id="addtagging" href="javascript:void(0);" onclick="joms.friends.showForm('', 'videos,inviteUsers','<?php echo $video->getId()?>','1','joms.videos.selectVideoTagFriends(<?php echo $video->getId()?>)');" >
								<?php echo JText::_('COM_COMMUNITY_TAG_THIS_VIDEO');?>
							</a>
						</div>
						<?php } ?>

						<div class="cMedia-Description">
							<b><?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_DESCRIPTION'); ?></b>
							<div class="video-description"><?php echo nl2br($video->getDescription()); ?></div>
							<a href="javascript:void(0)" class="btn btn-small more-text"><?php echo JText::_("COM_COMMUNITY_MORE"); ?></a>
						</div>
					</div><!--video details-->
					<?php if($wallCount > 0 || !empty($wallForm) ) {?>
						<div class="cPage-Wall">
							<a name="comments"></a>
							<div class="cWall-Header"><?php echo JText::_('COM_COMMUNITY_COMMENTS') ?></div>
							<?php if(!empty($wallForm)){?>
								<div id="wallForm" class="cWall-Form"><?php echo $wallForm; ?></div>
							<?php } ?>
							<div id="wallContent" class="cWall-Content"><?php echo $wallContent; ?></div>
						</div>
					<?php } ?>
				</div><!--cMain-->
			</div>

			<div class="span4">
				<div class="cSidebar">

					<?php if ( $zoomableMap ) { ?>
					<div id="video-map" class="cModule cVideo-Location app-box">
						<h3 class="app-box-header cResetH"><?php echo JText::sprintf('COM_COMMUNITY_PHOTOS_ALBUM_TAKEN_AT_DESC', ''); ?></h3>
						<div class="app-box-content">
							<?php echo $zoomableMap;?>
						</div>
					</div>
					<?php } ?>


					<?php if (count($otherVideos)>1) { ?>
					<div class="cModule cVideo-RelatedVideo app-box">
						<h3 class="app-box-header">
							<?php echo ($isGroup) ? JText::_('COM_COMMUNITY_VIDEOS_GROUP_OTHER'): JText::_('COM_COMMUNITY_VIDEOS_OTHER');?>
						</h3>

						<div class="app-box-content">
							<ul class="cThumbDetails cResetList">
									<?php
									$displayCount = 0;
									foreach($otherVideos as $others) {
										$videoInfo = JTable::getInstance( 'Video' , 'CTable' );
										$videoInfo->load($others->id);

										if ($others->id != $video->id)
										{
											$displayCount++;
										}
										else
										{
											continue;
										}

										$groupParam  = !empty($others->groupid) ?'&groupid=' . $others->groupid:'';
									?>
								<li>
									<a class="cThumb-Avatar cFloat-L" href="<?php echo $videoInfo->getUrl(); ?>">
										<b class="cAvatar cVideo-Thumb" style="background-image: url(<?php echo $videoInfo->getThumbnail(); ?>); " data="video_prop_<?php echo rand(0,200).'_'.$others->id;?>" ></b>
									</a>
									<div class="cThumb-Detail">
										<a class="cThumb-Title" href="<?php echo $videoInfo->getUrl(); ?>">
											<?php echo $this->escape($others->title); ?>
										</a>
										<div class="cThumb-Brief small">
											<?php echo $others->hits; ?> <?php echo JText::_('COM_COMMUNITY_VIDEOS_HITS') ?>
										</div>
									</div>
								</li>
								<?php if ($displayCount == 5) {
										break;
									}
								} //end foreach ?>
							</ul>
						</div>
					</div>
					<?php } //end if ?>
				</div><!--cSidebar-->

			</div>
		</div>

	</div><!--cLayout-->
</div>

<script type="text/javascript">
	var video_tags = [
						<?php foreach($video->tagged as $tagItem){ ?>
						{
							id:     <?php echo $tagItem->id;?>,
							videoId: <?php echo $video->id; ?>,
							userId: <?php echo $tagItem->userid;?>,
							displayName: '<?php echo addslashes($tagItem->user->getDisplayName()); ?>',
							profileUrl: '<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$tagItem->userid, false);?>',
							canRemove: <?php echo $tagItem->canRemoveTag;?>
						}
						<?php $end = end($video->tagged); if($end->id != $tagItem->id) echo ',';?>
						<?php } ?>
					];
	joms.jQuery(document).ready(function(){
		joms.videos.addVideoTextTag(video_tags,"<?php echo JText::_('COM_COMMUNITY_REMOVE')?>");
	});
</script>