<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

if( $photos )
{
?>
<div id="cPhoto">

	<!-- Slider Kit compatibility -->
		<!--[if IE 6]><?php CAssets::attach('assets/featuredslider/sliderkit-ie6.css', 'css'); ?><![endif]-->
		<!--[if IE 7]><?php CAssets::attach('assets/featuredslider/sliderkit-ie7.css', 'css'); ?><![endif]-->
		<!--[if IE 8]><?php CAssets::attach('assets/featuredslider/sliderkit-ie8.css', 'css'); ?><![endif]-->

		<!-- Slider Kit scripts -->
		<?php
			CAssets::attach('assets/featuredslider/sliderkit/jquery.sliderkit.1.8.js', 'js');
			CAssets::attach('assets/imgareaselect/scripts/jquery.imgareaselect.min.js', 'js');
			CAssets::attach('assets/imgareaselect/css/imgareaselect-default.css', 'css');
			CAssets::attach('assets/autocomplete/jquery.autocomplete.js', 'js');
			CAssets::attach('assets/easytabs/jquery.easytabs.min.js', 'js');
			CAssets::attach('assets/jquery.cj-swipe.min.js', 'js');
		?>

		<!-- Slider Kit launch -->
		<script type="text/javascript">
			joms.jQuery(window).load(function(){
				joms.jQuery(".single-photo").sliderkit({
					shownavitems:7,
					scroll:5,
					// set auto to true to autoscroll
					auto:false,
					mousewheel:true,
					circular:true,
					scrollspeed:500,
					autospeed:10000,
					start:0
				});

				// Initialize auto-complete
				var options = { serviceUrl:'<?php echo CRoute::_("index.php?option=com_community&view=friends&task=ajaxAutocomplete");?>',
					onChange : function( s, d, el ){
						joms.gallery.addPhotoTag(d);
					},
					onSelect : function( s, d, el ){
						joms.gallery.addPhotoTag(d);
					}
 					};
   				ac = joms.jQuery('#photoTagQuery').autocomplete(options);
				ac.enable();
			});
		</script>

<div class="cPageActions cPageAction clearfix page-actions clrfix"></div>
<div id="cGallery">
	<script type="text/javascript">
		joms.gallery.bindKeys();
		var jsPlaylist = {
			album: <?php echo $album->id;?>,
			photos:	[
					<?php



					for($i=0; $i < count($photos); $i++ )
					{
						$photo	=& $photos[$i];
						$storage = CStorage::getStorage( $photo->storage );
						$imgpath = str_replace('/', '/' , $photo->original);

					?>
						{id: <?php echo $photo->id; ?>,
						 loaded: false,
						 caption: '<?php echo addslashes( $photo->caption );?>',
						 thumbnail: '<?php echo $photo->getThumbURI(); ?>',
						 hits: '<?php echo $photo->hits; ?>',
						 url: '<?php  echo $photo->getImageURI(); ?>',
						 originalUrl: '<?php  echo $photo->getOriginalURI(); ?>',
						 sefURL:'<?php echo str_replace("&amp;","&",$photo->getPhotoURI());?>',
						 tags: [
							<?php foreach($photo->tagged as $tagItem){ ?>
							{
								id:     <?php echo $tagItem->id;?>,
								photoId: <?php echo $photo->id; ?>,
								userId: <?php echo $tagItem->userid;?>,
								displayName: '<?php echo addslashes($tagItem->user->getDisplayName()); ?>',
								profileUrl: '<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$tagItem->userid, false);?>',
								top: <?php echo $tagItem->posx;?>,
								left: <?php echo $tagItem->posy;?>,
								width: <?php echo $tagItem->width;?>,
								height: <?php echo $tagItem->height;?>,
								displayTop: null,
								displayLeft: null,
								displayWidth: null,
								displayHeight: null,
								canRemove: <?php echo $tagItem->canRemoveTag;?>
							}
							<?php $end = end($photo->tagged); if($end->id != $tagItem->id) echo ',';?>
							<?php } ?>
						 ]
						}
					<?php
						$end	= end( $photos );
						if ($end->id!=$photo->id)
							echo ',';
					}
					?>
					],
			currentPlaylistIndex: null,
			language: {
				COM_COMMUNITY_REMOVE: '<?php echo addslashes(JText::_('COM_COMMUNITY_REMOVE'));?>',
				COM_COMMUNITY_PHOTOS_NO_CAPTIONS_YET: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTOS_NO_CAPTIONS_YET'));?>',
				COM_COMMUNITY_SET_PHOTO_AS_DEFAULT_DIALOG: '<?php echo addslashes(JText::_('COM_COMMUNITY_SET_PHOTO_AS_DEFAULT_DIALOG'));?>',
				COM_COMMUNITY_REMOVE_PHOTO_DIALOG: '<?php echo addslashes(JText::_('COM_COMMUNITY_REMOVE_PHOTO_DIALOG'));?>',
				COM_COMMUNITY_SELECT_PERSON: '<?php echo addslashes(JText::_('COM_COMMUNITY_SELECT_PERSON')); ?>',
				COM_COMMUNITY_PHOTO_TAG_NO_FRIEND: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTO_TAG_NO_FRIEND')); ?>',
				COM_COMMUNITY_PHOTO_TAG_ALL_TAGGED: '<?php echo addslashes(JText::_('COM_COMMUNITY_PHOTO_TAG_ALL_TAGGED')); ?>',
				COM_COMMUNITY_CONFIRM: '<?php echo addslashes(JText::_('COM_COMMUNITY_CONFIRM')); ?>',
				COM_COMMUNITY_PLEASE_SELECT_A_FRIEND: '<?php echo addslashes(JText::_('COM_COMMUNITY_PLEASE_SELECT_A_FRIEND')); ?>'
			},
			config: {
				defaultTagWidth: <?php echo $config->get('tagboxwidth');?>,
				defaultTagHeight: <?php echo $config->get('tagboxheight');?>
			},
			customSetting:{
				defaultId : <?php echo $defaultId; ?>
			}
		};
	</script>

	<?php if ($default) { ?>

	<div class="photoViewport">
		<div class="photoDisplay">
			<img class="photoImage"/>
		</div>

		<?php if(intval( $config->get('photosgalleryslider'))) { ?>
		<!-- navigation slider starts -->
		<div class="photo_slider cFeaturedContent visible-desktop">
			<!--#####SLIDER#####-->
			<div class="cSlider perPhoto single-photo">
				<div class="cSlider-Nav cSlider-nav">
					<div class="cSlider-Clip cSlider-nav-clip">
						<ul class="cSlider-SinglePhoto cResetList">

						   <?php for($i=0; $i < count($photos); $i++ ){
								$photo	=& $photos[$i];
							?>

						<li id="cPhoto<?php echo $photo->id; ?>" class="slider-gallery">

								 <img src="<?php echo $photo->getThumbURI(); ?>" id="photoSlider_thumb<?php echo $photo->id;?>" width="75px" class="image_thumb" onclick="joms.photos.photoSlider.viewImage(<?php echo $photo->id;?>);" />

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
		</div><!-- navigation slider ends -->
		<?php } ?>

		<div class="photoActions">
			<div class="photoAction _next" onclick="joms.gallery.displayPhoto(joms.gallery.nextPhoto()); joms.photos.photoSlider.switchPhoto();"><img src="" height="50" alt="" class="hidden-phone" /></div>
			<div class="photoAction _prev" onclick="joms.gallery.displayPhoto(joms.gallery.prevPhoto()); joms.photos.photoSlider.switchPhoto();"><img src="" height="50" alt="" class="hidden-phone" /></div>
		</div>

		<div class="photoTags">
			<div class="photoTagActions">
				<!-- <button class="photoTagAction _select" onclick="joms.gallery.selectNewPhotoTagFriend();"><?php echo JText::_('COM_COMMUNITY_SELECT_PERSON');?></button> -->
				<button class="photoTagAction _cancel" onclick="joms.gallery.cancelNewPhotoTag(); cWindowHide();"><?php echo JText::_('COM_COMMUNITY_CANCEL');?></button>

				<!-- autocomplete friends selection -->
				<div style="z-index: 10000;width:200px;border:1px solid;min-height:39px;position: absolute;background:#FFF;bottom:-48px;left:-25px" id="taggingAutocompleteContainer">
					<input type="text" placeholder="<?php echo JText::_('COM_COMMUNITY_INVITE_TYPE_YOUR_FRIEND_NAME'); ?>" id="photoTagQuery" style="border: 1px solid #DDDDDD;margin-top: 5px;width:180px"/>
				</div>
			</div>


		</div>

		<div class="photoLoad"></div>

		<div class="cMedia-Option">

			<ul class="cMedia-Options cResetList cFloatedList clearfix">

				<li title="<?php echo JText::_('COM_COMMUNITY_VIDEOS_HITS') ?>">
					<i class="com-icon-chart"></i>
					<span>
						<strong class="photoHitsText" id="photo-hits"><?php echo $default->hits; ?></strong>
					</span>
				</li>
			<?php if( ($isOwner || $isAdmin) && ($photo->storage == 'file') ) { ?>
				<li>
					<a title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_LEFT'); ?>" href="javascript:void(0);"  class="photoRotaterActions" onclick="joms.gallery.rotatePhoto('left')">
						<i class="com-icon-rotate-anticlock"></i><span class="hidden-phone"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_LEFT'); ?></span>
					</a>
				</li>
				<li>
					<a title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_RIGHT'); ?>" href="javascript:void(0);" class="photoRotaterActions" onclick="joms.gallery.rotatePhoto('right')">
						<i class="com-icon-rotate-clock"></i><span class="hidden-phone"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ROTATE_RIGHT'); ?></span>
					</a>
				</li>
			<?php } ?>
				<li class="cFloat-R">
					<div id="like-container" class="cMedia-Like"></div>
				</li>
			</ul>
		</div>

	</div>

	<?php }

	$groupid = JRequest::getVar('groupid', '', 'REQUEST');
	if(!empty($groupid))
	{
	?>
		<div class="uploadedBy" id="uploadedBy">
			<?php echo JText::sprintf('COM_COMMUNITY_UPLOADED_BY', CRoute::_('index.php?option=com_community&view=profile&userid='.$photoCreator->id), $photoCreator->getDisplayName()); ?>
		</div>
	<?php
	}
	?>

	<div class="photoCaption">
		<textarea class="photoCaptionText <?php if( $isOwner || $isAdmin ) { ?>editable<?php } ?>" <?php if(!( $isOwner || $isAdmin )) {?> disabled="disabled" <?php } ?>  maxlength="255" ><?php echo $default->caption;?></textarea>
	</div>

	<div class="photoDescription">
		<div class="photoSummary"></div>
	</div>

	<?php if( isset($allowTag) && ($allowTag)) { ?>
	<div class="photoTagging visible-desktop">
		<a id="startTagMode" href="javascript: void(0);" onclick="joms.gallery.startTagMode();" class="btn"><?php echo JText::_('COM_COMMUNITY_TAG_THIS_PHOTO'); ?></a>

		<div class="photoTagSelectFriend">
			<dl id="system-message" class="js-system-message" style="display:none;">
				<dt class="notice"><?php echo JText::_('COM_COMMUNITY_NOTICE');?></dt>
				<dd class="notice message fade">
					<ul>
						<li><?php echo JText::_('COM_COMMUNITY_PLEASE_SELECT_A_FRIEND'); ?></li>
					</ul>
				</dd>
			</dl>

			<label for="photoTagFriendFilter"><?php echo JText::_('COM_COMMUNITY_PHOTO_TAG_TYPE_FRIEND'); ?></label>
			<div class="photoTagFriendFilters">
				<input type="text" name="photoTagFriendFilter" class="photoTagFriendFilter" id="friend-search-filter" onkeyup="joms.gallery.filterPhotoTagFriend();"/>
			</div>

			<label><?php echo JText::_('COM_COMMUNITY_PHOTO_TAG_CHOOSE_FRIEND'); ?></label>
			<div class="photoTagFriends" id="community-invitation-list">
			<!-- HERE -->
			</div>
			<div id="community-invitation-loadmore">
			<!-- HERE -->
			</div>
		</div>

		<div class="photoTagFriendsActions">
			<button class="photoTagFriendsAction _select">[<?php echo JText::_('COM_COMMUNITY_SELECT_PERSON');?>]</button>
			<button class="photoTagFriendsAction _cancel">[<?php echo JText::_('COM_COMMUNITY_CANCEL');?>]</button>
		</div>

		<div class="photoTagInstructions">
			<?php echo JText::_('COM_COMMUNITY_PHOTO_TAG_INSTRUCTIONS'); ?>
			<button class="btn photoTagInstructionsAction" onclick="joms.gallery.stopTagMode();"><?php echo JText::_('COM_COMMUNITY_PHOTO_DONE_TAGGING'); ?></button>
		</div>
	</div>
	<?php } ?>




</div>


<?php
	if($photos || $default)
	{
?>
<script type="text/javascript" language="javascript">
if( typeof wallRemove !=='function' )
{
	function wallRemove( id )
	{
		if(confirm('<?php echo JText::_('COM_COMMUNITY_WALL_CONFIRM_REMOVE'); ?>'))
		{
			joms.jQuery('#wall_'+id).fadeOut('normal').remove();
			jax.call('community','photos,ajaxRemoveWall', id );
		}
	}
}

</script>

<div class="cLayout row-fluid">

	<div class="span8">
		<div class="cMain">
			<?php
			if( $showWall )
			{
			?>
			<!-- Load walls for this photo -->
			<div class="cWall-Header"><?php echo JText::_('COM_COMMUNITY_COMMENTS');?></div>
			<?php
			}
			?>
			<div id="community-photo-walls" class="cWall-Form"></div>
			<div id="wallContent" class="cWall-Content"></div>

			<script type="text/javascript" language="javascript">
                        joms.jQuery(window).load(function(){
				joms.gallery.init();
				joms.photos.photoSlider._init("slider_item", "image_thumb");
			});
			</script>
		</div><!--#cPhoto-->
			<?php
				}
			}
			else
			{
			?>
				<div id="no-photos"><?php echo JText::_('COM_COMMUNITY_NO_PHOTOS_AVAILABLE_FOR_PREVIEW');?></div>
			<?php
			}
			?>
	</div>

	<div class="span4">
		<div class="cSidebar">
			<div class="cModule app-box">
				<div class="photoTagsTitle"><?php echo JText::_('COM_COMMUNITY_PHOTOS_IN_THIS_PHOTO'); ?> </div>
				<div class="photoTextTags"></div>
			</div>
		</div>
	</div>

</div>
</div>