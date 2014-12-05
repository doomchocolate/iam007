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
<form action="index.php?option=com_community" method="post" name="adminForm">
<table class="table table-bordered table-hover middle-content" >
	<thead>
		<tr class="title">
			<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
			<th width="10"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
			<th width="200">
				<?php echo JText::_('COM_COMMUNITY_REPORTS_CREATED_BY'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_CATEGORY'); ?>
			</th>
			<th width="400">
				<?php echo JText::_('COM_COMMUNITY_MESSAGE'); ?>
			</th>
			<th width="100">
				<?php echo JText::_('COM_COMMUNITY_REPORTS_IP_ADDRESS'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_CREATED'); ?>
			</th>
		</tr>
	</thead>
<?php
	if( !$this->reporters )
	{
?>
		<tr>
			<td colspan="7" align="center">
				<div><?php echo JText::_('COM_COMMUNITY_REPORTS_NOT_SUBMITTED'); ?></div>
			</td>
		</tr>
<?php
	}
	else
	{
		$count		= 0;

		foreach( $this->reporters as $row )
		{
			$count	= $count + 1;
			$user	= CFactory::getUser( $row->created_by );
?>
		<tr id="row<?php echo $count;?>">
			<td align="center"><?php echo $count; ?></td>
			<td>
				<?php echo JHTML::_('grid.id', $count++, $row->reportid); ?>
				<span class="lbl"></span>
			</td>
			<td>
				<div>
					<?php if( $user->id == 0 ){
						echo JText::_('COM_COMMUNITY_GUEST');
					 } else { ?>
					 	<a href="<?php echo JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $user->id ); ?>"><img src="<?php echo $user->getThumbAvatar()?>" width="36" /></a>
						<a href="<?php echo JURI::root() . '/index.php?option=com_community&view=profile&userid=' . $user->id;?>" target="_blank">
						<?php echo $user->name;?>
						</a>
					<?php } ?>
				</div>
			</td>
			<td>
				<div>
					<?php echo $row->method;?>
				</div>
			</td>
			<td style="text-align: center;">
				<div>
					<?php echo $this->escape( $row->message );?>
				</div>
			</td>
			<td align="center">
				<div>
					<?php echo $row->ip; ?>
				</div>
			</td>
			<td align="center">
				<div>
					<?php echo $row->created;?>
				</div>
			</td>
		</tr>
<?php
		}
	}
?>
	<tfoot>
	<tr>
		<td colspan="7">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
</table>
<input type="hidden" name="view" value="reports" />
<input type="hidden" name="layout" value="childs" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
</form>