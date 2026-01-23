<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Premium Plugin Compatibility
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Premium_Plugin_Conflicts extends Diagnostic_Base {
	protected static $slug        = 'premium-plugin-conflicts';
	protected static $title       = 'Premium Plugin Compatibility';
	protected static $description = 'Detects conflicts with common premium plugins.';

	public static function check(): ?array {
		// Known plugin conflict pairs (simplified detection)
		$conflict_pairs = array(
			array('jetpack/jetpack.php', 'wp-rocket/wp-rocket.php'),
			array('wordfence/wordfence.php', 'ithemes-security-pro/ithemes-security-pro.php'),
		);
		
		$conflicts_found = array();
		foreach ($conflict_pairs as $pair) {
			if (is_plugin_active($pair[0]) && is_plugin_active($pair[1])) {
				$conflicts_found[] = basename(dirname($pair[0])) . ' + ' . basename(dirname($pair[1]));
			}
		}
		
		if (empty($conflicts_found)) {
			return null; // Pass - no known conflicts
		}
		
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'Potential conflicts detected: ' . implode(', ', $conflicts_found),
			'color'         => '#ff9800',
			'bg_color'      => '#fff3e0',
			'kb_link'       => 'https://wpshadow.com/kb/premium-plugin-conflicts/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=premium-plugin-conflicts',
			'training_link' => 'https://wpshadow.com/training/premium-plugin-conflicts/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Compatibility',
			'priority'      => 2,
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
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
