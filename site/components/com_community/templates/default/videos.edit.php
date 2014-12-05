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
<form name="editvideo" action="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=saveVideo'); ?>" method="post">
	<ul class="cFormList cFormVertical cResetList">
		<li>
			<label class="form-label" for="title"><?php echo JText::_('COM_COMMUNITY_VIDEOS_TITLE');?></label>
			<div class="form-field">
				<input type="text" id="title" name="title" class="input text" value="<?php echo $this->escape($video->title);?>" style="width: 90%;"  maxlength="255"  />
			</div>
		</li>
		<li>
			<label class="form-label" for="description"><?php echo JText::_('COM_COMMUNITY_VIDEOS_DESCRIPTION');?></label>
			<div class="form-field">
				<textarea name="description" style="width: 90%;" rows="8" id="description"><?php echo $this->escape($video->description); ?></textarea>
			</div>
		</li>
		<li>
			<label class="form-label" for="category"><?php echo JText::_('COM_COMMUNITY_VIDEOS_CATEGORY');?></label>
			<div class="form-field">
				<?php  echo $categoryHTML; ?>
			</div>
		</li>
		<?php
			if($enableLocation)
			{
		?>
		<li>
			<label class="form-label" for="location"><?php echo JText::_('COM_COMMUNITY_VIDEOS_LOCATION');?></label>
			<div class="form-field">
				<input type="text" id="title" name="location" class="input text" value="<?php echo $this->escape($video->location);?>" style="width: 90%;" />
			</div>
		</li>
		<?php } ?>
		<?php
			if($showPrivacy)
			{
		?>
		<li>
			<label class="form-label" for="description"><?php echo JText::_('COM_COMMUNITY_VIDEOS_WHO_CAN_SEE');?></label>
			<div class="form-field">
				<?php echo CPrivacy::getHTML( 'permissions', $video->permissions, COMMUNITY_PRIVACY_BUTTON_LARGE, array(), 't' ); ?>
			</div>
		</li>
		<?php
			}
		?>
	</ul>

	<input type="hidden" name="id" value="<?php echo $video->id;?>" />
	<input type="hidden" name="option" value="com_community" />
	<input type="hidden" name="view" value="videos" />
	<input type="hidden" name="task" value="saveVideo" />
	<input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl;?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>