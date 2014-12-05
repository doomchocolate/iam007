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
<div class="cLayout cEvents-SendMail">
<form name="jsform-events-sendmail" action="<?php echo CRoute::getURI();?>" method="post" class="cForm event-email">
	<ul class="cFormList cFormVertical cResetList">
		<li>
			<div class="cNotice"><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_EMAIL_DESCRIPTION', $event->getMembersCount( COMMUNITY_EVENT_STATUS_ATTEND ) );?></div>
		</li>
		<li>
			<label class="form-label">*<?php echo JText::_('COM_COMMUNITY_TITLE'); ?>:</label>
			<div class="form-field">
				<input type="text" name="title" value="<?php echo $this->escape($title);?>" class="input text required" style="width: 95%" />
			</div>
		</li>
		<li>
			<label class="form-label"><?php echo JText::_('COM_COMMUNITY_MESSAGE'); ?>:</label>
			<div class="form-field"><?php echo $editor->displayEditor( 'message',  $message , '100%', '450', '10', '20' , false ); ?></div>
		</li>
		<li class="has-seperator">
			<span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span>
		</li>
		<li class="form-action">
			<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SEND'); ?>">
			<input type="hidden" name="eventid" value="<?php echo $event->id;?>">
			<?php echo JHTML::_( 'form.token' ); ?>
		</li>
	</ul>	
</form>
</div>