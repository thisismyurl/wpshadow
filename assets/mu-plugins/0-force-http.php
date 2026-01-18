<?php
/**
 * WordPress Must-Use Plugin: Force HTTP mode for development in Codespaces
 * 
 * This must-use plugin runs earliest to prevent HTTPS forcing
 * from the WordPress Docker image or reverse proxies.
 */

// STEP 1: IMMEDIATELY unset HTTPS before anything else runs
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_SCHEME'] = 'http';

// STEP 2: Remove ALL proxy headers that might trigger HTTPS
$https_headers = array(
	'HTTP_X_FORWARDED_PROTO',
	'HTTP_X_FORWARDED_SSL',
	'HTTP_X_FORWARDED_SCHEME',
	'HTTP_X_SSL',
	'HTTP_FRONT_END_HTTPS',
	'HTTP_X_ORIGINAL_PROTO',
	'HTTP_X_FORWARDED_PROTOCOL',
	'HTTP_X_PROTO',
	'HTTPS',
);

foreach ( $https_headers as $header ) {
	if ( isset( $_SERVER[ $header ] ) ) {
		unset( $_SERVER[ $header ] );
	}
}

// STEP 3: Define SSL constants BEFORE WordPress loads
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', false );
}
if ( ! defined( 'FORCE_SSL_LOGIN' ) ) {
	define( 'FORCE_SSL_LOGIN', false );
}

// STEP 4: After WordPress loads, hook into early actions to override SSL detection
add_action( 'plugins_loaded', function() {
	// Force $_SERVER['HTTPS'] to off again
	$_SERVER['HTTPS'] = 'off';
	$_SERVER['REQUEST_SCHEME'] = 'http';
	
	// Remove any lingering HTTPS headers
	foreach ( array( 'HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_SSL', 'HTTP_X_FORWARDED_SCHEME' ) as $header ) {
		if ( isset( $_SERVER[ $header ] ) ) {
			unset( $_SERVER[ $header ] );
		}
	}
}, 1 );

// STEP 5: Filter option values to force HTTP URLs
add_filter( 'pre_option_siteurl', function( $value ) {
	if ( empty( $value ) ) {
		return $value;
	}
	// Ensure siteurl uses HTTP
	return preg_replace( '#^https://#i', 'http://', $value );
}, 999, 1 );

add_filter( 'pre_option_home', function( $value ) {
	if ( empty( $value ) ) {
		return $value;
	}
	// Ensure home uses HTTP
	return preg_replace( '#^https://#i', 'http://', $value );
}, 999, 1 );

// STEP 6: Intercept all redirect attempts to force HTTP
add_filter( 'wp_redirect', function( $location ) {
	if ( ! empty( $location ) ) {
		// Replace any https:// with http://
		$location = str_replace( 'https://', 'http://', $location );
	}
	return $location;
}, 999, 1 );

// STEP 7: Prevent redirect_canonical from forcing HTTPS
add_filter( 'redirect_canonical', function( $redirect_url ) {
	if ( ! empty( $redirect_url ) ) {
		$redirect_url = str_replace( 'https://', 'http://', $redirect_url );
	}
	return $redirect_url;
}, 999, 1 );

// STEP 8: Prevent WP from detecting HTTPS on every request
add_action( 'template_redirect', function() {
	$_SERVER['HTTPS'] = 'off';
	$_SERVER['REQUEST_SCHEME'] = 'http';
}, 1 );

// STEP 9: Force home_url and site_url functions to return HTTP
if ( function_exists( 'add_filter' ) ) {
	add_filter( 'home_url', function( $url ) {
		return str_replace( 'https://', 'http://', $url );
	}, 999, 1 );

	add_filter( 'site_url', function( $url ) {
		return str_replace( 'https://', 'http://', $url );
	}, 999, 1 );
}

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
