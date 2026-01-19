<?php
/**
 * WordPress Must-Use Plugin: Handle Codespaces Port Forwarding
 * 
 * This must-use plugin properly handles GitHub Codespaces port forwarding
 * by preserving HTTPS and the forwarded host headers.
 */

// STEP 1: Preserve the forwarded protocol from GitHub Codespaces proxy
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
	$_SERVER['HTTPS'] = 'on';
	$_SERVER['REQUEST_SCHEME'] = 'https';
} else {
	$_SERVER['HTTPS'] = 'off';
	$_SERVER['REQUEST_SCHEME'] = 'http';
}

// STEP 2: Preserve the forwarded host (which includes the Codespaces domain)
if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
	$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

// STEP 3: Define SSL constants BEFORE WordPress loads
if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', true );
}
if ( ! defined( 'FORCE_SSL_LOGIN' ) ) {
	define( 'FORCE_SSL_LOGIN', true );
}

// STEP 4: After WordPress loads, ensure server variables stay correct
add_action( 'plugins_loaded', function() {
	// Preserve the forwarded protocol
	if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['REQUEST_SCHEME'] = 'https';
	}
	
	// Preserve the forwarded host
	if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
}, 1 );

// STEP 5: Ensure redirects use HTTPS
add_filter( 'wp_redirect', function( $location ) {
	if ( ! empty( $location ) && isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		// Ensure all redirects use the correct Codespaces domain and HTTPS
		$location = str_replace( 'https://localhost:9000', 'https://' . $_SERVER['HTTP_X_FORWARDED_HOST'], $location );
		$location = str_replace( 'http://', 'https://', $location );
	}
	return $location;
}, 999, 1 );

// STEP 6: Ensure canonical redirects use correct domain
add_filter( 'redirect_canonical', function( $redirect_url ) {
	if ( ! empty( $redirect_url ) && isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		$redirect_url = str_replace( 'https://localhost:9000', 'https://' . $_SERVER['HTTP_X_FORWARDED_HOST'], $redirect_url );
		$redirect_url = str_replace( 'http://', 'https://', $redirect_url );
	}
	return $redirect_url;
}, 999, 1 );

// STEP 7: Template redirect to maintain correct server state
add_action( 'template_redirect', function() {
	if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['REQUEST_SCHEME'] = 'https';
	}
	
	if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
		$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
}, 1 );

// STEP 8: Force home_url and site_url functions to use correct domain
if ( function_exists( 'add_filter' ) ) {
	add_filter( 'home_url', function( $url ) {
		if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
			$url = str_replace( 'localhost:9000', $_SERVER['HTTP_X_FORWARDED_HOST'], $url );
			$url = str_replace( 'http://', 'https://', $url );
		}
		return $url;
	}, 999, 1 );

	add_filter( 'site_url', function( $url ) {
if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
			$url = str_replace( 'localhost:9000', $_SERVER['HTTP_X_FORWARDED_HOST'], $url );
			$url = str_replace( 'http://', 'https://', $url );
		}
		return $url;
	}, 999, 1 );
}

add_filter( 'option_siteurl', function( $url ) {
	if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		$url = str_replace( 'localhost:9000', $_SERVER['HTTP_X_FORWARDED_HOST'], $url );
		$url = str_replace( 'http://', 'https://', $url );
	}
	return $url;
}, 999, 1 );

// Prevent redirect_canonical from using wrong domain
add_filter( 'redirect_canonical', function( $redirect_url ) {
	if ( $redirect_url && isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
		$redirect_url = str_replace( 'localhost:9000', $_SERVER['HTTP_X_FORWARDED_HOST'], $redirect_url );
		$redirect_url = str_replace( 'http://', 'https://', $redirect_url );
	}
	return $redirect_url;
}, 999, 1 );
