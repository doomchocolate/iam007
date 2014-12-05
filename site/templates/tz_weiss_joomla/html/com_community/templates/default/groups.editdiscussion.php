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
<form name="jsform-groups-editdiscussion" action="<?php echo CRoute::getURI(); ?>" method="post">
<ul class="cFormList cFormHorizontal cResetList">
	<?php echo $beforeFormDisplay;?>
	<?php if ( $config->get( 'htmleditor' ) == 'jce' ) : ?>

	<li>
		<label for="title" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE'); ?></label>
		<div class="form-field">
			<input type="text" name="title" id="title" size="40" class="input text" style="width: 90%" value="<?php echo $discussion->title;?>" />
		</div>
	</li>
	
	<li>
		<label for="message" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY'); ?></label>
		<div class="form-field">		
			<?php if( $config->get( 'htmleditor' ) && $config->getBool( 'allowhtml' ) ) : ?>
				<?php echo $editor->displayEditor( 'message',  $discussion->message , '95%', '450', '10', '20' , false ); ?>
			<?php else : ?>
				<textarea rows="3" cols="40" name="message" id="message" style="width: 90%"><?php echo $discussion->message;?></textarea>
			<?php endif; ?>
		</dov>
	</li>
	
	<?php else : ?>
	
	<li>
		<label for="title" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_TITLE'); ?></label>
		<div class="form-field">
			<input type="text" name="title" id="title" size="40" class="input text" style="width: 90%" value="<?php echo $discussion->title;?>" />
		</div>
	</li>
	
	<li>
		<label for="message" class="form-label">*<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSSION_BODY'); ?></label>
		<div class="form-field">
			<?php 
			if( $config->get( 'htmleditor' ) == 'none' && $config->getBool('allowhtml') )
			{
			?>
			<div class="htmlTag"><?php echo JText::_('COM_COMMUNITY_HTML_TAGS_ALLOWED');?></div>
			<?php
			}?>
			
			<?php if( $config->get( 'htmleditor' ) && $config->getBool( 'allowhtml' ) ) : ?>
				<?php echo $editor->displayEditor( 'message',  $discussion->message , '95%', '450', '10', '20' , false ); ?>
			<?php else : ?>
				<textarea rows="3" cols="40" name="message" id="message" style="width: 90%"><?php echo $discussion->message;?></textarea>
			<?php endif; ?>

		</div>
	</li>
	
	<?php endif; ?>  
	
	<li class="has-seperator">
		<label for="lock" class="form-label">*<?php echo JText::_('COM_COMMUNITY_LOCK_DISCUSSION'); ?></label>
		<div class="form-field">  				
			<label for="lock-yes" class="label-radio inline">
				<input type="radio" class="input radio" name="lock" id="lock-yes" value="1"<?php echo ($discussion->lock == true ) ? ' checked="checked"' : '';?> />
				<?php echo JText::_('COM_COMMUNITY_YES');?>
			</label>
			<label for="lock-no" class="label-radio inline">
				<input type="radio" class="input radio" name="lock" id="lock-no" value="0"<?php echo ($discussion->lock == false ) ? ' checked="checked"' : '';?> />
				<?php echo JText::_('COM_COMMUNITY_NO');?>
			</label>
		</div>
	</li>

	<?php if($gparams->get('groupdiscussionfilesharing') > 0) {?>
	<li class="has-seperator">
		<div class="form-field">
			<label for="filepermission-member" class="label-checkbox">
				<input type="checkbox" class="input checkbox" value="1" name="filepermission-member" <?php echo ($params->get('filepermission-member') > 0) ? 'checked="checked"' : '' ?>/>
				*<?php echo JText::_('COM_COMMUNITY_FILES_ALLOW_MEMBERS')?> 
			</label>
		</div>
	</li>
	<?php }?>

	<?php echo $afterFormDisplay;?>
	
	<li class="has-seperator">
		<div class="form-field">
			<span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span>
		</div>
	</li>
	
	<li>
		<div class="form-field">
			<input type="hidden" value="<?php echo $group->id; ?>" name="groupid" />
			<input type="hidden" value="<?php echo $discussion->id;?>" name="topicid" />
			<input type="button" name="cancel" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON'); ?>" onclick="javascript:history.go(-1);return false;" class="btn" />
			<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_COMMUNITY_SAVE');?>" onclick="saveContent();" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</div>
	</li>
</ul>
</form>