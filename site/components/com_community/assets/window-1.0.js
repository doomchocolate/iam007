var mouse_is_inside = false;
var cwindow_is_modeless = false;

/* Store window size to diffrent place */
var cWindowHelper = {
	/* Public var */
	contentOuterHeight: 0,
	contentWrapHeight: 0,
	contentHeight: 0,
	intervalHandle: 0,
	rotationHandle: 0,
	/* Save current size */
	save: function(){
		//Select cWindow
		var cWindow = joms.jQuery('#cWindow');
		//Check for cWindow existing
		if(cWindow.length > 0)
		{
			this.contentOuterHeight = cWindow.find('#cWindowContentOuter').height();
			this.contentWrapHeight = cWindow.find('#cWindowContentWrap').height();
			this.contentHeight = cWindow.find('#cWindowContent').outerHeight();
		}
	},
	/* Set old content mark */
	setMark: function (){
		if(this.getMark() == false)joms.jQuery('#cWindow').find('#cWindowContent').append('<div id="oldcontentmark"></div>');
	},
	/* Get old content mark` */
	getMark: function(){
		/* Select cWindow */
		var cWindow = joms.jQuery('#cWindow');
		/* Check for cWindow existing */
		if(cWindow.length > 0)
		{
			return (cWindow.find('#cWindowContent').find('#oldcontentmark').length > 0)? true: false;
		}else{
			return null;
		}
	},
	isMobile: function () {
		a = navigator.userAgent || navigator.vendor || window.opera;
		return (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) ? true : false;
	},
	/* Start autoresize */
	start: function()
	{
		//If cWindow isn't existed
		if(this.getMark() === null) return;
		//Stop old stream if existed
		this.stop();
		//Start auto resize
		// this.intervalHandle = window.setInterval(function(){
		// 	cWindowAutoResize();
		// }, 500);
	},
	/* Stop auto resize*/
	stop: function(){
		if(this.intervalHandle > 0)
			window.clearInterval(this.intervalHandle);
	},
	/* Start device rotation handle */
	startRotationHandle: function()
	{
		//If cWindow isn't existed
		if(this.getMark() === null) return;
		//Only start on mobile device
		if(this.isMobile() === false) return;
		//Stop old stream if existed
		this.stopRotationHandle();
		//Start auto resize
		this.rotationHandle = window.setInterval(function(){
			cWindowDeviceRotation();
		}, 500);
	},
	/* Stop device rotation handle */
	stopRotationHandle: function(){
		if(this.rotationHandle > 0)
			window.clearInterval(this.rotationHandle);
	},

	/* Browser information */
	hostInfo: function() {
		var host = this.host;
		if ( !host ) {
			host = {};
			host.ua = navigator.userAgent.toLowerCase();
			host.ios = host.ua.match(/iphone|ipad|ipod/);
			this.host = host;
		}
		return host;
	},

	/* Hide iframes as it appear on top of cWindow */
	hideIframe: function() {
		var iframes = joms.jQuery('#community-wrap iframe'),
			host = cWindowHelper.hostInfo();

		if ( !host.ios ) {
			iframes.css('visibility', 'hidden');
			return;
		}

		iframes.each(function( i, el ) {
			var liveleak = el.src.match( /liveleak/ ),
				iframe = joms.jQuery( el );

			if ( !liveleak ) {
				iframe.css('visibility', 'hidden');
				return;
			}

			iframe.parent('.cVideo-Wrapper').addClass('cVideo-WrapperX').css({ height: iframe.height() });
			iframe.css( 'height', 1 );
		});

	},

	/* Show iframes when cWindow closed */
	unhideIframe: function() {
		var iframes = joms.jQuery('#community-wrap iframe'),
			host = cWindowHelper.hostInfo();

		if ( !host.ios ) {
			iframes.css('visibility', 'visible');
			return;
		}

		iframes.each(function( i, el ) {
			var iframe = joms.jQuery( el ),
				parent = iframe.parent('.cVideo-Wrapper');

			if ( parent.hasClass('cVideo-WrapperX') ) {
				iframe.css('height', '');
			} else {
				iframe.css('visibility', 'visible');
			}
		});

	}

};

function cWindowShow(windowCall, winTitle, contentWidth, contentHeight, winType)
{
	// Concat `winType`
	if (winType instanceof Array) {
		winType = winType.join(' ');
	}

	// @TODO: Combine cAdminWindowShow with the current function
	if(/^.+\"\s*admin\s*,.+\;$/.test(windowCall)) {
		cAdminWindowShow(windowCall, winTitle, contentWidth, contentHeight, winType);
		return;
	}

	// Added to avoid the event bound on cMiniWindowShow
	joms.jQuery("body").unbind("mouseup");

	// Remove old cWindow
	joms.jQuery('#cWindow').remove();

	/* Original HTML at bottom. Edit, encodeURIComponent and put it back here. */
	var cWindowHTML = decodeURIComponent('%3Cdiv%20id%3D%22cWindow%22%20class%3D%22%7BcWindoClass%7D%22%3E%0A%09%3Cdiv%20id%3D%22cwin_tl%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20id%3D%22cwin_tm%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20id%3D%22cwin_tr%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20style%3D%22clear%3A%20both%3B%22%3E%3C%2Fdiv%3E%0A%0A%09%3Cdiv%20id%3D%22cwin_ml%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20id%3D%22cWindowContentOuter%22%3E%0A%0A%09%09%3Cdiv%20id%3D%22cWindowContentTop%22%3E%0A%09%09%09%3Ca%20href%3D%22javascript%3Avoid(0)%3B%22%20onclick%3D%22cWindowHide()%3B%22%20id%3D%22cwin_close_btn%22%3E%3C%2Fa%3E%0A%09%09%09%3Cdiv%20id%3D%22cwin_logo%22%3E%3C%2Fdiv%3E%0A%09%09%09%3Cdiv%20class%3D%22clr%22%3E%3C%2Fdiv%3E%0A%09%09%3C%2Fdiv%3E%0A%0A%09%09%3Cdiv%20id%3D%22cWindowContentWrap%22%3E%0A%09%09%09%3Cdiv%20id%3D%22cWindowContent%22%3E%3C%2Fdiv%3E%0A%09%09%3C%2Fdiv%3E%0A%0A%09%3C%2Fdiv%3E%0A%09%3Cdiv%20id%3D%22cwin_mr%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20style%3D%22clear%3A%20both%3B%22%3E%3C%2Fdiv%3E%0A%0A%09%3Cdiv%20id%3D%22cwin_bl%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20id%3D%22cwin_bm%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20id%3D%22cwin_br%22%3E%3C%2Fdiv%3E%0A%09%3Cdiv%20style%3D%22clear%3A%20both%3B%22%3E%3C%2Fdiv%3E%0A%3C%2Fdiv%3E');

	// add additional class to cWindow
	if (cwindow_is_modeless)
		cWindowHTML = cWindowHTML.replace('{cWindoClass}', 'dialog modeless');
	else
		cWindowHTML = cWindowHTML.replace('{cWindoClass}', 'dialog');

	cwindow_is_modeless = false;

	var cWindow = joms.jQuery(cWindowHTML);

	var cWindowSize;
	var cBrowserWidth = joms.jQuery(window).width();

	if (cBrowserWidth <= '480') {

		cWindowSize = {
			contentWrapHeight: function() {
				return +contentHeight
			},
			contentOuterWidth: function() {
				return +cBrowserWidth - 20
			},
			contentOuterHeight: function() {
				return +contentHeight + 40
			},
			width: function() {
				return this.contentOuterWidth() - 50
			},
			height: function() {
				return this.contentOuterHeight() + 2
			},
			left: function() {
				return (joms.jQuery(window).width() - this.width()) / 2
			},
			top: function() {
				return joms.jQuery(document).scrollTop() + ((joms.jQuery(window).height() - this.height()) / 2)
			},
			zIndex: function() {
				return cGetZIndexMax() + 1
			}
		};
		//console.log('smartphone');

	} else {

		cWindowSize = {
			contentWrapHeight: function() {
				return +contentHeight
			},
			contentOuterWidth: function() {
				return +contentWidth
			},
			contentOuterHeight: function() {
				return +contentHeight + 40
			},
			width: function() {
				return this.contentOuterWidth() + 2
			},
			height: function() {
				return this.contentOuterHeight() + 2
			},
			left: function() {
				return (joms.jQuery(window).width() - this.width()) / 2
			},
			top: function() {
				return joms.jQuery(document).scrollTop() + ((joms.jQuery(window).height() - this.height()) / 2)
			},
			zIndex: function() {
				return cGetZIndexMax() + 1
			}
		};
		//console.log('normal');

	}

	cWindow.find('#cwin_logo')
			.html(winTitle);

	cWindow.find('#cWindowContentWrap')
			.css(
			{
				'height': cWindowSize.contentWrapHeight()
			});

	cWindow.find('#cWindowContentOuter, #cwin_tm, #cwin_bm')
			.css(
			{
				'width': cWindowSize.contentOuterWidth()
			});

	cWindow.find('#cWindowContentOuter, #cwin_ml, #cwin_mr')
			.css(
			{
				'height': cWindowSize.contentOuterHeight() + 40
			});

	cWindow
			.attr(
			{
				'class': winType
			})
			.css(
			{
				'width': cWindowSize.width(),
				'height': cWindowSize.height(),
				'top': cWindowSize.top(),
				'left': cWindowSize.left(),
				'zIndex': cWindowSize.zIndex()
			})
			.prependTo('body');

	if ( winType && winType.match(/noresize/) ) {
		cWindowResize( contentHeight );
	}

	// Set up behaviour
	jax.loadingFunction = function() {
		joms.jQuery('#cWindowContentWrap').addClass('loading');
	};
	jax.doneLoadingFunction = function() {
		joms.jQuery('#cWindowContentWrap').removeClass('loading')
				.css({
					overflow: 'auto',
					overflowX: 'hidden',
					overflowY: 'auto'
				});
	};

	if (windowCall != undefined && typeof(windowCall) == "string")
		eval(windowCall);
	if (typeof(windowCall) == "function")
		windowCall();

	/* Fixes */
	// Rebuild alpha transparent border in IE6
	if (joms.jQuery.browser.msie && joms.jQuery.browser.version.substr(0, 1) < 7 && typeof(jomsIE6) != "undefined" && jomsIE6 == true)
	{
		joms.jQuery('#cwin_tm, #cwin_bm, #cwin_ml, #cwin_mr').each(function()
		{
			joms.jQuery(this)[0].filters(0).sizingMethod = "crop";
		})
	}

	// Hide iframe as it appear on top of cWindow
	cWindowHelper.hideIframe();

	/* Fixes */

	//Save current size
	cWindowHelper.save();
	//Start cWindow auto resize
	cWindowHelper.start();
    //Start device rotation handle
    cWindowHelper.startRotationHandle();
}

function cMiniWindowShow(windowCall, winTitle, contentWidth, contentHeight, winType) {
	cwindow_is_modeless = true;
	cWindowShow(windowCall, winTitle, contentWidth, contentHeight, winType);

	/** Catch all click outside main div */
	joms.jQuery('#cWindow').hover(function() {
		mouse_is_inside = true;
	}, function() {
		mouse_is_inside = false;
	});

	joms.jQuery("body").mouseup(function(e) {

		// Add to avoid IE treating select boxes in cWindow as something outside cWindow
		// Therefore, on select option will not close cWindow (in IE Only).
		if (joms.jQuery.browser.msie)
		{
			if (e.target.tagName == 'SELECT' || e.target.tagName == 'OPTION')
			{
				return false;
			}
		}

		if (!mouse_is_inside)
		{
			cMiniWindowHide();
		}
	});
}

function cAdminWindowShow(windowCall, winTitle, contentWidth, contentHeight, winType)
{
	// Added to avoid the event bound on cMiniWindowShow
	joms.jQuery("body").unbind("mouseup");

	//Remove old cWindow
	joms.jQuery('#cWindow').remove();

	/* Original HTML at bottom. Edit, encodeURIComponent and put it back here. */
	var cWindowHTML = decodeURIComponent('%3Cdiv%20id%3D%22cWindow%22%20class%3D%22modal%20%7BcWindoClass%7D%22%3E%20%3Cdiv%20class%3D%22modal-header%20cWindowContentTop%22%3E%20%3Ca%20href%3D%22javascript%3Avoid(0)%3B%22%20onclick%3D%22cWindowHide()%3B%22%20class%3D%22close%22%3E%C3%97%3C%2Fa%3E%20%3Ch3%20id%3D%22cwin_logo%22%3E%3C%2Fh3%3E%20%3C%2Fdiv%3E%20%3Cdiv%20id%3D%22cWindowContentOuter%22%3E%20%3Cdiv%20class%3D%22modal-body%22%3E%20%3Cdiv%20id%3D%22cWindowContentWrap%22%3E%20%3Cdiv%20id%3D%22cWindowContent%22%3E%3C%2Fdiv%3E%20%3C%2Fdiv%3E%20%3C%2Fdiv%3E%20%3C%2Fdiv%3E%20%3C%2Fdiv%3E');

/*
<div id="cWindow" class="modal {cWindoClass}">
	<div class="modal-header cWindowContentTop">
		<a href="javascript:void(0);" onclick="cWindowHide();" class="close">×</a>
		<h3 id="cwin_logo"></h3>
	</div>
	<div id="cWindowContentOuter">
		<div class="modal-body">
			<div id="cWindowContentWrap">
				<div id="cWindowContent"></div>
			</div>
		</div>
	</div>
</div>
 */

	// add additional class to cWindow
	if (cwindow_is_modeless)
		cWindowHTML = cWindowHTML.replace('{cWindoClass}', 'dialog modeless');
	else
		cWindowHTML = cWindowHTML.replace('{cWindoClass}', 'dialog');

	cwindow_is_modeless = false;

	var cWindow = joms.jQuery(cWindowHTML);

	var cWindowSize;

	cWindowSize = {
		contentWrapHeight: function() {
			return +contentHeight
		},
		contentOuterHeight: function() {
			return +contentHeight + 55
		},
		height: function() {
			return this.contentOuterHeight() + 2
		},
	};

	cWindow.find('#cwin_logo')
			.html(winTitle);

	cWindow.find('#cWindowContentOuter, #cwin_ml, #cwin_mr')
			.css(
			{
				// 'height': cWindowSize.contentOuterHeight()
			});

	cWindow.find('.modal-body')
			.css(
			{
				'max-height': (joms.jQuery(window).height() - 290) + 'px'
			});

	cWindow.attr(
			{
				'class': winType
			})
			.hide()
			.prependTo('#js-cpanel')
			.fadeIn();

	// Set up behaviour
	jax.loadingFunction = function() {
		joms.jQuery('#cWindowContentWrap').addClass('loading');
	};
	jax.doneLoadingFunction = function() {
		joms.jQuery('#cWindowContentWrap').removeClass('loading')
				.css({
					overflow: 'auto',
					overflowX: 'hidden',
					overflowY: 'auto'
				});
	};

	if (windowCall != undefined && typeof(windowCall) == "string")
		eval(windowCall);
	if (typeof(windowCall) == "function")
		windowCall();

	// Hide iframe as it appear on top of cWindow
	cWindowHelper.hideIframe();

	//Save current size
	cWindowHelper.save();

	//Start cWindow auto resize
	cWindowHelper.start();
}

function cWindowHide()
{
	var cWindow = joms.jQuery('#cWindow');

	cWindow.find('#cWindowAction').add('<div>')
			.animate({bottom: '-30px'}, 'fast', function()
	{
		cWindow.fadeOut('fast', function()
		{
			cWindow.find('iframe').prop('src', '');
			cWindow.remove();
			cWindowHelper.unhideIframe();
			//Stop cWindow auto resize
			cWindowHelper.stop();
            //Stop device rotation handle
            cWindowHelper.startRotationHandle();
		});
	});
}

function cMiniWindowHide()
{
	var cWindow = joms.jQuery('#cWindow');
	cWindow.remove();
	cWindowHelper.unhideIframe();
}

// Add content to cWindow and auto-resize them
function cWindowAddContent(html, actions, cmd)
{
	var cWindow = joms.jQuery('#cWindow'),
			cWindowContent;

	if (!cWindow.length) {
		return;
	}

	cWindowContent = cWindow.find('#cWindowContent');
	cWindowContent.html(html);

	if (actions)
	{
		joms.jQuery('#cWindowAction').remove();
		joms.jQuery('<div id="cWindowAction" style="position:relative; height:50px">')
				.html(actions)
				.appendTo('#cWindowContentOuter');
	}

	// Defer resize until current execution-cycle done.
	setTimeout(function(){
		cWindowResize( cWindowContent.innerHeight() );
		cWindowContent.find('img').load(function() {
			cWindowResize( cWindowContent.innerHeight() );
		});
	}, 0);

	if (cmd != undefined && cmd != '') {
		eval(cmd);
	}
}

function cWindowResize(h)
{
	var cWindow = joms.jQuery('#cWindow'),
		maxH, wrapper;

	if (!cWindow.length) {
		return;
	}

	// Limit cWindow size
	maxH = joms.jQuery(window).height() * 0.85;

	// Wrapper detected.
	wrapper = cWindow.find('.modal-body');
	if (wrapper && wrapper.length) {
		maxH = +wrapper.css('max-height').replace( /[^\d]+/g, '' );
		cWindow.find('#cWindowContentWrap').css({
			height: maxH
		});
		return;
	}

	if ( !h || h < 0 ) {
		cWindow.find('#cWindowContentWrap').css({ height: '' });
		h = cWindow.find('#cWindowContentWrap').height();
	}

	if (h > maxH) {
		h = maxH;
	}

	// Exit if cWindow has this flag.
	if(cWindow.hasClass('noresize-after')) {
		return;
	}

	// Set flag on non-resizable window, before set initial window size.
	if(cWindow.hasClass('noresize')) {
		cWindow.removeClass('noresize')
			.addClass('noresize-after');
	}

	// Override bottom padding (not needed)
	cWindow.css({ paddingBottom: 0 });

	// Get actions bar size
	var actions = cWindow.find('#cWindowAction');
	var actionBarHeight = (actions.length > 0) ? actions.innerHeight() : 0;

	// Get title bar size
	var title = cWindow.find('#cWindowContentTop');
	var titleBarHeight = (title.length > 0) ? title.innerHeight() : 0;

	// Get old wrap height
	var oldWrapHeight = cWindowHelper.contentWrapHeight;
	var oldOuterHeight = cWindowHelper.contentOuterHeight;

	// New height
	var WrapHeight = parseInt(h) + 4;
	var OuterHeight = WrapHeight + actionBarHeight + titleBarHeight;

	cWindow.find('#cWindowContentWrap').css({
		maxHeight: maxH,
		height: 'auto'
	});

	cWindow.find('#cWindowContentOuter, #cwin_ml, #cwin_mr').css({
		height: 'auto'
	});

	// Get window offset
	var doc = document.documentElement;
	var docTop = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);
	var docHeight = window.innerHeight || OuterHeight;

	cWindow.css({
		height: 'auto',
		top: docTop + (docHeight / 2) - (OuterHeight / 2)
	}, function(){
		cWindowHelper.save();
	});
}

function cWindowActions(action)
{
	// Remove any existing cWindowAction
	joms.jQuery('#cWindowAction').remove();

	//Append actions bar
	if (action)
	{
		joms.jQuery('<div id="cWindowAction" class="modal-footer" style="position:relative; height:50px">')
				.html(action)
				.appendTo('#cWindowContentOuter');
	}

	// Set up behavior when actions are invoked
	jax.loadingFunction = function() {
		joms.jQuery('#cWindowAction').addClass('loading');
		joms.jQuery('#cWindowContent').find('input, textarea, button')
				.attr('disabled', true);
	};
	jax.doneLoadingFunction = function() {
		joms.jQuery('#cWindowAction').removeClass('loading');
		joms.jQuery('#cWindowContent').find('input, textarea, button')
				.attr('disabled', false);
	};
	cWindowHelper.save();
	cWindowHelper.start();
}

function cGetZIndexMax()
{
	var allElems = document.getElementsByTagName ?
			document.getElementsByTagName("*") :
			document.all; // or test for that too
	var maxZIndex = 0;

	for (var i = 0; i < allElems.length; i++) {
		var elem = allElems[i];
		var cStyle = null;
		if (elem.currentStyle) {
			cStyle = elem.currentStyle;
		}
		else if (document.defaultView && document.defaultView.getComputedStyle) {
			cStyle = document.defaultView.getComputedStyle(elem, "");
		}

		var sNum;
		if (cStyle) {
			sNum = Number(cStyle.zIndex);
		} else {
			sNum = Number(elem.style.zIndex);
		}
		if (!isNaN(sNum)) {
			maxZIndex = Math.max(maxZIndex, sNum);
		}
	}
	return maxZIndex;
}

/*
 * Since 2.4.1
 */
function cWindowAutoResize()
{
	//Check stream locker
    if(cWindowHelper.getMark() === false)
	{
        //Mark content as old content
        cWindowHelper.setMark();
		//Get content height
		var h = joms.jQuery('#cWindow #cWindowContent').outerHeight();
        //Stop stream
        cWindowHelper.stop();
        //Resize cWindow
        cWindowResize(h);
    }
}

function cWindowDeviceRotation(){
    var cWindow = joms.jQuery('#cWindow');
    if(cWindow.length > 0){
        var w = joms.jQuery(window).width();
        /* User using mobile device */
        var maxW = Math.round(w*0.85);
        var l = (w - maxW)/2 - parseInt(cWindow.css('margin-left'));
        cWindow.css('left', l + 'px');
        cWindow.find('#cWindowContentOuter').css('width', maxW + 'px');
        cWindow.css('width', maxW + 'px');
    }
};
/*
 <div id="cWindow" class="{cWindoClass}">
 <div id="cwin_tl"></div>
 <div id="cwin_tm"></div>
 <div id="cwin_tr"></div>
 <div style="clear: both;"></div>

 <div id="cwin_ml"></div>
 <div id="cWindowContentOuter">

 <div id="cWindowContentTop">
 <a href="javascript:void(0);" onclick="cWindowHide();" id="cwin_close_btn">Close</a>
 <div id="cwin_logo"></div>
 <div class="clr"></div>
 </div>

 <div id="cWindowContentWrap">
 <div id="cWindowContent"></div>
 </div>

 </div>
 <div id="cwin_mr"></div>
 <div style="clear: both;"></div>

 <div id="cwin_bl"></div>
 <div id="cwin_bm"></div>
 <div id="cwin_br"></div>
 <div style="clear: both;"></div>
 </div>
 */
