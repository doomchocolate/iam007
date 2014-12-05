// -----------------------------------------------------------------------------
// views/stream/filterbar
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/base'
],

// definition
// ----------
function(
	$,
	BaseView
) {

	return BaseView.extend({

		render: function() {
			this.$btn = $('.joms-activity-filter-action');
			this.$list = $('.joms-activity-filter-dropdown');
			this.$chk = this.$list.find('.joms-icon-checkbox-unchecked').closest('li');
			this.$label = $('.joms-activity-filter-status');

			this.$btn.on( 'click', $.bind( this.toggle, this ) );
			this.$list.on( 'click', 'li', $.bind( this.select, this ) );
			this.listenTo( $, 'click', this.onDocumentClick );
		},

		toggle: function() {
			var collapsed = this.$list[0].style.display === 'none';
			collapsed ? this.expand() : this.collapse();
		},

		expand: function() {
			this.$list.show();
		},

		collapse: function() {
			this.$list.hide();
		},

		select: function( e ) {
			var url = '',
				li = $( e.currentTarget ),
				data = li.data(),
				chk = li.find('i'),
				checked;

			if ( chk.length ) {
				checked = !!chk.hasClass('joms-icon-checkbox-checked');
			}

			var params = [];
			for ( var prop in data ) {
				params.push( prop + '=' + data[prop] );
			}

			params = params.join('&');
			url += params ? '?' + params : '';

			this.toggle();
			window.location = url;
		},

		onDocumentClick: function( elem ) {
			if ( elem.closest('.joms-activity-filter').length )
				return;

			this.collapse();
		}

	});

});
