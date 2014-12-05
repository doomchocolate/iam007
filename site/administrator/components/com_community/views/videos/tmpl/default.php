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
	switch( action )
	{
	    case 'export':
			var items = new Array();
			joms.jQuery('#adminForm input[name="cid[]"]:checked').each( function(){
				items.push( joms.jQuery(this).val() );
			});
            window.open( 'index.php?option=com_community&view=users&tmpl=component&no_html=1&format=csv&task=export&cid[]=' + items.join('&cid[]=') );
			break;
		default:
	 	   submitform( action );
	 	   break;
	}

}
</script>
<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">
	<!-- page header -->
	<div class="row-fluid">
		<div class="span24">
			<input type="text" onchange="document.adminForm.submit();" class="no-margin" value="<?php echo ($this->search) ? $this->escape($this->search) : '';?>" id="search" name="search"/>
			<div class="btn btn-small btn-primary" onclick="document.adminForm.submit();">
				<i class="js-icon-search"></i>
				<?php echo JText::_('COM_COMMUNITY_SEARCH');?>
			</div>
			<div class="pull-right text-right">
				<?php echo $this->categories;?>
				<?php echo $this->_getStatusHTML();?>
			</div>
		</div>
	</div>

	<table class="table table-bordered table-hover">
		<thead>
			<tr class="title">
				<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
				<th width="10">
					<?php echo JText::_('COM_COMMUNITY_ID'); ?>
				</th>
				<th width="10">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
					<span class="lbl"></span>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_VIDEOS_THUMB') ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_NAME') ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_EDIT')?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_DESCRIPTION'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_STATUS'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_COMMUNITY_CATEGORY')?>
				</td>
				<th>
					<?php echo JText::_('COM_COMMUNITY_DATE'); ?>
				</th>
			</tr>
		</thead>
		<?php $i = 0; ?>
		<?php
			if( $this->videos )
			{
				foreach( $this->videos as $row )
				{
		?>
					<tr>
						<td>
							<?php echo ( $i + 1 ); ?>
						</td>
						<td><?php echo $row->id;?></td>
						<td>
							<?php echo JHTML::_('grid.id', $i++, $row->id); ?>
							<span class="lbl"></span>
						</td>
						<td>
							<a href="javascript:void(0);" onClick="azcommunity.viewVideo('<?php echo $row->id ?>');"><img src="<?php echo $this->getThumbnail($row)?>" width="112px" /></a>
						</td>
						<td>
							<a href="<?php echo JRoute::_(JUri::root().'index.php?option=com_community&view=videos&task=video&userid='.$row->creator.'&videoid='.$row->id) ?>" target="_blank"><?php echo $row->title; ?></a>
						</td>
						<td>
							<a href="javascript:void(0);" onClick="azcommunity.editVideo('<?php echo $row->id?>');"><?php echo JText::_('Edit')?></a>
						</td>
						<td align="center">
							<?php echo CStringHelper::trim($row->description,200); ?>
						</td>
						<td id="published<?php echo $row->id;?>" align="center" class='center'>
							<?php echo $this->getPublish( $row , 'published' , 'videos,ajaxTogglePublish' );?>
						</td>
						<td>
							<?php echo $row->categoryName; ?>
						</td>
						<td id="block<?php echo $row->id;?>">
							<?php
								$date		= JFactory::getDate( $row->created );
								$mainframe	= JFactory::getApplication();
								echo $row->created=='0000-00-00 00:00:00'?'0000-00-00 00:00:00':$date->format('Y-m-d H:i:s');
							?>
						</td>
					</tr>
		<?php
				}
			}
			else
			{
		?>
		<tr>
			<td colspan="10" align="center"><?php echo JText::_('COM_COMMUNITY_NO_RESULT');?></td>
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

<input type="hidden" name="view" value="videos" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="videos" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>