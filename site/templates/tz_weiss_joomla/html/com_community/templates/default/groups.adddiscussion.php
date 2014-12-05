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
<div class="cLayout cGroups-AddDiscussion">
<form class="cForm" name="jsform-groups-discussionform" action="<?php echo CRoute::getURI(); ?>" method="post">
	<?php
		if( !CStringHelper::isHTML($discussion->message) 
		&& $config->get('htmleditor') != 'none' 
		&& $config->getBool('allowhtml') )
		{
			$discussion->message = CStringHelper::nl2br($discussion->message);
		}
	?>

	<script type="text/javascript">
	function saveContent()
	{
		<?php echo $editor->saveText( 'message' ); ?>
		return true;
	}
	</script>	

	<ul class="cFormList cFormHorizontal cResetList">

		<?php echo $beforeFormDisplay;?>

		<li>
			<label for="title" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE'); ?></label>

			<div class="form-field">
				<input type="text" name="title" id="title" size="40" class="input text" style="width: 90%" value="<?php echo $discussion->title;?>" />
			</div>
		</li>

		<?php if ( $config->get( 'htmleditor' ) == 'jce' ) { ?>

			<li>
				<label for="message" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY'); ?></label>

				<div class="form-field">
					<?php if( $config->get( 'htmleditor' ) && $config->getBool( 'allowhtml' ) ) : ?>
						<?php echo $editor->displayEditor( 'message',  $discussion->message , '95%', '450', '10', '20' , false ); ?>
					<?php else : ?>
						<textarea rows="3" cols="40" name="message" id="message" class="textarea" style="width: 90%"><?php echo $discussion->message;?></textarea>
					<?php endif; ?>
				</div>
			</li>
		
		<?php } else { ?>

			<li>
				<label for="message" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY'); ?></label>

				<div class="form-field wysiwyg-field" style="display:none">
					<?php 
					if( $config->get( 'htmleditor' ) == 'none' && $config->getBool('allowhtml') )
					{
					?>
					<div class="htmlTag"><?php echo JText::_('COM_COMMUNITY_HTML_TAGS_ALLOWED');?></div>
					<?php
					}?>
					
					<?php if( $config->get( 'htmleditor' ) && $config->getBool('allowhtml') ) : ?>
						<?php echo $editor->displayEditor( 'message',  $discussion->message , '95%', '450', '10', '20' , false ); ?>
					<?php else : ?>
						<textarea rows="3" cols="40" name="message" id="message" style="width: 90%"><?php echo $discussion->message;?></textarea>
					<?php endif; ?>
				</div>

				<script type="text/javascript">
					joms.jQuery(window).load(function() {
						if(joms.jQuery(this).width() <= 980) {
							joms.jQuery('.wysiwyg-field').empty().append('<textarea name="message"><?php echo $discussion->message; ?></textarea>').show();
						} else {
							joms.jQuery('.wysiwyg-field').show();
						}
					});
				</script>

			</li>

		<?php } ?>



		<?php if($params->get('groupdiscussionfilesharing') > 0) { ?>
		<li class="has-seperator">
			<div class="form-field">
				<label for="filepermission-member" class="label-checkbox">
					<input type="checkbox" class="input checkbox" value="1" name="filepermission-member"/>
					<?php echo JText::_('COM_COMMUNITY_FILES_ALLOW_MEMBERS')?>
				</label>
			</div>
		</li>
		<?php } ?>


		<?php echo $afterFormDisplay;?>


		<li class="has-seperator">
			<div class="form-field">
				<span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span>
			</div>
		</li>
		<li class="form-action">
			<div class="form-field">
				<input type="hidden" value="<?php echo $group->id; ?>" name="groupid" />
				<input type="button" class="btn" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" onclick="javascript:history.go(-1);return false;" /> 
				<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_GROUPS_ADD_DISCUSSION_BUTTON');?>" onclick="saveContent();" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</div>
		</li>
	</ul>
</form>
</div>