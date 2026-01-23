<?php
declare(strict_types=1);
/**
 * Database Table Optimization Diagnostic
 *
 * Philosophy: Optimized tables improve query speed
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Database_Table_Optimization extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'seo-database-table-optimization',
			'title'         => 'Database Table Optimization',
			'description'   => 'Regularly optimize database tables to reduce overhead and improve query performance.',
			'severity'      => 'low',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/database-optimization/',
			'training_link' => 'https://wpshadow.com/training/database-maintenance/',
			'auto_fixable'  => false,
			'threat_level'  => 15,
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
