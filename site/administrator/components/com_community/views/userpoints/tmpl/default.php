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
	switch( action )
	{
		case 'ruleScan':
			azcommunity.ruleScan();
			break;
		case 'ruleEdit':
			if( !confirm( '<?php echo JText::_('COM_COMMUNITY_DELETE_FIELD_CONFIRMATION'); ?>' ) )
			{
				break;
			}
		case 'documentation':
			window.open('http://documentation.jomsocial.com','_blank');
			break;
		case 'publish':
		case 'unpublish':
		default:
			submitform( action );
	}


}
</script>
<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">

<div class="well">
	<p><?php echo JText::_('COM_COMMUNITY_USERPOINTS_HEADER_MESSAGE')?></p>
<a class="btn btn-info btn-mini" href="http://tiny.cc/userpoints" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
</div>


<table class="table table-bordered table-hover" cellspacing="1">
	<thead>
		<tr class="title">
			<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_USERPOINTS_ACTIVITY_TYPE'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_USERPOINTS_RULE_DESCRIPTION'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',  JText::_('COM_COMMUNITY_USERPOINTS_PLUGIN'), 'rule_plugin', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',  JText::_('COM_COMMUNITY_USERPOINTS_USER_ACCESS'), 'access', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_USERPOINTS_POINTS'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',  JText::_('COM_COMMUNITY_USERPOINTS_ACTIVITY_STATE'), 'published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
			</th>
		</tr>
	</thead>
<?php
	if( !$this->userpoints )
	{
?>
		<tr>
			<td colspan="8" align="center">
				<div><?php echo JText::_('COM_COMMUNITY_NO_RULES_SUBMITTEDT'); ?></div>
			</td>
		</tr>
<?php
	}
	else
	{
		$count		= 0;

		foreach( $this->userpoints as $row )
		{
			//$userlevel = $this->acl->get_group_name($row->access, 'ARO');
			$userlevel = '';
			switch($row->access)
			{
				case PUBLIC_GROUP_ID : $userlevel = 'Public'; break;
				case REGISTERED_GROUP_ID : $userlevel = 'Registered'; break;
				case SPECIAL_GROUP_ID : $userlevel = 'Special'; break;
				case ACCESSLEVEL_GROUP_ID: $userlevel = 'Access Level';
				default : $userlevel = 'Unknown'; break;
			}
?>
		<tr id="row<?php echo $row->id;?>">
			<td align="center"><?php echo $count + 1; ?></td>
			<td align="center">
				<?php echo JHTML::_('grid.id', $count++, $row->id); ?>
				<span class="lbl"></span>
			</td>
			<td>
				<span class="editlinktip">
					<?php echo JHTML::_('link', 'javascript:void(0);', $row->rule_name, array('onclick'=>'azcommunity.editRule(\'' . $row->id . '\');')); ?>
				</span>
			</td>
			<td id="description<?php echo $row->id;?>">
				<div>
					<?php echo $row->rule_description; ?>
				</div>
			</td>
			<td id="plugin<?php echo $row->id;?>">
				<div>
					<?php echo $row->rule_plugin; ?>
				</div>
			</td>
			<td id="access<?php echo $row->id;?>">
				<div>
					<?php echo $userlevel; ?>
				</div>
			</td>
			<td align="center" id="points<?php echo $row->id;?>">
				<div>
					<?php echo $row->points; ?>
				</div>
			</td>
			<td align="center" id="published<?php echo $row->id;?>" class='center'>
				<?php echo $this->getPublish($row, 'published', 'userpoints,ajaxTogglePublish'); ?>
			</td>
		</tr>
<?php
		}
	}
?>
</table>

<div class="pull-left">
<?php echo $this->pagination->getListFooter(); ?>
</div>

<div class="pull-right">
<?php echo $this->pagination->getLimitBox(); ?>
</div>

<input type="hidden" name="view" value="userpoints" />
<input type="hidden" name="task" value="userpoints	" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>