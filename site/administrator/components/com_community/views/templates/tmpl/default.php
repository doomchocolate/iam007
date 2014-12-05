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

<div class="well">
	<strong><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_NOTE');?></strong>:
	<p><?php echo JText::_('COM_COMMUNITY_TEMPLATE_PARAMETER_INFO');?></p>
	<a class="btn btn-mini btn-info" href="http://tiny.cc/templating" target="_blank"><i class="js-icon-info-sign"></i> <?php echo JText::_('COM_COMMUNITY_DOC'); ?></a>
</div>

<form name="adminForm" id="adminForm" method="post">


<table class="table table-bordered table-hover">
<thead>
	<tr>
		<th width="10">
			<?php echo JText::_( 'COM_COMMUNITY_NUM' ); ?>
		</th>
		<th>
			<?php echo JText::_( 'COM_COMMUNITY_TEMPLATES_NAME' ); ?>
		</th>
		<th width="50">
			<?php echo JText::_('COM_COMMUNITY_TEMPLATES_DEFAULT');?>
		</th>
		<th>
			<?php echo JText::_( 'COM_COMMUNITY_TEMPLATES_VERSION' ); ?>
		</th>
		<th>
			<?php echo JText::_( 'COM_COMMUNITY_DATE' ); ?>
		</th>
		<th>
			<?php echo JText::_( 'COM_COMMUNITY_TEMPLATES_AUTHOR' ); ?>
		</th>
	</tr>
</thead>
<?php $i = 0; ?>
<?php foreach( $this->templates as $row ): ?>
<tr>
	<td>
		<?php echo ( $i + 1 ); ?>
	</td>
	<td>
		<input type="radio" id="cb<?php echo $i;?>" name="template" value="<?php echo $row->element; ?>" onclick="Joomla.isChecked(this.checked);" />
		<span class="lbl"></span>
		<a href="index.php?option=com_community&view=templates&layout=edit&override=<?php echo $row->override ? 1 : 0;?>&id=<?php echo $row->element;?>"><?php echo $row->element;?></a>
	</td>
	<td align="center" class="text-center">
	<?php
	if( $this->config->get('template') == $row->element )
	{
	?>
		<i class="js-icon-heart red"></i>
	<?php
	}
	?>
	</td>
	<td align="center">
		<?php echo ($row->info) ? $row->info['version'] : 'N/A';?>
	</td>
	<td align="center">
		<?php echo ($row->info) ? $row->info[TEMPLATE_CREATION_DATE] : 'N/A';?>
	</td>
	<td align="center">
		<?php echo ($row->info) ? $row->info['author'] : 'N/A';?>
	</td>
</tr>
	<?php $i++;?>
<?php endforeach; ?>
</table>
<input type="hidden" name="view" value="templates" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="publish" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>