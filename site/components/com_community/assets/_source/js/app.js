// -----------------------------------------------------------------------------
// app
// -----------------------------------------------------------------------------

// definition
// ----------
define(function() {
	var staticUrl;

	staticUrl = require.toUrl('');
	staticUrl = staticUrl.replace( /js\/$/, '' );
	staticUrl = staticUrl.replace( /\?.+$/, '' );

	return {
		baseUrl: '',
		staticUrl: staticUrl,
		legacyUrl: staticUrl + '../../'
	};

});
