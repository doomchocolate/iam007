// -----------------------------------------------------------------------------
// views/base
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox'
],

// definition
// ----------
function( $ ) {

	return $.mvc.View.extend({

		assign: function( element, view ) {
			if ( !$.isArray( element ) ) {
				element = [ [ element, view ] ];
			}

			$.each( element, function( item ) {
				item[ 1 ].setElement( item[ 0 ] ).render();
			});
		},

		show: function() {
			if ( this.isHidden() ) {
				this.$el.show();
				this.trigger('show');
			}
		},

		hide: function() {
			if ( !this.isHidden() ) {
				this.$el.hide();
				this.trigger('hide');
			}
		},

		toggle: function() {
			this.isHidden() ? this.show() : this.hide();
		},

		isHidden: function() {
			return this.el.style.display === 'none';
		}

	});

});
