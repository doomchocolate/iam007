<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
?>
<script type="text/javascript">
//<![CDATA[
(function($) {
var Creator;
joms.status.Creator['video'] =
{
	attachment: {},
	initialize: function()
	{
		Creator = this;
		Creator.Preview = Creator.View.find('.creator-preview');
		Creator.Form = Creator.View.find('.creator-form');
		Creator.Form
			.submit(function()
			{
				console.log(Creator);
				Creator.add();
				return false;
			});

		Creator.VideoUrl = Creator.View.find('.creator-video-url');

		Creator.VideoUrl.on('blur', function() {
			Creator.add.apply(Creator)
		});

		/*Creator.VideoUrl
			.defaultValue("<?php echo JText::_('COM_COMMUNITY_VIDEOS_ENTER_LINK_TIPS'); ?>", 'hint');*/
		Creator.Hint = Creator.View.find('.creator-hint');

	},

	focus: function()
	{
		//this.Message.defaultValue("<?php echo JText::_('COM_COMMUNITY_STATUS_VIDEO_HINT'); ?>", 'hint');
	},

	add: function()
	{
		// Disable upload button.
		$('#joms-postbox-video-upload').addClass('disabled');

		var videoUrl = Creator.VideoUrl.val();
		joms.ajax.call('videos,ajaxLinkVideoPreview', [videoUrl], {
			beforeSend: function()
			{
				Creator.LoadingIndicator.show();
			},
			success: function(video, html)
			{


				Creator.VideoUrl.val('');
				Creator.Hint.hide();
				Creator.Form.hide();
				video.preview = $(html);
				video.preview
					.find('.creator-change-video')
					.click(function()
					{
						Creator.remove();
						$('#joms-postbox-video-upload').removeClass('disabled');
						joms.jQuery('.tipsy.tipsy-south').remove();
					});

				Creator.Preview.html(video.preview);
				Creator.attachment = video;

				$( '<div id="tag-'+ video.id +'" class="tag-people" style="bottom:0;"></div>' ).appendTo( '.creator-preview .cVideo-Thumb'  ).show();

				Creator.Preview.find('#video-'+video.id+ ' .cVideo-Thumb').popover({
					title : '<i class="joms-icon-users"></i> tag people',
					content : '<div class="tag-people-placeholder"><input type="text" class="input-block" placeholder="type name here .." /><span id="people-id-1">merav knafo <i class="joms-icon-remove"></i></span><span id="people-id-2">ahmad justin <i class="joms-icon-remove"></i></span><span id="people-id-3">Dimas tekad santosa <i class="joms-icon-remove"></i></span><span id="people-id-4">Viet Vu <i class="joms-icon-remove"></i></span><span id="people-id-5">Neil <i class="joms-icon-remove"></i></span><span id="people-id-6">Hermanto Lim <i class="joms-icon-remove"></i></span></div>',
					placement : 'top',
					trigger : 'click',
					selector: '#tag-'+video.id
				}).on('click', function (e) {
					$('.popover').addClass('joms-tag-popover');
    		});

			},
			error: function(message)
			{
				$('#joms-postbox-video-upload').removeClass('disabled');

				if ($.trim(message).length>0)
				{
					Creator.Hint
						.html(message)
						.show()
						.fadeOut(5000);
				}
			},
			complete: function()
			{
				Creator.LoadingIndicator.hide();
			}
		});
	},

	remove: function()
	{
		Creator.attachment.preview.remove();
		Creator.attachment = {};
		Creator.Form.show();
	},

	reset: function()
	{
		Creator.remove();
	},

	submit: function()
	{
		return Creator.attachment.id!=undefined;
	},

	error: function(message)
	{
		Creator.Hint
			.html(message);
	},

	getAttachment: function()
	{
		var attachment = {
			type: 'video',
			id:  Creator.attachment.id,
		}
		return attachment;
	}
};

// Toggle enable/disable for video buttons.
$('#joms-postbox-video-upload').click(function() {
	var $this = $( this );

	if ( $this.hasClass('disabled') )
		return;

	joms.sharebox.uploadVideo(1,0);
});


})(joms.jQuery);
//]]>
</script>

<!-- Post video panel -->
<div id="joms-video-panel" class="joms-postbox-panel joms-tab-panel">
	<div class="joms-postbox-element clearfix">
		<figure class="joms-postbox-avatar">
	      <img class="img-responsive joms-radius-rounded" src="<?php echo $my->getThumbAvatar(); ?>" alt="">
	    </figure>
		<div class="joms-postbox-field joms-textarea-bubble">
			<textarea data-minlength="0" data-maxlength="<?php echo CFactory::getConfig()->get('statusmaxchar');?>" id="joms-video-status" placeholder="<?php echo JText::_('COM_COMMUNITY_STATUS_VIDEO_HINT'); ?>" class="creator-message joms-postbox-status joms-radius-normal" name="joms-video-status"></textarea>
		</div>
	</div>
	<div class="joms-postbox-element clearfix" style="margin-bottom: 0;">
		<span class="joms-i">
			<i class="joms-icon-youtube-play"></i>
		</span>
		<div class="joms-postbox-field joms-upload-field">
			<a id="joms-postbox-video-upload" class="btn btn-success btn-block">Select a Video From Your Hard Disk</a>
			<input class="joms-radius-normal" id="file-upload" onchange="joms.videos.checkSize(this)" name="joms-vide-upload-status" type="file" />
		</div>
	</div>
	<div class="joms-postbox-element clearfix" style="margin-bottom: 0;">
		<div class="joms-postbox-field">
			<div style="text-align: center; padding: 2px 0;"><b>OR</b></div>
		</div>
	</div>
	<div class="joms-postbox-element clearfix" style="margin-bottom: 7px;">
		<span class="joms-i">
			<i class="joms-icon-upload"></i>
		</span>
		<div class="joms-postbox-field">
			<div class="creator-hint"></div>
			<div class="creator-preview"></div>
		</div>
		<form class="creator-form joms-postbox-field">
			<input class="creator-video-url joms-radius-normal wide" type="text" placeholder="<?php echo JText::_('COM_COMMUNITY_VIDEOS_ENTER_LINK_TIPS'); ?>" name="joms-video-link-status" />
		</form>
	</div>
	<div class="joms-postbox-element last clearfix">
		<!-- <span class="pull-left" style="width:50px;"><strong>Attach</strong></span> -->
		<div class="joms-postbox-field joms-postbox-attach joms-postbox-overflow">
			<button class="btn btn-block joms-postbox-location-button" data-default-string="Location"><i class="joms-icon-map-marker"></i> Location</button>
		</div>
		<div class="joms-postbox-field joms-postbox-location hidden">
			<input type="text" class="joms-status-location typeahead" name="joms-video-location" placholder="Enter your location here..." />
			<button class="btn joms-location-add"><i class="joms-icon-ok"></i></button>
			<button class="btn last joms-location-cancel" ><i class="joms-icon-remove"></i></button>
		</div>
	</div>
</div>

<!--
<div class="creator-view type-video">
	<div class="creator-hint"></div>
	<form class="creator-form reset-gap">
		<input type="text" name="videoUrl" class="creator-video-url input-block-level" value="" size="36" onblur='Creator.add.apply(Creator)'/>
		<span class="hint"></span>
	</form>
	<div class="creator-preview"></div>
</div>
-->
