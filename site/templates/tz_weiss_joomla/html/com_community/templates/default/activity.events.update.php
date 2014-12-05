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
<ul class ="cDetailList clrfix">
	<li class="avatarWrap">
		<a href="<?php echo ($event->contentid) ?  CUrlHelper::groupeventLink($event->id,$event->contentid) : CUrlHelper::eventLink($event->id); ?>"><img style="width: 64px; height: auto" class="cAvatar" src="<?php echo $event->getThumbAvatar();?>" /></a>
	</li>
	<li class="detailWrap">
		
		<strong><a href="<?php echo ($event->contentid) ?  CUrlHelper::groupeventLink($event->id,$event->contentid) : CUrlHelper::eventLink($event->id); ?>"><?php echo strip_tags($event->title); ?></a></strong>
		<small>
			<?php if (strlen(strip_tags($event->description))) echo JHTML::_('string.truncate', strip_tags($event->description) , $config->getInt('streamcontentlength')).'<br />';?>
			<?php echo $event->getStartDateHTML(); ?><br />
			<?php echo $event->getEndDateHTML(); ?><br />
			<?php echo strip_tags($event->location); ?><br />
		</small>
	</li>
</ul>
<div class="clr"></div>