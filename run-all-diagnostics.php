<?php
/**
 * Run All Diagnostics and Output Results
 *
 * Usage: php run-all-diagnostics.php
 * Or via WP-CLI: wp eval-file run-all-diagnostics.php
 *
 * This script loads the plugin and runs all registered diagnostics,
 * outputting results in a structured format for manual verification.
 */

// Detect if running via WP-CLI or direct PHP
$is_wp_cli = defined( 'WP_CLI' );

if ( ! $is_wp_cli ) {
	// Load WordPress if running directly
	$wp_load = false;
	$search_paths = array(
		dirname( __FILE__ ) . '/../../wp-load.php',
		dirname( __FILE__ ) . '/wp-load.php',
		getenv( 'WP_ROOT' ) . '/wp-load.php',
	);

	foreach ( $search_paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			$wp_load = true;
			break;
		}
	}

	if ( ! $wp_load ) {
		die( "Error: Could not load WordPress. Make sure this script is in your WordPress root or set WP_ROOT environment variable.\n" );
	}
}

// Load the plugin if not already loaded
if ( ! defined( 'WPSHADOW_VERSION' ) ) {
	$plugin_file = WP_PLUGIN_DIR . '/wpshadow/wpshadow.php';
	if ( file_exists( $plugin_file ) ) {
		require_once $plugin_file;
	} else {
		die( "Error: WPShadow plugin not found at: $plugin_file\n" );
	}
}

// Ensure the diagnostics registry is loaded
$registry_file = WP_PLUGIN_DIR . '/wpshadow/includes/diagnostics/class-diagnostic-registry.php';
if ( file_exists( $registry_file ) ) {
	require_once $registry_file;
} else {
	die( "Error: Diagnostic registry not found at: $registry_file\n" );
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "WPShadow Diagnostic Test Suite\n";
echo "Site: " . get_option( 'siteurl' ) . "\n";
echo "WordPress: " . get_bloginfo( 'version' ) . "\n";
echo "PHP: " . phpversion() . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Get all registered diagnostics
$registry = \WPShadow\Diagnostics\Diagnostic_Registry::class;

if ( ! method_exists( $registry, 'get_all' ) ) {
	die( "Error: Diagnostic registry not properly loaded.\n" );
}

$all_diagnostics = $registry::get_all();

if ( empty( $all_diagnostics ) ) {
	die( "Error: No diagnostics registered.\n" );
}

$total = count( $all_diagnostics );
$passed = 0;
$failed = 0;
$errors = array();
$findings_count = 0;

echo "Running " . $total . " diagnostics...\n\n";

foreach ( $all_diagnostics as $slug => $diagnostic_class ) {
	// Check if class exists
	if ( ! class_exists( $diagnostic_class ) ) {
		echo "❌ FAIL: $slug - Class not found: $diagnostic_class\n";
		$failed++;
		$errors[] = "$slug: Class not found";
		continue;
	}

	// Check if it has the required check method
	if ( ! method_exists( $diagnostic_class, 'check' ) ) {
		echo "❌ FAIL: $slug - No check() method\n";
		$failed++;
		$errors[] = "$slug: Missing check() method";
		continue;
	}

	try {
		// Run the diagnostic
		$result = $diagnostic_class::check();

		if ( $result === null ) {
			// No issues found
			echo "✅ PASS: $slug - No issues\n";
			$passed++;
		} elseif ( is_array( $result ) ) {
			// Finding returned
			$findings_count++;
			$threat = isset( $result['threat_level'] ) ? $result['threat_level'] : 'unknown';
			$title = isset( $result['title'] ) ? $result['title'] : 'Unknown';
			echo "⚠️  FIND: $slug - $title (threat: $threat)\n";
			$passed++;
		} else {
			echo "❌ FAIL: $slug - Invalid return type: " . gettype( $result ) . "\n";
			$failed++;
			$errors[] = "$slug: Invalid return type";
		}
	} catch ( Exception $e ) {
		echo "❌ ERROR: $slug - " . $e->getMessage() . "\n";
		$failed++;
		$errors[] = "$slug: " . $e->getMessage();
	}
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Results Summary\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total Diagnostics: $total\n";
echo "Passed/Executed:   $passed (" . round( ( $passed / $total ) * 100 ) . "%)\n";
echo "Failed:            $failed\n";
echo "Findings Found:    $findings_count\n";
echo "\n";

if ( ! empty( $errors ) ) {
	echo "Errors:\n";
	foreach ( $errors as $error ) {
		echo "  • $error\n";
	}
	echo "\n";
}

if ( $failed === 0 ) {
	echo "✅ All diagnostics executed successfully!\n";
} else {
	echo "⚠️  Some diagnostics had errors. Review the list above.\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

exit( $failed > 0 ? 1 : 0 );
