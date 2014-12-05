// -----------------------------------------------------------------------------
// utils/ajax
// -----------------------------------------------------------------------------

// dependencies
// ------------
define([
	'sandbox'
],

// definition
// ----------
function( $ ) {

	var defaults = {
		type: 'post',
		data: {},
		dataType: 'json',
		error: $.noop,
		success: $.noop,
		complete: $.noop
	};

	function ajax( options ) {
		options = $.extend({}, defaults, options || {});
		options.url = window.jax_live_site;
		options.data = encodeData( options.fn, options.data );
		return $.ajax( options );
	}

	// encode request data
	function encodeData( fn, data ) {
		var params = {};

		// azrul's ajax parameters
		params.option = 'community';
		params.no_html = 1;
		params.task = 'azrul_ajax';
		params.func = fn;
		params[ window.jax_token_var ] = 1;

		// azrul's data format
		$.isArray( data ) || (data = []);
		for ( var i = 0, arg; i < data.length; i++ ) {
			arg = data[ i ];
			$.isString( arg ) && ( arg = arg.replace( /"/g, '&quot;' ) );
			$.isArray( arg ) || ( arg = [ '_d_', arg ] );
			params[ 'arg' + ( i + 2 ) ] = JSON.stringify( arg );
		}

		return params;
	}

	return ajax;

});
