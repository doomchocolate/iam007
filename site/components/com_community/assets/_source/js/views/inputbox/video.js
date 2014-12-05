// -----------------------------------------------------------------------------
// views/inputbox/video
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/inputbox/status',
	'utils/language'
],

// definition
// ----------
function( $, InputboxView, language ) {

	return InputboxView.extend({

		template: joms.jst[ 'html/inputbox/video' ],

		getTemplate: function() {
			var hint = language.get('status.video_hint') || '',
				html = this.template({ placeholder: hint });

			return $( html );
		}

	});

});
