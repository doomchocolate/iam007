// -----------------------------------------------------------------------------
// views/postbox/status
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'app',
	'views/postbox/default',
	'views/postbox/fetcher',
	'views/inputbox/status',
	'views/dropdown/mood',
	'views/dropdown/location',
	'views/dropdown/privacy',
	'utils/constants',
	'utils/language'
],

// definition
// ----------
function(
	$,
	App,
	DefaultView,
	FetcherView,
	InputboxView,
	MoodView,
	LocationView,
	PrivacyView,
	constants,
	language
) {

	return DefaultView.extend({

		subviews: {
			mood: MoodView,
			location: LocationView,
			privacy: PrivacyView
		},

		template: joms.jst[ 'html/postbox/status' ],

		events: $.extend({}, DefaultView.prototype.events, {
			'click li[data-tab=photo]': 'onAddPhoto',
			'click li[data-tab=video]': 'onAddVideo'
		}),

		initialize: function() {
			var settings = constants.get('settings') || {};
			if ( this.inheritPrivacy = (settings.isGroup || settings.isEvent || !settings.isMyProfile))
				this.subviews = $.omit( this.subviews, 'privacy' );

			this.enableMood = +constants.get('conf.enablemood');
			if ( !this.enableMood )
				this.subviews = $.omit( this.subviews, 'mood' );

			this.enableLocation = +constants.get('conf.enablelocation');
			if ( !this.enableLocation )
				this.subviews = $.omit( this.subviews, 'location' );

			DefaultView.prototype.initialize.apply( this );
		},

		render: function() {
			DefaultView.prototype.render.apply( this );

			this.$inputbox = this.$('.joms-postbox-inputbox');
			this.$fetcher = this.$('.joms-postbox-fetched');
			this.$tabmood = this.$tabs.find('[data-tab=mood]');
			this.$tablocation = this.$tabs.find('[data-tab=location]');
			this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

			if ( !this.enableMood )
				this.$tabmood.remove();

			if ( !this.enableLocation )
				this.$tablocation.remove();

			if ( this.inheritPrivacy ) {
				if ( this.$tabprivacy.next().length )
					this.$tabprivacy.remove();
				else
					this.$tabprivacy.css({ visibility: 'hidden' });
			}

			// inputbox
			this.inputbox = new InputboxView({ attachment: true, charcount: true });
			this.assign( this.$inputbox, this.inputbox );
			this.listenTo( this.inputbox, 'focus', this.onInputFocus );
			this.listenTo( this.inputbox, 'keydown', this.onInputUpdate );

			// init privacy
			var defaultPrivacy, settings;
			if ( !this.inheritPrivacy ) {
				settings = constants.get('settings') || {};
				if ( settings.isProfile && settings.isMyProfile )
					defaultPrivacy = constants.get('conf.profiledefaultprivacy');
				this.initSubview('privacy', { defaultPrivacy: defaultPrivacy || 'public' });
			}

			if ( this.single )
				this.listenTo( $, 'click', this.onDocumentClick );

			return this;
		},

		// ---------------------------------------------------------------------
		// Data validation and retrieval.
		// ---------------------------------------------------------------------

		reset: function() {
			DefaultView.prototype.reset.apply( this );
			this.inputbox && this.inputbox.reset();
			this.fetcher && this.fetcher.remove();
		},

		value: function() {
			this.data.text = this.inputbox.value() || '';
			this.data.attachment = {};

			this.data.text = this.data.text.replace( /\n/g, '\\n' );

			var value;
			for ( var prop in this.subflags )
				if ( value = this.subviews[ prop ].value() )
					this.data.attachment[ prop ] = value;

			if ( this.fetcher )
				this.data.attachment.fetch = this.fetcher.value();

			return DefaultView.prototype.value.apply( this, arguments );
		},

		validate: $.noop,

		// ---------------------------------------------------------------------
		// Inputbox event handlers.
		// ---------------------------------------------------------------------

		onInputFocus: function() {
			this.showMainState();
		},

		onInputUpdate: function( text, key ) {
			var div;

			text = text || '';
			this.togglePostButton( text );

			if ( key === 32 || key === 13 ) {
				if ( this.fetcher && this.fetcher.fetched )
					return;

				if ( this.fetcher )
					this.fetcher.remove();

				div = $('<div>').appendTo( this.$fetcher );
				this.fetcher = new FetcherView();
				this.fetcher.setElement( div );
				this.listenTo( this.fetcher, 'fetch:start', this.onFetchStart );
				this.listenTo( this.fetcher, 'fetch:done', this.onFetchDone );
				this.listenTo( this.fetcher, 'remove', this.onFetchRemove );
				this.fetcher.fetch( text.replace( /^\s+|\s+$/g, '' ) );
			}
		},

		onFetchStart: function() {
			this.saving = true;
			this.$loading.show();
		},

		onFetchDone: function() {
			this.$loading.hide();
			this.saving = false;
		},

		onFetchRemove: function() {
			this.fetcher = false;
		},

		onDocumentClick: function( elem ) {
			if ( elem.closest('.joms-postbox').length )
				return;

			var text = this.inputbox.value();
			text = text.replace( /^\s+|\s+$/g, '' );
			if ( !text )
				this.showInitialState();
		},

		// ---------------------------------------------------------------------
		// Dropdowns event handlers.
		// ---------------------------------------------------------------------

		onMoodSelect: function( mood ) {
			this.inputbox.updateAttachment( mood );
			this.togglePostButton();
		},

		onMoodRemove: function() {
			this.inputbox.updateAttachment( false );
			this.togglePostButton();
		},

		onLocationSelect: function( location ) {
			this.inputbox.updateAttachment( null, location );
			this.togglePostButton();
		},

		onLocationRemove: function() {
			this.inputbox.updateAttachment( null, false );
			this.togglePostButton();
		},

		onPrivacySelect: function( data ) {
			this.$tabprivacy.find('i').attr( 'class', 'joms-icon-' + data.icon );
			this.$tabprivacy.find('span').html( data.label );
		},

		// ---------------------------------------------------------------------
		// Add photo/video event handlers.
		// ---------------------------------------------------------------------

		onAddPhoto: function() {
			App.postbox || (App.postbox = {});
			App.postbox.value = this.value( true );
			$.trigger( 'postbox:photo' );
		},

		onAddVideo: function() {
			App.postbox || (App.postbox = {});
			App.postbox.value = this.value( true );
			$.trigger( 'postbox:video' );
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getTemplate: function() {
			var settings = constants.get('settings') || {},
				conf = constants.get('conf') || {},
				enablephoto = true,
				enablevideo = true;

			if ( settings.isEvent ) {
				enablephoto = enablevideo = false;
			} else if ( settings.isProfile || settings.isGroup ) {
				conf.enablephotos || (enablephoto = false);
				conf.enablevideos || (enablevideo = false);
			}

			var html = this.template({
				juri: constants.get('juri'),
				enablephoto: enablephoto,
				enablevideo: enablevideo,
				language: {
					postbox: language.get('postbox') || {},
					status: language.get('status') || {}
				}
			});

			return $( html ).hide();
		},

		getStaticAttachment: function() {
			if ( this.staticAttachment )
				return this.staticAttachment;

			this.staticAttachment = $.extend({},
				constants.get('postbox.attachment') || {},
				{ type: 'message' }
			);

			return this.staticAttachment;
		},

		togglePostButton: function( text ) {
			var enabled = false;

			if ( text )
				enabled = true;

			if ( !enabled && this.subflags.mood )
				enabled = this.subviews.mood.value();

			if ( !enabled && this.subflags.location )
				enabled = this.subviews.location.value();

			this.$save[ enabled ? 'show' : 'hide' ]();
		}

	});

});
