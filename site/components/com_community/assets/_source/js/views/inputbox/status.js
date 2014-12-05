// -----------------------------------------------------------------------------
// views/inputbox/status
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/inputbox/base',
	'utils/constants',
	'utils/language'
],

// definition
// ----------
function( $, InputboxView, constants, language ) {

	return InputboxView.extend({

		template: joms.jst[ 'html/inputbox/status' ],

		initialize: function() {
			InputboxView.prototype.initialize.apply( this, arguments );
			this.language = language.get('mood');
		},

		render: function() {
			var div = this.getTemplate();
			this.$el.replaceWith( div );
			this.setElement( div );
			InputboxView.prototype.render.apply( this, arguments );
		},

		set: function( value ) {
			this.resetTextntags( this.$textarea, value );
			this.flags.attachment && this.updateAttachment( false, false );
			this.flags.charcount && this.updateCharCounterProxy();
			this.onKeydownProxy();
		},

		reset: function() {
			this.resetTextntags( this.$textarea, '' );
			this.flags.attachment && this.updateAttachment( false, false );
			this.flags.charcount && this.updateCharCounterProxy();
			this.onKeydownProxy();
		},

		value: function() {
			return this.textareaValue;
		},

		updateInput: function() {
			InputboxView.prototype.updateInput.apply( this, arguments );
			var that = this;
			this.$textarea.textntags( 'val', function( text ) {
				that.textareaValue = text;
			});
		},

		updateAttachment: function( mood, location ) {
			var attachment = [];

			this.mood = mood || mood === false ? mood : this.mood;
			this.location = location || location === false ? location : this.location;

			if ( this.location && this.location.name ) {
				attachment.push( '<b>at ' + this.location.name + '</b>' );
			}

			if ( this.mood ) {
				attachment.push(
					'<i class="joms-emoticon joms-emo-' + this.mood + '"></i> ' +
					'<b>' + ( this.language[ this.mood ] || this.mood ) + '</b>'
				);
			}

			if ( !attachment.length ) {
				this.$attachment.html('');
				this.$textarea.attr( 'placeholder', this.placeholder );
				return;
			}

			this.$attachment.html( ' &nbsp;&mdash; ' + attachment.join(' and ') + '.' );
			this.$textarea.removeAttr('placeholder');
		},

		getTemplate: function() {
			var hint = language.get('status.status_hint') || '',
				html = this.template({ placeholder: hint });

			return $( html );
		}

	});

});
