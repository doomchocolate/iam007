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
<form action="index.php?option=com_community" method="post" name="adminForm" id="adminForm">
<script type="text/javascript" language="javascript">
Joomla.submitbutton = function(action){
	submitbutton( action );
}

function submitbutton(action)
{
	if( action == 'purge' )
	{
		if(confirm('<?php echo JText::_('COM_COMMUNITY_ACTIVITIES_PURGE_ACTIVITIES');?>'))
		{
			submitform( action );
		}
	}
	else
	{
		submitform( action );
	}
}
</script>

<!-- page header -->
<div class="row-fluid">
	<div class="span24">
		<input type="text" name="actor" onchange="submitform();" class="no-margin" value="<?php echo $this->currentUser?>"/>
		<div class="btn btn-small btn-primary" onclick="submitform();">
			<i class="js-icon-filter"></i>
			<?php echo JText::_('COM_COMMUNITY_GO_BUTTON');?>
		</div>
		<div class="pull-right text-right">
			<select name="app" onchange="submitform();" class="no-margin">
				<option value="none"<?php echo ( $this->currentApp == 'none' ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_SELECT_APPLICATION');?></option>
					<?php
					for( $i = 0; $i < count( $this->filterApps ); $i++ )
					{
					?>
						<option value="<?php echo $this->filterApps[ $i ]->app;?>"<?php echo ( $this->currentApp === $this->filterApps[ $i ]->app ) ? ' selected="selected"' : '';?>><?php echo $this->filterApps[ $i ]->app; ?></option>
					<?php
					}
					?>
			</select>
			<select name="archived" onchange="submitform();" class="no-margin">
				<option value="0"<?php echo ( $this->currentArchive == 0 ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_SELECT_STATE');?></option>
				<option value="1"<?php echo ($this->currentArchive == 1 ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_ACTIVE');?></option>
				<option value="2"<?php echo ($this->currentArchive == 2 ) ? ' selected="selected"' : '';?>><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_ARCHIVED');?></option>
			</select>

		</div>
	</div>
</div>


<table class="table table-bordered table-hover">
	<thead>
		<tr class="title">
			<th width="10">
				<?php echo JText::_('COM_COMMUNITY_NUMBER'); ?>
			</th>
			<th width="10">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				<span class="lbl"></span>
			</th>
			<th>
			</th>
			<th>
				<?php echo JText::_('COM_COMMUNITY_TITLE'); ?>
			</th>
			<th width="170">
				<?php echo JText::_('COM_COMMUNITY_CREATED');?>
			</th>
			<th><?php echo JText::_('COM_COMMUNITY_STATUS')?></th>
		</tr>
	</thead>
<?php
	if( $this->activities )
	{
		$i	= 0;
		foreach($this->activities as $row )
		{

			$row->title	= CString::str_ireplace('{target}', $this->_getUserLink( $row->target ) , $row->title);
			$row->title	= preg_replace('/\{multiple\}(.*)\{\/multiple\}/i', '', $row->title);
			$search		= array('{single}','{/single}');
			$row->title	= CString::str_ireplace($search, '', $row->title);
			$row->title	= CString::str_ireplace('{actor}', $this->_getUserLink( $row->actor ) , $row->title);
			$row->title	= CString::str_ireplace('{app}', $row->app, $row->title);

			//strip out _QQQ_
			$row->title	= CString::str_ireplace('_QQQ_','', $row->title);
			preg_match_all("/{(.*?)}/", $row->title, $matches, PREG_SET_ORDER);
			if(!empty( $matches ))
			{
				$params = new CParameter( $row->params );
				foreach ($matches as $val)
				{

					$replaceWith = $params->get($val[1], null);
					//if the replacement start with 'index.php', we can CRoute it
					if( strpos($replaceWith, 'index.php') === 0){
						$replaceWith = JURI::root().$replaceWith;
					}

					if( !is_null( $replaceWith ) )
					{
						$row->title	= CString::str_ireplace($val[0], $replaceWith, $row->title);
					}
				}
			}
			 $row->title = preg_replace('/(<a href[^<>]+)>/is', '\\1 target="_blank">', $row->title);
?>
	<tr>
		<td align="center"><?php echo ( $i + 1 ); ?></td>
		<td>
			<?php echo JHTML::_('grid.id', $i++, $row->id); ?>
			<span class="lbl"></span>
		</td>
		<td>
			<?php $user = CFactory::getUser($row->actor); ?>
			<img src="<?php echo $user->getThumbAvatar(); ?>" />
		</td>
		<td><?php echo $row->title;?></td>
		<td align="center"><?php echo $row->created;?></td>
		<td align="center"><?php echo ($row->archived) ? 'Archived' : 'Active' ;?></td>
	</tr>
<?php
		}
?>

<?php
	}
	else
	{
?>
	<tr>
		<td colspan="6" align="center">
			<?php echo JText::_('COM_COMMUNITY_ACTIVITIES_NO_ACTIVITIES_YET');?>
		</td>
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


<input type="hidden" name="view" value="activities" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="activities" />
<input type="hidden" name="boxchecked" value="0" />
</form>
<script type="text/javascript">

joms.jQuery('div.span24 input[type=text]').focus(function(){
        this.value='';
        });
joms.jQuery('div.span24 input[type=text]').blur(function(){
        if(this.value==''){
        	this.value= '<?php echo JText::_("COM_COMMUNITY_ACTIVITIES_ENTER_NAME_VALUE")?>';
        }
    });

</script>