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

<?php if(!empty($data)) { ?>
<div class="js-row-fluid">
<?php foreach( $data as $video ) { ?>
	<div class="js-col2 bottom-gap">
		<a class="cVideo-Thumb" href="<?php echo $video->getURL(); ?>">
			<img src="<?php echo $video->getThumbNail(); ?>" alt="<?php echo $video->getTitle(); ?>" class="cAvatar Video cMediaAvatar jomNameTips"  title="<?php echo $this->escape($video->title); ?>" />
			<b><?php echo $video->getDurationInHMS(); ?></b>
		</a>
	</div>
<?php } ?>
</div>
<?php } else {
?>
<div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_VIDEOS_NO_VIDEO'); ?></div>
<?php } ?>
