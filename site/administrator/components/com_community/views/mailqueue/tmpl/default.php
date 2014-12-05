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
	<p><?php echo JText::sprintf('COM_COMMUNITY_MAILQUEUE_DESCRIPTION','http://www.jomsocial.com/support/docs/item/720-setting-up-cron-job-scheduled-task.html'); ?></p>
	<a class="btn btn-mini btn-info" href="http://tiny.cc/mailqueue" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
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

<table class="table table-bordered table-hover">
	<thead>
		<tr class="title">
			<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_MAILQUEUE_RECIPIENT'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_MAILQUEUE_SUBJECT'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_MAILQUEUE_CONTENT'); ?>
			</th>
			<th width="80">
				<?php echo JText::_('COM_COMMUNITY_CREATED'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_STATUS'); ?>
			</th>
		</tr>
	</thead>
<?php
	if( !$this->mailqueues )
	{
?>
		<tr>
			<td colspan="7" align="center">
				<div><?php echo JText::_('COM_COMMUNITY_MAILQUEUE_NO_MAIL_QUEUE'); ?></div>
			</td>
		</tr>
<?php
	}
	else
	{
		$i		= 0;

		$mainframe	= JFactory::getApplication();

		foreach( $this->mailqueues as $queue )
		{
			$created	= JFactory::getDate( $queue->created );
			if(method_exists('JDate','getOffsetFromGMT')){
				$created->setTimezone( new DateTimeZone($mainframe->getCfg('offset')) ); //Joomla 3 compat
			} else {
				$systemOffset = $mainframe->getCfg('offset');
				$created->setOffSet($systemOffset );
			}

?>

		<tr>
			<td align="center"><?php echo $i + 1; ?></td>
			<td>
				<?php echo JHTML::_('grid.id', $i++, $queue->id); ?>
				<span class="lbl"></span>
			</td>
			<td>
				<div>
					<?php echo $queue->recipient; ?>
				</div>
			</td>
			<td>
				<div>
					<?php echo $queue->subject; ?>
				</div>
			</td>
			<td>
				<div>
					<?php echo $queue->body; ?>
				</div>
			</td>
			<td>
				<div>
					<?php echo $created->format('Y-m-d H:i:s'); ?>
				</div>
			</td>
			<td>
				<span class="label label-important"><?php echo $this->getStatusText( $queue->status ); ?></span>
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

<input type="hidden" name="view" value="mailqueue" />
<input type="hidden" name="task" value="mailqueue" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
</form>