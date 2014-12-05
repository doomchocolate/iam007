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
<div style="margin-bottom: 10px;font-weight: 700;"><?php echo JText::_('COM_COMMUNITY_EMAIL_NOT_UPDATED');?></div>
<form name="facebook-email-update" id="facebook-email-update" action="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=save');?>" method="POST">
<div>
	<span><?php echo JText::_('COM_COMMUNITY_EMAIL');?></span><input type="text" class="input text" name="email" value="" size="50" style="margin-left: 10px;" />
	<input type="hidden" name="emailpass" value="" />
	<input type="hidden" name="id" value="<?php echo $my->id;?>" />
	<input type="hidden" name="id" value="<?php echo $my->get('id');?>" />
	<input type="hidden" name="gid" value="<?php echo $my->get('gid');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</div>
</form>