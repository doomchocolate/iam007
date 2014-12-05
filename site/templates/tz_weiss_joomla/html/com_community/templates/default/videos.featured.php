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
if ($videos && $showFeatured)
{
?>

	<!-- Slider Kit compatibility -->
		<!--[if IE 6]><?php CAssets::attach('assets/featuredslider/sliderkit-ie6.css', 'css'); ?><![endif]-->
		<!--[if IE 7]><?php CAssets::attach('assets/featuredslider/sliderkit-ie7.css', 'css'); ?><![endif]-->
		<!--[if IE 8]><?php CAssets::attach('assets/featuredslider/sliderkit-ie8.css', 'css'); ?><![endif]-->

		<!-- Slider Kit scripts -->
		<?php CAssets::attach('assets/featuredslider/sliderkit/jquery.sliderkit.1.8.js', 'js'); ?>

		<!-- Slider Kit launch -->
		<script type="text/javascript">
			joms.jQuery(window).load(function(){

				<?php if($jinput->get('limitstart')!="" || $jinput->get('sort')!="" || $jinput->get('catid')!=""){?>
					var target_offset = joms.jQuery("#lists").offset();
					var target_top = target_offset.top;
					joms.jQuery('html, body').animate({scrollTop:target_top}, 200);
				<?php } ?>

				jax.call('community' , 'videos,ajaxShowVideoFeatured' , <?php echo $videos[0]->id; ?> );
				joms.jQuery(".featured-video").sliderkit({
					shownavitems:5,
					scroll:<?php echo $config->get('featuredvideoscroll'); ?>,
					// set auto to true to autoscroll
					auto:false,
					mousewheel:true,
					circular:true,
					scrollspeed:500,
					autospeed:10000,
					start:0
				});
				joms.jQuery('.cBoxPad').click(function(){
					jax.call('community' , 'videos,ajaxShowVideoFeatured' , joms.jQuery(this).attr("id") );
					joms.jQuery('.cSlider-selected').removeClass('cSlider-selected');
					joms.jQuery('#cVideo'+this.id).addClass('cSlider-selected');
				});

				

			});

			function updatePlayer(embedCode, title, likes, view, wallCount, videoLink, videoCommentLink, creatorName, creatorLink){
			  joms.jQuery('.cPlayer').html(embedCode);
			  joms.jQuery('.ctitle').children().children().html(title);
			  joms.jQuery('#featured-view').html(view);
			  joms.jQuery('#featured-wall-count').html(wallCount);
			  
			joms.jQuery('.comment-text').html((wallCount > 1) ? ' <?php echo JText::_('COM_COMMUNITY_COMMENTS'); ?>' : ' <?php echo JText::_('COM_COMMUNITY_COMMENT'); ?>' );

			  joms.jQuery('#creator-name').html(creatorName);
			  creatorLink = creatorLink.replace(/\&amp;/g,'&');
			  joms.jQuery('#creator-link').attr('href',creatorLink);
			  joms.jQuery('#like-container').html(likes);
			  videoLink = videoLink.replace(/\&amp;/g,'&');
			  joms.jQuery('.ctitle').children().children().attr('href',videoLink);
			  videoCommentLink = videoCommentLink.replace(/\&amp;/g,'&');
			  joms.jQuery('#featured-video-comment-link').attr('href',videoCommentLink);

			  joms.jQuery(".video-player").children('iframe').attr('src',function() {
				return this.src + "?wmode=opaque";
				});
			}

		</script>

<div id="cFeatured" class="cFeatured-Video">
	<!--video player-->
	<div class="cFeaturedTop cPlayer"></div>

	<!--title, comments, desc etc-->
	<div class="cFeaturedMiddle cMeta">
		<div id="like-container" class="cFloat-R"></div>
		<h3 class="cFeaturedTitle ctitle reset-h">
			<span><a id="featured-video-link" href=""><?php echo $videos[0]->title; ?></a></span>
		</h3>
		<ul class="cFeaturedMeta cFloatedList cResetList clearfull">
			<li class="cAuthor"><a id="creator-link" href=""><span id="creator-name"></span></a></li>
			<li class="cHits"><span id="featured-view"></span> <?php echo JText::_('COM_COMMUNITY_VIDEOS_HITS') ?></li>
			<li class="cComCount"><a id="featured-video-comment-link" href=""><span id="featured-wall-count"></span> <span class="comment-text"><?php echo JText::_('COM_COMMUNITY_COMMENTS'); ?></span></a></li>
		</ul>
	</div>

	<!--slider-->
	<div class="cFeaturedBottom">
		<div class="cSlider featured-video">
			<div class="cSlider-Wrap cSlider-nav">
				<div class="cSlider-Clip cSlider-nav-clip">
					<ul class="cSlider-List Videos cFloatedList cResetList clearfix">

						<?php $x = 0; foreach($videos as $video) { ?>
						<li id="cVideo<?php echo $video->id; ?>" style="width: 112px; height:84px;">
							 <div id="<?php echo $video->id; ?>" class="cBoxPad">
								<div class="cFeaturedThumb">
									<a class="cVideo-Thumb" href="javascript:void(0);">
										<img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $this->escape($video->title);?>" />
										<b><?php echo $video->getDurationInHMS(); ?></b>
									</a>
									<?php
									if( $isCommunityAdmin )
									{
									?>
										<b>
											<a class="album-action remove-featured" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $video->getId();?>','videos');" href="javascript:void(0);">
												<i class="com-icon-award-minus"></i>
											<?php  // echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>
											</a>
										</b>
									<?php } ?>
								</div>
								<div class="cFeaturedName">
									<?php if ($video->isPending()) {
											echo $video->getTitle();
										} else {	?>
											<a href="javascript:void(0);"><?php echo $video->getTitle(); ?></a>
									<?php } ?>
								</div>



								<div class="cFeaturedMeta">
										<?php echo JText::_('COM_COMMUNITY_BY') ?>
										<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$video->creator); ?>">
											<?php echo $video->getCreatorName(); ?>
										</a>
								</div><!--cVideoDetails-->


							<br class="clr" />

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
	</div><!--.cFeaturedBottom-->
</div><!--cFeatured-->

	<div class="clr"></div>
<?php
}
