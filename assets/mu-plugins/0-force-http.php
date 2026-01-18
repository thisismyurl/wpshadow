<?php
/**
 * Force HTTP mode for development in Codespaces
 * 
 * This must-use plugin runs earliest to prevent HTTPS forcing
 * from the WordPress Docker image or reverse proxies.
 */

// IMMEDIATELY unset HTTPS before anything else runs
$_SERVER['HTTPS'] = 'off';

// Remove proxy headers that might trigger HTTPS
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
	unset( $_SERVER['HTTP_X_FORWARDED_PROTO'] );
}
if ( isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) ) {
	unset( $_SERVER['HTTP_X_FORWARDED_SSL'] );
}
if ( isset( $_SERVER['HTTP_X_FORWARDED_SCHEME'] ) ) {
	unset( $_SERVER['HTTP_X_FORWARDED_SCHEME'] );
}

// Define SSL constants BEFORE WordPress loads
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', false );
}
if ( ! defined( 'FORCE_SSL_LOGIN' ) ) {
	define( 'FORCE_SSL_LOGIN', false );
}

// Hook into init to ensure is_ssl() returns false
add_action( 'init', function() {
	// Ensure HTTPS is still off after WordPress initializes
	$_SERVER['HTTPS'] = 'off';
}, 1 );

// Additional safety: filter home_url and site_url to ensure HTTP
add_filter( 'option_home', function( $url ) {
	return preg_replace( '#^https://#i', 'http://', $url );
}, 999, 1 );

add_filter( 'option_siteurl', function( $url ) {
	return preg_replace( '#^https://#i', 'http://', $url );
}, 999, 1 );

// Prevent redirect_canonical from adding HTTPS
add_filter( 'redirect_canonical', function( $redirect_url ) {
	if ( $redirect_url ) {
		$redirect_url = str_replace( 'https://', 'http://', $redirect_url );
	}
	return $redirect_url;
}, 999, 1 );
