<?php
/**
 * Debug script to check findings storage
 */

// Define ABSPATH if not already defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/var/www/html/' );
}

// Load WordPress
require_once ABSPATH . 'wp-load.php';

echo "=== WPShadow Findings Debug ===\n\n";

// Check wpshadow_site_findings option
$findings = get_option( 'wpshadow_site_findings', array() );
echo 'Findings count in wpshadow_site_findings: ' . count( $findings ) . "\n\n";

if ( count( $findings ) > 0 ) {
	echo "Sample findings (first 5):\n";
	$sample = array_slice( $findings, 0, 5, true );
	foreach ( $sample as $id => $finding ) {
		echo '  - ID: ' . $id . "\n";
		echo '    Title: ' . ( $finding['title'] ?? 'N/A' ) . "\n";
		echo '    Category: ' . ( $finding['category'] ?? 'N/A' ) . "\n";
		echo '    Severity: ' . ( $finding['severity'] ?? 'N/A' ) . "\n\n";
	}
}

// Check last scan time
$last_scan = get_option( 'wpshadow_last_quick_scan', 0 );
echo 'Last quick scan: ' . ( $last_scan ? date( 'Y-m-d H:i:s', $last_scan ) : 'Never' ) . "\n";

// Check dashboard snapshot
$snapshot = get_option( 'wpshadow_dashboard_snapshot', array() );
echo 'Dashboard snapshot total_findings: ' . ( $snapshot['total_findings'] ?? 0 ) . "\n";
echo 'Dashboard snapshot critical_count: ' . ( $snapshot['critical_count'] ?? 0 ) . "\n";
