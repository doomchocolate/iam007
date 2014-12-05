// -----------------------------------------------------------------------------
// sandbox
// -----------------------------------------------------------------------------

define(function() {
	var jqr = window.joms.jQuery,
		und = window._,
		bbe = window.Backbone;

	// Sandbox object, also serves as a DOM selector.
	function Sandbox( selector, context ) {
		return jqr( selector, context );
	}

	// Set Backbone to use default jQuery.
	bbe.$ = jqr;

	// Filter used Underscore functions.
	und.pick( und, [
		'each',
		'filter',
		'intersection',
		'without',
		'bind',
		'debounce',
		'defer',
		'keys',
		'extend',
		'pick',
		'omit',
		'isArray',
		'isNumber',
		'isString',
		'isUndefined',
		'uniqueId'
	]);

	// Extend sandbox with events, selected Underscore functions,
	// Backbone MVC, and some.
	und.extend( Sandbox, bbe.Events, und, {

		// MV*
		mvc: {
			Model: bbe.Model,
			Models: bbe.Collection,
			View: bbe.View
		},

		// Ajax helper.
		ajax: jqr.ajax,
		param: jqr.param,

		// NOOP
		noop: function() {}

	});

	// Enable deep-extend via jQuery extend.
	Sandbox.__extend = Sandbox.extend;
	Sandbox._$extend = jqr.extend;
	Sandbox.extend = function() {
		var isDeep = arguments[0] === true;
		return Sandbox[ isDeep ? '_$extend' : '__extend' ].apply( null, arguments );
	};

	// Browser detection.
	Sandbox.ua = navigator.userAgent;

	var ua = Sandbox.ua.toLowerCase();
	Sandbox.mobile    = !!ua.match( /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i );
	Sandbox.webkit    = !!ua.match( /webkit/i );
	Sandbox.ie        = !!ua.match( /msie/i );
	Sandbox.ieVersion = Sandbox.ie && +( ua.match( /msie (\d+)\./i )[1] );

	// Experimental flag.
	Sandbox.xpriment = !Sandbox.ie && 1;

	// Publish onclick event.
	Sandbox( document.body ).on( 'click', function( e ) {
		Sandbox.trigger( 'click', Sandbox( e.target ) );
	});

	return Sandbox;

});
