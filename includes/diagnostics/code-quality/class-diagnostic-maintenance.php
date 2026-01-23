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
