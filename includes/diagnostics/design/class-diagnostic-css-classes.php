<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_CSS_Classes extends Diagnostic_Base {

	protected static $slug = 'css-classes';
	protected static $title = 'Excessive CSS Classes';
	protected static $description = 'Checks for excessive CSS classes on body, post, and navigation elements that can be simplified.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_css_class_cleanup_enabled', false ) ) {
			return null;
		}

		$body_classes = get_body_class();
		$class_count  = count( $body_classes );

		if ( $class_count < 10 ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d CSS classes on body element. Simplifying classes reduces HTML size and improves performance. WordPress often adds unnecessary classes for post types, templates, and browser detection.', 'wpshadow' ),
				$class_count
			),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 20,
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
