// -----------------------------------------------------------------------------
// views/widget/select
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

		template: joms.jst[ 'html/widget/select' ],

		events: {
			'click span': 'toggle',
			'click li': 'onSelect'
		},

		initialize: function( options ) {
			this.options = options.options || [];
			if ( +options.width )
				this.width = +options.width + 'px';
		},

		render: function() {
			var data = {};
			data.options = this.options;
			data.width = this.width || false;

			this.$el.html( this.template( data ) );
			this.$span = this.$('span');
			this.$ul = this.$('ul');

			if ( data.options ) {
				if ( data.options.length > 6 ) {
					this.$ul.slimScroll({ height: '165px', alwaysVisible: true });
					this.$ul = this.$ul.closest('.slimScrollDiv').hide();
					this.$ul.css({ position: 'absolute', width: '100%' });
					this.$ul.find('ul').css({ display: '', width: '' });
				}
				if ( data.options[0] )
					this.select( data.options[0][0], data.options[0][1] );
			}
		},

		select: function( value, text ) {
			this.$span.html( text );
			this.$span.data( 'value', value );
			this.trigger( 'select', value, text );
		},

		toggle: function() {
			this.$ul.toggle();
		},

		value: function() {
			return this.$span.data('value');
		},

		reset: function() {
			this.$ul && this.$ul.hide();
			if ( this.options && this.options.length ) {
				this.$span.html( this.options[0][1] );
				this.$span.data( 'value', this.options[0][0] );
			}
		},

		onSelect: function( e ) {
			var el = $( e.currentTarget ),
				value = el.data('value'),
				text = el.html();

			this.select( value, text );
			this.toggle();
		}

	});

});
