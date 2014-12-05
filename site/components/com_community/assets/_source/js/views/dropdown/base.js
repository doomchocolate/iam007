// -----------------------------------------------------------------------------
// views/dropdown/base
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox',
	'views/base'
],

// definition
// ----------
function( $, BaseView ) {

	return BaseView.extend({

		initialize: function() {
			this.listenTo( $, 'click', this._onDocumentClick );
		},

		// hide on there is onclick event outside postbox
		_onDocumentClick: function( elem ) {
			if ( !elem.closest('.joms-postbox').length )
				this.hide();
		}

	});

});
