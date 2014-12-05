// -----------------------------------------------------------------------------
// views/postbox/custom
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/postbox/default',
	'views/dropdown/privacy',
	'utils/ajax',
	'utils/constants',
	'utils/language'
],

// definition
// ----------
function(
	$,
	DefaultView,
	PrivacyView,
	ajax,
	constants,
	language
) {

	return DefaultView.extend({

		subviews: {
			privacy: PrivacyView
		},

		template: joms.jst[ 'html/postbox/custom' ],

		events: $.extend({}, DefaultView.prototype.events, {
			'click .joms-postbox-predefined-message': 'onCustomPredefined',
			'click .joms-postbox-custom-message': 'onCustomCustom',
			'keyup [name=custom]': 'onTextareaUpdate'
		}),

		initialize: function() {
			var settings = constants.get('settings') || {};
			if ( this.inheritPrivacy = (settings.isGroup || settings.isEvent || !settings.isMyProfile))
				this.subviews = $.omit( this.subviews, 'privacy' );

			DefaultView.prototype.initialize.apply( this );
			this.attachment = { type: 'custom' };
			$.extend( this.attachment, constants.get('postbox.attachment') || {} );
		},

		render: function() {
			DefaultView.prototype.render.apply( this );

			this.$initial = this.$el.children('.joms-postbox-inner-panel');
			this.$main = this.$el.children('.joms-postbox-custom');
			this.$statepredefined = this.$('.joms-postbox-custom-state-predefined');
			this.$statecustom = this.$('.joms-postbox-custom-state-custom');

			this.$predefined = this.$('[name=predefined]');
			this.$custom = this.$('[name=custom]');
			this.$divs = this.$('.joms-postbox-dropdown').hide();
			this.$tabprivacy = this.$tabs.find('[data-tab=privacy]');

			if ( this.inheritPrivacy )
				this.$tabprivacy.css({ visibility: 'hidden' });

			// init privacy
			if ( !this.inheritPrivacy ) {
				this.initSubview('privacy', { privacylist: [ 'public', 'site_members' ] });
				this.subviews.privacy.setPrivacy('public');
			}

			return this;
		},

		showInitialState: function() {
			this.$main.hide();
			this.$initial.show();
			DefaultView.prototype.showInitialState.apply( this );
		},

		showMainState: function( predefined ) {
			DefaultView.prototype.showMainState.apply( this );
			predefined ? this.showPredefinedState() : this.showCustomState();
		},

		showPredefinedState: function() {
			this.$initial.hide();
			this.$statepredefined.show();
			this.$statecustom.hide();
			this.$main.show();
			this.$save.show();
			this.predefined = true;
		},

		showCustomState: function() {
			this.$initial.hide();
			this.$statepredefined.hide();
			this.$statecustom.show();
			this.$main.show();
			this.predefined = false;
		},

		// ---------------------------------------------------------------------
		// Data validation and retrieval.
		// ---------------------------------------------------------------------

		reset: function() {
			DefaultView.prototype.reset.apply( this );
			this.$predefined && this.$predefined.val( this.$predefined.find('option:first').val() );
			this.$custom && this.$custom.val('');
		},

		value: function() {
			var data = [];
			if ( this.predefined ) {
				data.push( this.$predefined.val() );
				data.push( this.$predefined.find('option:selected').text() );
			} else {
				data.push( 'system.message' );
				data.push( this.$custom.val() );
			}

			return data;
		},

		validate: function() {
			var value = this.value(),
				error;

			if ( this.predefined ) {
				value[0] || (error = 'Predefined message cannot be empty.');
			} else  {
				value[1] || (error = 'Custom message cannot be empty.');
			}

			return error;
		},

		// ---------------------------------------------------------------------
		// Textare event handlers.
		// ---------------------------------------------------------------------

		onTextareaUpdate: function() {
			var value = this.$custom.val();
			value = value.replace( /^\s+|\s+$/g, '' );
			this.$save[ value ? 'show' : 'hide' ]();
		},

		// ---------------------------------------------------------------------
		// Panel event handlers.
		// ---------------------------------------------------------------------

		onCustomPredefined: function() {
			this.showMainState('predefined');
		},

		onCustomCustom: function() {
			this.showMainState();
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
				fn: 'activities,ajaxAddPredefined',
				data: this.value(),
				success: $.bind( this.onPostSuccess, this ),
				complete: function() {
					that.$loading.hide();
					that.saving = false;
					that.showInitialState();
				}
			});
		},

		// ---------------------------------------------------------------------
		// Dropdowns event handlers.
		// ---------------------------------------------------------------------

		onPrivacySelect: function( data ) {
			this.$tabprivacy.find('i').attr( 'class', 'joms-icon-' + data.icon );
			this.$tabprivacy.find('span').html( data.label );
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getTemplate: function() {
			var obj = constants.get('customActivities') || {},
				messages = [];

			for ( var prop in obj )
				messages.push([ prop, obj[ prop ] ]);

			var html = this.template({
				juri: constants.get('juri'),
				messages: messages,
				language: {
					postbox: language.get('postbox'),
					custom: language.get('custom')
				}
			});

			return $( html ).hide();
		}

	});

});
