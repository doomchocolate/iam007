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
<div class="cLayout cProfile-UploadAvatar">
	<?php if ($firstLogin) { ?>
	<div class="skipLink">
		<p>
			<a href="<?php echo $skipLink; ?>"class="btn"><?php echo JText::_('COM_COMMUNITY_SKIP_UPLOAD_AVATAR'); ?></a>
		</p>
	</div>
	<?php } ?>

	<!-- JS and CSS for imagearea selection -->
	<link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>components/com_community/assets/imgareaselect/css/imgareaselect-default.css" />
	<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_community/assets/imgareaselect/scripts/jquery.imgareaselect.min.js"></script>


	<div class="app-box upload-avatar">
		<form name="jsform-profile-uploadavatar" action="<?php echo CRoute::getURI(); ?>" id="uploadForm" method="post" enctype="multipart/form-data">
			<input type="file" id="file-upload" name="Filedata" />
			<input class="btn btn-primary" size="30" type="submit" id="file-upload-submit" value="<?php echo JText::_('COM_COMMUNITY_BUTTON_UPLOAD_PICTURE'); ?>">
			<input type="hidden" name="action" value="doUpload" />
			<input type="hidden" name="profileType" value="<?php echo $profileType;?>" />
		</form>

		<?php if( $uploadLimit != 0 ){ ?>
		<div class="small">
			<?php echo JText::sprintf('COM_COMMUNITY_MAX_FILE_SIZE_FOR_UPLOAD' , $uploadLimit ); ?>
		</div>
		<?php } ?>

		<?php if (!$firstLogin) {?>
		<div class="app-box-footer">
			<a href="javascript:void(0);" onclick="joms.profile.confirmRemoveAvatar();" class="btn btn-danger"><?php echo JText::_('COM_COMMUNITY_REMOVE_PROFILE_PICTURE');?></a>
		</div>
		<?php } ?>
	</div>


	<div class="app-box show-avatar">
		<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_PICTURE_LARGE_HEADING');?></h3>

		<div id="imagePreview" class="imagePreview">
            <?php $largeAvatar = (isset($largeAvatar) ? $largeAvatar : $user->getAvatar() ); ?>
			<img id="large-profile-pic" src="<?php echo $largeAvatar;?>" alt="<?php echo JText::_('COM_COMMUNITY_LARGE_PICTURE_DESCRIPTION'); ?>" class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_LARGE_PICTURE_DESCRIPTION'); ?>" />
		</div>

		<div class="app-box-footer">
			<?php
			if (!$firstLogin)
			{
			?>
				<a href="javascript:updateThumbnail()" id="update-thumbnail" class="btn btn-primary"><?php echo JText::_('COM_COMMUNITY_UPDATE_THUMBNAIL'); ?></a>
				<a href="javascript:saveThumbnail()" id="update-thumbnail-save" class="btn btn-primary" style="display:none"><?php echo JText::_('COM_COMMUNITY_THUMBNAIL_SAVE'); ?></a>
			<?php
			}
			else
			{
			?>
				<a href="<?php echo $skipLink; ?>"class="btn btn-primary saveButton"><span><?php echo JText::_('COM_COMMUNITY_NEXT'); ?></span></a>
			<?php
			}
			?>
			<div id="update-thumbnail-guide" style="display: none;"><?php echo JText::_('COM_COMMUNITY_UPDATE_THUMBNAIL_GUIDE'); ?></div>
		</div>
	</div>

	<div class="app-box show-thumbnail">
		<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_PICTURE_THUMB_HEADING');?></h3>
		<div class="app-box-content">
			<img id="thumbnail-profile-pic" src="<?php echo $user->getThumbAvatar();?>" alt="" title="" />
		</div>
	</div>






	<!-- Start thumbnail selection -->
	<script type="text/javascript">
	joms.jQuery('#large-profile-pic').load(function () {
		// Recalculate max height of the large avatar. We know the max width is 160
		// but for landscape, height can be smaller
		var imgH = this.clientHeight;
		var imgW = 160;
		if(imgH < 160){imgW = imgH;}
		if(imgH > 160){imgH = 160;}
		// Create select object
		joms.jQuery('#large-profile-pic').imgAreaSelect(
			{
			  parent:'.app-box.show-avatar',
			  maxWidth: 160, maxHeight: 160, handles: true ,aspectRatio: '1:1',
			  x1: 0, y1: 0, x2: imgW, y2: imgH,
			  show: false, hide: true, enable: false,
			  minHeight:<?php echo COMMUNITY_SMALL_AVATAR_WIDTH; ?>, minWidth:<?php echo COMMUNITY_SMALL_AVATAR_WIDTH; ?>
			}
		);

	});

	function saveThumbnail(){
		var ias = joms.jQuery('#large-profile-pic').imgAreaSelect({ instance: true });
		var obj = ias.getSelection();
		jax.call('community', 'profile,ajaxUpdateThumbnail', obj.x1, obj.y1, obj.width, obj.height );

		// Hide it
		ias.setOptions({ show: false, hide: true, enable:false });
		ias.update();

		// Show the update button, but hide the save button
		joms.jQuery('#update-thumbnail').show();
		joms.jQuery('#update-thumbnail-save').hide();
		joms.jQuery('#update-thumbnail-guide').hide();
	}

	function updateThumbnail()
	{
		var ias = joms.jQuery('#large-profile-pic').imgAreaSelect({ instance: true });
		ias.setOptions({ show: true, hide: false, enable:true });
		ias.update();

		// Show the save button, but hide the update button
		joms.jQuery('#update-thumbnail').hide();
		joms.jQuery('#update-thumbnail-save').show();
		joms.jQuery('#update-thumbnail-guide').show();
	}

	function refreshThumbnail(){
		var src = joms.jQuery('#thumbnail-profile-pic').attr('src');
		joms.jQuery('#thumbnail-profile-pic').attr('src', src+'?'+Math.random());
	}
	</script>
</div>