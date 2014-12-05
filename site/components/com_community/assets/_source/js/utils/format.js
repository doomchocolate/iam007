// -----------------------------------------------------------------------------
// utils/format
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox'
],

// definition
// ----------
function( $ ) {

	function pad( str, len, padStr ) {
		if ( !$.isString( str ) )
			return str;

		if ( !$.isNumber( len ) || str.length >= len )
			return str;

		len = len - str.length;
		for ( var i = 0; i < len; i++ )
			str = ( padStr || ' ' ) + str;

		return str;
	}

	return {
		pad: pad
	};

});
