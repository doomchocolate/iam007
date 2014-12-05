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
<script type="text/javascript">
function patch()
{
	joms.jQuery( "#submit-patch" ).attr('disabled' , 'true');
	joms.jQuery( "#submit-patch" ).html('Updating ...');
	
	joms.jQuery( "#no-progress" ).css( 'display' , 'none' );
	// Patch the table first
	jax.call('community' , 'admin,maintenance,ajaxPatchTable' );
	jax.call('community' , 'admin,maintenance,ajaxPatchFriendTable' );
	jax.call('community' , 'admin,maintenance,ajaxPatch' );
}

function patchPrivacy()
{
	joms.jQuery( "#submit-privacy-patch" ).attr('disabled' , 'true');
	joms.jQuery( "#submit-privacy-patch" ).html('Updating ...');
	
	jax.call('community' , 'admin,maintenance,ajaxPatchPrivacy' );
}

function checkHash(){
	joms.jQuery('#no-progress').empty().html('Checking file ...');	
	joms.jQuery('#progress-status').empty();	
	jax.call('community' , 'admin,maintenance,ajaxCheckHash' );	
}
</script>
<h3 style="text-decoration: underline;"><?php echo JText::_('COM_COMMUNITY_MAINTENANCE');?></h3>
<fieldset style="width: 50%">
	<legend><?php echo JText::_('COM_COMMUNITY_MAINTENANCE_UPDATE_USER_PRIVACY');?></legend>
	<div>Run this fix to update the user's privacy.</div>
	<button id="submit-privacy-patch" class="button" onclick="patchPrivacy();"><?php echo JText::_('COM_COMMUNITY_MAINTENANCE_RUN_FIX_NOW'); ?></button>
	<div><button id="submit-privacy-patch" class="button" onclick="checkHash();"><?php echo JText::_('COM_COMMUNITY_MAINTENANCE_CHECK_FILE_HASH'); ?></button></div>
</fieldset>
<?php
if( !$this->isLatest )
{
?>
<p>
	It is most likely that you are currently running an earlier build prior to 1.0.122.
	This new build requires you to perform some update on the database structure.
</p>
<fieldset style="width: 50%">
	<legend>Database Fixes</legend>
	<div>Run this fix to upgrade your database table for the groups tables. The process might take up to several minutes. Please wait until the process complete.</div>
	<button id="submit-patch" class="button" onclick="patch();"><?php echo JText::_('COM_COMMUNITY_MAINTENANCE_RUN_FIX_NOW'); ?></button>
</fieldset>
<?php
}
?>
<fieldset style="width: 50%">
	<legend>Progress</legend>
	<div id="no-progress">No progress yet.</div>
	<div id="progress-status"></div>
</fieldset>
<?php
if(file_exists(JPATH_ROOT . '/administrator/components/com_community/installer.dummy.ini'))
{
?>
<fieldset style="width: 50%; margin-top:20px;">
	<legend>Continue Installation</legend>
	<div style="text-align:center;">Once you had perform all the neccessary update above. Please click on the following button to continue your installation.</div>
	<div id="communityContainer" style="margin:5px; text-align:center;">
		<form action="?option=com_community" method="POST" name="installform" id="installform">
			<input type="hidden" name="install" value="1"/>
			<input type="hidden" name="step" value="UPDATE_DB"/>
			<input type="submit" class="button" onclick="" value="Continue"/>
		</form>
	</div>
</fieldset>
<?php
}
?>