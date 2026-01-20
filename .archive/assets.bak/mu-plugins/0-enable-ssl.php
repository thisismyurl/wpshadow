<?php
/**
 * MU Plugin: Enable SSL/HTTPS Support
 *
 * This plugin enables Apache SSL modules and sites for HTTPS support.
 * It runs once during WordPress initialization.
 *
 * @package wpshadow
 */

// Only run this once
if ( ! defined( 'WPS_SSL_ENABLED' ) ) {
	define( 'WPS_SSL_ENABLED', true );

	// Enable SSL modules
	if ( function_exists( 'shell_exec' ) ) {
		@shell_exec( 'a2enmod ssl 2>/dev/null' );
		@shell_exec( 'a2ensite default-ssl 2>/dev/null' );
		@shell_exec( 'apachectl graceful 2>/dev/null' );
	}
}
