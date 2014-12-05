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
 * This function needs to be here because, Joomla toolbar calls it
 **/
 Joomla.submitbutton = function(action){
 	submitbutton( action );
 }

function submitbutton( action )
{
	submitform( action );
}
</script>

<div class="well">
	<p><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_HEADER_MESSAGE')?></p>
	<a class="btn btn-mini btn-info" href="http://tiny.cc/jsmultiprofile" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
</div>

<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">

<table class="table table-bordered table-hover">
	<thead>
		<tr class="title">
			<th width="10">#</th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_NAME');?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_DESCRIPTION');?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_TOTAL_USERS');?>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_PUBLISHED');?>
			</th>
			<th width="80">
				<?php echo JHTML::_('grid.sort',   'Order', 'ordering', $this->orderingDirection, $this->ordering ); ?>
				<?php // echo JHTML::_( 'grid.order' , $this->profiles ); ?>
			</th>
			<th>
				<?php echo JText::_( 'COM_COMMUNITY_CREATED' );?>
			</th>
		</tr>
	</thead>
	<?php $i = 0; ?>
	<?php
		if( empty( $this->profiles ) )
		{
	?>
	<tr>
		<td colspan="8" align="center"><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_NO_PROFILE_CREATED_YET');?></td>
	</tr>
	<?php
		}
		else
		{
			$n=count( $this->profiles );
			for( $i=0; $i < $n; $i++ )
			{
				$row	= $this->profiles[ $i ];
	?>
		<tr>
			<td align="center">
				<?php echo ( $i + 1 ); ?>
			</td>
			<td>
				<?php echo JHTML::_('grid.id', $i , $row->id); ?>
				<span class="lbl"></span>
			</td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_community&view=multiprofile&layout=edit&id=' . $row->id ); ?>">
					<?php echo $row->name; ?>
				</a>
			</td>
			<td>
				<?php echo $row->description; ?>
			</td>
			<td align="center">
				<?php echo $this->getTotalUsers( $row->id );?>
			</td>
			<td id="published<?php echo $row->id;?>" align="center" class='center'>
				<?php echo $this->getPublish( $row , 'published' , 'multiprofile,ajaxTogglePublish' );?>
			</td>
			<td align="center" class="order">
				<div class="pull-right">
					<?php echo $this->pagination->orderUpIcon($i);?>
					<?php echo $this->pagination->orderDownIcon($i,$n);?>
				</div>
				<!-- input type="text" name="order[]" size="10" value="<?php echo $row->ordering; ?>" class="text_area pull-left" / -->
			</td>
			<td align="center">
				<?php echo $row->created; ?>
			</td>
		</tr>
	<?php
			}
	?>
	<?php } ?>
</table>

<div class="pull-left">
<?php echo $this->pagination->getListFooter(); ?>
</div>

<div class="pull-right">
<?php echo $this->pagination->getLimitBox(); ?>
</div>

<input type="hidden" name="view" value="multiprofile" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="multiprofile" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' );?>
</form>