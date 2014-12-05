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
<script src="<?php echo COMMUNITY_BASE_ASSETS_URL; ?>/jqueryui/datepicker/js/jquery-ui-1.9.2.custom.js"></script>
<link rel="stylesheet" href="<?php echo COMMUNITY_BASE_ASSETS_URL; ?>/jqueryui/datepicker/css/ui-lightness/jquery-ui-1.9.2.custom.css" type="text/css" />
<script type="text/javascript" language="javascript">
/**
 * This function needs to be here because, Joomla calls it
 **/
 Joomla.submitbutton = function(action){
 	submitbutton( action );
 }
function submitbutton( action )
{
	if( action == 'removeavatar' )
	{
		jax.call('community' , 'admin,users,ajaxRemoveAvatar' , '<?php echo $this->user->id;?>');
	}
	else
	{
		var form = document.adminForm;

		if( action == 'cancel')
		{
            window.location = "<?php echo JUri::root().'administrator/index.php?option=com_community&view=users';?> ";
			return;
		}

		var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&]", "i");

		// do field validation
		if (joms.jQuery.trim(form.name.value) == "")
		{
			alert( "<?php echo JText::_('COM_COMMUNITY_USERS_PROVIDE_NAME_WARNING'); ?>" );
		}
		else if (form.username.value == "")
		{
			alert( "<?php echo JText::_('COM_COMMUNITY_USERS_PROVIDE_LOGIN_NAME_WARNING'); ?>" );
		}
		else if (r.exec(form.username.value) || form.username.value.length < 2)
		{
			alert( "<?php echo JText::_('COM_COMMUNITY_USERS_INVALID_LOGIN_WARNING'); ?>" );
		}
		else if (joms.jQuery.trim(form.email.value) == "")
		{
			alert( "<?php echo JText::_('COM_COMMUNITY_USERS_PROVIDE_EMAIL_WARNING'); ?>" );
		}
		else if (((joms.jQuery.trim(form.password.value) != "") || (joms.jQuery.trim(form.password2.value) != "")) && (form.password.value != form.password2.value))
		{
			alert( "<?php echo JText::_('COM_COMMUNITY_USERS_PASSWORD_MISMATCH_WARNING'); ?>" );
		}
		else
		{
			if(action == 'saveonly')
			{
				form.redirect.value = 0;
				action = 'save';
			}

			submitform( action );
		}
	}
}
</script>
<form name="adminForm" id="profile-fields-form" action="index.php?option=com_community" method="POST" autocomplete="off">
<?php
echo JHtml::_('tabs.start', 'profile-fields-tabs', array('startOffset'=>0));
echo JHtml::_('tabs.panel', JText::_('COM_COMMUNITY_USERS_ACCOUNT_DETAILS') , 'details-page' );
?>

<div class="row-fluid">
	<div class="span12">
		<table width="100%">
			<tr>
				<td class="key">
					<span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_USERS_PROFILE_PICTURE_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_USERS_PROFILE_PICTURE'); ?></span>
				</td>
				<td class="paramlist_value">
					<img id="user-avatar" src="<?php echo $this->escape( $this->user->getThumbAvatar() );?>" style="border: 1px solid #eee;" alt="<?php echo $this->escape( $this->user->getDisplayName() );?>" />
					<div id="user-avatar-message"></div>
				</td>
			</tr>
			<tr>
				<td class="key"><span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_USER_STATUS_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_USER_STATUS');?></span></td>
				<td class="paramlist_value">
					<!-- <input type="text" name="status" size="80" value="<?php echo $this->escape( $this->user->getStatus() );?>" /> -->
					<textarea name="status" cols="56" rows="6"><?php echo $this->escape( $this->user->getStatus(true) );?></textarea>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="username"><span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_USER_NAME_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_USER_NAME'); ?></span></label>
				</td>
				<td class="paramlist_value">
					<input type="text" name="username" value="<?php echo $this->user->get('username');?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label id="jsemailmsg"><span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_EMAIL_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_EMAIL'); ?></span></label>
				</td>
				<td class="paramlist_value">
					<input type="text" class="inputbox" id="email" name="email" value="<?php echo $this->escape( $this->user->get('email') );?>" size="80" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="name"><span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_NAME_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_NAME'); ?></span></label>
				</td>
				<td>
					<input class="inputbox" type="text" id="name" name="name" value="<?php echo $this->escape( $this->user->get('name') );?>" size="80" />
					<div style="clear:both;"></div>
					<span id="errnamemsg" style="display:none;">&nbsp;</span>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="jspassword">
						<span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_PASSWORD_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?></span>
					</label>
				</td>
				<td>
					<input id="password" name="password" class="inputbox" type="password" value="" size="80"/>
					<span id="errjspasswordmsg" style="display: none;"> </span>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="jspassword2">
						<span class="js-tooltip" title="<?php echo Jtext::_('COM_COMMUNITY_PASSWORD_TIPS')?>"><?php echo JText::_('COM_COMMUNITY_VERIFY_PASSWORD'); ?></span>
					</label>
				</td>
				<td>
					<input id="password2" class="inputbox" type="password" value="" size="80" name="password2"/>
					<span id="errjspassword2msg" style="display:none;"> </span>
					<div style="clear:both;"></div>
					<span id="errpasswordmsg" style="display:none;">&nbsp;</span>
				</td>
			</tr>
			<?php if($this->profilelist): ?>
			<tr>
				<td class="key">
					<label for="profiletype">
						<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_USERS_PROFILE_TYPE');?>" ><?php echo JText::_( 'COM_COMMUNITY_USERS_PROFILE_TYPE' ); ?></span>
					</label>
				</td>
				<td>
					<?php echo $this->profilelist; ?>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_USER_POINTS_TYPE_TIPS');?>" ><?php echo JText::_('COM_COMMUNITY_USER_POINTS'); ?></span>
				</td>
				<td>
					<input id="userpoint" name="userpoint" class="inputbox" type="text" value="<?php echo $this->user->getKarmaPoint();?>" size="8" style="text-align: center;"/>
				</td>
			</tr>
		</table>
	</div>
<?php
// Create tabs
foreach( $this->user->profile['fields'] as $group => $groupFields )
{
	echo JHtml::_('tabs.panel', JText::_($group) , $group . '-page' );
?>

<table width="100%">
	<?php
	foreach( $groupFields as $field )
	{
		$field	= JArrayHelper::toObject ( $field );
		$field->value	= $this->escape( $field->value );
	?>
		<tr>
			<td width="20%" class="key" id="lblfield<?php echo $field->id;?>"><span class="js-tooltip" title="<?php echo $field->tips?>"><?php echo JText::_( $field->name );?></span><?php if($field->required == 1) echo '<span class="required-sign"> *</span>'; ?></td>
			<td><?php echo CProfileLibrary::getFieldHTML( $field , '&nbsp; *',false ); ?></td>
		</tr>
	<?php
	}
	?>
</table>

<?php }
echo JHtml::_('tabs.panel', JText::_('Setting') , 'setting-page' );
?>
<div class="row-fluid">
	<div class='span12'>
		<table width='100%'>
			<tr>
				<td class="key" width="37.6%">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_BLOCK_USER_TIPS');?>" ><?php echo JText::_('COM_COMMUNITY_BLOCK_USER'); ?></span>
				</td>
				<td>
					<?php echo CHTMLInput::checkbox('block' ,'ace-switch ace-switch-5', null , $this->user->get('block') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_RECEIVE_SYSTEM_EMAILS_TIPS');?>" ><?php echo JText::_('COM_COMMUNITY_RECEIVE_SYSTEM_EMAILS'); ?></span>
				</td>
				<td>
					<?php echo CHTMLInput::checkbox('sendEmail' ,'ace-switch ace-switch-5', null , $this->user->get('sendEmail') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_USERS_REGISTERED_DATE_TIPS');?>" ><?php echo JText::_('COM_COMMUNITY_USERS_REGISTERED_DATE'); ?></span>
				</td>
				<td>
					<?php echo JHTML::_('date', $this->user->get('registerDate'), COMMUNITY_DATE_FORMAT_REGISTERED );?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_USERS_LAST_VISIT_DATE_TIPS');?>" ><?php echo JText::_('COM_COMMUNITY_USERS_LAST_VISIT_DATE'); ?></span>
				</td>
				<td>
					<?php echo ($this->user->get('lastvisitDate') == "0000-00-00 00:00:00") ? JText::_('COM_COMMUNITY_NEVER') : JHTML::_('date', $this->user->get('lastvisitDate'), COMMUNITY_DATE_FORMAT_REGISTERED ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="daylightsavingoffset">
						<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DAYLIGHT_SAVING_OFFSET_TOOLTIP');?>"><?php echo JText::_( 'COM_COMMUNITY_DAYLIGHT_SAVING_OFFSET' ); ?></span>
					</label>
				</td>
				<td>
					<?php echo $this->offsetList; ?>
				</td>
			</tr>
		</table>

		<?php if(isset($this->params)) : echo $this->params->renderTable( 'params' ); endif; ?>

	</div>
</div>
<?php
echo JHtml::_('tabs.end');
?>

<?php echo JHTML::_( 'form.token' ); ?>

</div>

<input type="hidden" name="view" value="users" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="userid" value="<?php echo $this->user->id; ?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="redirect" value= 1 />
</form>