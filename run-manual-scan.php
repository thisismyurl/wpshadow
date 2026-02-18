<?php
/**
 * Script to manually trigger a Quick Scan
 */

// Define ABSPATH if not already defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/var/www/html/' );
}

// Load WordPress
require_once ABSPATH . 'wp-load.php';

// Check if Diagnostic_Registry exists
if ( ! class_exists( 'WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
	echo "Error: Diagnostic_Registry not found\n";
	exit( 1 );
}

echo "=== Manual Quick Scan Trigger ===\n\n";

// Get all diagnostic classes
$diagnostic_classes = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostics();
echo "Total diagnostics found: " . count( $diagnostic_classes ) . "\n\n";

$findings = array();
$completed = 0;

foreach ( $diagnostic_classes as $diagnostic_class ) {
	try {
		$class_name = 'WPShadow\\Diagnostics\\' . $diagnostic_class;
		
		if ( ! class_exists( $class_name ) ) {
			// Try to find and require the diagnostic file
			$diagnostic_file = ABSPATH . '../wp-content/plugins/wpshadow/includes/diagnostics/' . str_replace( '_', '-', strtolower( $diagnostic_class ) ) . '.php';
			if ( file_exists( $diagnostic_file ) ) {
				require_once $diagnostic_file;
			}
		}
		
		if ( class_exists( $class_name ) && method_exists( $class_name, 'check' ) ) {
			$result = call_user_func( array( $class_name, 'check' ) );
			if ( null !== $result && is_array( $result ) ) {
				$findings[] = $result;
				echo "✓ " . $diagnostic_class . " - Found issue\n";
			} else {
				echo "✓ " . $diagnostic_class . "\n";
			}
			$completed++;
		}
	} catch ( Exception $e ) {
		echo "✗ " . $diagnostic_class . " - Error: " . $e->getMessage() . "\n";
	}
}

echo "\n=== Results ===\n";
echo "Completed: $completed\n";
echo "Issues found: " . count( $findings ) . "\n\n";

if ( count( $findings ) > 0 ) {
	// Store findings
	if ( function_exists( 'wpshadow_index_findings_by_id' ) && function_exists( 'wpshadow_store_gauge_snapshot' ) ) {
		$indexed_findings = wpshadow_index_findings_by_id( $findings );
		wpshadow_store_gauge_snapshot( array_values( $indexed_findings ) );
		
		update_option( 'wpshadow_last_quick_scan', time() );
		
		echo "✓ Findings stored successfully\n";
		echo "Sample findings (first 5):\n";
		$sample = array_slice( $findings, 0, 5 );
		foreach ( $sample as $finding ) {
			echo "  - " . $finding['title'] . " (" . $finding['category'] . ")\n";
		}
	} else {
		echo "✗ Failed to store findings - missing helper functions\n";
	}
} else {
	echo "No issues found during scan\n";
}
