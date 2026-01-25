<?php
/**
 * Dry Run and Rollback Manual Test Script
 *
 * This script demonstrates and validates the dry run and rollback functionality.
 * Run this file from the command line to test the new features.
 *
 * Usage: php includes/core/test-dry-run-rollback.php
 *
 * @package WPShadow
 * @subpackage Core\Tests
 */

namespace WPShadow\Core\Tests;

// This is a demonstration script - not meant to be loaded by WordPress
if ( ! defined( 'ABSPATH' ) && php_sapi_name() === 'cli' ) {
	echo "Dry Run and Rollback Test Script\n";
	echo "=================================\n\n";
	echo "This script demonstrates the new dry run and rollback functionality.\n\n";

	echo "Features Implemented:\n";
	echo "1. Dry Run Mode\n";
	echo "   - Call: wpshadow_attempt_autofix(\$finding_id, true)\n";
	echo "   - Checks if treatment can be applied without making changes\n";
	echo "   - Returns: ['success' => bool, 'dry_run' => true, 'would_apply' => bool]\n\n";

	echo "2. Normal Treatment Application\n";
	echo "   - Call: wpshadow_attempt_autofix(\$finding_id, false) or wpshadow_attempt_autofix(\$finding_id)\n";
	echo "   - Applies the treatment and records it in rollback log\n";
	echo "   - Returns: ['success' => bool, 'message' => string]\n\n";

	echo "3. Rollback/Undo\n";
	echo "   - Call: wpshadow_rollback_fix(\$finding_id)\n";
	echo "   - Reverts a previously applied treatment\n";
	echo "   - Returns: ['success' => bool, 'message' => string]\n\n";

	echo "4. Check Rollback Capability\n";
	echo "   - Call: wpshadow_can_rollback(\$finding_id)\n";
	echo "   - Returns: bool - whether treatment supports undo\n\n";

	echo "5. Get Rollback History\n";
	echo "   - Call: wpshadow_get_rollback_history()\n";
	echo "   - Returns: array of recent treatment applications\n\n";

	echo "AJAX Endpoints:\n";
	echo "- wp_ajax_wpshadow_dry_run_treatment: Performs dry run\n";
	echo "- wp_ajax_wpshadow_rollback_treatment: Rolls back treatment\n\n";

	echo "Example Usage in PHP:\n";
	echo "---------------------\n";
	echo "// Test if SSL fix can be applied\n";
	echo "\$result = wpshadow_attempt_autofix('ssl-missing', true);\n";
	echo "if (\$result['would_apply']) {\n";
	echo "    // Actually apply the fix\n";
	echo "    \$result = wpshadow_attempt_autofix('ssl-missing', false);\n";
	echo "    if (\$result['success']) {\n";
	echo "        echo 'Fix applied successfully';\n";
	echo "        // Later, if needed:\n";
	echo "        \$rollback = wpshadow_rollback_fix('ssl-missing');\n";
	echo "    }\n";
	echo "}\n\n";

	echo "Example Usage via AJAX:\n";
	echo "----------------------\n";
	echo "// JavaScript dry run request\n";
	echo "jQuery.post(ajaxurl, {\n";
	echo "    action: 'wpshadow_dry_run_treatment',\n";
	echo "    nonce: wpshadow_nonces.dry_run,\n";
	echo "    finding_id: 'debug-mode-enabled'\n";
	echo "}, function(response) {\n";
	echo "    if (response.success && response.data.would_apply) {\n";
	echo "        // Show confirmation dialog\n";
	echo "    }\n";
	echo "});\n\n";

	echo "// JavaScript rollback request\n";
	echo "jQuery.post(ajaxurl, {\n";
	echo "    action: 'wpshadow_rollback_treatment',\n";
	echo "    nonce: wpshadow_nonces.rollback,\n";
	echo "    finding_id: 'debug-mode-enabled'\n";
	echo "}, function(response) {\n";
	echo "    if (response.success) {\n";
	echo "        alert('Treatment rolled back successfully');\n";
	echo "    }\n";
	echo "});\n\n";

	echo "Implementation Details:\n";
	echo "----------------------\n";
	echo "1. Treatment_Base::execute() now accepts \$dry_run parameter\n";
	echo "2. Treatment_Registry::apply_treatment() supports dry run mode\n";
	echo "3. Treatment_Registry::undo_treatment() handles rollback\n";
	echo "4. Rollback log stored in wp_options as 'wpshadow_rollback_log'\n";
	echo "5. All treatments inherit dry run capability from base class\n";
	echo "6. All treatments already have undo() methods for rollback\n\n";

	echo "Testing Checklist:\n";
	echo "-----------------\n";
	echo "[ ] Call dry run on a finding - verify no changes made\n";
	echo "[ ] Apply a treatment normally - verify it works\n";
	echo "[ ] Check rollback history - verify entry logged\n";
	echo "[ ] Roll back the treatment - verify undo works\n";
	echo "[ ] Test via AJAX endpoints with proper nonces\n";
	echo "[ ] Verify all treatments still work without dry run parameter\n\n";

	exit( 0 );
}

// If loaded by WordPress, provide test functions
if ( defined( 'ABSPATH' ) ) {

	/**
	 * Test dry run functionality
	 */
	function test_dry_run() {
		echo "Testing Dry Run Functionality\n";
		echo "============================\n\n";

		// Test with SSL finding
		$finding_id = 'ssl-missing';
		echo "Testing dry run for finding: {$finding_id}\n";

		$result = wpshadow_attempt_autofix( $finding_id, true );

		echo "Result:\n";
		print_r( $result );
		echo "\n";

		if ( ! empty( $result['dry_run'] ) && $result['dry_run'] === true ) {
			echo "✓ Dry run mode confirmed\n";
		}

		if ( isset( $result['would_apply'] ) ) {
			echo '✓ Would apply status: ' . ( $result['would_apply'] ? 'Yes' : 'No' ) . "\n";
		}

		echo "\n";
	}

	/**
	 * Test rollback functionality
	 */
	function test_rollback() {
		echo "Testing Rollback Functionality\n";
		echo "=============================\n\n";

		// Get rollback history
		$history = wpshadow_get_rollback_history();

		echo 'Current rollback log entries: ' . count( $history ) . "\n";

		if ( ! empty( $history ) ) {
			echo "Recent entries:\n";
			foreach ( array_slice( $history, -3 ) as $entry ) {
				echo "  - Finding: {$entry['finding_id']}, Time: " . date( 'Y-m-d H:i:s', $entry['timestamp'] ) . "\n";
			}
		}

		echo "\n";
	}

	/**
	 * Run all tests
	 */
	function run_all_tests() {
		test_dry_run();
		test_rollback();
	}
}
