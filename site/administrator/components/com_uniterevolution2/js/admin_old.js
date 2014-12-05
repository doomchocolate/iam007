var UniteAdminRev = new function(){
	
	var t = this;
	
	var errorMessageID = null;
	var successMessageID = null;
	var ajaxLoaderID = null;
	var ajaxHideButtonID = null;

	
	/**
	 * debug html on the top of the page (from the master view)
	 */
	t.debug = function(html){
		jQuery("#div_debug").show().html(html);
	}
	
	/**
	 * output data to console
	 */
	t.trace = function(data,clear){
		if(clear && clear == true)
			console.clear();	
		console.log(data);
	}
	
	/**
	 * show error message
	 */
	t.showErrorMessage = function(message){
		var html = "<div class='error_message_box' id='system-message-error'>"+message+"</div>";
		jQuery("#system-message-container").html(html);
		
		showAjaxButton();
	}
	
	
	/**
	 * escape html, turn html to a string
	 */
	t.htmlspecialchars = function(string){
		  return string
		      .replace(/&/g, "&amp;")
		      .replace(/</g, "&lt;")
		      .replace(/>/g, "&gt;")
		      .replace(/"/g, "&quot;")
		      .replace(/'/g, "&#039;");
	}
	
	/**
	 * strip html tags
	 */
	t.stripTags = function(input, allowed) {
		
	    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
        var commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
       
	    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
	        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	    });
	}
	
	
	
	/**
	 * hide error message
	 */
	var hideErrorMessage = function(){
		jQuery("#system-message-container").html("");		
	}
	
	/**
	 * set success message id
	 */
	t.setSuccessMessageID = function(id){
		successMessageID = id;
	}

	/**
	 * set success message id
	 */
	t.setErrorMessageID = function(id){
		errorMessageID = id;
	}
	
	/**
	 * hide success message and show ajax button 
	 */
	t.hideSuccessMessageTimeout = function(){
		jQuery("#"+successMessageID).hide();		
		showAjaxButton();
	}
	
	/**
	 * show system message
	 */
	t.showSystemMessage = function(message){
		var html = "<div class='success_message_box' id='system-message-success'>"+message+"</div>";
		jQuery("#system-message-container").html(html);
		
		setTimeout("jQuery('#system-message-success').hide('slow')",3000);
	}
	
	
	
	/**
	 * how success message
	 */
	var showSuccessMessage = function(message){
		
		if(successMessageID){	//show custom message
			jQuery("#"+successMessageID).show();
			if(message && message != "")
				jQuery("#"+successMessageID).html(message);
			
			setTimeout("UniteAdminRev.hideSuccessMessageTimeout()",500);
			
		}else{	//show system message
			
			var html = "<div class='success_message_box' id='system-message-success'>"+message+"</div>";
			jQuery("#system-message-container").html(html);
	 		
			//hide the message delay
			if(jQuery('#system-message-success').length)
				setTimeout("jQuery('#system-message-success').hide('slow')",3000);
			
			showAjaxButton();
		}
	}
	
		
	/**
	 * hide system message with delay
	 */
	t.hideSystemMessageDelay = function(){
		
		if(jQuery('#system-message').length)		
			setTimeout("jQuery('#system-message').hide('slow')",1000);
	}
	
	
	/**
	 * set ajax loader id that will be shown, and hidden on ajax request
	 * this loader will be shown only once, and then need to be sent again.
	 */
	this.setAjaxLoaderID = function(id){
		ajaxLoaderID = id;
	}
	
	/**
	 * show loader on ajax actions
	 */
	var showAjaxLoader = function(){
		if(ajaxLoaderID)
			jQuery("#"+ajaxLoaderID).show();
	}
	
	/**
	 * hide and remove ajax loader. next time has to be set again before "ajaxRequest" function.
	 */
	var hideAjaxLoader = function(){
		if(ajaxLoaderID){
			jQuery("#"+ajaxLoaderID).hide();
			ajaxLoaderID = null;
		}
	}
	
	/**
	 * set button to hide / show on ajax operations.
	 */
	this.setAjaxHideButtonID = function(buttonID){
		ajaxHideButtonID = buttonID;
	}
	
	
	/**
	 * if exist ajax button to hide, hide it.
	 */
	var hideAjaxButton = function(){
		if(ajaxHideButtonID)
			jQuery("#"+ajaxHideButtonID).hide();
	}
	
	/**
	 * if exist ajax button, show it, and remove the button id.
	 */
	var showAjaxButton = function(){
		
		if(ajaxHideButtonID){
			jQuery("#"+ajaxHideButtonID).show();
			ajaxHideButtonID = null;
		}
	}
	
	
	/**
	 * Ajax request function. call wp ajax, if error - print error message.
	 * if success, call "success function" 
	 */
	this.ajaxRequest = function(action,data,successFunction){
		
		var objData = {
			action:action,
			client_action:action,
			data:data
		}
		
		hideErrorMessage();
		showAjaxLoader();
		hideAjaxButton();
		
		jQuery.ajax({
			type:"post",
			url:g_urlAjax,
			dataType: 'json',
			data:objData,
			success:function(response){
				hideAjaxLoader();
				
				if(!response){
					t.showErrorMessage("Empty ajax response!");
					return(false);					
				}

				if(response == -1){
					t.showErrorMessage("ajax error!!!");
					return(false);
				}
				
				if(response == 0){
					t.showErrorMessage("ajax error, action: <b>"+action+"</b> not found");
					return(false);
				}
				
				if(response.success == undefined){
					t.showErrorMessage("The 'success' param is a must!");
					return(false);
				}
				
				if(response.success == false){
					t.showErrorMessage(response.message);
					return(false);
				}
				
				//success actions:

				//run a success event function
				if(typeof successFunction == "function"){
					successFunction(response);
				}
				
				if(successMessageID)
					showSuccessMessage(response.message);
				else
					showAjaxButton();
				
				if(response.is_redirect)
					location.href=response.redirect_url;
			
			},		 	
			error:function(jqXHR, textStatus, errorThrown){
				hideAjaxLoader();
				
				if(textStatus == "parsererror")
					t.debug(jqXHR.responseText);
				
				t.showErrorMessage("Ajax Error!!! " + textStatus);
			}
		});
		
	}//ajaxrequest
	
	
	/**
	 * load css file on the fly
	 * replace current item if exists
	 */
	this.loadCssFile = function(urlCssFile,replaceID){
		var rand = Math.floor((Math.random()*100000)+1);
		
		urlCssFile += "?rand="+rand;
			
		if(replaceID)
			jQuery("#"+replaceID).remove();
		
		jQuery("head").append("<link>");
		var css = jQuery("head").children(":last");
		css.attr({
		      rel:  "stylesheet",
		      type: "text/css",
		      href: urlCssFile
		});
		
		//replace current element
		if(replaceID)
			css.attr({id:replaceID});
		
	}
	
	/**
	 * on arrow change setting event. Changes arrow image
	 */
	this.onArrowsChange = function(data){
		var settingID = data.settingID;
		var urlImage = data.url_right;
		var arrowName = data.arrowName;
		
		jQuery("#"+settingID).val(arrowName);
		jQuery("#"+settingID+"-img").prop({"src":urlImage,"title":arrowName});
	}
	
	
	/**
	 * hide form field
	 */
	this.hideFormField = function(field){
		jQuery("#"+field).hide();
		jQuery("#"+field+"-lbl").hide();
		jQuery("#"+field+"-btn").hide();
	}
	
	this.showFormField = function(field){
		jQuery("#"+field).show().removeClass("hidden");
		jQuery("#"+field+"-lbl").show().removeClass("hidden");
	}

	/**
	 * upen "add image" dialog
	 */
	t.openAddImageDialog = function(title,onInsert){
		
		//clear field (from master view)
		jQuery("#field_image_dialog_choose").val("");
		
		var url = "index.php?option=com_media&view=images&tmpl=component&author=&fieldid=field_image_dialog_choose";
		var options = {};
		options.handler = "iframe";
		options.size = {x:800,y:500};
		
		var obj = SqueezeBox.open(url,options);
		obj.addEvent('onClose',function(){
			var urlImage = jQuery("#field_image_dialog_choose").val();
			if(urlImage != "" && urlImage != undefined){
				urlImage = g_urlBase + urlImage;
				onInsert(urlImage);	
			}
		});
	}
	
	
	/**
	 * set html to youtube dialog
	 * if empty data - clear the dialog
	 */
	var setYoutubeDialogHtml = function(data){
		
		//if empty data - clear the dialog
		if(!data){
			jQuery("#video_content").html("");
			return(false);
		}
		
		var thumb = data.thumb_medium;
		
		var html = '<div class="video-content-title">'+data.title+'</div>';
		html += '<img src="'+thumb.url+'" width="'+thumb.width+'" height="'+thumb.height+'" alt="thumbnail">';
		html += '<div class="video-content-description">'+data.desc_small+'</div>';
		
		jQuery("#video_content").html(html);
	}
	
	
	/**
	 * youtube callback script, set and store youtube data, and add it to dialog
	 */
	t.onYoutubeCallback = function(obj){
		jQuery("#youtube_loader").hide();
		var desc_small_size = 200;
		
		//prepare data
		var entry = obj.entry;
		var data = {};
		data.id = jQuery("#youtube_id").val();
		data.video_type = "youtube";
		data.title = entry.title.$t;
		data.author = entry.author[0].name.$t;
		data.link = entry.link[0].href;
		data.description = entry.media$group.media$description.$t;
		data.desc_small = data.description;
		
		if(data.description.length > desc_small_size)
			data.desc_small = data.description.slice(0,desc_small_size)+"...";
		
		var thumbnails = entry.media$group.media$thumbnail;
		
		data.thumb_small = {url:thumbnails[0].url,width:thumbnails[0].width,height:thumbnails[0].height};
		data.thumb_medium = {url:thumbnails[1].url,width:thumbnails[1].width,height:thumbnails[1].height};
		data.thumb_big = {url:thumbnails[2].url,width:thumbnails[2].width,height:thumbnails[2].height};
		
		//set html in dialog
		setYoutubeDialogHtml(data);
		
		//store last video data
		lastVideoData = data;
		
		//show controls:
		jQuery("#video_hidden_controls").show();
	}
	
	
	/**
	 * vimeo callback script, set and store vimeo data, and add it to dialog
	 */	
	t.onVimeoCallback = function(obj){
		jQuery("#vimeo_loader").hide();
		
		var desc_small_size = 200;
		obj = obj[0];
		
		var data = {};
		data.video_type = "vimeo";
		data.id = obj.id;
		data.title = obj.title;
		data.link = obj.url;
		data.author = obj.user_name;
		
		data.description = obj.description;
		if(data.description.length > desc_small_size)
			data.desc_small = data.description.slice(0,desc_small_size)+"...";
		
		data.thumb_large = {url:obj.thumbnail_large,width:640,height:360};
		data.thumb_medium = {url:obj.thumbnail_medium,width:200,height:150};
		data.thumb_small = {url:obj.thumbnail_small,width:100,height:75};
		
		//set html in dialog
		setYoutubeDialogHtml(data);
		
		//store last video data
		lastVideoData = data;
		
		//show controls:
		jQuery("#video_hidden_controls").show();
	}

	
	/**
	 * show error message on the dialog
	 */
	t.videoDialogOnError = function(){
		//if ok, don't do nothing
		if(jQuery("#video_hidden_controls").is(":visible"))
			return(false);
		
		//if error - show message
		jQuery("#youtube_loader").hide();
		var html = "<div class='video-content-error'>Video Not Found!</div>";
		jQuery("#video_content").html(html);
	}
	
	
	/**
	 * open dialog for youtube or vimeo import , add / update
	 */
	t.openVideoDialog = function(callback,objCurrentVideoData){
		
		lastVideoCallback = callback;
		
		var dialogVideo = jQuery("#dialog_video");
		
		//set buttons:
		var buttons = {
			"Close":function(){
				dialogVideo.dialog("close");
			}
		};
		
		//clear the dialog content
		setYoutubeDialogHtml(false);
		jQuery("#video_hidden_controls").hide();
		jQuery("#button-video-add").text("Add This Video");

		
		//open the dialog
		dialogVideo.dialog({
				buttons:buttons,
				minWidth:700,
				minHeight:400,
				modal:true
		});
		
		//if update dialog open:		
		if(objCurrentVideoData)
			setVideoDialogUpdateMode(objCurrentVideoData);
		
	}
	
	
	/**
	 * prepare the dialog for video update
	 */
	var setVideoDialogUpdateMode = function(data){
		
		//set mode and video id
		if(data.video_type == "youtube"){
			jQuery("#video_radio_youtube").trigger("click");
			jQuery("#youtube_id").val(data.id);
		}
		else{
			jQuery("#video_radio_vimeo").trigger("click");
			jQuery("#vimeo_id").val(data.id);
		}
		
		//set width and height:
		jQuery("#input_video_width").val(data.width);
		jQuery("#input_video_height").val(data.height);

		//change button text:
		jQuery("#button-video-add").text("Update Video");
		
		//search
		if(data.video_type == "youtube")
			jQuery("#button_youtube_search").trigger("click");
		else
			jQuery("#button_vimeo_search").trigger("click");
	}
	
	
	/**
	 * init video dialog buttons
	 */
	var initVideoDialog = function(){
		
		//set youtube radio checked:
		jQuery("#video_radio_youtube").prop("checked",true);
		
		//set radio boxes:
		jQuery("#video_radio_vimeo").click(function(){
			jQuery("#video_block_youtube").hide();
			jQuery("#video_block_vimeo").show();
		});
		
		jQuery("#video_radio_youtube").click(function(){
			jQuery("#video_block_vimeo").hide();
			jQuery("#video_block_youtube").show();
		});
		
		
		//set youtube search action
		jQuery("#button_youtube_search").click(function(){
			
			//init data
			setYoutubeDialogHtml(false);
			jQuery("#video_hidden_controls").hide();
			
			jQuery("#youtube_loader").show();
			var youtubeID = jQuery("#youtube_id").val();
			youtubeID = jQuery.trim(youtubeID);
			var urlAPI = "http://gdata.youtube.com/feeds/api/videos/"+youtubeID+"?v=2&alt=json-in-script&callback=UniteAdminRev.onYoutubeCallback";
			
			jQuery.getScript(urlAPI);
			
			//handle not found:
			setTimeout("UniteAdminRev.videoDialogOnError()",2000);
		});
		
		
		//add the selected video to the callback function
		jQuery("#button-video-add").click(function(){
			if(!lastVideoData)
				return(false);
			
			lastVideoData.width = jQuery("#input_video_width").val();
			lastVideoData.height = jQuery("#input_video_height").val();
			
			if(typeof lastVideoCallback == "function")
				lastVideoCallback(lastVideoData);
			
			jQuery("#dialog_video").dialog("close");
			
		});
		
		//set vimeo search
		jQuery("#button_vimeo_search").click(function(){
			
			//init data
			setYoutubeDialogHtml(false);
			jQuery("#video_hidden_controls").hide();
			
			jQuery("#vimeo_loader").show();
			
			var vimeoID = jQuery("#vimeo_id").val();
			vimeoID = jQuery.trim(vimeoID);
			
			var urlAPI = 'http://www.vimeo.com/api/v2/video/' + vimeoID + '.json?callback=UniteAdminRev.onVimeoCallback'; 
			jQuery.getScript(urlAPI);
		});
	}//end initVideoDialog

	
	/**
	 * init footer version dialog
	 */
	var initeFooterVersionDialog = function(){
		jQuery("#link_footer_version").click(function(){
			
			//get the log
			if(jQuery("#dialog_version_content").text() == ""){
				
				UniteAdminRev.ajaxRequest("get_release_log","",function(response){
					var data = response.data;
					jQuery("#dialog_version_content").html(data);
				});
			}
			
			//open dialog
			jQuery("#dialog_version").dialog({
					minWidth:800,
					modal:true,
					buttons:{"Close":function(){jQuery(this).dialog("close")}}					
				}
			);
		});
				
	}
	
	/**
	 * init every page in the project
	 */
	initGlobal = function(){
		t.hideSystemMessageDelay();
		initVideoDialog();
		initeFooterVersionDialog();
	}

	//script for global init
	jQuery(document).ready(function(){
		initGlobal();
	})
	
} //end object


//user functions:
function trace(data,clear){
	UniteAdminRev.trace(data,clear);
}

function debug(data){
	UniteAdminRev.debug(data);
}

