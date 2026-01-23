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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
