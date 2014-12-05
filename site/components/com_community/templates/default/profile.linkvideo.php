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
<div class="cLayout cProfile-LinkVideo">
	<div class="cPageActions clearfix">

		<?php if(!empty($video->id)){ ?>
		<div class="cPageAction cFloat-R">
			<div class="cPageDelete page-action">
				<a onclick="joms.videos.removeConfirmProfileVideo(<?php echo $video->creator; ?>, <?php echo $video->getId(); ?>);" href="javascript:void(0);">
					<i class="com-icon-block-shade"></i>
					<span><?php echo JText::_('COM_COMMUNITY_VIDEOS_REMOVE_PROFILE_VIDEO'); ?></span>
				</a>
			</div>
		</div>
		<?php } ?>
		<b class="cFloat-L"><?php echo JText::_('COM_COMMUNITY_VIDEOS_CURRENT_PROFILE_VIDEO_HEADING');?></b>
	</div>

	<?php if(!empty($video->id)){ ?>
		<div class="cVideo-Screen video-player clearfix">
			<?php echo $video->getPlayerHTML(); ?>
		</div>
		<div class="cMedia-Description">
			<b><?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_DESCRIPTION'); ?></b>
			<p><?php echo $this->escape($video->getDescription()); ?></p>
		</div>

	<?php } else { ?>
		<div style="text-align: center;"><img src="<?php echo JURI::root(); ?>/components/com_community/assets/video_thumb.png" alt="<?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_NOT_EXIST'); ?>" class="jomNameTips" title="<?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_NOT_EXIST'); ?>" /></div>
		<p align="center"><?php echo JText::_('COM_COMMUNITY_VIDEOS_NO_USER_PROFILE_VIDEO'); ?></p>
	<?php } ?>

	<?php
	echo $sortings;

	if ($videos) { ?>
		<div class="cVideoIndex">
			<ul class="cMedia-ThumbList Videos cResetList cFloatedList clearfix">
				<?php
				$x = 1;
				$i = 0;
				foreach($videos as $vid) {
					$v = JTable::getInstance( 'Video' , 'CTable' );
					$v->load($vid->id);
					$v->_wallcount = $vid->_wallcount;
				?>
					<li>
						<div class="cMedia-Box">

								<?php
								/**
								 * ----------------------------------------------------------------------------------------------------------
								 * VIDEO THUMBNAIL
								 * ----------------------------------------------------------------------------------------------------------
								 */
								?>
								<div class="cMedia-VideoCover">
									<?php if ($v->status=='pending'): ?>
										<img class="cVideo-Thumb" src="<?php echo JURI::root(); ?>/components/com_community/assets/video_thumb.png" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;" alt="<?php echo $v->getTitle(); ?>" />
									<?php else: ?>
										<a class="cVideo-Thumb" href="<?php echo $v->getURL(); ?>">
											<img src="<?php echo $v->getThumbnail(); ?>" width="<?php echo $videoThumbWidth; ?>" height="<?php echo $videoThumbHeight; ?>" alt="<?php echo $v->getTitle(); ?>" />
											<b class="cVideo-Duration"><?php echo $v->getDurationInHMS(); ?></b>
										</a>
									<?php endif; ?>

									<div class="cMedia-Actions small">
										<div>
											<a href="javascript:void(0);" onclick="joms.videos.linkConfirmProfileVideo('<?php echo $v->getId(); ?>', '<?php echo $redirectUrl;?>');" title="<?php echo JText::_('COM_COMMUNITY_VIDEOS_PROFILE_VIDEO_LINK') ?>">
												<i class="com-icon-profile-link"></i>
											</a>
										</div>

									</div><!-- end .album-actions -->

								</div> <!-- end .ccMedia-Avatar -->



								<div class="cMedia-Summary">
									<div class="cMedia-Title">
										<?php
											if ($v->status=='pending') {
												echo $v->getTitle();
											} else {
											?>
											<a href="<?php echo $v->getURL(); ?>"><?php echo $v->getTitle(); ?></a>
										<?php } ?>
									</div>

									<div class="cMedia-Details small">
										<div class="cMedia-Hit"><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_HITS_COUNT', $v->getHits()) ?></div>
										<div class="cMedia-LastUpdate"><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_LAST_UPDATED', $v->getLastUpdated());?></div>
									</div>
								</div><!-- end .cMedia-Summary -->

						</div><!-- END .cMedia-Box -->
					</li>
				<?php
					$i++;

					if( $i % 4 == 0 )
					{
						$i = 0;
				?>
				<div class="clearfull"></div>
				<?php } ?>

				<?php
				} // end foreach
				?>
			</ul><!-- end .cVideoItems -->
		</div><!-- end .cVideoIndex-->

	<?php
	}
	else
	{
		$isMine	= ( isset($video) && $video->creator==$my->id);
		$msg	= $isMine ? JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO') : JText::sprintf('COM_COMMUNITY_VIDEOS_NO_VIDEOS', $my->getDisplayName());
		?>
			<div><?php echo $msg; ?></div>
		<?php
	}
	?>

	<?php
	if ( $pagination->getPagesLinks() )
	{
	?>
	<div class="cPagination">
		<?php echo $pagination->getPagesLinks(); ?>
	</div>
	<?php
	}
	?>
</div>