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
<div class="cLayout cProfile-DeleteProfile">
	<div class="ctitle"><h2><?php echo JText::_('COM_COMMUNITY_DELETE_PROFILE_TITLE'); ?></h2></div>

	<p><?php echo JText::_('COM_COMMUNITY_DELETE_PROFILE_DESCRIPTION'); ?></p>
	<p><span style="color:red;font-weight:bold"><?php echo JText::_('COM_COMMUNITY_DELETE_WARNING'); ?></span></p>

	<form method="post" action="<?php echo CRoute::getURI();?>" name="deleteProfile">

	<table class="formtable" cellspacing="1" cellpadding="0">
	<tr>
		<td class="key"></td>
		<td class="value">
			<input type="submit" class="button" value="<?php echo JText::_('COM_COMMUNITY_YES_DELETE_MY_PROFILE'); ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</td>
	</tr>
	</table>

	</form>
</div>