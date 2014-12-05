 <?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<div id="community-event-join">
	<?php if($isMember){ ?>
		<p><?php echo JText::_('COM_COMMUNITY_EVENTS_ALREADY_MEMBER'); ?></p>
	<?php }else{ ?>
		<p><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_CONFIRM_INVITATION_REQUEST', $event->title );?></p>
	<?php } ?>
</div>