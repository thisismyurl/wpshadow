<?php
/**
 * Diagnostic: Expired Transients Bloat
 *
 * Detects expired transients clogging the database.
 *
 * Philosophy: Show Value (#9) - Measure wasted database space
 * KB Link: https://wpshadow.com/kb/expired-transients-bloat
 * Training: https://wpshadow.com/training/expired-transients-bloat
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expired Transients Bloat diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Expired_Transients_Bloat extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wpdb;

		// Count expired transients
		$expired_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		if ( $expired_count < 50 ) {
			return null; // Not significant
		}

		// Get size estimate
		$transient_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(LENGTH(option_value)) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);
		$transient_size_kb = round( $transient_size / 1024, 2 );

		// Sample some expired transients
		$sample_transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d 
				LIMIT 5",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		$severity = $expired_count > 500 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database contains %s expired transients consuming %s KB. WordPress doesn\'t automatically clean up expired transients, causing database bloat and slower queries.', 'wpshadow' ),
			number_format( $expired_count ),
			number_format( $transient_size_kb )
		);

		return [
			'id'                => 'expired-transients-bloat',
			'title'             => __( 'Expired Transients Buildup', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/expired-transients-bloat',
			'training_link'     => 'https://wpshadow.com/training/expired-transients-bloat',
			'affected_resource' => sprintf( '%s transients, %s KB', number_format( $expired_count ), number_format( $transient_size_kb ) ),
			'metadata'          => [
				'expired_count'    => $expired_count,
				'size_kb'          => $transient_size_kb,
				'sample_transients' => $sample_transients,
			],
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Expired Transients Bloat
	 * Slug: -expired-transients-bloat
	 * File: class-diagnostic-expired-transients-bloat.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Expired Transients Bloat
	 * Slug: -expired-transients-bloat
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__expired_transients_bloat(): array {
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
			'message' => 'Test not yet implemented',
		);
	}

}
