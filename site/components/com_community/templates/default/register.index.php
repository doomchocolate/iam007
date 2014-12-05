<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();
?>
<form action="<?php echo CRoute::getURI(); ?>" method="post" id="jomsForm" name="jomsForm" class="community-form-validate">
<div class="ctitle">
	<h2><?php echo JText::_( 'COM_COMMUNITY_REGISTER_TITLE_USER_INFO' ); ?></h2>
</div>
<ul class="cFormList cFormHorizontal cResetList">
	<li class="reminder">
		<div class="form-field">
			<?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?>
		</div>
	</li>
<?php if ($isUseFirstLastName) { ; ?>
	<li>
		<label id="jsfirstnamemsg" for="jsfirstname" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_FIRST_NAME' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input type="text" id="jsfirstnamemsg" name="jsfirstname"
					value="<?php echo $data['html_field']['jsfirstname']; ?>"
					class="input text required validate-firstname" style="width: 70%" />
			<span id="errjsfirstnamemsg" style="display:none;">&nbsp;</span>
		</div>
	</li>
	<li>
		<label id="jslastnamemsg" for="jslastname" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_LAST_NAME' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input type="text" id="jslastnamemsg" name="jslastname"
					value="<?php echo $data['html_field']['jslastname']; ?>"
					class="input text required validate-jslastname" style="width: 70%" />
			<span id="errjslastnamemsg" style="display:none;">&nbsp;</span>
		</div>
	</li>
<?php } else { ?>
	<li>
		<label id="jsnamemsg" for="jsname" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_NAME' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input type="text" name="jsname" id="jsname" value="<?php echo $data['html_field']['jsname']; ?>" class="input text required validate-name" style="width: 70%" />
			<span id="errjsnamemsg" style="display:none;">&nbsp;</span>
		</div>
	</li>
<?php } ?>
	<li>
		<label id="jsusernamemsg" for="jsusername" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_USERNAME' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input type="text" id="jsusername" name="jsusername" value="<?php echo $data['html_field']['jsusername']; ?>"
			       class="input text  required validate-username" style="width: 70%" />
		    <input type="hidden" name="usernamepass" id="usernamepass" value="N"/>
			<span id="errjsusernamemsg" style="display:none;">&nbsp;</span>
		</div>
	</li>
	<li>
		<label id="jsemailmsg" for="jsemail" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_EMAIL' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input type="text" id="jsemail" name="jsemail" value="<?php echo $data['html_field']['jsemail']; ?>" class="input text required validate-email" style="width: 70%" />
		    <input type="hidden" name="emailpass" id="emailpass" value="N"/>
		    <span id="errjsemailmsg" style="display:none;">&nbsp;</span>
		</div>
	</li>
	<li>
		<label id="pwmsg" for="jspassword" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_PASSWORD' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input class="input password required validate-password" type="password" id="jspassword" name="jspassword" style="width: 50%" />
		    <span id="errjspasswordmsg" style="display:none;">&nbsp</span>
		</div>
	</li>
	<li>
		<label id="pw2msg" for="jspassword2" class="form-label"><?php echo JText::_( 'COM_COMMUNITY_VERIFY_PASSWORD' ); ?><span class="required-sign">&nbsp;*</span></label>

		<div class="form-field">
		    <input class="input password  required validate-passverify" type="password" id="jspassword2" name="jspassword2" style="width: 50%" />
		    <span id="errjspassword2msg" style="display:none;">&nbsp;</span>
		</div>
	</li>
	<?php
	if(!empty($recaptchaHTML))
	{
	?>
	<li>
		<div class="form-field">
			<div class="cRecaptcha">
				<?php echo $recaptchaHTML;?>
			</div>
		</div>
	</li>
	<?php
	}
	if( $config->get('enableterms') )
	{
	?>
	<li class="has-seperator">
		<div class="form-field">
			<label class="label-checkbox">
				<input type="checkbox" name="tnc" id="tnc" value="Y" data-message="<?php echo JText::_('COM_COMMUNITY_REGISTER_ACCEPT_TNC')?>" class="input checkbox validate-tnc"/>
				<?php echo JText::_('COM_COMMUNITY_I_HAVE_READ').' <a href="javascript:void(0);" onclick="joms.registrations.showTermsWindow(0);">'.JText::_('COM_COMMUNITY_TERMS_AND_CONDITION').'</a>.';?>
			</label>
		</div>
	</li>
	<?php }?>
	<li class="form-action has-seperator">
		<div class="form-field">
			<input class="btn btn-primary validateSubmit" type="submit" id="btnSubmit" value="<?php echo JText::_('COM_COMMUNITY_NEXT'); ?>" name="submit" />
			<?php if ( $fbHtml ) { ?>
			<div class="auth-facebook">
				<?php echo $fbHtml;?>
			</div>
			<?php } ?>
		</div>
	</li>
</ul>
<?php
if( $config->get('enableterms') )
{
?>

<?php
}
?>



<input type="hidden" name="isUseFirstLastName" value="<?php echo $isUseFirstLastName; ?>" />
<input type="hidden" name="task" value="register_save" />
<input type="hidden" name="id" value="0" />
<input type="hidden" name="gid" value="0" />
<input type="hidden" id="authenticate" name="authenticate" value="0" />
<input type="hidden" id="authkey" name="authkey" value="" />
</form>

<script type="text/javascript">
	cvalidate.init();
	cvalidate.noticeTitle	= '<?php echo addslashes(JText::_('COM_COMMUNITY_NOTICE') );?>';
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');
	cvalidate.setSystemText('JOINTEXT','<?php echo addslashes(JText::_("COM_COMMUNITY_AND")); ?>');

joms.jQuery( '#jomsForm' ).submit( function(){
    joms.jQuery('#btnSubmit').hide();
	joms.jQuery('#cwin-wait').show();
	joms.jQuery('#jomsForm input').attr('readonly', true);

	if(joms.jQuery('#authenticate').val() != '1')
	{
		joms.registrations.authenticate();
		return false;
	}
});

// Password strenght indicator
var password_strength_settings = {
	'texts' : {
		1 : '<?php echo addslashes(JText::_('COM_COMMUNITY_PASSWORD_STRENGHT_L1')); ?>',
		2 : '<?php echo addslashes(JText::_('COM_COMMUNITY_PASSWORD_STRENGHT_L2')); ?>',
		3 : '<?php echo addslashes(JText::_('COM_COMMUNITY_PASSWORD_STRENGHT_L3')); ?>',
		4 : '<?php echo addslashes(JText::_('COM_COMMUNITY_PASSWORD_STRENGHT_L4')); ?>',
		5 : '<?php echo addslashes(JText::_('COM_COMMUNITY_PASSWORD_STRENGHT_L5')); ?>'
	}
}

joms.jQuery('#jspassword').password_strength(password_strength_settings);

</script>