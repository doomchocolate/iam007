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
	submitform(action);
}
</script>

<div class="well">
	<p>A Built-in Reporting System provides every user with the ability to report any bad or unwanted content on the site. Currently, users can report objects such as photos, video, events, groups, discussions, etc.. Reporting small User-Posted content, e.g. Activity Stream Comments, is not yet possible.</p>
	<a class="btn btn-mini btn-info" href="http://tiny.cc/reportingsystem" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
</div>

<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">

<!-- page header -->
<div class="row-fluid">
	<div class="span24">

		<div class="pull-right text-right">
			<?php echo $this->_getStatusHTML();?>
		</div>
	</div>
</div>


<table class="table table-bordered table-hover" >
	<thead>
		<tr class="title">
			<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
			<th width="10"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_ITEM_LINK');?>
			</th>
			<th width="2">
				<?php echo JText::_('COM_COMMUNITY_COUNT'); ?>
			</th>
			<th width="65">
				<?php echo JText::_('COM_COMMUNITY_STATUS'); ?>
			</th>
			<th width="60">
				<?php echo JText::_('COM_COMMUNITY_VIEW_ITEM'); ?>
			</th>
			<th width="125">
				<?php echo JText::_('COM_COMMUNITY_SUBMITTED_ON'); ?>
			</th>
			<th width="150">
				<div style="width:150px">
					<?php echo JText::_('COM_COMMUNITY_ACTIONS'); ?>
				</div>
			</th>
		</tr>
	</thead>
<?php
	if( !$this->reports )
	{
?>
		<tr>
			<td colspan="9" align="center">
				<div><?php echo JText::_('COM_COMMUNITY_REPORTS_NOT_SUBMITTED'); ?></div>
			</td>
		</tr>
<?php
	}
	else
	{
		$count		= 0;

		foreach( $this->reports as $row )
		{
?>
		<tr id="row<?php echo $row->id;?>">
			<td align="center"><?php echo $count + 1; ?></td>
			<td>
				<?php echo JHTML::_('grid.id', $count++, $row->id); ?>
				<span class="lbl"></span>
			</td>
			<td>
				<a class="word-break" href="<?php echo $row->link;?>" target="_blank"><?php echo $row->link;?></a>
			</td>
			<td>
				<div>
					<?php echo count( $row->reporters ); ?>
				</div>
			</td>
			<td>
				<div style="text-align: center;">
					<?php
						if( $row->status == 1 )
						{
							?> <span class="label label-success"><?php echo JText::_('COM_COMMUNITY_REPORTS_PROCESSED'); ?></span> <?php
						}
						else if( $row->status == 2 )
						{
							?> <span class="label"><?php echo JText::_('COM_COMMUNITY_REPORTS_IGNORED'); ?></span> <?php
						}
						else
						{
							?> <span class="label label-important"><?php echo JText::_('COM_COMMUNITY_PENDING'); ?></span> <?php
						}
					?>
				</div>
			</td>
			<td style="text-align: center;">
				<a href="<?php echo JRoute::_('index.php?option=com_community&view=reports&layout=childs&reportid=' . $row->id );?>">
						<span class="icon js-icon-eye-open"></span>
						<?php echo JText::_('COM_COMMUNITY_VIEW');?>
					</a>
			</td>
			<td align="center">
				<div>
					<?php echo $row->created; ?>
				</div>
			</td>
			<td align="center">
				<div>
					<?php
					for( $i = 0; $i < count( $row->actions ); $i++ )
					{
						$action	=& $row->actions[ $i ];

						$btnClass = ($row->status == 1)  ? 'btn-primary' : 'btn-warning';
				?>
						<a class="btn btn-small <?php echo $btnClass ?>" href="javascript:void(0);" onclick="azcommunity.reportAction('<?php echo $action->id;?>');">
							<?php
								if($row->status == 1)
								{
									echo JText::_('COM_COMMUNITY_PUBLISH');

								}
								else
								{
									echo JText::_('COM_COMMUNITY_UNPUBLISH');
								}
							?>
						</a>
				<?php
						if( ( $i + 1 )!= count( $row->actions ) )
						{
							echo ' | ';
						}
					}
				?>
						<a class="btn btn-small btn-success" href="javascript:void(0);" onclick="azcommunity.reportAction( '<?php echo $action->id;?>', 1 );">
							<?php echo JText::_('COM_COMMUNITY_REPORTS_IGNORE'); ?>
						</a>
				</div>
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

<input type="hidden" name="view" value="reports" />
<input type="hidden" name="task" value="reports	" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
</form>