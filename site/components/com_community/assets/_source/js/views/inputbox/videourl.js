// -----------------------------------------------------------------------------
// views/inputbox/videourl
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/inputbox/base',
	'utils/language'
],

// definition
// ----------
function( $, InputboxView, language ) {

	return InputboxView.extend({

		template: joms.jst[ 'html/inputbox/videourl' ],

		render: function() {
			var div = this.getTemplate();
			this.$el.replaceWith( div );
			this.setElement( div );
			InputboxView.prototype.render.apply( this, arguments );
		},

		onKeydown: function( e ) {
			if ( e && e.keyCode === 13 )
				e.preventDefault();

			var that = this;
			$.defer(function() {
				that.updateInput( e );
			});
		},

		getTemplate: function() {
			var hint = language.get('video.link_hint') || '',
				html = this.template({ placeholder: hint });

			return $( html );
		}

	});

});
