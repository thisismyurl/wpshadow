<?php
/**
 * PHPUnit Bootstrap for WPShadow Tests
 *
 * @package WPShadow
 */

// Composer autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Initialize WP_Mock
WP_Mock::bootstrap();

// Define WordPress constants if not defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Include core classes needed for diagnostics
require_once dirname( __DIR__ ) . '/includes/core/class-diagnostic-base.php';
require_once dirname( __DIR__ ) . '/includes/core/class-diagnostic-lean-checks.php';

// Include the ai-structured-data diagnostic
require_once dirname( __DIR__ ) . '/includes/diagnostics/general/class-diagnostic-ai-structured-data.php';
