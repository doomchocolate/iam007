// -----------------------------------------------------------------------------
// views/postbox/default
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'app',
	'views/base',
	'utils/ajax',
	'utils/constants'
],

// definition
// ----------
function( $, App, BaseView, ajax, constants ) {

	return BaseView.extend({

		subviews: {},

		// @abstract
		template: function() {
			throw new Error('Method not implemented.');
		},

		events: {
			'click li[data-tab]': 'onToggleDropdown',
			'click button.joms-postbox-cancel': 'onCancel',
			'click button.joms-postbox-save': 'onPost'
		},

		initialize: function( options ) {
			if ( options && options.single )
				this.single = true;

			this.subflags = {};
			this.reset();
		},

		render: function() {
			var div = this.getTemplate();
			this.$el.replaceWith( div );
			this.setElement( div );

			this.$tabs = this.$('.joms-postbox-tab');
			this.$action = this.$('.joms-postbox-action').hide();
			this.$loading = this.$('.joms-postbox-loading').hide();
			this.$save = this.$('.joms-postbox-save').hide();

			return this;
		},

		show: function() {
			this.showInitialState();
			BaseView.prototype.show.apply( this );
		},

		showInitialState: function() {
			this.reset();
			this.$tabs.hide();
			this.$action.hide();
			this.$save.hide();
			this.trigger('show:initial');
		},

		showMainState: function() {
			this.$tabs.show();
			this.$action.show();
			this.trigger('show:main');
		},

		// ---------------------------------------------------------------------
		// Data validation and retrieval.
		// ---------------------------------------------------------------------

		reset: function() {
			this.data = {};
			this.data.text = '';
			this.data.attachment = {};
			for ( var prop in this.subflags ) {
				this.subviews[ prop ].reset();
				this.subviews[ prop ].hide();
			}
		},

		value: function( noEncode ) {
			var attachment = $.extend({}, this.getStaticAttachment(), this.data.attachment );

			// DEBUGGING PURPOSE
			// if ( !noEncode ) {
			// 	console.log( this.data.text );
			// 	console.log( attachment );
			// }

			return [
				this.data.text,
				noEncode ? attachment : JSON.stringify( attachment )
			];
		},

		// Data validation method, truthy return value will raise error.
		// Go to `this.onPost` to see how this method is used.
		validate: $.noop,

		// ---------------------------------------------------------------------
		// Event handlers.
		// ---------------------------------------------------------------------

		onToggleDropdown: function( e ) {
			var elem = $( e.currentTarget );
			if ( elem.data('bypass') )
				return;

			var type = elem.data('tab');
			if ( !this.subviews[ type ] )
				return;

			if ( !this.subflags[ type ] )
				this.initSubview( type );

			if ( !this.subviews[ type ].isHidden() ) {
				this.subviews[ type ].hide();
				return;
			}

			for ( var prop in this.subflags )
				if ( prop !== type )
					this.subviews[ prop ].hide();

			this.subviews[ type ].show();
		},

		onCancel: function() {
			if ( App.postbox && App.postbox.value )
				App.postbox.value = false;

			if ( !this.saving )
				this.showInitialState();
		},

		onPost: function() {
			if ( this.saving )
				return;

			var error = this.validate();
			if ( error ) {
				window.alert( error );
				return;
			}

			this.saving = true;
			this.$loading.show();

			var that = this;
			ajax({
				fn: 'system,ajaxStreamAdd',
				data: this.value(),
				success: $.bind( this.onPostSuccess, this ),
				complete: function() {
					that.$loading.hide();
					that.saving = false;
					that.showInitialState();
				}
			});
		},

		onPostSuccess: function( response ) {
			var html = this.parseResponse( response ),
				stream;

			if ( html ) {
				stream = $('#activity-stream-container');
				stream.html( html );

				// reset filter to default
				var filter = $('.joms-activity-filter-action .joms-activity-filter-status');
				if ( filter && filter.length )
					filter.html( filter.data('default') );
			}
		},

		// ---------------------------------------------------------------------
		// Lazy subview initialization.
		// ---------------------------------------------------------------------

		initSubview: function( type, options ) {
			var Type = type.replace( /^./, function( chr ){ return chr.toUpperCase(); });
			if ( !this.subflags[ type ] ) {
				this.subviews[ type ] = new this.subviews[ type ]( options );
				this.assign( this.getSubviewElement(), this.subviews[ type ] );
				this.listenTo( this.subviews[ type ], 'init', this[ 'on' + Type + 'Init' ] );
				this.listenTo( this.subviews[ type ], 'show', this[ 'on' + Type + 'Show' ] );
				this.listenTo( this.subviews[ type ], 'hide', this[ 'on' + Type + 'Hide' ] );
				this.listenTo( this.subviews[ type ], 'select', this[ 'on' + Type + 'Select' ] );
				this.listenTo( this.subviews[ type ], 'remove', this[ 'on' + Type + 'Remove' ] );
				this.subflags[ type ] = true;
			}
		},

		getSubviewElement: function() {
			var div = $('<div>').hide().appendTo( this.$el );
			return div;
		},

		// ---------------------------------------------------------------------
		// Ajax response parser.
		// ---------------------------------------------------------------------

		parseResponse: function( response ) {
			var elid = 'activity-stream-container',
				data;

			if ( response && response.length ) {
				for ( var i = 0; i < response.length; i++ )	{
					if ( response[i][1] === elid ) {
						data = response[i][3];
						break;
					}
				}
			}

			return data;
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getTemplate: function() {
			var html = this.template({ juri: constants.get('juri') });
			return $( html ).hide();
		},

		getStaticAttachment: function() {
			if ( this.staticAttachment )
				return this.staticAttachment;

			this.staticAttachment = $.extend({},
				constants.get('postbox.attachment') || {},
				{ type: '' }
			);

			return this.staticAttachment;
		}

	});

});
