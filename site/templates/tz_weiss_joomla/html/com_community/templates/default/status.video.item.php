<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>
<div id="video-<?php echo $video->id; ?>" class="compose-video clearfix">

		<div class="row-fluid">
			<div class="cVideo-Thumb span4">
				<img src="<?php echo $video->getThumbnail(); ?>" width="<?php echo $videoThumbWidth; ?>" height="<?php echo $videoThumbHeight; ?>" alt="" />
				<b><?php echo $video->getDurationInHMS(); ?></b>

			</div>
			<div class="cVideo-Content span8">
				<h5 class="reset-gap"><?php echo $video->getTitle(); ?></h5>
				<p><?php echo $this->escape( JHTML::_('string.truncate', $video->description , VIDEO_TIPS_LENGTH )); ?></p>
				<a class="btn btn-warning creator-change-video clearfix" href="javascript: void(0);"><?php echo JText::_('COM_COMMUNITY_CHANGE_VIDEO'); ?></a>
			</div>
		</div>

		<div class="compose-category">
			<label><?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY');?></label>
			<div class="span8 pull-right">
			<?php echo $categoryHTML; ?>
			<script type="text/javascript">
				function updateCategoryId()
				{
					var catid = joms.jQuery('#category_id').val();
					jax.call('community','videos,ajaxSetVideoCategory', '<?php echo $video->id; ?>', catid );
				}
			</script>
			</div>
		</div>

</div>