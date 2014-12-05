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

if( $this->requestType == 'component' )
{
	$url	= 'index.php?option=com_community&amp;view=memberlist&amp;tmpl=component&amp;object=' . $this->object;
}
else
{
	$url	= 'index.php?option=com_community&view=memberlist&task=memberlist';
}
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
<form action="<?php echo $url;?>" method="post" name="adminForm" id="adminForm">

<!-- page header -->
<div class="row-fluid">
	<div class="span24">
		<span><?php echo JText::_('COM_COMMUNITY_FILTER');?></span>
		<input type="text" onchange="document.adminForm.submit();" class="no-margin" id="search" name="search"/>
		<div class="btn btn-small btn-primary" onclick="this.form.submit();">
			<i class="js-icon-filter"></i>
			<?php echo JText::_('COM_COMMUNITY_FILTER');?>
		</div>
	</div>
</div>

<table class="table table-bordered table-hover" cellspacing="1">
<thead>
	<tr>
		<th width="10">
			<?php echo JText::_( 'COM_COMMUNITY_NUM' ); ?>
		</th>
		<th width="10">
			<?php if( $this->requestType == 'component' ){ ?>
			<?php echo JHTML::_('grid.sort','ID', 'a.id', $this->orderDirection , $this->ordering ); ?>
			<span class="lbl"></span>
			<?php }else{ ?>
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			<?php } ?>
		</th>
		<th>
			<?php echo JHTML::_('grid.sort','Title', 'a.title', $this->orderDirection , $this->ordering ); ?>
		</th>
		<th>
			<?php echo JHTML::_('grid.sort', 'Description', 'description', $this->orderDirection , $this->ordering ); ?>
		</th>
		<th>
			<?php echo JHTML::_('grid.sort', 'Date', 'a.created', $this->orderDirection, $this->ordering ); ?>
		</th>
		<th>
			<?php echo JHTML::_('grid.sort', 'ID', 'a.id', $this->orderDirection, $this->ordering ); ?>
		</th>
	</tr>
</thead>
<tbody>
<?php
$k = 0;
$i = 0;
foreach( $this->memberlist as $row )
{
	$link 	= '';
	$date	= JHTML::_('date',  $row->created, JText::_('DATE_FORMAT_LC4') );
?>
	<tr class="<?php echo "row$k"; ?>">
		<td>
			<?php echo ($i + 1); ?>
		</td>
		<td>
			<?php if( $this->requestType == 'component' ){ ?>
				<?php echo $row->id; ?>
			<?php }else{ ?>
				<?php echo JHTML::_('grid.id', $i++, $row->id); ?>
			<?php } ?>
		</td>
		<td>
			<?php if( $this->requestType == 'component' ){ ?>
				<a style="cursor: pointer;" onclick="window.parent.jSelectMemberList('<?php echo $row->id;?>','<?php echo str_replace(array("'", "\""), array("\\'", ""),$row->title); ?>','<?php echo $this->object;?>');"><?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?></a>
			<?php }else{ ?>
				<?php echo htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8'); ?>
			<?php } ?>
		</td>
		<td>
			<?php echo $row->description;?>
		</td>
		<td>
			<?php echo $date; ?>
		</td>
		<td style="text-align: center;">
			<?php echo $row->id; ?>
		</td>
	</tr>
<?php
	$k = 1 - $k;
}
?>
</tbody>
<tfoot>
<tr>
	<td colspan="15">
		<?php echo $this->pagination->getListFooter(); ?>
	</td>
</tr>
</tfoot>
</table>
<?php if( $this->requestType != 'component' ){ ?>
<input type="hidden" name="view" value="memberlist" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="memberlist" />
<?php } ?>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->ordering;?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderDirection; ?>" />
</form>
