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
$task	= JRequest::getString( 'task' , '' );

if( $task == 'element' )
{
	echo $this->loadTemplate( 'element' );
}
else
{
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
		<input type="text" onchange="document.adminForm.submit();" class="no-margin" value="<?php echo ($this->search) ? $this->escape( $this->search ) : ''; ?>" id="search" name="search"/>
		<div onclick="this.form.submit();" class="btn btn-small btn-primary">
			<i class="js-icon-search"></i>
			<?php echo JText::_('COM_COMMUNITY_SEARCH');?>
		</div>

		<div class="pull-right text-right">
			<select name="usertype" onchange="document.adminForm.submit();" class="no-margin">
				<option value="all"<?php echo $this->usertype == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_USER_TYPE');?></option>
				<option value="jomsocial"<?php echo $this->usertype == 'jomsocial' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_JOMSOCIAL_USERS');?></option>
				<option value="joomla"<?php echo $this->usertype == 'joomla' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_JOOMLA_USERS');?></option>
				<option value="facebook"<?php echo $this->usertype == 'facebook' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_FACEBOOK_USERS');?></option>
			</select>
			<select name="profiletype" onchange="document.adminForm.submit();" class="no-margin">
				<option value="all"<?php echo $this->profileType == 'all' ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_USERS_ALL_PROFILE_TYPES');?></option>
				<?php
				if( $this->profileTypes )
				{
					foreach( $this->profileTypes as $profile )
					{
				?>
					<option value="<?php echo $profile->id;?>"<?php echo $this->profileType == $profile->id ? ' selected="selected"' : '';?>><?php echo $profile->name;?></option>
				<?php
					}
				}
				?>
			</select>
			<?php echo $this->_getStatusHTML();?>
		</div>
	</div>
</div>

<table class="table table-hover middle-content">
	<thead>
		<tr class="title">
			<th width="10"><?php echo JText::_('COM_COMMUNITY_NUMBER'); ?></th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			</th>
			<th width="64">
				<?php echo JText::_('COM_COMMUNITY_AVATAR') ?>
			</th>
			<th width="150">
				<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_NAME') , 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_USERNAME'), 'username', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_EMAIL'), 'email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_ENABLED'), 'block', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_LAST_VISITED'), 'lastvisitDate', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="150">
				&nbsp;
			</th>
			<th width="10">
				<?php echo JHTML::_('grid.sort',   JText::_('COM_COMMUNITY_ID'), 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
		</tr>
	</thead>
	<?php $i = 0; ?>
	<?php
		if( $this->users )
		{
			foreach( $this->users as $row )
			{
	?>
				<tr>
					<td>
						<?php echo ( $i + 1 ); ?>
					</td>
					<td>
						<?php echo JHTML::_('grid.id', $i++, $row->id); ?>
						<span class="lbl"></span>
					</td>
					<td>
						<div class="avatar-wrapper thumbnail">
							<a href="<?php echo JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $row->id ); ?>"><img src="<?php echo $row->getThumbAvatar();?>" /></a>
<!--							<span class="connect-type">--><?php //echo $this->getConnectType( $row->id ); ?><!--</span>-->
						</div>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_community&view=users&layout=edit&id=' . $row->id ); ?>">
							<h5 class="no-margin"><?php echo $row->name; ?></h5>
						</a>
						<span class="label label-success"><?php echo $this->getProfileName($row) ?></span>
					</td>
					<td align="center">
						<?php echo $row->username; ?>
					</td>
					<td>
						<a href="mailto:<?php echo $row->email; ?>"><?php echo $row->email;?></a>
					</td>
					<td id="block<?php echo $row->id;?>" align="center" class='center'>
						<?php echo $this->getPublish( $row , 'block' , 'users,ajaxTogglePublish' );?>
					</td>
					<td id="published<?php echo $row->id;?>">
						<?php
							$date		= JFactory::getDate( $row->lastvisitDate );
							$mainframe	= JFactory::getApplication();
							//$date->setOffset( $mainframe->getCfg( 'offset' ) );
							echo $row->lastvisitDate=='0000-00-00 00:00:00'?'0000-00-00 00:00:00':$date->format('Y-m-d H:i:s');
						?>
					</td>
					<td>
						<a class="btn btn-small btn-yellow" href="javascript:void(0);" onclick="azcommunity.assignGroup('<?php echo $row->id;?>');"><?php echo JText::_('COM_COMMUNITY_ASSIGN_TO_GROUP');?></a>
					</td>
					<td><?php echo $row->id;?></td>
				</tr>
	<?php
			}
		}
		else
		{
	?>
	<tr>
		<td colspan="12" align="center"><?php echo JText::_('COM_COMMUNITY_NO_RESULT');?></td>
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
<input type="hidden" name="view" value="users" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="users" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php
}
?>