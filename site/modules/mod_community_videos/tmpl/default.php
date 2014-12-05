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
<script type="text/javascript">joms.filters.bind();</script>
<div class="js-module js-mod-videos">
	<div id="latest-videos-nav" class="app-box-filter">
		<i class="loading cFloat-R"></i>
		<div>
			<a class="newest-videos active-state" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_VIDEOS_NEWEST') ?></a>
			<b>&nbsp;&nbsp;</b>
			<a class="featured-videos" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_FEATURED') ?></a>
			<b>&nbsp;&nbsp;</b>
			<a class="popular-videos" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_VIDEOS_POPULAR') ?></a>
		</div>
	</div>
	<div id="latest-videos-container" class="app-box-content">
		<?php if(!empty($videos)) { ?>
		<div class="js-row-fluid">
			<?php foreach( $videos as $video ) { ?>
			<div class="js-col2 bottom-gap">
				<a class="cVideo-Thumb" href="<?php echo $video->getURL(); ?>">
					<img src="<?php echo $video->getThumbNail(); ?>" alt="<?php echo $video->getTitle(); ?>" class="cAvatar Video cMediaAvatar jomNameTips"  title="<?php echo CStringHelper::escape($video->title); ?>" />
					<b><?php echo $video->getDurationInHMS(); ?></b>
				</a>
			</div>
			<?php } ?>
		</div>
		<?php } else {
		?>
		<div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO'); ?></div>
		<?php } ?>
	</div>

	<div class="app-box-footer">
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos'); ?>"><?php echo JText::_('COM_COMMUNITY_VIDEOS_ALL'); ?></a>
	</div>
</div>