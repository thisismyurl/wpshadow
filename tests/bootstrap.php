<?php
/**
 * PHPUnit Bootstrap for WPShadow
 * Sets up WordPress test environment
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php\n";
	exit( 1 );
}

// Load WordPress test functions
require_once $_tests_dir . '/includes/functions.php';

// Manually load plugin
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wpshadow.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start WordPress
require $_tests_dir . '/includes/bootstrap.php';
