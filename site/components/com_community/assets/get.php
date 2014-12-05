<?php

// Based on Google PageSpeed rules:
// https://developers.google.com/speed/docs/insights/rules

// Usage example.
// Before : `$js = 'assets/bootstrap/bootstrap.min.js';`
// After  : `$js = 'assets/get.php/20140410/bootstrap/bootstrap.min.js';`

// -----------------------------------------------------------------------------

// Expiration time.
// -----------------------------------------------------------------------------
$expires = 365 * 24 * 60 * 60;

// Static file map, prevent access to non-listed extensions.
// -----------------------------------------------------------------------------
$filemap = array(
	'css'   => 'text/css',
	'gif'   => 'image/gif',
	'jpeg'  => 'image/jpeg',
	'jpg'   => 'image/jpeg',
	'js'    => 'application/javascript',
	'json'  => 'application/json',
	'png'   => 'image/png'
);

// Get static file path.
// -----------------------------------------------------------------------------
$dirname = pathinfo( $_SERVER[ 'SCRIPT_FILENAME' ], PATHINFO_DIRNAME );
$path = $dirname . $_SERVER[ 'PATH_INFO' ];

// Cache-busting capability.
// -----------------------------------------------------------------------------
$path = preg_replace( '/\/\d+(\.\d+)*\//', '/', $path );

// Check file availability.
// -----------------------------------------------------------------------------
$dirname = realpath( $dirname );
$path = realpath( $path );
$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
$mime = $filemap[ $ext ];

if ( strpos( $path, $dirname ) !== 0 || !$mime || !file_exists( $path ) ) {
	header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404 );
	die();
}

// Set headers.
// -----------------------------------------------------------------------------
header( 'Content-Type: ' . $mime );
header( 'Cache-Control: max-age=' . $expires . ', public' );
header( 'Expires: ' . str_replace( '+0000', 'GMT', gmdate( 'r', time() + $expires ) ) );
header( 'Last-Modified: ' . str_replace( '+0000', 'GMT', gmdate( 'r', filemtime( $path ) ) ) );
header( 'Vary: Accept-Encoding' );

// Print file contents to stdout.
// -----------------------------------------------------------------------------
readfile( $path );
