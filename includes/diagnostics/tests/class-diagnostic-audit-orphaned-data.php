<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Audit_Orphaned_Data extends Diagnostic_Base {
	protected static $slug = 'audit-orphaned-data';

	protected static $title = 'Audit Orphaned Data';

	protected static $description = 'Automatically initialized lean diagnostic for Audit Orphaned Data. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'audit-orphaned-data';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are deleted references cleaned with audit trail?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are deleted references cleaned with audit trail?. Part of Audit & Activity Trail analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'audit_trail';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Are deleted references cleaned with audit trail? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 46;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/audit-orphaned-data/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/audit-orphaned-data/';
	}

	public static function check(): ?array {
		// Check if orphaned data is being tracked/cleaned
		// Orphaned data includes post metadata without associated posts, etc.

		global $wpdb;

		// Check for orphaned postmeta
		$orphaned_postmeta = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		// Check for audit logging plugins that track data cleanup
		$audit_plugins = array(
			'wsal/wp-security-audit-log.php',
			'stream/stream.php',
		);

		$has_cleanup_tracking = false;
		foreach ( $audit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cleanup_tracking = true;
				break;
			}
		}

		// If there's significant orphaned data and no tracking, flag it
		if ( $orphaned_postmeta > 100 && ! $has_cleanup_tracking ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'audit-orphaned-data',
				'Audit Orphaned Data',
				'Found ' . number_format( $orphaned_postmeta ) . ' orphaned database records. Install an audit plugin and run database cleanup.',
				'security',
				'medium',
				55,
				'audit-orphaned-data'
			);
		}

		// If no tracking mechanism at all, flag it
		if ( ! $has_cleanup_tracking && $orphaned_postmeta > 10 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'audit-orphaned-data',
				'Audit Orphaned Data',
				'Orphaned database records are not being tracked. Install an audit logging plugin to monitor data integrity.',
				'security',
				'low',
				35,
				'audit-orphaned-data'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Audit Orphaned Data
	 * Slug: audit-orphaned-data
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Audit Orphaned Data. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_audit_orphaned_data(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		// TODO: Implement actual test logic
		return array(
			'passed'  => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}
}
