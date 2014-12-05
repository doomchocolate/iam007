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
<div class="cLayout cMail Inbox">
<ul class="cMailBar cResetList cFloatedList clearfix">
	<li>
		<b class="cMail-MasterCheck">
			<input type="checkbox" name="select" class="checkbox jomNameTips" onclick="checkAll();" id="checkall" title="<?php echo JText::_('COM_COMMUNITY_INOBOX_SELECT_ALL'); ?>" />
		</b>
	</li>
	<li>
		<div class="btn-group">
			<?php if ( !JRequest::getVar('task') == 'sent' ) { ?>
				<a class="btn btn-small" href="javascript:void(0);" onclick="setAllAsRead();"><?php echo JText::_('COM_COMMUNITY_INBOX_MARK_READ'); ?></a>&nbsp;&nbsp;&nbsp;
				<a class="btn btn-small" href="javascript:void(0);" onclick="setAllAsUnread();"><?php echo JText::_('COM_COMMUNITY_INBOX_MARK_UNREAD'); ?></a>&nbsp;&nbsp;&nbsp;
				<a class="btn btn-small" href="javascript:void(0);" onclick="joms.messaging.confirmDeleteMarked('inbox');"><?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_MESSAGE'); ?></a>&nbsp;
			<?php } else { ?>
				<a class="btn btn-small" href="javascript:void(0);" onclick="joms.messaging.confirmDeleteMarked('sent');"><?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_MESSAGE'); ?></a>&nbsp;
			<?php } ?>
		</div>
	</li>
</ul>

<table id="inbox-listing" class="table table-hover" >
	<?php foreach ( $messages as $message ) : ?>
	<tr class="js-mail-item<?php echo $message->isUnread ? ' unread' : ' read'; ?>" id="message-<?php echo $message->id; ?>" >
		<td class="js-mail-checkbox">
			<input type="checkbox" name="message[]" value="<?php echo $message->id; ?>" class="cMail-Check checkbox" onclick="checkSelected();" />
		</td>
		<td>
			<!-- Mail Avatar -->
			<a class="cMail-Avatar pull-left" href="<?php echo CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid='. $message->parent); ?>">
				<?php if((JRequest::getVar('task') == 'sent') && (! empty($message->smallAvatar[0])) ) { ?>
					<img width="48" src="<?php echo $message->smallAvatar[0]; ?>" alt="<?php echo $this->escape( JString::ucfirst( $message->to_name[0] ) ); ?>" class="cAvatar" />
				<?php } else { ?>
					<img width="48" src="<?php echo $message->avatar; ?>" alt="<?php echo $this->escape( JString::ucfirst( $message->from_name ) ); ?>" class="cAvatar" />
				<?php }//end if ?>
			</a>
			<!-- Mail Author -->
			<strong class="cMail-Author">
				<?php if((JRequest::getVar('task') == 'sent') && (! empty($message->smallAvatar[0])) ) {
					echo $message->to_name[0];
				} else {
					echo $message->from_name;
				}//end if  ?> 
			</strong>
			<!-- Mail time -->
			<small class="cMail-Time">
			<?php
				$postdate =  CTimeHelper::timeLapse(CTimeHelper::getDate($message->posted_on));
				echo $postdate;
			?>
			</small>
			<!-- Mail Subject -->
			<h5 class="reset-gap"><a href="<?php echo CRoute::_('index.php?option=com_community&view=inbox&task=read&msgid='. $message->parent); ?>">
				<?php echo filter_var($message->subject, FILTER_SANITIZE_STRING); ?>
			</a></h5>
		</td>
		<td>
			<div class="js-mail-actions pull-right small">
				<a href="javascript:jax.call('community', 'inbox,ajaxRemoveFullMessages', <?php echo $message->id; ?>);" class="btn btn-small btn-danger" title="<?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE_CONVERSATION'); ?>">
					<?php echo JText::_('COM_COMMUNITY_INBOX_REMOVE'); ?>
				</a>
			</div>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php 
if ( $pagination ) 
{
?>
<div class="cPagination">
	<?php echo $pagination; ?>
</div>
<?php 
} 
?>
<script type="text/javascript">
function checkAll()
{
	joms.jQuery("#inbox-listing INPUT[type='checkbox']").each( function() {
		if ( joms.jQuery('#checkall').attr('checked') )
			joms.jQuery(this).attr('checked', true);
		else
			joms.jQuery(this).attr('checked', false);
	});
	return false;
}
function checkSelected()
{
	var sel;
	sel = false;
	joms.jQuery("#inbox-listing INPUT[type='checkbox']").each( function() {
		if ( !joms.jQuery(this).attr('checked') )
			joms.jQuery('#checkall').attr('checked', false);
	});
}
function markAsRead( id )
{
	joms.jQuery('#message-'+id).removeClass('unread');
	joms.jQuery('#message-'+id).addClass('read');
	joms.jQuery('#new-message-'+id).hide();
	joms.jQuery("#message-"+id+" INPUT[type='checkbox']").attr('checked', false);
	joms.jQuery('#checkall').attr('checked', false);
}
function markAsUnread( id )
{
	joms.jQuery('#message-'+id).removeClass('read');
	joms.jQuery('#message-'+id).addClass('unread');
	joms.jQuery('#new-message-'+id).show();
	joms.jQuery("#message-"+id+" INPUT[type='checkbox']").attr('checked', false);
	joms.jQuery('#checkall').attr('checked', false);
}
function setAllAsRead()
{
	joms.jQuery("#inbox-listing INPUT[type='checkbox']").each( function() {
		if ( joms.jQuery(this).attr('checked') ) {
			if ( joms.jQuery('#message-'+joms.jQuery(this).attr('value')).hasClass('unread') ) {
				jax.call( 'community', 'inbox,ajaxMarkMessageAsRead', joms.jQuery(this).attr('value') );
			}
		}
	});
}
function setAllAsUnread()
{
	joms.jQuery("#inbox-listing INPUT[type='checkbox']").each( function() {
		if ( joms.jQuery(this).attr('checked') )
			if ( joms.jQuery('#message-'+joms.jQuery(this).attr('value')).hasClass('read') ) {
				jax.call( 'community', 'inbox,ajaxMarkMessageAsUnread', joms.jQuery(this).attr('value') );
			}
	});
}
</script>
</div>
