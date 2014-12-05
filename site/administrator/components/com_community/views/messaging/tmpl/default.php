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
Joomla.submitbutton = function(action){
	submitbutton( action );
}

function submitbutton( action )
{
	if( action == 'save' )
	{
		sendMessage( joms.jQuery('#title').val() , CKEDITOR.instances.message.getData() , 1 );
	}
}

function sendMessage( title , message , limit )
{
	jax.call( 'community' , 'admin,messaging,ajaxSendMessage' , title , message, limit );
}
</script>
<form method="post">
<div id="messaging-form">
<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert">
		<i class="js-icon-remove"></i>
	</button>
	<?php echo JText::_('COM_COMMUNITY_MESSAGING_ALLOWS_SEND_EMAIL');?>
</div>
<table class="admintable">
	<tr>
		<td class="key" width="100"><?php echo JText::_('COM_COMMUNITY_TITLE');?><span style="color:red;">*</span></td>
		<td><input type="text" id="title" name="title" value="" class="input-xxlarge" /></td>
	</tr>
	<tr>
		<td class="key"><?php echo JText::_('COM_COMMUNITY_MESSAGE');?><span style="color:red;">*</span></td>
		<td>
			<?php echo $this->editor->displayEditor('message', '' , '100%', '300', '10', '20' , false); ?>
		</td>
	</tr>
	<tr>
		<td class="key"></td>
		<td class="send-message-wrapper"></td>
	</tr>
</table>
</div>
<div id="messaging-result" style="display: none;">
<fieldset>
	<legend><?php echo JText::_('COM_COMMUNITY_MESSAGING_SENDING_MESSAGES');?></legend>
	<div><?php echo JText::_('COM_COMMUNITY_MESSAGING_DONT_REFRESH_PAGE');?></div>
	<div id="no-progress"><?php echo JText::_('COM_COMMUNITY_MESSAGING_NO_PROGRESS');?></div>
	<div id="progress-status" style="padding-top: 5px;"></div>
</fieldset>
</div>
</form>

<script>
	jQuery("#toolbar-save").prependTo(".send-message-wrapper");
	var click = jQuery("#toolbar-save button").attr('onclick');
	click +=';return false;';
	jQuery("#toolbar-save button").attr('onclick',click);
</script>

<?php
$version = new JVersion();
	if($version->getHelpVersion() <='0.25') { ?>

	<script>
		jQuery("#toolbar-save").prependTo(".send-message-wrapper");
		jQuery("#toolbar-save").replaceWith(function() {
			return jQuery("<div />", {html: jQuery(this).html()});
		});
		jQuery(".send-message-wrapper span").addClass('icon-save');
		jQuery(".send-message-wrapper a").addClass('btn btn-success btn-small');
	</script>

	<?php } else { ?>

	<script>
		jQuery("#toolbar-save").prependTo(".send-message-wrapper");
		var click = jQuery("#toolbar-save button").attr('onclick');
		click +=';return false;';
		jQuery("#toolbar-save button").attr('onclick',click);
	</script>

<?php } ?>