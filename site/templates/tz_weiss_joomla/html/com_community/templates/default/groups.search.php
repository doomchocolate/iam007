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
<div class="cLayout cSearch Groups">
	<form name="jsform-groups-search" method="get" action="" class="cSearch-Form cForm">
		<?php if(!empty($beforeFormDisplay)){ ?>
			<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
				<?php echo $beforeFormDisplay; ?>
			</table>
		<?php } ?>

		<div class="row-fluid">
			<div class="span6">
				<select name="catid" id="catid" class="required input-block-level" >
					<option value="0" selected><?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_TIPS');?></option>
					<?php
					foreach( $categories as $category )
					{
					?>
						<option value="<?php echo $category->id; ?>" <?php if( $category->id == $catId ) { ?>selected<?php } ?>>
							<?php echo JText::_( $this->escape($category->name) ); ?>
						</option>
					<?php
					}
					?>
				</select>
			</div>
			<div class="span6">
				<span class="input-append">
					<input type="text" id="q" class="input-block-level" name="search" value="<?php echo $this->escape($search); ?>"  placeholder="<?php echo JText::_('COM_COMMUNITY_SEARCH_GROUP_PLACEHOLDER');?>" />
					<input type="submit" value="<?php echo JText::_('COM_COMMUNITY_SEARCH_BUTTON');?>" class="btn btn-primary" />
				</span>
			</div>
		</div>

		<?php if(!empty($afterFormDisplay)){ ?>
			<table class="formtable" cellspacing="1" cellpadding="0" >
				<?php echo $afterFormDisplay; ?>
			</table>
		<?php } ?>

		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" value="com_community" name="option" />
		<input type="hidden" value="groups" name="view" />
		<input type="hidden" value="search" name="task" />
		<input type="hidden" value="<?php echo CRoute::getItemId();?>" name="Itemid" />
	</form>
	
	<?php
	if( $posted )
	{
	?>
	<div class="cSearch-Result">
		<p>
			<span>
				<?php echo JText::sprintf( 'COM_COMMUNITY_GROUPS_SEARCH_RESULT' , $search ); ?>
			</span>
			<span class="cFloat-R">
				<?php echo JText::sprintf( (CStringHelper::isPlural($groupsCount)) ? 'COM_COMMUNITY_GROUPS_SEARCH_RESULT_TOTAL_MANY' : 'COM_COMMUNITY_GROUPS_SEARCH_RESULT_TOTAL' , $groupsCount ); ?>
			</span>
			<div style="clear:both;"></div>
		</p>

		<?php echo $groupsHTML; ?>
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