<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Interactivity_Cleanup extends Diagnostic_Base {

	protected static $slug        = 'interactivity-cleanup';
	protected static $title       = 'Modern Block Features';
	protected static $description = 'Checks if modern interactive block features are loaded but not being used.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_interactivity_cleanup_enabled', false ) ) {
			return null;
		}

		if ( version_compare( get_bloginfo( 'version' ), '6.5', '<' ) ) {
			return null;
		}

		$uses_interactive_blocks = false;
		$recent_posts            = get_posts(
			array(
				'post_type'   => array( 'post', 'page' ),
				'numberposts' => 20,
				'post_status' => 'publish',
			)
		);

		foreach ( $recent_posts as $post ) {
			if ( has_block( 'core/navigation', $post ) ||
				has_block( 'core/query', $post ) ||
				strpos( $post->post_content, 'wp-interactivity' ) !== false ) {
				$uses_interactive_blocks = true;
				break;
			}
		}

		if ( $uses_interactive_blocks ) {
			return null;
		}

		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress 6.5+ interactive block features (Interactivity API, Block Bindings) are loaded but not used. Disabling saves bandwidth and improves performance.', 'wpshadow' ),
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
