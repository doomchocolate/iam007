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

if(! empty($messages))
{
?>
	<script type="text/javascript">
		function cAddReply() {
			if(joms.jQuery('textarea#replybox').val() == '<?php echo addslashes( JText::_('COM_COMMUNITY_INBOX_MESSAGE_MISSING') ); ?>' || joms.jQuery('textarea#replybox').val() == '') {
				alert('<?php echo addslashes( JText::_('COM_COMMUNITY_INBOX_MESSAGE_MISSING') ); ?>');
				return;
			}
			var html='<div class=\'ajax-wait\'>&nbsp;</div>';
			joms.jQuery('#community-wrap table tbody').append(html);
			jax.call('community', 'inbox,ajaxAddReply', <?php echo $parentData->id; ?>, joms.jQuery('textarea#replybox').val());
			joms.jQuery('textarea#replybox').attr('disabled', true);
			joms.jQuery('button#replybutton').attr('disabled', true);
		}

		// function cReplyFocus(){
		// 	if(joms.jQuery('textarea#replybox').attr('placeholder') == '<?php echo addslashes( JText::_('COM_COMMUNITY_INBOX_DEFAULT_REPLY') ); ?>')
		// 	joms.jQuery('textarea#replybox').attr('placeholder','');
		// }

		// function cReplyBlur(){
		// 	if(joms.jQuery('textarea#replybox').val() == '')
		// 	joms.jQuery('textarea#replybox').attr('placeholder','<?php echo addslashes( JText::_('COM_COMMUNITY_INBOX_DEFAULT_REPLY') ); ?>');
		// 	}

		function cAppendReply(html){
			joms.jQuery('div.ajax-wait').remove();
			joms.jQuery('textarea#replybox').attr('disabled', false);
			joms.jQuery('button#replybutton').attr('disabled', false);
			joms.jQuery('textarea#replybox').val('');
			// joms.jQuery('textarea#replybox').attr('placeholder','<?php echo addslashes( JText::_('COM_COMMUNITY_INBOX_DEFAULT_REPLY') ); ?>');
			joms.jQuery('#community-wrap div#inbox-messages').append(html);
		}

		joms.jQuery(document).ready(function() {
			joms.jQuery('a.cInbox-ShowMore').click(function(e) {
				e.preventDefault();
				joms.jQuery('#cInbox-Recipients').removeClass('hide');
				joms.jQuery(this).addClass('hide');
			});
		});
	</script>
	<div class="cMail-Actors">
		<?php echo $messageHeading;?>
		<?php
			// Generate recipient names.
			echo '<span id="cInbox-Recipients" class="hide">';
			$i = 0;

			$profile = 'index.php?option=com_community&view=profile&userid=';
			// Add owner name in the header
			if ($parentData->from != $my->id) {
				$user	  = CFactory::getUser( $parentData->from );
				$userLink = CRoute::_($profile . $parentData->from );
				echo '<a href="' . $userLink .'">' . $user->getDisplayName(). '</a>';
				$i++;
			}

			// Generate recipient name in the header.
			foreach ($recipient as $row) {
				if ($my->id != $row->to ) {
					if ($i >= 1) echo ', ';
					$user	  = CFactory::getUser( $row->to );
					$userLink = CRoute::_($profile . $row->to );
					echo '<a href="' . $userLink .'">' . $user->getDisplayName(). '</a>';
					$i++;
				}
			}
			echo '</span>';
		?>
	</div>
	<div class="cMail-Streams" id="inbox-messages">
		<?php echo $htmlContent; ?>
	</div>

	<a name="latest"></a>

	<div class="cMail-Compose clearfix">
		<div class="cMessage-Avatar cFloat-L">
			<?php
				$user = CFactory::getUser();
			?>
			<img src="<?php echo $user->getThumbAvatar(); ?>" width="48" class="cAvatar" />
		</div>
		<div class="cMessage-Body">
			<form name="jsform-inbox-read" action="" method="post" class="cForm">
				<ul class="cFormList cFormVertical cResetList">
					<li>
						<div class="input-wrap">
							<!-- with placeholder <textarea id="replybox" onfocus="cReplyFocus()" onblur="cReplyBlur()" class="input-block-level"></textarea> -->
							<textarea id="replybox" class="input-block-level"></textarea>
						</div>
					</li>
					<li>
						<input type="hidden" name="action" value="doSubmit" />
						<button id="replybutton" class="btn btn-primary ajax-wait" onclick="cAddReply();return false;"><?php echo JText::_('COM_COMMUNITY_ADD_REPLY_BUTTON'); ?></button>
					</li>
				</ul>
			</form>
		</div>
	</div>
<?php } else { ?>
	<?php echo $htmlContent; ?>
<?php } ?>