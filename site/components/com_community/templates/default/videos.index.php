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

<div class="cIndex">
	<!--Featured Listing-->
	<?php echo $featuredHTML; ?><!--call video.featured.php -->

	<div class="row-fluid">

		<div class="span8">
			<div class="cMain">
				<?php echo $sortings; ?>

				<div class="cVideoIndex">
					<?php echo $videosHTML; ?><!--call video.list.php -->
				</div>
			</div><!--.cMain-->
		</div>

		<div class="span4">
			<div class="cSidebar">
				<!--Videos Category-->
				<div class="cModule cVideos-Categories app-box">
					<h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY');?></h3>
					<div class="app-box-content">
						<ul class="app-box-list for-menu cResetList">
							<li>
								<i class="com-icon-folder"></i>
								<?php if( $category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT ){ ?>
									<a href="<?php echo CRoute::_($allVideosUrl);?>"><?php echo JText::_( 'COM_COMMUNITY_VIDEOS_ALL_DESC' ); ?></a>
								<?php
									}
									else
									{
										$catid = '';
										if( $category->parent != 0) {
											$catid = '&catid=' . $category->parent;
										}
								?>
									<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos' . $catid ); ?>"><?php echo JText::_('COM_COMMUNITY_BACK_TO_PARENT'); ?></a>
								<?php } ?>
							</li>

							<?php if( $categories ): ?>
							<?php foreach( $categories as $row ): ?>
							<li>
								<i class="com-icon-folder"></i>
								<a href="<?php echo CRoute::_($catVideoUrl . $row->id ); ?>">
									<?php echo JText::_($this->escape($row->name)); ?>
								</a>
								<span class="label"><?php echo empty($row->count) ? '' : $row->count; ?></span>
							</li>
							<?php endforeach; ?>

							<?php else: ?>
							<?php if( $category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT ){ ?>
								<li><div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_NOITEM'); ?></div></li>

								<?php } ?>
							<?php endif; ?>
						</ul>
					</div>
				</div>

				<?php if (count($featuredVideoUsers)>1) { ?>
				<div class="cModule cVideos-Authors app-box">
					<h3 class="app-box-header cResetH"><?php echo JText::_('COM_COMMUNITY_VIDEOS_FEATURED_USERS');?></h3>
					<div class="app-box-content">
						<ul class="cThumbDetails cResetList">
							<?php
							$featuredUser = array();

							foreach($featuredVideoUsers as $featuredVideo) {

							if(!in_array($featuredVideo->creator, $featuredUser)) {
							?>
							<li>
								<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$featuredVideo->creator); ?>" class="cThumb-Avatar cFloat-L">
									<img class="cAvatar" src="<?php echo CFactory::getUser($featuredVideo->creator)->getThumbAvatar(); ?>" />
								</a>
								<div class="cThumb-Detail">
									<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$featuredVideo->creator); ?>" class="cThumb-Title">
										<?php echo $featuredVideo->getCreatorName();  ?>
									</a>
									<div class="cThumb-Brief small">
										<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$featuredVideo->creator); ?>"><?php echo $this->view('videos')->getUserTotalVideos($featuredVideo->creator); ?> <?php if($this->view('videos')->getUserTotalVideos($featuredVideo->creator)){ echo JText::_('COM_COMMUNITY_SEARCH_VIDEOS_TITLE'); } else { echo JText::_('COM_COMMUNITY_SINGULAR_VIDEO'); } ?></a>
									</div>
								</div>
							</li>
						<?php
								$featuredUser[] = $featuredVideo->creator;
							}
						} //end foreach ?>
						</ul>
					</div>
				</div>
				<?php } ?>


			</div><!--.cSidebar-->
		</div>

	</div>

</div><!--.index-->