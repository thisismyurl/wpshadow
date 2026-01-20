<?php
/**
 * Test script to diagnose plugin action links issue.
 * Run this from WordPress to test the hook.
 */

// Simulate WordPress environment
define( 'ABSPATH', __DIR__ . '/../../../' );

// Load WordPress
require_once ABSPATH . 'wp-load.php';

// Test 1: Check if function exists
echo "=== Test 1: Function Existence ===\n";
if ( function_exists( 'wpshadow_plugin_action_links' ) ) {
	echo "✓ wpshadow_plugin_action_links() function EXISTS in global namespace\n";
} else {
	echo "✗ wpshadow_plugin_action_links() function DOES NOT EXIST\n";
}

if ( function_exists( 'WPShadow\\wpshadow_admin_init' ) ) {
	echo "✓ WPShadow\\wpshadow_admin_init() function EXISTS\n";
} else {
	echo "✗ WPShadow\\wpshadow_admin_init() function DOES NOT EXIST\n";
}

// Test 2: Check constants
echo "\n=== Test 2: Constants ===\n";
if ( defined( 'WPShadow\\WPSHADOW_BASENAME' ) ) {
	echo "✓ WPSHADOW_BASENAME = " . constant( 'WPShadow\\WPSHADOW_BASENAME' ) . "\n";
} else {
	echo "✗ WPSHADOW_BASENAME not defined\n";
}

// Test 3: Calculate expected hook name
echo "\n=== Test 3: Expected Hook Name ===\n";
$plugin_file = __DIR__ . '/wpshadow.php';
$basename = plugin_basename( $plugin_file );
$expected_hook = 'plugin_action_links_' . $basename;
echo "Plugin basename: $basename\n";
echo "Expected hook: $expected_hook\n";

// Test 4: Check if filter is registered
echo "\n=== Test 4: Filter Registration ===\n";
global $wp_filter;
if ( isset( $wp_filter[$expected_hook] ) ) {
	echo "✓ Filter '$expected_hook' IS REGISTERED\n";
	echo "Callbacks:\n";
	foreach ( $wp_filter[$expected_hook]->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$func = is_array( $callback['function'] ) ? 
				get_class( $callback['function'][0] ) . '::' . $callback['function'][1] :
				$callback['function'];
			echo "  - Priority $priority: $func\n";
		}
	}
} else {
	echo "✗ Filter '$expected_hook' is NOT REGISTERED\n";
	echo "Checking similar hooks:\n";
	foreach ( $wp_filter as $hook => $data ) {
		if ( strpos( $hook, 'plugin_action_links' ) !== false ) {
			echo "  - Found: $hook\n";
		}
	}
}

// Test 5: Test function directly
echo "\n=== Test 5: Direct Function Call ===\n";
if ( function_exists( 'wpshadow_plugin_action_links' ) ) {
	$test_links = array( 'deactivate' => '<a href="#">Deactivate</a>' );
	$result = wpshadow_plugin_action_links( $test_links );
	echo "✓ Function executed successfully\n";
	echo "Result links: " . implode( ' | ', array_keys( $result ) ) . "\n";
} else {
	echo "✗ Cannot test - function doesn't exist\n";
}

// Test 6: Check if admin_init was fired
echo "\n=== Test 6: admin_init Hook ===\n";
if ( did_action( 'admin_init' ) ) {
	echo "✓ admin_init hook has been fired " . did_action( 'admin_init' ) . " time(s)\n";
} else {
	echo "✗ admin_init hook has NOT been fired yet\n";
	echo "Triggering admin_init manually...\n";
	do_action( 'admin_init' );
	echo "Re-running Test 4 after manual trigger...\n\n";
	// Re-run test 4
	if ( isset( $wp_filter[$expected_hook] ) ) {
		echo "✓ Filter '$expected_hook' IS NOW REGISTERED\n";
	} else {
		echo "✗ Filter '$expected_hook' is STILL NOT REGISTERED\n";
	}
}
