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


<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/browserplus-min.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.gears.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.silverlight.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.flash.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.browserplus.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.html4.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/plupload.html5.js"></script>
<script type="text/javascript" src="<?php echo JURI::root();?>components/com_community/assets/multiupload_js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
<script type="text/javascript">
//<![CDATA[

(function($) {

joms.status.Creator['photo'] =
{
	attachment: {},
	initialize: function() {

		Creator = this;

		//Creator.Preview = Creator.View.find('.creator-preview');
		Creator.Preview = Creator.View.find('.joms-upload-preview');

		//Creator.Form = Creator.View.find('.creator-form');
		Creator.Form = Creator.View.find('.joms-upload-field');

		Creator.uploader = new plupload.Uploader({
	           runtimes: 'gears,html5,browserplus,html4', // Set runtimes, here it will use HTML5, if not supported will use flash, etc.
	           browse_button : 'joms-pickfiles', // The id on the select files button
	           multi_selection: true, // Allow to select one file each time
	           container : 'joms-multi-uploader', // The id of the upload form container
	           max_file_size : joms.photos.multiUpload.maxFileSize, // Maximum file size allowed
	           url : 'index.php?option=com_community&view=photos&task=ajaxPreview', // The url to the upload file
	           resize: {width: 2100, height: 2100, quality: 90}, // Resize images on clientside if we can
	           filters: [{title: "Image files", extensions: "jpg,gif,png,jpeg"}], // Specify what files to browse for
	           // Flash/Silverlight paths
	           flash_swf_url: 'components/com_community/assets/multiupload_js/plupload.flash.swf',
	           silverlight_xap_url: 'components/com_community/assets/multiupload_js/plupload.silverlight.xap',
	      });

		Creator.multiUpload();
	},
	getAttachment: function() {
		var attachment = {
			type: 'photo',
			id: Creator.attachment.id,
		}
		return attachment;
	},

	multiUpload: function() { // start multiupload

	      // Start Upload ////////////////////////////////////////////
	      // When the button with the id "#uploadfiles" is clicked the upload will start
	      joms.jQuery('.joms-postbox-action .creator-share.submit').on('click', null, function(e) {
	           //uploader.start();
	           //e.preventDefault();
	      });


		// Progress bar ////////////////////////////////////////////
		// Add the progress bar when the upload starts
		// Append the tooltip with the current percentage
		Creator.uploader.bind('UploadProgress', function(up, file) {

			$('#progress-'+file.id+' .bar').css({
				width: file.percent + "%"
			});
		});

		Creator.uploader.init(); // Initializes the Uploader instance and adds internal event listeners.

	      // Selected Files //////////////////////////////////////////
		  // When the user select a file it wiil append one div with the class "addedFile" and a unique id to the "#filelist" div.
		  // This appended div will contain the file name and a remove button
		  Creator.uploader.bind('FilesAdded', Creator.handlePluploadFilesAdded );

		// Creator.uploader.bind('BeforeUpload',function(up,file){
		// 	var defaultUrl = Creator.uploader.settings.url;
		// 	if(defaultUrl.indexOf('&albumid=') == -1){
		// 		jax.call('community', 'photos,ajaxCheckDefaultAlbum');
		// 		Creator.uploader.stop();
		// 	}
		// });

		// Remove file button //////////////////////////////////////
		// On click remove the file from the queue
		Creator.Preview.on('click', 'a.remove-image', function(e) {
			var id = $(this).data('photo-id');
			Creator.uploader.removeFile(Creator.uploader.getFile(id));
			$('#'+id).remove();
			e.preventDefault();
		});

	  Creator.uploader.bind('FileUploaded', function(up, file, info){
	  		Creator.ShareButton.removeAttr('disabled');
	  		info = joms.jQuery.parseJSON(info.response);
	  		Creator.attachment.info = info;
	  		Creator.Preview.find('#'+file.id+' .filename').remove();
	  		Creator.Preview.find('#'+file.id).data('photo-id', info.id);
	  		Creator.Preview.find('#'+file.id+' img').attr('src', "<?php echo JURI::base() ?>" + info.thumbnail).end().find('.remove-image').show();
	  		Creator.add.apply(Creator);
	  		Creator.attachment.id = info.id;

				Creator.Preview.find('#'+file.id).popover({
					title : '<i class="joms-icon-users"></i> tag people',
					content : '<div class="tag-people-placeholder"><input type="text" class="input-block" placeholder="type name here .." /><span id="people-id-1">merav knafo <i class="joms-icon-remove"></i></span><span id="people-id-2">ahmad justin <i class="joms-icon-remove"></i></span><span id="people-id-3">Dimas tekad santosa <i class="joms-icon-remove"></i></span><span id="people-id-4">Viet Vu <i class="joms-icon-remove"></i></span><span id="people-id-5">Neil <i class="joms-icon-remove"></i></span><span id="people-id-6">Hermanto Lim <i class="joms-icon-remove"></i></span></div>',
					placement : 'top',
					trigger : 'click',
					selector: '#tag-'+file.id
				}).on('click', function (e) {
					$('.popover').addClass('joms-tag-popover');
    		});

	  });

	  Creator.uploader.bind('UploadComplete', function(up, file) {
	  		up.files.each(function(e){
	  			setTimeout(function() {
				  Creator.Preview.find('#progress-'+e.id).fadeOut();
				  Creator.Preview.find('#tag-'+e.id).delay(800).show(400);
				}, 800);
	  		});
	  });


      // Error Alert /////////////////////////////////////////////
      // If an error occurs an alert window will popup with the error code and error message.
      // Ex: when a user adds a file with now allowed extension
      Creator.uploader.bind('Error', function(up, err) {
           alert("Error: " + err.code + ", Message: " + err.message + (err.file ? ", File: " + err.file.name : "") + "");
           up.refresh(); // Reposition Flash/Silverlight
      });


    }, // end multiupload

     // I handle the files-added event. This is different
	// that the queue-changed event. At this point, we
	// have an opportunity to reject files from the queue.
	handlePluploadFilesAdded: function ( up, files ) {

		// Show the client-side preview using the loaded File.
		for ( var i = 0 ; i < files.length ; i++ ) {

			Creator.showImagePreview( files[ i ] );
		}
		Creator.ShareButton.attr('disabled', 'disabled');
		up.start();
		up.refresh();
	},
    showImagePreview: function( file ) {

		var item = $( '<div class="compose-photo" id="'+file.id+'"><span class="filename">'+file.name+'</span><a data-photo-id="'+file.id+'" href="#" class="remove-image joms-remove-attachment" title="Remove Photo"><i class="joms-icon-remove"></i></a><div id="tag-'+file.id+'" class="tag-people"></div><div id="progress-'+file.id+'" class="progress"><div class="bar" style="width:0;"></div></div></div>' ).prependTo( Creator.Preview );

	  	item.find('.remove-image').hide();
	  	var image = $( new Image() ).prependTo( item );

		// Create an instance of the mOxie Image object. This
		// utility object provides several means of reading in
		// and loading image data from various sources.
		// --
		// Wiki: https://github.com/moxiecode/moxie/wiki/Image
		//var preloader = new mOxie.Image();

		// Define the onload BEFORE you execute the load()
		// command as load() does not execute async.
		//preloader.onload = function() {

			// This will scale the image (in memory) before it
			// tries to render it. This just reduces the amount
			// of Base64 data that needs to be rendered.
			//preloader.downsize( 300, 300 );

			// Now that the image is preloaded, grab the Base64
			// encoded data URL. This will show the image
			// without making an Network request using the
			// client-side file binary.
			//image.prop( "src", preloader.getAsDataURL() );

			// NOTE: These previews "work" in the FLASH runtime.
			// But, they look seriously junky-to-the-monkey.
			// Looks like they are only using like 256 colors.

		//};

		// Calling the .getSource() on the file will return an
		// instance of mOxie.File, which is a unified file
		// wrapper that can be used across the various runtimes.
		// --
		// Wiki: https://github.com/moxiecode/plupload/wiki/File
		//preloader.load( file.getSource() );

	},

	add: function()
	{
		Creator.attachment.photos = Creator.uploader.files;

	},
	// setURL: function(albumid){
	// 	var defaultUrl = Creator.uploader.settings.url;

	// 	var url = defaultUrl+'&albumid='+albumid;

	// 	Creator.uploader.settings.url = url;

	// 	Creator.uploader.start();
	// },
	reset: function()
	{
		Creator.remove();
	},
	submit: function() {
		if(Creator.attachment.id==undefined) {
			if(this.Message.hasClass('hint'))
			{
				Creator.Hint
				.html("<?php echo JText::_('COM_COMMUNITY_STATUS_PHOTOS_ERROR'); ?>")
					.show();
			}
		}

		return Creator.attachment.id!=undefined;
	},
};

})(joms.jQuery);



//]]>
</script>
<!-- Post photo panel -->
<div id="joms-photo-panel" class="joms-postbox-panel joms-tab-panel">
	<div class="joms-postbox-element clearfix last">
		<figure class="joms-postbox-avatar">
	    <img class="img-responsive joms-radius-rounded" src="<?php echo $my->getThumbAvatar(); ?>" alt="">
	  </figure>
	  	<div class="joms-postbox-field joms-textarea-bubble">
			<textarea data-minlength="0" data-maxlength="<?php echo CFactory::getConfig()->get('statusmaxchar');?>"  id="joms-write-status" placeholder="<?php echo JText::_('COM_COMMUNITY_STATUS_PHOTO_HINT'); ?>" class="joms-postbox-status creator-message joms-radius-normal" name="joms-photo-status"></textarea>
		</div>


	</div>

	<div class="joms-postbox-element clearfix last">
		<span class="joms-i">
			<i class="joms-icon-upload-alt"></i>
		</span>
		<div id="joms-multi-uploader" class="joms-postbox-field joms-upload-field">

				<div class="joms-upload-photo clearfix">
					<button id="joms-pickfiles" class="joms-upload-single-photo" onclick=""><i class="joms-icon-images"></i> <?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_STREAM'); ?></button>
					<button id="uploadfiles" class="joms-upload-album-photo" onclick="joms.notifications.showUploadPhoto('0','0'); return false;"><i class="joms-icon-images"></i> <?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD_ALBUM'); ?></button>
				</div>

		</div>
		<div class="joms-postbox-field joms-postbox-overflow">
			<div class="joms-upload-preview">

			</div>
		</div>
		<div class="creator-hint"></div>

	</div>
</div>

<!--
<div class="creator-view type-photo">
	<div class="creator-hint"></div>

	<div class="creator-preview"></div>

	<div class="creator-form">
		<label class="creator-upload-container label-filetype" for="creator-upload"></label>
		<a class="creator-toggle-upload icon-add" href="javascript: void(0);"><?php echo JText::_('COM_COMMUNITY_ADD_PHOTO'); ?></a>
	</div>
</div>
-->