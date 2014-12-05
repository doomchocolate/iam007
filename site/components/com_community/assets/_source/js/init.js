// -----------------------------------------------------------------------------
// application initialization
// -----------------------------------------------------------------------------

// requirements
// ------------
require([
   'sandbox',
   'views/postbox/layout',
   'views/stream/layout',
   'views/header/layout'
],

// description
// -----------
function( $, PostboxView, StreamView, HeaderView ) {

	function initPostbox() {
		var el = $('.joms-postbox'),
			postbox;

		if ( el.length ) {
			postbox = new PostboxView({ el: el });
			postbox.render();
			postbox.show();
		}
	}

	function initStream() {
		var stream = new StreamView();
		stream.render();
	}

	function initHeader() {
		var header = new HeaderView();
		header.render();
	}

	$(function() {
		initPostbox();
		initStream();
		initHeader();

	});

});
