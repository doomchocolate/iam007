// -----------------------------------------------------------------------------
// utils/language
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox'
],

// definition
// ----------
function( $ ) {

	var language = {};

	function get( key ) {
		if ( typeof key !== 'string' || !key.length )
			return;

		if ( joms && joms.language ) {
			$.extend( true, language, joms && joms.language );
			delete joms.language;
		}

		var data = language;

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
