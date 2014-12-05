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

<?php if( $videos ){ ?>
<div class="cModule cGroups-VideoUpdates app-box">
	<!-- top title -->
	<h3 class="app-box-header"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPDATES'); ?></h3>
	<div class="app-box-content">
		<ul class="cThumbsList cResetList clearfix">	
		<?php	foreach($videos as $video){ ?>
		<li>
			<!-- thumbnail for videos -->
			<a class="cVideo-Thumb" href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=video&groupid='.$video->getId().'&videoid='.$video->getId());?>">
				<img class="cAvatar cMediaAvatar jomNameTips" src="<?php echo $video->getThumbnail(); ?>" title="<?php echo $video->getTitle(); ?>" />
				<b class="cVideo-Duration"><?php echo $video->getDurationInHMS(); ?></b>
			</a>
		</li> 
		<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>
