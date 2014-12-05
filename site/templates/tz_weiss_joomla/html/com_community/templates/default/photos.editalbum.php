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
<form name="newalbum" id="newalbum" method="post" action="<?php echo CRoute::getURI(); ?>" class="cForm community-form-validate">


<ul class="cFormList cFormHorizontal cResetList">
	<?php echo $beforeFormDisplay;?>

	<!-- name -->
	<li>
		<label for="name" class="form-label">
			<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_NAME');?><span class="required-sign"> *</span>
		</label>
		<div class="form-field">
			<input type="text" id="name" name="name" class="input text required" size="35" value="<?php echo $this->escape($album->name); ?>" />
		</div>
	</li>
	<!-- location -->
	<?php if ($enableLocation) { ?>
	<li>
		<label for="location" class="form-label">
			<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_LOCATION');?>
		</label>
		<div class="form-field">
			<input name="location" id="location" class="input text" type="text" size="35" value ="<?php echo $this->escape($album->location); ?>"/>
			<div class="form-helper"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_LOCATION_DESC'); ?></div>
		</div>
	</li>
	<?php } ?>

	<!-- description -->
	<li>
		<label for="description" class="form-label">
			<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC');?>
		</label>
		<div class="form-field">
			<textarea name="description" id="description" class="description input textarea"><?php echo $this->escape($album->description); ?></textarea>
		</div>
	</li>

	<!-- permission -->
	<li>
		<label for="privacy" class="form-label">
			<?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_VISIBILITY');?>
		</label>
		<?php if ($type == 'group') { ?>
		<div class="form-field">
			<span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_PHOTOS_GROUP_MEDIA_PRIVACY_TIPS' ); ?></span>
		</div>
		<?php } else { ?>
		<div class="form-field">
			<div class="form-privacy inline">
				<?php echo CPrivacy::getHTML( 'permissions', $permissions, COMMUNITY_PRIVACY_BUTTON_LARGE ); ?>
			</div>
		</div>
		<?php } ?>
	</li>

	<!-- hint -->
	<li class="has-seperator">
		<div class="form-field"><span class="form-helper"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span></div>
	</li>
	<?php echo $afterFormDisplay;?>

	<!-- button -->
	<li>
		<div class="form-field">
			<input type="hidden" name="albumid" value="<?php echo $album->id; ?>" />
			<input type="hidden" name="referrer" value="<?php echo $referrer; ?>" />
			<input type="hidden" name="type" value="<?php echo $type;?>" />
			<input type="button" class="btn" onclick="history.go(-1);return false;" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON');?>" />
			<?php if(empty($album->id)) { ?>
			<input type="submit" class="btn btn-primary validateSubmit" value="<?php echo JText::_('COM_COMMUNITY_PHOTOS_CREATE_ALBUM_BUTTON');?>" />
			<?php } else { ?>
			<input type="submit" class="btn btn-primary validateSubmit" value="<?php echo JText::_('COM_COMMUNITY_PHOTOS_SAVE_ALBUM_BUTTON');?>" />
			<?php } ?>
			<?php echo JHTML::_( 'form.token' ); ?>
		</div>
	</li>
</ul>
</form>
<script type="text/javascript">
	joms.jQuery( document ).ready( function(){
    	joms.privacy.init();
	});
	cvalidate.init();
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');
</script>