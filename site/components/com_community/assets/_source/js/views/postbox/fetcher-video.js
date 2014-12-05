// -----------------------------------------------------------------------------
// views/postbox/fetcher-video
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/base',
	'views/widget/select',
	'utils/ajax',
	'utils/language'
],

// definition
// ----------
function( $, BaseView, SelectWidget, ajax, language ) {

	return BaseView.extend({

		template: joms.jst[ 'html/postbox/fetcher-video' ],

		events: {
			'click .joms-fetched-close': 'onClose',
			'click .joms-fetched-field span': 'onFocus',
			'keyup .joms-fetched-field input': 'onKeyup',
			'keyup .joms-fetched-field textarea': 'onKeyup',
			'blur .joms-fetched-field input': 'onBlur',
			'blur .joms-fetched-field textarea': 'onBlur'
		},

		initialize: function() {
			var lang = language.get('fetch') || {};
			this.titlePlaceholder = lang.title_hint || '';
			this.descPlaceholder = lang.description_hint || '';
			lang = language.get('video');
			this.categoryLabel = lang.category_label;
		},

		fetch: function( text ) {
			var rUrl = /^(|.*\s)(https?:\/\/|www\.)([a-z0-9-]+\.)+[a-z]{2,4}(:\d+)?(\/.*)?(\s.*|)$/i,
				isFetchable = text.match( rUrl );

			if ( this.fetching || !isFetchable )
				return;

			this.id = false;
			this.url = text;
			this.fetching = true;
			this.fetched = false;

			this.trigger('fetch:start');

			// TODO video limit...

			var that = this;
			ajax({
				fn: 'videos,ajaxLinkVideoPreview',
				data: [ text ],
				complete: function() {
					that.fetching = false;
					that.trigger('fetch:done');
				},
				success: $.bind( this.render, this )
			});
		},

		render: function( resp ) {
			resp = this.parseResponse( resp );
			if ( !resp ) {
				this.trigger( 'fetch:failed' );
				return;
			}

			var video = resp && resp.video,
				categories = resp && resp.category || [];

			if ( !(video && video.id) ) {
				this.trigger( 'fetch:failed', resp );
				return;
			}

			this.video_id = video.id;
			this.fetched = true;

			var data = {
				title: video.title || '',
				titlePlaceholder: this.titlePlaceholder,
				description: video.description || '',
				descPlaceholder: this.descPlaceholder,
				image: video.thumb || false
			};

			this.select && this.select.remove();
			this.$el.html( this.template( data ) );
			this.$image = this.$('.joms-fetched-images').find('img');
			this.$title = this.$('.joms-fetched-title').find('input');
			this.$description = this.$('.joms-fetched-description').find('textarea');
			this.$category = this.$('.joms-fetched-category');

			var options = [];
			for ( var i = 0; i < categories.length; i++ ) {
				options.push([ categories[i].id, this.categoryLabel + ': ' + categories[i].name ]);
			}

			this.select = new SelectWidget({ options: options });
			this.assign( this.$category, this.select );

			return this;
		},

		change: function( el ) {
			var input = $( el ),
				span = input.prev('span'),
				val = input.val().replace( /^\s+|\s+$/g, '' );

			if ( !val ) {
				if ( input.parent().hasClass('joms-fetched-title') )
					val = this.titlePlaceholder;
				else
					val = this.descPlaceholder;
			}

			input.hide();
			span.text( val ).show();
		},

		remove: function() {
			BaseView.prototype.remove.apply( this );
			this.trigger('remove');
		},

		value: function() {
			if ( this.fetching )
				return [];

			return [
				this.video_id,
				this.url,
				this.$image && this.$image.attr('src'),
				this.$title && this.$title.val().replace( /\t/g, '\\t' ).replace( /\n/g, '\\n' ),
				this.$description && this.$description.val().replace( /\t/g, '\\t' ).replace( /\n/g, '\\n' ),
				this.select && this.select.value()
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
			var json;

			if ( resp && resp.length ) {
				for ( var i = 0; i < resp.length; i++ ) {
					if ( resp[i][1] === '__throwError' ) {
						json = { msg: resp[i][3] };
						break;
					} else if ( resp[i][1] === '__callback' ) {
						json = resp[i][3][0];
						break;
					}
				}
			}

			return json;
		}

	});

});
