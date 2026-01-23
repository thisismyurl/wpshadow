<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Maintenance extends Diagnostic_Base {

	protected static $slug = 'maintenance';
	protected static $title = 'Stuck Maintenance Mode';
	protected static $description = 'Checks for stuck .maintenance file that prevents site access after failed updates.';

	public static function check(): ?array {
		$maint_file = ABSPATH . '.maintenance';

		if ( ! file_exists( $maint_file ) ) {
			return null;
		}

		$mtime     = filemtime( $maint_file );
		$age_hours = ( time() - $mtime ) / 3600;

		if ( $age_hours < 0.5 ) {
			return null;
		}

		$severity = 'medium';
		$threat   = 50;

		if ( $age_hours > 2 ) {
			$severity = 'critical';
			$threat   = 95;
		} elseif ( $age_hours > 1 ) {
			$severity = 'high';
			$threat   = 75;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Site has been in maintenance mode for %.1f hours. This usually means an update process failed. The site is currently inaccessible to visitors.', 'wpshadow' ),
				$age_hours
			),
			'category'     => 'stability',
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Stuck Maintenance Mode
	 * Slug: maintenance
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks for stuck .maintenance file that prevents site access after failed updates.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_maintenance(): array {
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
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
