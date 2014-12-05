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
<div class="cStream-Video clearfix">
	<a class="cVideo-Thumb cFloat-L" href="<?php //echo $url;?>javascript:joms.walls.showVideoWindow('<?php echo $video->getId(); ?>')">
		<img alt="<?php echo $this->escape( $video->getTitle() );?>" src="<?php echo $video->getThumbnail();?>"/>
		<b><?php echo $duration;?></b>
	</a>

	<div class="cVideo-Content">
		<b class="cVideo-Title">
			<a href="javascript:joms.walls.showVideoWindow('<?php echo $video->getId(); ?>')"><?php echo $video->getTitle(); ?></a>
		</b>
		<?php if ( $video->getDescription() ) { ?>
		<div class="cVideo-Desc">
			<?php echo JHTML::_('string.truncate', $video->getDescription() , $config->getInt('streamcontentlength'));?>
		</div>
		<?php } ?>
	</div>
</div>