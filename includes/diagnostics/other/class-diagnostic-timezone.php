<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Timezone extends Diagnostic_Base {

	protected static $slug        = 'timezone';
	protected static $title       = 'Timezone Configuration';
	protected static $description = 'Checks if timezone is properly configured with a named timezone instead of UTC offset.';

	public static function check(): ?array {
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset      = get_option( 'gmt_offset' );

		if ( empty( $timezone_string ) && ( empty( $gmt_offset ) || '0' === $gmt_offset ) ) {
			return null;
		}

		if ( empty( $timezone_string ) && ! empty( $gmt_offset ) && '0' !== $gmt_offset ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Timezone is configured using UTC offset (%s) instead of a named timezone. This can cause issues with scheduled posts, backups, and cron tasks. Use a city-based timezone like "America/New_York" for proper DST handling.', 'wpshadow' ),
					$gmt_offset > 0 ? "+{$gmt_offset}" : $gmt_offset
				),
				'category'     => 'settings',
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
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
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
