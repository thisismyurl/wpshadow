<?php
/**
 * WordPress Drop-in: Advanced Cache
 * 
 * This file is intentionally named as a WordPress drop-in
 * to load our HTTP forcing code at the absolute earliest point.
 * 
 * WARNING: This is a hack for development only.
 */

// MOST AGGRESSIVE: Kill HTTPS immediately
$_SERVER['HTTPS'] = 'off';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
unset($_SERVER['HTTP_X_FORWARDED_SSL']);
unset($_SERVER['HTTP_X_FORWARDED_SCHEME']);

// No caching for this drop-in
if ( isset( $wp_object_cache ) ) {
	wp_cache_delete( 'siteurl', 'options' );
	wp_cache_delete( 'home', 'options' );
}

// Don't do the actual drop-in caching - just the HTTPS fix
define( 'WP_CACHE', false );
