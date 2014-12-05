// -----------------------------------------------------------------------------
// views/inputbox/photo
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

		template: joms.jst[ 'html/inputbox/photo' ],

		initialize: function() {
			InputboxView.prototype.initialize.apply( this, arguments );
			this.hint = {
				single: language.get('status.photo_hint') || '',
				multiple: language.get('status.photos_hint') || ''
			};
		},

		reset: function() {
			InputboxView.prototype.reset.apply( this, arguments );
			this.single();
		},

		single: function() {
			this.$textarea.attr( 'placeholder', this.hint.single );
		},

		multiple: function() {
			this.$textarea.attr( 'placeholder', this.hint.multiple );
		},

		getTemplate: function() {
			var html = this.template({ placeholder: this.hint.single });
			return $( html );
		}

	});

});
