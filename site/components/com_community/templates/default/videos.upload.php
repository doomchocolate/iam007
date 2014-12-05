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

$browser = JBrowser::getInstance();
if ($browser->isBrowser('msie') && ($browser->getVersion()== '9.0')) {
    $ieMode = true;
}else {
    $ieMode = false;
}
?>


<form name="uploadVideo" id="uploadVideo" class="form-horizontal" method="post" action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=upload');?>" enctype="multipart/form-data">

	<div class="control-group">
		<label class="label-filetype" for="creator-upload">
			<?php if ($ieMode ) { ?>
				<input type="file" name="videoFile" id="file-upload" class="required" onChange='' style="" />
			<?php } else { ?>
				<a class="btn btn-warning" href="javascript:joms.jQuery('#file-upload').click(); void(0);" style="display: block"><?php echo JText::_('COM_COMMUNITY_VIDEOS_SELECT_VIDEO_FILE');?></a>
				<input type="file" name="videoFile" id="file-upload" class="required" onChange='joms.videos.checkSize(this)' <?php if($browser->isBrowser('msie')){?>style="display:none;" <?php }?>/>
			<?php } ?>
		</label>
		<span class="help-block"><?php echo JText::sprintf('COM_COMMUNITY_MAXIMUM_UPLOAD_LIMIT', $uploadLimit); ?></span>
	</div>

  <div class="control-group">
    <label for="videoTitle" class="control-label">
			<?php echo JText::_('COM_COMMUNITY_VIDEOS_TITLE'); ?><span class="required-sign"> *</span>
		</label>
    <div class="controls">
      <input type="text" id="videoTitle" name="title" class="input-block-level required" size="35"  maxlength="255" />
    </div>
  </div>

  <div class="control-group">
		<label for="description" class="control-label">
			<?php echo JText::_('COM_COMMUNITY_VIDEOS_DESCRIPTION'); ?>
		</label>
    <div class="controls">
      <textarea id="description" name="description" class="input-block-level"></textarea>
    </div>
  </div>

	<?php if ($enableLocation) { ?>
  <div class="control-group">
		<label for="location" class="control-label">
			<?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION');?>
		</label>
		<div class="controls">
			<input name="location" id="location" type="text" size="35" value ="" class="input-block-level location"/>
			<span class="help-block"><?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION_DESCRIPTION'); ?></span>
    </div>
  </div>
	<?php } ?>

  <div class="control-group">
		<label for="category" class="control-label">
			<?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY'); ?>
		</label>
    <div class="controls">
      <?php echo $list['category']; ?>
    </div>
  </div>

	<?php if ($creatorType != VIDEO_GROUP_TYPE) { ?>
  <div class="control-group">
		<label for="category" class="control-label">
			<?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE'); ?>
		</label>
		<div class="controls">
			<?php echo CPrivacy::getHTML( 'permissions', $permissions, COMMUNITY_PRIVACY_BUTTON_LARGE, array(), 't' ); ?>
    </div>
  </div>
	<?php } ?>

  <div class="control-group">
    <label class="control-label"></label>
    <div class="controls">
      <span class="help-block"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span>
			<?php if($videoUploadLimit > 0 && $videoUploaded/$videoUploadLimit>=COMMUNITY_SHOW_LIMIT) { ?>
			<span class="help-block"><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_LIMIT_STATUS', $videoUploaded, $videoUploadLimit ); ?></span>
			<?php } ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label"></label>
    <div class="controls">
    	<button class="btn btn-primary" onclick="joms.videos.submitUploadVideo();">
			<?php echo '' . JText::_('COM_COMMUNITY_VIDEOS_UPLOAD') . ''; ?>
			</button>
    </div>
  </div>

	<input type="hidden" name="creatortype" value="<?php echo $creatorType; ?>" />
	<input type="hidden" name="groupid" value="<?php echo $groupid; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>

</form>