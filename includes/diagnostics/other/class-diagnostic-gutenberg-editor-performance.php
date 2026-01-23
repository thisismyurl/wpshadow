<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Gutenberg Block Editor Performance (WORDPRESS-008)
 *
 * Monitors post editor loading and typing responsiveness.
 * Philosophy: Show value (#9) - Improve content creation experience.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Gutenberg_Editor_Performance extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$editor_tti     = (int) get_transient( 'wpshadow_editor_tti_ms' );
		$typing_latency = (int) get_transient( 'wpshadow_editor_typing_latency_ms' );

		if ( $editor_tti > 2000 || $typing_latency > 120 ) {
			return array(
				'id'                => 'gutenberg-editor-performance',
				'title'             => __( 'Gutenberg editor feels slow', 'wpshadow' ),
				'description'       => __( 'Editor load or typing latency is high. Reduce heavy plugins/blocks in editor, disable unneeded metabox scripts, or profile typing handlers.', 'wpshadow' ),
				'severity'          => 'medium',
				'category'          => 'other',
				'kb_link'           => 'https://wpshadow.com/kb/gutenberg-performance/',
				'training_link'     => 'https://wpshadow.com/training/editor-performance/',
				'auto_fixable'      => false,
				'threat_level'      => 50,
				'editor_tti_ms'     => $editor_tti,
				'typing_latency_ms' => $typing_latency,
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
	}}
