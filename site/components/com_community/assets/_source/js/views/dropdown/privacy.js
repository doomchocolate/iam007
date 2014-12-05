// -----------------------------------------------------------------------------
// views/dropdown/privacy
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/dropdown/base',
	'utils/language'
],

// definition
// ----------
function( $, BaseView, language ) {

	return BaseView.extend({

		template: joms.jst[ 'html/dropdown/privacy' ],

		events: { 'click li': 'select' },

		privacies: {
			'public': [ 10, 'globe' ],
			'site_members': [ 20, 'users' ],
			'friends': [ 30, 'user' ],
			'me': [ 40, 'lock' ]
		},

		privmaps: {
			'public': '10',
			'site_members': '20',
			'friends': '30',
			'me': '40',
			'0': 'public',
			'10': 'public',
			'20': 'site_members',
			'30': 'friends',
			'40': 'me'
		},

		initialize: function( options ) {
			BaseView.prototype.initialize.apply( this );

			this.privkeys = $.keys( this.privacies );
			if ( options && options.privacylist && options.privacylist.length )
				this.privkeys = $.intersection( this.privkeys, options.privacylist );

			var langs = language.get('privacy') || {};
			for ( var prop in this.privacies ) {
				this.privacies[ prop ][ 2 ] = langs[ prop ] || prop;
				this.privacies[ prop ][ 3 ] = langs[ prop + '_desc' ] || prop;
			}

			// set default privacy
			this.defaultPrivacy = this.privkeys[0];
			if ( typeof options.defaultPrivacy !== 'undefined' ) {
				options.defaultPrivacy = '' + options.defaultPrivacy;
				if ( options.defaultPrivacy.match(/^\d+$/) )
					options.defaultPrivacy = this.privmaps[ options.defaultPrivacy ] || this.defaultPrivacy;
				this.defaultPrivacy = options.defaultPrivacy;
			}
		},

		render: function() {
			var items = [];
			for ( var i = 0, priv; i < this.privkeys.length; i++ ) {
				priv = this.privkeys[ i ];
				items[ i ] = [
					priv,
					this.privacies[ priv ][ 1 ],
					this.privacies[ priv ][ 2 ],
					this.privacies[ priv ][ 3 ]
				];
			}

			this.$el.html( this.template({ items: items }) );
			this.setPrivacy( this.defaultPrivacy );
			return this;
		},

		select: function( e ) {
			var item = $( e.currentTarget ),
				priv = item.attr('data-priv');

			this.setPrivacy( priv );
			this.hide();
		},

		setPrivacy: function( priv ) {
			var data = {};

			if ( this.privkeys.indexOf( priv ) >= 0 ) {
				this.privacy = this.privacies[ priv ][ 0 ];
				data.icon = this.privacies[ priv ][ 1 ];
				data.label = this.privacies[ priv ][ 2 ].toLowerCase();
				this.trigger( 'select', data );
			}
		},

		value: function() {
			return this.privacy;
		},

		reset: function() {
			this.setPrivacy( this.defaultPrivacy );
		}

	});

});
