// -----------------------------------------------------------------------------
// views/layout/layout
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/base'
],

// definition
// ----------
function( $, BaseView ) {

	return BaseView.extend({

		render: function() {
			this.onResize();
			$( window ).on( 'resize', this.onResize );
		},

		onResize: function() {
			var cover = $('.js-focus-cover'),
				img = cover.children('img'),
				position = img.position() || {},
				height, ratio;

			if ( !position.top )
				return;

			height = img.height();
			ratio = img.data('ratio');

			if ( !ratio ) {
				ratio = Math.abs( position.top ) / height;
				img.data('ratio', ratio );
			}

			img.css({ top: 0 - ratio * height });
		}

	});

});
