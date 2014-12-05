// -----------------------------------------------------------------------------
// utils/constants
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox'
],

// definition
// ----------
function( $ ) {

	var constants = {};

	function get( key ) {
		if ( typeof key !== 'string' || !key.length )
			return;

		if ( joms && joms.constants ) {
			$.extend( true, constants, joms && joms.constants );
			delete joms.constants;
		}

		var data = constants;

		key = key.split('.');
		while ( key.length ) {
			data = data[ key.shift() ];
		}

		return data;
	}

	return {
		get: get
	};

});
