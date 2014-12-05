// -----------------------------------------------------------------------------
// views/inputbox/eventdesc
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

		template: joms.jst[ 'html/inputbox/eventdesc' ],

		render: function() {
			var div = this.getTemplate();
			this.$el.replaceWith( div );
			this.setElement( div );
			InputboxView.prototype.render.apply( this, arguments );
		},

		getTemplate: function() {
			var hint = language.get('status.event_hint') || '',
				html = this.template({ placeholder: hint });

			return $( html );
		}

	});

});
