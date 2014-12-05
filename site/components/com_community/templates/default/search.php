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
<div class="cLayout cSearch">
	<form name="jsform-search" method="get" action="" class="cSearch-Form cForm">

		<div class="input-append">
			<input type="text" id="q" class="input-large" name="q" value="<?php echo $this->escape( $query ); ?>" placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_PEOPLE_PLACEHOLDER');?>" />
			<input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH_BUTTON_TEMP');?>" class="btn btn-primary " name="Search" />
		</div>

		<div class="cSearch-Helper">
			<label class="label-checkbox">
				<input type="checkbox" name="avatar" id="avatar" value="1" class="input checkbox "<?php echo ($avatarOnly) ? ' checked="checked"' : ''; ?>>
				<?php echo JText::_('COM_COMMUNITY_EVENTS_AVATAR_ONLY'); ?>
			</label>
		</div>

		<input type="hidden" name="option" value="com_community" />
		<input type="hidden" name="view" value="search" />
		<input type="hidden" name="Itemid" value="<?php echo CRoute::_getDefaultItemid();?>">
	</form>

	<?php
	if( $results )
	{
	?>
	<div class="cSearch-Result">
		<p>
			<b><?php echo JText::_('COM_COMMUNITY_SEARCH_RESULTS');?></b>
		</p>
		<?php echo $resultHTML;?>
	</div>
	<?php
	}
	else if( empty( $results ) && !empty( $query ) )
	{
	?>
	<div class="cAlert cEmpty">
		<?php echo JText::_('COM_COMMUNITY_NO_RESULT_FROM_SEARCH');?>
	</div>
	<?php
	}
	?>

	<div class="cSearch-Jumper">
	<?php
	echo JText::_('COM_COMMUNITY_SEARCH_FOR');

	foreach ($searchLinks as $key => $value)
	{
	?>
		<a href="<?php echo $value; ?>"><?php echo $key; ?></a>
	<?php
	}
	?>
	</div>
</div>