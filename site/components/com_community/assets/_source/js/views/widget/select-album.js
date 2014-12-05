// -----------------------------------------------------------------------------
// views/widget/select-album
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/widget/select'
],

// definition
// ----------
function( $, SelectWidget ) {

	return SelectWidget.extend({

		template: joms.jst[ 'html/widget/select-album' ],

		render: function() {
			var data = {};
			data.options = this.options;
			data.width = this.width || false;

			this.$el.html( this.template( data ) );
			this.$span = this.$('span');
			this.$ul = this.$('ul');

			if ( data.options ) {
				if ( data.options.length > 3 ) {
					this.$ul.slimScroll({ height: '150px', alwaysVisible: true });
					this.$ul = this.$ul.closest('.slimScrollDiv').hide();
					this.$ul.css({ position: 'absolute', width: '100%' });
					this.$ul.find('ul').css({ display: '', width: '' });
				}
				if ( data.options[0] )
					this.select( data.options[0][0], data.options[0][1] );
			}
		},

		onSelect: function( e ) {
			var el = $( e.currentTarget ),
				value = el.data('value'),
				text = el.find('p').html();

			this.select( value, text );
			this.toggle();
		}

	});

});
