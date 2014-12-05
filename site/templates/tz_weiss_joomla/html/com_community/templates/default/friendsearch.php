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
$mainframe	= JFactory::getApplication();
$jinput 	= $mainframe->input;	
?>
<div class="people-search-form">
	<form name="jsform-search" method="get" action="">
		<div>
			<input type="text" class="input text" size="40" name="q" value="<?php echo $this->escape( $query ); ?>" />
			<input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH_BUTTON_TEMP');?>" class="button" name="Search" />
		</div>
		<div class="labelradio">
			<label class="lblradio"><input type="checkbox" class="input checkbox" name="avatar" id="avatar" style="margin-right: 5px;" value="1" class="radio"<?php echo ($avatarOnly) ? ' checked="checked"' : ''; ?>><?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_ONLY'); ?></label>
		</div>

		<input type="hidden" name="option" value="com_community" />
		<input type="hidden" name="view" value="friends" />
		<input type="hidden" name="task" value="friendsearch" />
		<input type="hidden" name="userid" value="<?php echo $jinput->request->get('userid', ''); //JRequest::getVar( 'userid', '', 'REQUEST' ); ?>" />
		<input type="hidden" name="Itemid" value="<?php echo CRoute::_getDefaultItemid();?>">
	</form>
</div>
<?php
if( $results )
{
?>
	<h2>
		<?php echo JText::_('COM_COMMUNITY_SEARCH_RESULTS');?>
	</h2>
	<?php echo $resultHTML;?>
<?php		
}
else if( empty( $results ) && !empty( $query ) )
{
?>
	<div class="people-not-found">
		<?php echo JText::_('COM_COMMUNITY_NO_RESULT_FROM_SEARCH');?>
	</div>
<?php
}
?>