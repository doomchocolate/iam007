// -----------------------------------------------------------------------------
// views/dropdown/mood
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

		template: joms.jst[ 'html/dropdown/mood' ],

		events: {
			'click li': 'onSelect',
			'click .joms-remove-button': 'onRemove'
		},

		moods: [
			'happy',
			'meh',
			'sad',
			'loved',
			'excited',
			'pretty',
			'tired',
			'angry',
			'speachless',
			'shocked',
			'irretated',
			'sick',
			'annoyed',
			'relieved',
			'blessed',
			'bored'
		],

		render: function() {
			var html = this.template({
					items: this.moods,
					language: {
						status: language.get('status') || {},
						moodshort: language.get('moodshort') || {}
					}
				}),
				div = $( html ).hide();

			this.$el.replaceWith( div );
			this.setElement( div );

			this.$btnremove = this.$('.joms-remove-button').hide();

			return this;
		},

		select: function( mood ) {
			if ( this.moods.indexOf( mood ) >= 0 ) {
				this.$btnremove.show();
				this.trigger( 'select', this.mood = mood );
			}
		},

		value: function() {
			return this.mood;
		},

		reset: function() {
			this.mood = false;
			this.$btnremove.hide();
			this.trigger('reset');
		},

		// ---------------------------------------------------------------------
		// Event handlers.
		// ---------------------------------------------------------------------

		onSelect: function( e ) {
			var item = $( e.currentTarget ),
				mood = item.attr('data-mood');

			this.select( mood );
			this.hide();
		},

		onRemove: function() {
			this.mood = false;
			this.$btnremove.hide();
			this.trigger('remove');
		}

	});

});
