<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<script type="text/javascript" language="javascript">
/**
 * This function needs to be here because, Joomla calls it
 **/
 Joomla.submitbutton = function(action){
 	submitbutton( action );
 }

 function submitbutton(action)
{
	if(action == 'newcategory')
	{
		azcommunity.editEventCategory( 0 , '<?php echo JText::_('COM_COMMUNITY_CATEGORY_NEW'); ?>');
	}

	if(action == 'removecategory')
	{
		submitform(action);
	}
}
</script>
	<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">
	<table class="table table-bordered table-hover" cellspacing="1">
		<thead>
			<tr class="title">
				<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
				<th width="10">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
					<span class="lbl"></span>
				</th>
				<th style="text-align: left;">
					<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_NAME') , 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_PARENT'); ?>
				</th>
				<th style="text-align: left;">
					<?php echo JText::_('COM_COMMUNITY_CATEGORY_DESCRIPTION'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_COUNT'); ?>
				</th>
				<th>
					<?php echo JHTML::_('grid.sort',   JText::_('ID'), 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
			</tr>
		</thead>
<?php
		$i		= 0;

		foreach($this->categories as $category)
		{
			$count = empty($this->catCount[$category->id]->count)? '0' : $this->catCount[$category->id]->count;
?>
			<tr>
				<td align="center"><?php echo $i + 1; ?></td>
				<td>
					<?php echo JHTML::_('grid.id', $i++, $category->id); ?>
					<span class="lbl"></span>
				</td>
				<td>
					<?php echo JHTML::_('link', 'javascript:void(0);', $category->name, array('id' => 'event-title-' . $category->id , 'onclick'=>'azcommunity.editEventCategory(\'' . $category->id . '\',\'' . JText::_('COM_COMMUNITY_CATEGORY_EDIT') . '\');')); ?>
				</td>
				<td><?php echo $category->pname; ?></td>
				<td id="event-description-<?php echo $category->id; ?>">
					<?php echo $category->description;?>
				</td>
				<td id="event-count-<?php echo $category->id; ?>">
					<?php echo $count;?>
				</td>
				<td>
					<?php echo $category->id; ?>
				</td>
			</tr>
<?php
		}
?>

	</table>

	<div class="pull-left">
	<?php echo $this->pagination->getListFooter(); ?>
	</div>

	<div class="pull-right">
	<?php echo $this->pagination->getLimitBox(); ?>
	</div>

	<input type="hidden" name="view" value="eventcategories" />
	<input type="hidden" name="option" value="com_community" />
	<input type="hidden" name="task" value="eventcategories" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	</form>