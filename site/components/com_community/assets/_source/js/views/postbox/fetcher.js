// -----------------------------------------------------------------------------
// views/postbox/fetcher
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/base',
	'utils/ajax',
	'utils/image',
	'utils/language'
],

// definition
// ----------
function( $, BaseView, ajax, image, language ) {

	return BaseView.extend({

		template: joms.jst[ 'html/postbox/fetcher' ],

		events: {
			'click .joms-fetched-close': 'onClose',
			'click .joms-fetched-field span': 'onFocus',
			'keyup .joms-fetched-field input': 'onKeyup',
			'keyup .joms-fetched-field textarea': 'onKeyup',
			'blur .joms-fetched-field input': 'onBlur',
			'blur .joms-fetched-field textarea': 'onBlur',
			'click .joms-fetched-previmg': 'prevImage',
			'click .joms-fetched-nextimg': 'nextImage'
		},

		initialize: function() {
			var lang = language.get('fetch') || {};
			this.titlePlaceholder = lang.title_hint || '';
			this.descPlaceholder = lang.description_hint || '';
		},

		fetch: function( text ) {
			var rUrl = /^(|.*\s)(https?:\/\/|www\.)([a-z0-9-]+\.)+[a-z]{2,4}(:\d+)?(\/.*)?(\s.*|)$/i,
				isFetchable = text.match( rUrl );

			if ( this.fetching || !isFetchable )
				return;

			this.fetching = true;
			this.fetched = false;
			delete this.url;

			this.trigger('fetch:start');

			ajax({
				fn: 'system,ajaxGetFetchUrl',
				data: [ text ],
				success: $.bind( this.render, this ),
				complete: $.noop
			});

		},

		render: function( resp ) {
			resp = resp && resp[2] && resp[2][3] && resp[2][3][0] || false;
			if ( !resp )
				return;

			var json;
			try {
				json = JSON.parse( resp );
			} catch ( e ) {}

			if ( !json )
				return;

			this.fetched = true;
			this.url = json.url || '';

			var data = {
				title: json.title || '',
				titlePlaceholder: this.titlePlaceholder,
				description: json.description || '',
				descPlaceholder: this.descPlaceholder,
				image: json.image || []
			};

			// normalize url
			if ( !this.url.match( /^https?:\/\//i ) )
				this.url = 'http://' + this.url;

			// normalize images
			for ( var i = 0; i < data.image.length; i++ )
				if ( !data.image[i].match( /^(http:|https:)?\/\//i ) )
					data.image[i] = '//' + data.image[i];

			// preload images
			image.preload( data.image, $.bind(function( images ) {
				data.image = images;
				this.$el.html( this.template( data ) );
				this.$images = this.$('.joms-fetched-images').find('img');
				this.$title = this.$('.joms-fetched-title').find('input');
				this.$description = this.$('.joms-fetched-description').find('textarea');
				this.fetching = false;
				this.trigger('fetch:done');
			}, this ) );
		},

		change: function( el ) {
			var input = $( el ),
				span = input.prev('span'),
				val = input.val().replace( /^\s+|\s+$/g, '' );

			if ( !val ) {
				val = input.parent().hasClass('joms-fetched-title') ?
					this.titlePlaceholder :
					this.descPlaceholder;
			}

			input.hide();
			span.text( val ).show();
		},

		remove: function() {
			delete this.url;
			BaseView.prototype.remove.apply( this );
			this.trigger('remove');
		},

		prevImage: function() {
			var currImg = this.$images.filter(':visible'),
				prevImg = currImg.prev();

			if ( prevImg.length ) {
				currImg.hide();
				prevImg.show();
			}
		},

		nextImage: function() {
			var currImg = this.$images.filter(':visible'),
				nextImg = currImg.next();

			if ( nextImg.length ) {
				currImg.hide();
				nextImg.show();
			}
		},

		value: function() {
			if ( this.fetching )
				return [];

			return [
				this.url,
				this.$images && this.$images.filter(':visible').attr('src'),
				this.$title && this.$title.val().replace( /\t/g, '\\t' ).replace( /\n/g, '\\n' ),
				this.$description && this.$description.val().replace( /\t/g, '\\t' ).replace( /\n/g, '\\n' )
			];
		},

		// ---------------------------------------------------------------------
		// Event handlers.
		// ---------------------------------------------------------------------

		onClose: function() {
			this.remove();
		},

		onFocus: function( e ) {
			var span = $( e.currentTarget ),
				input = span.next('input,textarea');

			span.hide();
			input.show();
			setTimeout(function() {
				input[0].focus();
			}, 300 );
		},

		onKeyup: function( e ) {
			if ( e.keyCode === 13 ) {
				this.change( e.currentTarget );
			}
		},

		onBlur: function( e ) {
			this.change( e.currentTarget );
		},

		// ---------------------------------------------------------------------
		// Ajax response parser.
		// ---------------------------------------------------------------------

		parseResponse: function( resp ) {
			resp = resp && resp[2] && resp[2][3] && resp[2][3][0] || false;
			if ( !resp )
				return;

			var json;
			try {
				json = JSON.parse( resp );
			} catch ( e ) {}

			return json;
		}

	});

});
