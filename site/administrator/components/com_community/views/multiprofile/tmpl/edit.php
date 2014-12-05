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
<!-- @TODO: Less -->
<style type="text/css">
#js-cpanel .ace-file-input {
	margin-bottom: 0;
}
.ace-file-input .icon-picture, .ace-file-input .icon-upload-alt {
	height: 24px;
}
#js-cpanel .ace-file-input label.selected .icon-picture {
	line-height: 25px !important;
}
</style>

<form name="adminForm" id="adminForm" action="index.php?option=com_community" method="POST" enctype="multipart/form-data">

<ul class="nav nav-tabs">
  <li class="active"><a href="#general" data-toggle="tab">General</a></li>
  <li><a href="#fields" data-toggle="tab">Fields</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="general">
		<div class="row-fluid">
			<div class="span18">
					<p><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_DETAILS_INFO');?></p>
							<table>
								<tbody>
									<tr>
										<td width="250"  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_TITLE_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_TITLE' ); ?></span>
										</td>
										<td>
											<input type="text" maxlength="50" size="50" id="name" name="name" class="text_area" value="<?php echo $this->multiprofile->name;?>">
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_DESCRIPTION_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_DESCRIPTION' ); ?></span>
										</td>
										<td>
											<textarea name="description" id="description" rows="10" cols="50"><?php echo $this->multiprofile->description;?></textarea>
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_PUBLISHED_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_PUBLISHED' ); ?></span>
										</td>
										<td>
											<?php echo CHTMLInput::checkbox('published' ,'ace-switch ace-switch-5', null , $this->multiprofile->published ); ?>
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_REQUIRE_APPROVALS_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_REQUIRE_APPROVALS' ); ?></span>
										</td>
										<td>
											<?php echo CHTMLInput::checkbox('approvals' ,'ace-switch ace-switch-5', null , $this->multiprofile->approvals ); ?>
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ALLOW_GROUP_CREATION_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ALLOW_GROUP_CREATION' ); ?></span>
										</td>
										<td>
											<?php echo CHTMLInput::checkbox('create_groups' ,'ace-switch ace-switch-5', null , $this->multiprofile->create_groups ); ?>
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_ALLOW_EVENT_CREATION_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_ALLOW_EVENT_CREATION' ); ?></span>
										</td>
										<td>
											<?php echo CHTMLInput::checkbox('create_events' ,'ace-switch ace-switch-5', null , $this->multiprofile->create_events ); ?>
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_CONFIGURATION_MULTIPROFILES_LOCK_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_CONFIGURATION_MULTIPROFILES_LOCK' ); ?></span>
										</td>
										<td>
											<?php echo CHTMLInput::checkbox('profile_lock' ,'ace-switch ace-switch-5', null , $this->multiprofile->profile_lock ); ?>
										</td>
									</tr>
									<tr>
										<td  class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_WATERMARK_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_MULTIPROFILE_WATERMARK' ); ?></span>
										</td>
										<td>
											<div>
												<div class="ace-file-input">
													<input type="file" name="watermark" id="watermark">
												</div>
												<div>
													<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_MAXIMUM_WATERMARK_IMAGE_SIZE');?>
												</div>
											</div>
										</td>
									</tr>
									<tr>
										<td class="key">
											<span class="js-tooltip" title=""><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_WATERMARK_IMAGE'); ?></span>
										</td>
										<td>
											<div class="space-12"></div>
											<div>
												<?php if( !empty( $this->multiprofile->watermark) ){ ?>
													<img src="<?php echo $this->multiprofile->getWatermark();?>" style="border: 1px solid #eee;" />&nbsp;
													<a href="<?php echo JRoute::_("index.php?option=com_community&view=multiprofile&task=removeWatermark&id=".$this->multiprofile->id)?>"><?php echo JText::_("COM_COMMUNITY_REMOVE_WATERMARK")?></a>
												<?php } else { ?>
													<?php echo JText::_('N/A');?>
												<?php } ?>
											</div>
										</td>
									</tr>
									<tr>
										<td class="key">
											<span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_WATERMARK_POSITION_TIPS'); ?>"><?php echo JText::_( 'COM_COMMUNITY_MULTIPROFILE_WATERMARK_POSITION' ); ?></span>
										</td>
										<td>
											<div class="space-12"></div>
											<div class="watermark-position" style="position:relative; width:64px; height:64px; border:1px solid #ccc; z-index:1">
												<img src="<?php echo $this->multiprofile->getThumbAvatar();?>" width="64" height="64" />
												<input type="radio" value="top" id="watermark_top" name="watermark_location" style="opacity:1;position:absolute; margin:0;padding:0; top:0;    left: 25px;"<?php echo ($this->multiprofile->watermark_location == 'top' ) ? ' checked="checked"' : '';?> />
												<input type="radio" value="right" id="watermark_right" name="watermark_location" style="opacity:1;position:absolute; margin:0;padding:0; right:0;  top:  25px;"<?php echo ($this->multiprofile->watermark_location == 'right' ) ? ' checked="checked"' : '';?>>
												<input type="radio" value="bottom" id="watermark_bottom" name="watermark_location" style="opacity:1;position:absolute; margin:0;padding:0; bottom:0; left: 25px;"<?php echo ($this->multiprofile->watermark_location == 'bottom' ) ? ' checked="checked"' : '';?> >
												<input type="radio" value="left" id="watermark_left" name="watermark_location" style="opacity:1;position:absolute; margin:0;padding:0; left:0;   top:  25px;"<?php echo ($this->multiprofile->watermark_location == 'left' ) ? ' checked="checked"' : '';?> >
											</div>
										</td>
									</tr>
									<tr>
										<td class="key">
											<span class="js-tooltip" title=""><?php echo JText::_( 'COM_COMMUNITY_MULTIPROFILE_WATERMARK_PREVIEW' ); ?></span>
										</td>
										<td>
											<div class="space-12"></div>
											<?php if( !empty( $this->multiprofile->thumb) ){ ?>
												<img src="<?php echo $this->multiprofile->getThumbAvatar();?>" style="border: 1px solid #eee;" />
											<?php } else { ?>
												<?php echo JText::_('N/A');?>
											<?php } ?>
										</td>
									</tr>
					</table>
			</div>
		</div>
  </div>

  <div class="tab-pane" id="fields">
		<div class="row-fluid">
			<div class="span24">
				<p><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_FIELDS_INFO'); ?></p>
						<div class="alert alert-info">
							<span><?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_NOTE_INFO');?></span>
						</div>
						<table class="table table-bordered table-hover">
							<thead>
								<tr class="title">
									<th width="10">#</th>
									<th>
										<?php echo JText::_('COM_COMMUNITY_NAME');?>
									</th>
									<th>
										<?php echo JText::_('COM_COMMUNITY_FIELD_CODE');?>
									</th>
									<th>
										<?php echo JText::_('COM_COMMUNITY_TYPE');?>
									</th>
									<th>
										<?php echo JText::_('COM_COMMUNITY_MULTIPROFILE_INCLUDE');?>
									</th>
								</tr>
							</thead>
							<?php
							$count	= 0;
							$i		= 0;

							foreach( $this->fields as $field )
							{
								if($field->type == 'group')
								{
				?>
							<tr class="parent">
								<td  style="background-color: #EEEEEE;">&nbsp;</td>
								<td colspan="4" style="background-color: #EEEEEE;">
									<strong><?php echo JText::_('COM_COMMUNITY_GROUPS');?>
										<span><?php echo $field->name;?></span>
									</strong>
									<div style="clear: both;"></div>
									<input type="hidden" name="parents[]" value="<?php echo $field->id;?>" />
								</td>
							</tr>
								<?php
									$i	= 0;	// Reset count
								}
								else if($field->type != 'group')
								{
									// Process publish / unpublish images
									++$i;
								?>
							<tr class="row<?php echo $i%2;?>" id="rowid<?php echo $field->id;?>">
								<td><?php echo $i;?></td>
								<td><span><?php echo $field->name;?></span></td>
								<td align="center"><?php echo $field->fieldcode; ?></td>
								<td align="center"><?php echo $field->type;?></td>
								<td id="publish<?php echo $field->id;?>">
									<input class="ace-switch ace-switch-5" type="checkbox" name="fields[]" value="<?php echo $field->id;?>"<?php echo ($this->multiprofile->isChild($field->id) || (is_array($this->postedFields) && in_array($field->id,$this->postedFields)) ) ? ' checked="checked"' : '';?> />
									<span class="lbl"></span>
								</td>
							</tr>
						<?php
								}
							$count++;
						}
						?>
						</table>

			</div>
		</div>
  </div>

</div>

<input type="hidden" name="view" value="multiprofile" />
<input type="hidden" name="task" value="save" />
<input type="hidden" name="id" value="<?php echo $this->multiprofile->id;?>" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script>
jQuery('#watermark').ace_file_input({
   no_file:'No File ...',
   btn_choose:'Choose',
   btn_change:'Change'
}).on('change', function(){
	// var files = $(this).data('ace_input_files');
	//or
	var files = $(this).ace_file_input('files');

	// var method = $(this).data('ace_input_method');
	//method will be either 'drop' or 'select'
	//or
	var method = $(this).ace_file_input('method');
});
</script>
