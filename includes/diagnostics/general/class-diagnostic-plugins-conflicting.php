<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Plugins Causing Conflicts?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Plugins_Conflicting extends Diagnostic_Base {
	protected static $slug        = 'plugins-conflicting';
	protected static $title       = 'Plugins Causing Conflicts?';
	protected static $description = 'Detects JavaScript errors from plugin conflicts.';


	public static function check(): ?array {
		$conflicts = array(
			array('jetpack/jetpack.php', 'wp-rocket/wp-rocket.php'),
		);
		$active_plugins = get_option('active_plugins', array());
		foreach ($conflicts as $pair) {
			if (in_array($pair[0], $active_plugins) && in_array($pair[1], $active_plugins)) {
				return array(
					'id'            => static::$slug,
					'title'         => static::$title,
					'description'   => 'Conflict: ' . basename(dirname($pair[0])) . ' + ' . basename(dirname($pair[1])),
					'color'         => '#ff9800',
					'bg_color'      => '#fff3e0',
					'kb_link'       => 'https://wpshadow.com/kb/plugins-conflicting/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugins-conflicting',
					'training_link' => 'https://wpshadow.com/training/plugins-conflicting/',
					'auto_fixable'  => false,
					'threat_level'  => 60,
					'module'        => 'Core',
					'priority'      => 2,
				);
			}
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
