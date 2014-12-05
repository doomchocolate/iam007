// -----------------------------------------------------------------------------
// views/postbox/photo-preview
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'app',
	'views/base',
	'views/widget/select-album',
	'utils/ajax',
	'utils/constants',
	'utils/language'
],

// definition
// ----------
function( $, App, BaseView, SelectWidget, ajax, constants, language ) {

	return BaseView.extend({

		templates: {
			div: joms.jst[ 'html/postbox/photo-preview' ],
			img: joms.jst[ 'html/postbox/photo-item' ]
		},

		events: {
			'click .joms-postbox-photo-remove': 'onRemove'
		},

		render: function() {
			this.$el.html( this.templates.div() );
			this.$list = this.$('ul');
			this.$form = this.$('div.joms-postbox-photo-form');
		},

		add: function( file ) {
			this.$list.append( this.templates.img({
				id: this.getFileId( file ),
				src: App.legacyUrl + '/assets/photo-upload-placeholder.png'
			}) );

			if ( this.select )
				return;

			var albums = constants.get('album'),
				privs = language.get('privacy'),
				options = [];

			var privmap = {
				'0': 'public',
				'10': 'public',
				'20': 'site_members',
				'30': 'friends',
				'40': 'me'
			};

			var icons = {
				'0': 'globe',
				'10': 'public',
				'20': 'users',
				'30': 'user',
				'40': 'lock'
			};

			if ( !(albums && albums.length) )
				return;

			this.albummap = {};
			for ( var i = 0, album, permission; i < albums.length; i++ ) {
				album = albums[i];
				permission = '' + album.permissions;
				this.albummap[ '' + album.id ] = permission;
				album = [ album.id, album.name, privs[ privmap[permission] || '0' ], icons[permission || '0'] ];
				options[ +album['default'] ? 'unshift' : 'push' ]( album );
			}

			this.select = new SelectWidget({ options: options });

			var div = $('<div class="joms-postbox-select-album joms-select">').insertAfter( this.$form );
			this.assign( div, this.select );
		},

		value: function() {
			var album_id,
				privacy;

			if ( this.select ) {
				album_id = '' + this.select.value();
				privacy = this.albummap[ album_id ];
			}

			return {
				id: this.pics || [],
				album_id: album_id,
				privacy: privacy
			};
		},

		updateProgress: function( file ) {
			var id = this.getFileId( file ),
				elem = this.$list.find( '#'+ id ).find('.joms-postbox-photo-progress'),
				percent;

			if ( elem && elem.length ) {
				percent = Math.min( 100, Math.floor( file.loaded / file.size * 100 ) );
				elem.stop().animate({ width: percent + '%' });
			}
		},

		setImage: function( file, json ) {
			json || (json = {});

			var elem = this.$list.find( '#' + this.getFileId(file) ),
				src = constants.get('juri.base') + json.thumbnail,
				id = json.id;

			elem.find('img').attr( 'src', src ).data( 'id', id );
			elem.find('img').attr( 'style', 'visibility:visible');
			elem.find('.joms-postbox-photo-action').show();
			elem.addClass('joms-postbox-photo-loaded');
			elem.find('.joms-postbox-photo-progressbar').remove();

			this.pics || (this.pics = []);
			this.pics.push( '' + id );

			this.trigger( 'update', this.pics.length );
		},

		removeFailed: function() {
			this.$list.find('.joms-postbox-photo-item')
				.not('.joms-postbox-photo-loaded')
				.remove();

			this.trigger( 'update', this.pics && this.pics.length || 0 );
		},

		// ---------------------------------------------------------------------
		// Thumbnail event handlers.
		// ---------------------------------------------------------------------

		onRemove: function( e ) {
			var li = $( e.target ).closest('li'),
				id = li.find('img').data('id'),
				num;

			li.remove();
			this.pics = $.without( this.pics, '' + id );
			num = this.pics.length;

			if ( num <= 0 )
				this.select.remove();

			this.ajaxRemove([ id ]);
			this.trigger( 'update', num );
		},

		remove: function() {
			this.pics && this.pics.length && this.ajaxRemove( this.pics );
			return BaseView.prototype.remove.apply( this, arguments );
		},

		ajaxRemove: function( pics ) {
			var params = {};
			params.option = 'community';
			params.no_html = 1;
			params.task = 'azrul_ajax';
			params.func = 'system,ajaxDeleteTempImage';
			params[ window.jax_token_var ] = 1;

			if ( pics && pics.length )
				params[ 'arg2[]' ] = pics;

			$.ajax({
				url: window.jax_live_site,
				type: 'post',
				dataType: 'json',
				data: params
			});
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getFileId: function( file ) {
			return 'postbox-preview-' + file.id;
		}

	});

});
