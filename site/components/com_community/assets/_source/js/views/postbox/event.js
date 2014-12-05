// -----------------------------------------------------------------------------
// views/postbox/event
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/postbox/default',
	'views/inputbox/eventtitle',
	'views/inputbox/eventdesc',
	'views/dropdown/event',
	'utils/constants',
	'utils/format',
	'utils/language'
],

// definition
// ----------
function(
	$,
	DefaultView,
	TitleView,
	InputboxView,
	EventView,
	constants,
	format,
	language
) {

	return DefaultView.extend({

		subviews: {
			event: EventView
		},

		template: joms.jst[ 'html/postbox/event' ],

		events: $.extend({}, DefaultView.prototype.events, {
			'click .joms-postbox-event-title': 'onFocus'
		}),

		render: function() {
			DefaultView.prototype.render.apply( this );

			this.$title = this.$('.joms-postbox-title');
			this.$inputbox = this.$('.joms-postbox-inputbox');
			this.$category = this.$('.joms-postbox-event-label-category');
			this.$location = this.$('.joms-postbox-event-label-location');
			this.$date = this.$('.joms-postbox-event-label-date');

			// title
			this.title = new TitleView();
			this.assign( this.$title, this.title );
			this.listenTo( this.title, 'focus', this.onInputFocus );
			this.listenTo( this.title, 'keydown', this.onNewInputUpdate );

			// inputbox
			this.inputbox = new InputboxView({ charcount: true });
			this.assign( this.$inputbox, this.inputbox );
			this.listenTo( this.inputbox, 'focus', this.onInputFocus );
			this.listenTo( this.inputbox, 'keydown', this.onNewInputUpdate );

			return this;
		},

		// ---------------------------------------------------------------------
		// Data validation and retrieval.
		// ---------------------------------------------------------------------

		reset: function() {
			DefaultView.prototype.reset.apply( this );
			this.title && this.title.reset();
			this.inputbox && this.inputbox.reset();
			this.$category && this.onEventSelect({});
		},

		value: function() {
			this.data.text = this.inputbox.value() || '';
			this.data.attachment = {};

			var value;
			for ( var prop in this.subflags )
				if ( value = this.subviews[ prop ].value() )
					this.data.attachment[ prop ] = value;

			var starttime = null,
				endtime = null,
				allday = this._data.allday ? 1 : 0;

			if ( !allday ) {
				starttime = (this._data.starttime || '').split(':');
				endtime = (this._data.endtime || '').split(':');
			}

			$.extend( this.data.attachment, {
				title: this.title.value(),
				catid: this._data.category && this._data.category[0] || null,
				location: this._data.location,
				startdate: this._data.startdate,
				enddate: this._data.enddate,
				allday: allday,
				'starttime-hour': allday ? null : (starttime[0] || null),
				'starttime-min': allday ? null : (starttime[1] || null),
				'endtime-hour': allday ? null : (endtime[0] || null),
				'endtime-min': allday ? null : (endtime[1] || null)
			});

			return DefaultView.prototype.value.apply( this, arguments );
		},

		validate: function() {
			var value = this.value( true ),
				text = value[0];

			if ( !text )
				return 'Event description cannot be empty.';
		},

		// ---------------------------------------------------------------------
		// Inputbox event handlers.
		// ---------------------------------------------------------------------

		onInputFocus: function() {
			this.showMainState();
		},

		onInputUpdate: $.debounce(function() {
			var value = this.value( true ),
				text = value[0],
				attachment = value[1],
				show = true;

			if ( !this.trim( attachment.title ) )
				show = false;
			else if ( !this.trim( text ) )
				show = false;
			else if ( !attachment.catid )
				show = false;
			else if ( !attachment.location )
				show = false;
			else if ( !attachment.startdate )
				show = false;
			else if ( !attachment.enddate )
				show = false;
			else if ( !attachment.allday && !attachment['starttime-hour'] && !attachment['starttime-min'] )
				show = false;
			else if ( !attachment.allday && !attachment['endtime-hour'] && !attachment['endtime-min'] )
				show = false;

			this.$save[ show ? 'show' : 'hide' ]();

		}, 300 ),

		onNewInputUpdate: $.debounce(function() {
			var value = this.value( true ),
				text = value[0],
				attachment = value[1],
				show = true;

			if ( !this.trim( attachment.title ) )
				show = false;
			else if ( !this.trim( text ) )
				show = false;
			else if ( !attachment.catid )
				show = false;
			else if ( !attachment.location )
				show = false;
			else if ( !attachment.startdate )
				show = false;
			else if ( !attachment.enddate )
				show = false;
			else if ( !attachment.allday && !attachment['starttime-hour'] && !attachment['starttime-min'] )
				show = false;
			else if ( !attachment.allday && !attachment['endtime-hour'] && !attachment['endtime-min'] )
				show = false;

			this.$save[ show ? 'show' : 'hide' ]();

		}, 300 ),

		// ---------------------------------------------------------------------
		// Dropdowns event handlers.
		// ---------------------------------------------------------------------

		onEventSelect: function( data ) {
			if ( !data.category ) {
				this.$category.hide();
			} else {
				this.$category.find('.joms-input-text').html( data.category && data.category[1] );
				this.$category.show();
			}

			if ( !data.location ) {
				this.$location.hide();
			} else {
				this.$location.find('.joms-input-text').html( data.location );
				this.$location.show();
			}

			var date, str = [];
			if ( !data.startdate || !data.enddate ) {
				this.$date.hide();
			} else {
				date = data.startdate.split('-');
				date.reverse();
				date = date.join('/');
				if ( !data.allday ) {
					date += ' ' + data.starttime;
				}
				str.push( date );

				date = data.enddate.split('-');
				date.reverse();
				date = date.join('/');
				if ( !data.allday ) {
					date += ' ' + data.endtime;
				}
				str.push( date );

				this.$date.find('.joms-input-text').html( str.join(' &mdash; ') );
				this.$date.show();
			}

			this._data = data;
			this.onInputUpdate();
		},

		onPrivacySelect: function( data ) {
			this.$tabprivacy.find('i').attr( 'class', 'joms-icon-' + data.icon );
			this.$tabprivacy.find('span').html( data.label );
		},

		// ---------------------------------------------------------------------
		// Ajax response parser.
		// ---------------------------------------------------------------------

		parseResponse: function( response ) {
			var elid = 'activity-stream-container',
				data, temp;

			if ( response && response.length ) {
				for ( var i = 0; i < response.length; i++ )	{
					if ( response[i][1] === elid ) {
						data = response[i][3];
					}
					if ( response[i][0] === 'al' ) {
						temp = response[i][3];
						window.alert( $.isArray( temp ) ? temp.join('. ') : temp );
					}
					if ( response[i][1] === '__throwError' ) {
						temp = response[i][3];
						window.alert( $.isArray( temp ) ? temp.join('. ') : temp );
					}
				}
			}

			return data;
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getTemplate: function() {
			var lang = language.get('event') || {};
			if ( lang.event_detail )
				lang.event_detail = lang.event_detail.toLowerCase();

			var html = this.template({
				juri: constants.get('juri'),
				language: {
					postbox: language.get('postbox') || {},
					event: lang
				}
			});

			return $( html ).hide();
		},

		getStaticAttachment: function() {
			if ( this.staticAttachment )
				return this.staticAttachment;

			this.staticAttachment = $.extend({},
				constants.get('postbox.attachment') || {},
				{ type: 'event' }
			);

			return this.staticAttachment;
		},

		trim: function( text ) {
			return (text || '').replace( /^\s+|\s+$/g, '' );
		}

	});

});
