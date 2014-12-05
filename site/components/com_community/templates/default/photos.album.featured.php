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

if($featuredList && $showFeatured){//display only if there is featured list

?>

<!-- Slider Kit styles -->
		<!-- Slider Kit compatibility -->
		<!--[if IE 6]><link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>components/com_community/assets/featuredslider/sliderkit-ie6.css" /><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>components/com_community/assets/featuredslider/sliderkit-ie7.css" /><![endif]-->
		<!--[if IE 8]><link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>components/com_community/assets/featuredslider/sliderkit-ie8.css" /><![endif]-->

		<!-- Slider Kit scripts -->
		<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_community/assets/featuredslider/sliderkit/jquery.sliderkit.1.8.js"></script>

		<!-- Slider Kit launch -->
<script>
	joms.jQuery(document).ready(function(){

				<?php if(JRequest::getVar('limitstart')!="")
				{?>
					var target_offset = joms.jQuery("#lists").offset();
					var target_top = target_offset.top;
					joms.jQuery('html, body').animate({scrollTop:target_top}, 200);
				<?php } ?>

		joms.jQuery(".featured-photo").sliderkit({
						shownavitems:5,
						scroll:<?php echo $config->get('featuredalbumscroll'); ?>,
						// set auto to true to autoscroll
						auto:false,
						mousewheel:true,
						circular:true,
						scrollspeed:200,
						autospeed:10000,
						start:0
		});

		joms.jQuery('div.alb_'+0).show();
		jax.call('community' , 'photos,ajaxShowAlbumFeatured' , <?php echo $featuredList[0]->id; ?> );

		//store all the featured album div in an array
		var album_item = [];
		joms.jQuery('div.cFeaturedAlbum').each(function(i){
			album_item[i] = this;
		});

		//highlight the current selected navi button

		<?php if(count($featuredList) > 1){ ?>

		joms.jQuery('.cBoxPad').click(function(){
				var album_id = joms.jQuery(this).parent().attr('id');
				album_id = album_id.split("cPhoto");
				album_id = album_id[1];
				var id = joms.jQuery(this).attr('id');
				jax.call('community' , 'photos,ajaxShowAlbumFeatured' , album_id );
				joms.jQuery('div.cFeaturedAlbum').hide();
				joms.jQuery(album_item[id]).show();
				});


		<?php } ?>
	});

	function updateGallery(id, likes, wallCount, photoCommentLink, commentCountText ){
		joms.jQuery('.like-'+id).html(likes);
		joms.jQuery('#featured-wall-count-'+id).html(wallCount);
		joms.jQuery('#comment-count-text-'+id).html(commentCountText);
		photoCommentLink = photoCommentLink.replace(/\&amp;/g,'&');
		joms.jQuery('#featured-photo-comment-link-'+id).attr('href',photoCommentLink);
		joms.jQuery('.cSlider-selected').removeClass('cSlider-selected');
		joms.jQuery('#'+id).parent().addClass('cSlider-selected');
	}
</script>
<div id="cFeatured" class="cFeatured-Albums">

	<div class="cFeaturedTop">
	<?php
		$x = 1;
		$album_count = 0;
		foreach($featuredList as $album){
	?>

		<div class="cFeaturedAlbum alb_<?php echo $album_count; ?>" style="display:none;">
			<div class="clearfull">

				<!-- album covers -->
				<div class="cFeatured-AlbumCover cFeaturedThumb cFloat-L">
					<a href="<?php echo CRoute::_($album->getURI()); ?>">
						<img src="<?php echo $album->getCoverThumbURI();?>" alt="<?php echo $this->escape($album->name); ?>"  data="album_prop_<?php echo rand(0,200).'_'.$album->id;?>" />
						<!--album-actions-->
					</a>
					<?php if( $isCommunityAdmin ){?>
					<b>
						<a onclick="joms.featured.remove('<?php echo $album->id;?>','photos');" href="javascript:void(0);">
							<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>
						</a>
					</b>
					<?php } ?>

					<div id="like-container" class="cFeaturedLike like-<?php echo $album->id; ?>"></div>
				</div>

				<div class="cFeaturedInfo">
					<?php
					$user = CFactory::getUser($album->creator);
					?>
					<!-- album name/title -->
					<b class="cFeaturedTitle">
						<a href="<?php echo CRoute::_($album->getURI($user->id)); ?>"><?php echo $this->escape($album->name);?></a>
					</b>

					<!-- album meta -->
					<ul class="cFeaturedMeta cFloatedList cResetList clearfull">
						<li class="cAuthor">
							<span><a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName();?></a></span>
						</li>
						<li class="cLastUpdated">
							<span><?php echo $album->lastUpdated; ?></span>
						</li>
						<li class="cComment">
							<a id="featured-photo-comment-link-<?php echo $album_count; ?>" href="">
								<span id="featured-wall-count-<?php echo $album_count; ?>">0</span>
								<span id="comment-count-text-<?php echo $album_count; ?>"><?php echo JText::_('COM_COMMUNITY_COMMENT'); ?></span>
							</a>
						</li>
						<?php if (!empty($album->location)){ ?>
						<li class="cFeatured-Map">
							<?php echo $album->location;?>
						</li>
						<?php } ?>
					</ul>

					<!-- description for the album -->
					<?php if ($album->description) { ?>
					<div class="cFeaturedDesc">
						<?php echo JHTML::_('string.truncate', strip_tags($album->description),200);?>
					</div>
					<?php } ?>

					<!-- Photos from the album -->
					<div class="cFeaturedExtra clearfix">
						<div><strong><?php echo JText::_('COM_COMMUNITY_PHOTOS_IMAGES_FROM_ALBUM');?>:</strong></div>
					<?php
						$photos = $album->photos;

						for($i=0; $i<count($photos); $i++)
						{
							$row =& $photos[$i];
					?>
						<a href="<?php echo $row->link;?>" class="cPhotoItem cFloat-L" id="photo-<?php echo $i;?>" title="<?php echo $this->escape($row->caption);?>">
							<img src="<?php echo $row->getThumbURI();?>" id="photoid-<?php echo $row->id;?>" width="50" height="50" />
						</a>
					<?php } ?>
					</div>
				</div>

				<!-- tagged person -->
				<?php if ($album->tagged){ ?>
				<div class="cFeaturedTagged">
					<strong><?php echo JText::_('COM_COMMUNITY_PHOTOS_IN_THIS_ALBUM'); ?></strong>
					<div>
						<?php
						$totalpeople = sizeof($album->tagged);
						$count = 1;
						foreach($album->tagged as $ppl){

							//max tagged = 5
							if($count > 5){
								break;
							}
						?>
							<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $ppl->id); ?>">
								<?php
									echo $ppl->getDisplayName();
									if($count < $totalpeople){ echo ","; }
								?>
							</a>

						<?php
						$count++;
						};
						?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	<!--.cFeaturedAlbum-->
	<?php
			$x++;
			$album_count++;
		} // end foreach

	?>
	</div>

	<!-- navigation container -->
	<div class="cFeaturedBottom">
		<!--#####SLIDER#####-->
		<div class="cSlider featured-photo">
			<div class="cSlider-Wrap cSlider-nav">
				<div class="cSlider-Clip cSlider-nav-clip">
					<ul class="cSlider-List Photos cFloatedList cResetList clearfix">
						<?php
							$album_count = 0; $x = 0; foreach($featuredList as $album) {
								$user = CFactory::getUser($album->creator);
						?>
						<li id="cPhoto<?php echo $album->id; ?>" class="<?php echo $album->id;?>">
							 <div id="<?php echo $album_count; ?>" class="cBoxPad">
								<div class="cFeatured-AlbumCover cFeaturedThumb">
									<a href="javascript:void(0);">
										<img src="<?php echo $album->getCoverThumbURI(); ?>" alt="<?php echo $this->escape($album->name);?>" />
									</a>

									<?php if( $isCommunityAdmin ){?>
									<b>
										<a class="album-action remove-featured" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $album->id;?>','photos');" href="javascript:void(0);">
											<i class="com-icon-award-minus"></i>
										</a>
									</b>
									<?php } ?>
								</div>

								<div class="cFeaturedName">
									<a href="<?php echo CRoute::_($album->getURI()); ?>"><?php echo $this->escape($album->name);?></a>
								</div>
								<div class="cFeaturedMeta">
									<?php echo JText::_('COM_COMMUNITY_BY').' '.CFactory::getUser($album->creator)->getDisplayName();?>
								</div>
							</div>
						</li>
						<?php
								$album_count++;
							} // end foreach
						?>
					</ul>
				</div>
				<div class="cSlider-btn cSlider-nav-btn cSlider-nav-prev"><a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_PREVIOUS_BUTTON');?>">Previous</a></div>
				<div class="cSlider-btn cSlider-nav-btn cSlider-nav-next"><a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_NEXT_BUTTON');?>">Next</a></div>
			</div>
		</div><!--.cSlider-->
	</div>
</div>
<?php } ?>
<!-- end #cFeatured -->