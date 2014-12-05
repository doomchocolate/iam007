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
		azcommunity.editVideosCategory( 0 , '<?php echo JText::_('COM_COMMUNITY_CATEGORY_NEW'); ?>');
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
				<th>
					<?php echo JText::_('COM_COMMUNITY_NAME'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_PARENT'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_CATEGORY_DESCRIPTION'); ?>
				</th>
				<th>
					<?php echo JText::_('ID'); ?>
				</th>
			</tr>
		</thead>
<?php
		$i		= 0;

		foreach($this->categories as $category)
		{
?>
			<tr>
				<td align="center"><?php echo $i + 1; ?></td>
				<td>
					<?php echo JHTML::_('grid.id', $i++, $category->id); ?>
					<span class="lbl"></span>
				</td>
				<td>
					<?php echo JHTML::_('link', 'javascript:void(0);', $category->name, array('id' => 'videos-title-' . $category->id , 'onclick'=>'azcommunity.editVideosCategory(\'' . $category->id . '\',\'' . JText::_('COM_COMMUNITY_CATEGORY_EDIT') . '\');')); ?>
				</td>
				<td><?php echo $category->pname; ?></td>
				<td id="videos-description-<?php echo $category->id; ?>">
					<?php echo $category->description;?>
				</td>
				<td align="center">
					<?php echo $category->id; ?>
				</td>
			</tr>
<?php
		}
?>

	</table>
	<input type="hidden" name="view" value="videoscategories" />
	<input type="hidden" name="option" value="com_community" />
	<input type="hidden" name="task" value="videoscategories" />
	<input type="hidden" name="boxchecked" value="0" />
	</form>