<?php
/**
 * Extra WordPress configuration for Codespaces and Local Development
 * 
 * This file is auto-loaded by docker-compose.yml volume mount.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRITICAL: This must disable HTTPS forcing and enforce HTTP-only mode.
 */

// MOST AGGRESSIVE: Force $_SERVER variables to HTTP at the earliest point
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_SCHEME'] = 'http';

// REMOVE all proxy headers that might indicate HTTPS
$https_headers = array(
	'HTTP_X_FORWARDED_PROTO',
	'HTTP_X_FORWARDED_SSL',
	'HTTP_X_FORWARDED_SCHEME',
	'HTTP_X_SSL',
	'HTTP_FRONT_END_HTTPS',
	'HTTP_X_ORIGINAL_PROTO',
);

foreach ( $https_headers as $header ) {
	if ( isset( $_SERVER[ $header ] ) ) {
		unset( $_SERVER[ $header ] );
	}
}

// Define SSL constants BEFORE WordPress loads
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', false );
}
if ( ! defined( 'FORCE_SSL_LOGIN' ) ) {
	define( 'FORCE_SSL_LOGIN', false );
}

// Set WordPress to use HTTP-only URLs
// Detect if running in Codespaces environment
if ( getenv( 'CODESPACES' ) === 'true' ) {
	$codespace_name = getenv( 'CODESPACE_NAME' );

	if ( $codespace_name ) {
		// Use HTTP for Codespaces preview URLs (avoids SSL certificate warnings)
		$codespace_url = 'http://' . $codespace_name . '-8000.preview.app.github.dev';

		if ( ! defined( 'WP_HOME' ) ) {
			define( 'WP_HOME', $codespace_url );
		}
		if ( ! defined( 'WP_SITEURL' ) ) {
			define( 'WP_SITEURL', $codespace_url );
		}
	}
} else {
	// Fallback to localhost for non-Codespace environments (Docker local development)
	if ( ! defined( 'WP_HOME' ) ) {
		define( 'WP_HOME', 'http://localhost:8000' );
	}
	if ( ! defined( 'WP_SITEURL' ) ) {
		define( 'WP_SITEURL', 'http://localhost:8000' );
	}
}

