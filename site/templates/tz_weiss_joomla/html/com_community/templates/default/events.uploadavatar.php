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
<div class="cModule">
	<p class="info"><?php echo JText::_('COM_COMMUNITY_EVENTS_UPLOAD_DESC');?></p>
	<form name="jsform-events-uploadavatar" action="<?php echo CRoute::getURI();?>" method="post" enctype="multipart/form-data">
		<?php echo $beforeFormDisplay;?>
	    <input type="file" name="filedata" size="40" class="button" />
	    <?php echo $afterFormDisplay;?>
	    <input type="submit" value="<?php echo JText::_('COM_COMMUNITY_UPLOAD_BUTTON');?>" class="button" />
	    <input type="hidden" name="eventid" value="<?php echo $eventId; ?>" />
	    <input type="hidden" name="action" value="avatar"/>
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php if( $uploadLimit != 0 ){ ?>
	<p class="info"><?php echo JText::sprintf('COM_COMMUNITY_MAX_FILE_SIZE_FOR_UPLOAD' , $uploadLimit ); ?></p>
	<?php } ?>
</div>

<div class="cModule avatarPreview leftside">
	<h3><?php echo JText::_('COM_COMMUNITY_EVENTS_LARGE_AVATAR_ERROR');?></h3>
	<p><?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_LARGE_DESC');?></p>
	<img src="<?php echo $avatar;?>" alt="<?php echo JText::_('COM_COMMUNITY_EVENTS_LARGE_AVATAR_ERROR');?>" border="0" />
</div>

<div class="cModule avatarPreview rightside">
	<h3><?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_THUMB');?></h3>
	<p><?php echo JText::_('COM_COMMUNITY_EVENTS_THUM_DESC');?></p>
	<img class="event-thumb" src="<?php echo $thumbnail;?>" alt="<?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_THUMB');?>" border="0" />
</div>