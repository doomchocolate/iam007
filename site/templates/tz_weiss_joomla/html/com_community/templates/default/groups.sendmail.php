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
<!--FORM-->
<form name="jsform-groups-sendmail" action="<?php echo CRoute::getURI();?>" method="post" class="event-email">
	<!--INSTRUCTION-->
	<div class="instruction"><?php echo JText::sprintf('COM_COMMUNITY_GROUP_SEND_EMAIL_TO_MEMBERS_DESCRIPTION', $group->getMembersCount() );?></div>
	<!--INSTRUCTION-->
	<!--EMAIL TITLE-->
	<label>*<?php echo JText::_('COM_COMMUNITY_TITLE'); ?>:</label>
	<div class="event-email-row"><input type="text" name="title" value="<?php echo $this->escape($title);?>" class="input text required" /></div>
	
	<!--EMAIL MESSAGE-->
	<label><?php echo JText::_('COM_COMMUNITY_MESSAGE'); ?>:</label>
	<div class="event-email-row"><?php echo $editor->displayEditor( 'message',  $message , '98%', '450', '10', '20' , false ); ?></div>
	
	<br style="clear:both"/><br/>
	<div class="event-email-row"><span class="hints"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span></div>
	<br/>
	<!--SUBMIT BUTTON-->
	
	<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SEND'); ?>">
	<input type="hidden" name="groupid" value="<?php echo $group->id;?>">
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<!--FORM-->