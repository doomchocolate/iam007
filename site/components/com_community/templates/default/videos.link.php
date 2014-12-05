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
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_community/assets/validate-1.5.min.js"></script>
<form name="linkVideo" id="linkVideo" class="cForm community-form-validate" method="post" action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=link');?>">
	<ul class="cFormList cFormVertical cResetList">
		<li>
			<label for="videoLinkUrl" class="form-label">
				<?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK_URL');?><span class="required-sign"> *</span>
			</label>
			<div class="form-field">
				<input type="text" id="videoLinkUrl" name="videoLinkUrl" class="input-block-level required" value="" />

				<div class="form-helper">
					<?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK_ADDTYPE_DESC'); ?>
					<div style="margin-top: 5px">
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/youtube.png' ?>" title="YouTube" alt="YouTube"/></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/yahoo.png' ?>" title="Yahoo Video" alt="Yahoo Video" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/myspace.png' ?>" title="MySpace Video" alt="MySpace Video" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/flickr.png' ?>" title="Flickr" alt="Flickr" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/vimeo.png' ?>" title="Vimeo" alt="Vimeo" /></span>
						<!-- <span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/metacafe.png' ?>" title="Metacafe" alt="Metacafe" /></span> -->
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/bliptv.png' ?>" title="Blip.tv" alt="Blip.tv" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/dailymotion.png' ?>" title="Dailymotion" alt="Dailymotion" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/break.png' ?>" title="Break" alt="Break" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/liveleak.png' ?>" title="Live Leak" alt="Live Leak" /></span>
						<span><img src="<?php echo JURI::root() . 'components/com_community/assets/videoicons/viddler.png' ?>" title="Viddler" alt="Viddler" /></span>
					</div>
				</div>
			</div>
		</li>
		<?php if ($enableLocation) { ?>
		<li>
			<label for="location">
				<?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION');?>
			</label>
			<div class="form-field">
				<input name="location" id="location" type="text" size="35" value ="" class="input-block-level" />
				<div class="small"><?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION_DESCRIPTION'); ?></div>
			</div>
		</li>
		<?php } ?>
		<li>
			<label for="category">
				<?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY');?>
			</label>
			<div class="form-field">
				<?php echo $list['category']; ?>
			</div>
		</li>
		<?php if ($creatorType != VIDEO_GROUP_TYPE) { ?>
		<li>
			<label>
				<?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE');?>
			</label>
			<div class="form-field">
				<?php echo CPrivacy::getHTML( 'permissions', $permissions, COMMUNITY_PRIVACY_BUTTON_LARGE, array(), 't' ); ?>
			</div>
		</li>
		<?php }?>
		<li>
			<div class="form-field">
				<div class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></div>
				<?php if($videoUploadLimit > 0 && $videoUploaded/$videoUploadLimit>=COMMUNITY_SHOW_LIMIT) { ?>
				<div class="form-helper"><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_LIMIT_STATUS', $videoUploaded, $videoUploadLimit ); ?></div>
				<?php }?>
			</div>
		</li>
		<li>
			<?php echo '<button class="btn btn-primary" onclick="joms.videos.submitLinkVideo();">' . JText::_('COM_COMMUNITY_VIDEOS_LINK') . '</button>'; ?>
		</li>
	</ul>
	<input type="hidden" name="creatortype" value="<?php echo $creatorType; ?>" />
	<input type="hidden" name="groupid" value="<?php echo $groupid; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script type="text/javascript">
	cvalidate.init();
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');
</script>
