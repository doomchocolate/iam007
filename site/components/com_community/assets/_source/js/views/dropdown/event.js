// -----------------------------------------------------------------------------
// views/dropdown/datepicker
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/dropdown/base',
	'views/widget/select',
	'utils/constants',
	'utils/language',
	'utils/format'
],

// definition
// ----------
function( $, BaseView, SelectWidget, constants, language, format ) {

	return BaseView.extend({

		template: joms.jst[ 'html/dropdown/event' ],

		events: {
			'click .joms-event-allday': 'onToggleCheckbox',
			'click .joms-postbox-done': 'onSave'
		},

		initialize: function() {
			BaseView.prototype.initialize.apply( this );

			var categories = constants.get('eventCategories') || [],
				options = [];

			this.categoriesMap = {};
			if ( categories && categories.length ) {
				for ( var i = 0, id = categories[i].id, name = categories[i].name; i < categories.length; i++ ) {
					id = categories[i].id;
					name = categories[i].name;
					this.categoriesMap[ id ] = name;
					options.push([ id, name ]);
				}
			}

			this.category = new SelectWidget({ options: options });
		},

		render: function() {
			var div = this.getTemplate();

			this.$el.replaceWith( div );
			this.setElement( div );
			this.$category = this.$('.joms-event-category');
			this.$location = this.$('[name=location]').val('');
			this.$startdate = this.$('.joms-pickadate-startdate').pickadate({ min: new Date(), klass: { frame: 'picker__frame startDate' } });
			this.$starttime = this.$('.joms-pickadate-starttime').pickatime({ interval: 10, klass: { frame: 'picker__frame startTime' } });
			this.$enddate = this.$('.joms-pickadate-enddate').pickadate({ klass: { frame: 'picker__frame endDate' } });
			this.$endtime = this.$('.joms-pickadate-endtime').pickatime({ interval: 10, klass: { frame: 'picker__frame endTime' } });
			// this.$allday = this.$('.joms-event-allday').hide().show();
			this.$done = this.$('.joms-event-done');

			this.assign( this.$category, this.category );
			this.startdate = this.$startdate.pickadate('picker');
			this.starttime = this.$starttime.pickatime('picker');
			this.enddate = this.$enddate.pickadate('picker');
			this.endtime = this.$endtime.pickatime('picker');

			this.startdate.on({ set: $.bind( this.onSetStartDate, this ) });
			this.starttime.on({ set: $.bind( this.onSetStartTime, this ) });
			this.enddate.on({ set: $.bind( this.onSetEndDate, this ) });
			this.endtime.on({ set: $.bind( this.onSetEndTime, this ) });

			return this;
		},

		show: function() {
			this.category && this.category.reset();
			return BaseView.prototype.show.apply( this, arguments );
		},

		toggleCheckbox: function( on ) {
			if ( !(this.$allday && this.$allday.length) )
				return;

			var chk = this.$allday.find('i');
			if ( !(chk && chk.length) )
				return;

			var checked = chk.hasClass('joms-icon-check');
			if ( checked === on )
				return;

			this.allday = on;
			chk[ this.allday ? 'removeClass' : 'addClass' ]('joms-icon-check-empty');
			chk[ this.allday ? 'addClass' : 'removeClass' ]('joms-icon-check');
			this.$starttime[ this.allday ? 'hide' : 'show' ]();
			this.$endtime[ this.allday ? 'hide' : 'show' ]();
			this.allday || this._checkTime();
		},

		// ---------------------------------------------------------------------

		value: function() {
			return this.data;
		},

		validate: function() {
		},

		reset: function() {
			this.$location.val('');
			this.$startdate.val('');
			this.$starttime.val('');
			this.$enddate.val('');
			this.$endtime.val('');
			this.toggleCheckbox( false );
		},

		// ---------------------------------------------------------------------

		onToggleCheckbox: function( e ) {
			var elem = $( e.currentTarget ).find('i');
			this.toggleCheckbox( !elem.hasClass('joms-icon-check') );
		},

		onSetStartDate: function( o ) {
			var ts = o.select;
			this.enddate.set({ min: new Date(ts) }, { muted: true });
			this.allday || this._checkTime();
		},

		onSetEndDate: function( o ) {
			var ts = o.select;
			this.startdate.set({ max: new Date(ts) }, { muted: true });
			this.allday || this._checkTime();
		},

		onSetStartTime: function() {
			this._checkTime('start');
		},

		onSetEndTime: function() {
			this._checkTime('end');
		},

		onSave: function() {
			var category = this.category.value(),
				startdate = this.startdate.get('select'),
				starttime = null,
				enddate = this.enddate.get('select'),
				endtime = null,
				error;

			startdate && (startdate = this._formatDate(startdate));
			enddate && (enddate = this._formatDate(enddate));

			if ( !this.allday ) {
				starttime = this.starttime.get('select');
				starttime && (starttime = this._formatTime(starttime));
				endtime = this.endtime.get('select');
				endtime && (endtime = this._formatTime(endtime));
			}

			this.data = {
				category: category ? [ category, this.categoriesMap[ category ] ] : false,
				location: this.$location.val(),
				startdate: startdate,
				enddate: enddate,
				allday: this.allday,
				starttime: starttime,
				endtime: endtime
			};

			if ( !this.data.category ) {
				error = 'Category is not selected.';
			} else if ( !this.data.location ) {
				error = 'Location is not selected.';
			} else if ( !this.data.startdate ) {
				error = 'Start date is not selected.';
			} else if ( !this.data.allday && !this.data.starttime ) {
				error = 'Start time is not selected.';
			} else if ( !this.data.enddate ) {
				error = 'End date is not selected.';
			} else if ( !this.data.allday && !this.data.endtime ) {
				error = 'End time is not selected.';
			}

			if ( error ) {
				window.alert( error );
				return;
			}

			this.trigger( 'select', this.data );
			this.hide();
		},

		// ---------------------------------------------------------------------
		// Helper functions.
		// ---------------------------------------------------------------------

		getTemplate: function() {
			var html = this.template({
				language: {
					event: language.get('event') || {}
				}
			});

			return $( html ).hide();
		},

		_formatDate: function( date ) {
			if ( !date )
				return date;

			return [
				date.year,
				format.pad(date.month + 1 + '', 2, '0' ),
				format.pad(date.date + '', 2, '0' )
			].join('-');
		},

		_formatTime: function( time ) {
			if ( !time )
				return time;

			return [
				format.pad(time.hour + '', 2, '0'),
				format.pad(time.mins + '', 2, '0')
			].join(':');
		},

		_checkTime: function() {
			if ( this.allday )
				return;

			var startdate = this.startdate.get('select'),
				enddate = this.enddate.get('select'),
				starttime, endtime;

			if ( !startdate || !enddate )
				return;

			if ( enddate.year <= startdate.year &&
					enddate.month <= startdate.month &&
					enddate.date <= startdate.date ) {

				starttime = this.starttime.get('select');
				endtime = this.endtime.get('select');

				if ( !starttime )
					this.endtime.set({ min: false }, { muted: true });
				else {
					this.endtime.set({ min: [ starttime.hour, starttime.mins ] }, { muted: true });
					if ( endtime && endtime.time < starttime.time )
						this.endtime.set({ select: [ starttime.hour, starttime.mins ] }, { muted: true });
				}

				if ( !endtime )
					this.starttime.set({ max: false }, { muted: true });
				else {
					this.starttime.set({ max: [ endtime.hour, endtime.mins ] }, { muted: true });
					if ( starttime && starttime.time > endtime.time )
						this.starttime.set({ select: [ endtime.hour, endtime.mins ] }, { muted: true });
				}

			} else {
				this.starttime.set({ max: false }, { muted: true });
				this.endtime.set({ min: false }, { muted: true });
			}
		}

	});

});
