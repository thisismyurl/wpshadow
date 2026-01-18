<?php
/**
 * Extra WordPress configuration for Codespaces
 */

// Only handle HTTPS detection from GitHub Codespaces proxy
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

// Use dynamic URLs with proper Codespaces support
if ( isset( $_SERVER['HTTP_HOST'] ) ) {
	$host = $_SERVER['HTTP_HOST'];
	define( 'WP_HOME', 'https://' . $host );
	define( 'WP_SITEURL', 'https://' . $host );
}

// Prevent history.replaceState SecurityError in Codespaces by filtering admin_url
if ( function_exists( 'add_filter' ) ) {
	add_filter(
		'admin_url',
		function ( $url ) {
			// In Codespaces, replace localhost references with actual host
			if ( isset( $_SERVER['HTTP_HOST'] ) && false !== strpos( $_SERVER['HTTP_HOST'], 'app.github.dev' ) ) {
				$url = str_replace( 'localhost', $_SERVER['HTTP_HOST'], $url );
			}
			return $url;
		},
		10,
		1
	);
}

